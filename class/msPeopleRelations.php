<?php
/*
* This file is part of MedShakeEHR.
*
* Copyright (c) 2019
* Bertrand Boutillier <b.boutillier@gmail.com>
* http://www.medshake.net
*
* MedShakeEHR is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* any later version.
*
* MedShakeEHR is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with MedShakeEHR.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* Gestion des individus : relations
*
* @author Bertrand Boutillier <b.boutillier@gmail.com>
*/

class msPeopleRelations extends msPeople
{

/**
 * Obtenir les pros en relation avec ce patient
 * @param  array  $dataComp         data types sup à extraire
 * @param  array  $dataCompNotEmpty data types qui ne doivent pas être null (filtre)
 * @return array                   tableau des pros
 */
    public function getRelationsWithPros($dataComp=[], $dataCompNotEmpty=[])
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }

        $data = new msData();
        $name2typeID = $data->getTypeIDsFromName(array_merge(['relationID', 'relationPatientPraticien', 'relationPatientPatient', 'titre', 'firstname', 'lastname', 'birthname'], $dataComp));

        $champsSql=[];
        $tablesSql=[];
        $groupBy=[];
        $notEmpty=[];
        if(!empty($dataComp)) {
          $i=1;
          foreach($dataComp as $k=>$v) {
            if(key_exists($v,$name2typeID)) {
              $champsSql[] = ', co'.$i.'.value as '.$v;
              $tablesSql[] = " left join objets_data as co".$i." on co".$i.".toID=o.value and co".$i.".typeID='".$name2typeID[$v]."' and co".$i.".outdated='' and co".$i.".deleted='' ";
              $groupBy[]= ', co'.$i.'.id';

              if(in_array($v,$dataCompNotEmpty)) {
                $notEmpty[]=' and co'.$i.'.value is not null';
              }
            }
            $i++;
          }
        }

        $relations = [];
        if($relations =  msSQL::sql2tab("select o.value as pratID, c.value as typeRelation, p.value as prenom, t.value as titre, CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom ".implode(" ", $champsSql)."
        from objets_data as o
        inner join objets_data as c on c.instance=o.id and c.typeID='".$name2typeID['relationPatientPraticien']."' and c.value != 'patient'
        left join objets_data as n on n.toID=o.value and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
        left join objets_data as bn on bn.toID=o.value and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
        left join objets_data as p on p.toID=o.value and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
        left join objets_data as t on t.toID=o.value and t.typeID='".$name2typeID['titre']."' and t.outdated='' and t.deleted='' ".implode(" ", $tablesSql)."
        where o.toID='".$this->_toID."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated='' ".implode("", $notEmpty)."
        group by o.value, c.id, bn.id, n.id, p.id, t.id".implode("", $groupBy)."
        order by typeRelation = 'MT' desc, nom asc")) {

          $typeID = $data->getTypeIDFromName('relationPatientPraticien');
          $options = $data->getSelectOptionValue(array($typeID))[$typeID];
          foreach($relations as $k=>$v) {
            $relations[$k]['typeRelationTxt']=$options[$relations[$k]['typeRelation']];
          }
        }
        return (array)$relations;
    }

/**
 * Créer une relation patient praticien
 * @param string $praticienStatus statut du praticien vis à vis du patient
 * @param int $praticienID     ID du praticien
 */
    public function setRelationWithPro($praticienStatus, $praticienID)
    {

      if (!is_numeric($this->_toID)) {
          throw new Exception('ToID is not numeric');
      }

      if (!is_numeric($this->_fromID)) {
          throw new Exception('FromID is not numeric');
      }

      if (!is_numeric($praticienID)) {
          throw new Exception('PraticienID is not numeric');
      }

      //sortir les choix de relations patient<->prat pour valider $praticienStatus
      $data = new msData();
      $typeID = $data->getTypeIDFromName('relationPatientPraticien');
      $options = $data->getSelectOptionValue(array($typeID))[$typeID];
      if (!array_key_exists($praticienStatus, $options)) {
          throw new Exception('PraticienStatus is not a valid choice');
      }

      // patient -> praticien
      $patient = new msObjet();
      $patient->setToID($this->_toID);
      $patient->setFromID($this->_fromID);
      $supportID=$patient->createNewObjetByTypeName('relationID', $praticienID);
      $patient->createNewObjetByTypeName('relationPatientPraticien', $praticienStatus, $supportID);

      // praticien -> patient
      $praticien = new msObjet();
      $praticien->setToID($praticienID);
      $praticien->setFromID($this->_fromID);
      $supportID=$praticien->createNewObjetByTypeName('relationID', $this->_toID);
      $praticien->createNewObjetByTypeName('relationPatientPraticien', 'patient', $supportID);
    }

/**
 * Obtenir les autres patients liés généalogiquement avec ce patient
 * @return array array des autres patients
 *
 */
    public function getRelationsWithOtherPatients()
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }

        $name2typeID = new msData();
        $name2typeID = $name2typeID->getTypeIDsFromName(['relationID', 'relationPatientPraticien', 'relationPatientPatient', 'titre', 'firstname', 'lastname', 'birthdate', 'birthname']);

          if($data = msSQL::sql2tab("select o.value as patientID, c.value as typeRelation, p.value as prenom, d.value as ddn, CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom,
          TIMESTAMPDIFF(YEAR, STR_TO_DATE(d.value, '%d/%m/%Y'), CURDATE()) AS age
          from objets_data as o
          inner join objets_data as c on c.instance=o.id and c.typeID='".$name2typeID['relationPatientPatient']."'
          left join objets_data as n on n.toID=o.value and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
          left join objets_data as bn on bn.toID=o.value and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
          left join objets_data as p on p.toID=o.value and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
          left join objets_data as d on d.toID=o.value and d.typeID='".$name2typeID['birthdate']."' and d.outdated='' and d.deleted=''
          where o.toID='".$this->_toID."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated=''
          group by o.value, c.id, bn.id, n.id, p.id, d.id
          order by STR_TO_DATE(d.value, '%d/%m/%Y') desc, nom asc")) {
          return $data;
        } else {
          return [];
        }
    }

/**
 * Créer une relation patient patient
 * @param string $toIdStatus statut familial de l'individu identifé par _toID
 * @param int $withID     ID du 2e individu concerné
 */
    public function setRelationWithOtherPatient($toIdStatus, $withID)
    {

      if (!is_numeric($this->_toID)) {
          throw new Exception('ToID is not numeric');
      }

      if (!is_numeric($this->_fromID)) {
          throw new Exception('FromID is not numeric');
      }

      if (!is_numeric($withID)) {
          throw new Exception('WithID is not numeric');
      }

      //sortir les choix de relations patient<->patient pour faire un reverse tab
      $data = new msData();
      $typeID = $data->getTypeIDFromName('relationPatientPatient');
      $options = $data->getSelectOptionValue(array($typeID))[$typeID];
      $reversOptions = array_flip($options);

      if (!in_array($toIdStatus, $options)) {
          throw new Exception('ToIdStatus is not a valid choice');
      }

      // patientPrin -> patient
      $patient = new msObjet();
      $patient->setToID($this->_toID);
      $patient->setFromID($this->_fromID);
      $supportID=$patient->createNewObjetByTypeName('relationID', $withID);
      $patient->createNewObjetByTypeName('relationPatientPatient', $toIdStatus, $supportID);

      // patient -> patientPrin
      $patient2 = new msObjet();
      $patient2->setToID($withID);
      $patient2->setFromID($this->_fromID);
      $supportID=$patient2->createNewObjetByTypeName('relationID', $this->_toID);
      $patient2->createNewObjetByTypeName('relationPatientPatient', $reversOptions[$toIdStatus], $supportID);
    }

/**
 * Retirer une relation entre 2 individus
 * @param int $withID 2e individu concerné
 */
    public function setRelationDeleted($withID)
    {
      if (!is_numeric($this->_toID)) {
          throw new Exception('ToID is not numeric');
      }

      if (!is_numeric($this->_fromID)) {
          throw new Exception('FromID is not numeric');
      }

      if (!is_numeric($withID)) {
          throw new Exception('WithID is not numeric');
      }

      $typeID = msData::getTypeIDFromName('relationID');

      // patient -> praticien/patient
      if ($id=msSQL::sqlUniqueChamp("select id from objets_data where typeID='".$typeID."' and toID='".$this->_toID."' and value='".$withID."' and deleted='' limit 1")) {
        $obj = new msObjet;
        $obj->setFromID($this->_fromID);
        $obj->setObjetID($id);
        $obj->setDeletedObjetAndSons();
      }

      // praticien/patient -> patient
      if ($id=msSQL::sqlUniqueChamp("select id from objets_data where typeID='".$typeID."' and toID='".$_POST['ID2']."' and value='".$_POST['ID1']."' and deleted='' limit 1")) {
        $obj = new msObjet;
        $obj->setFromID($this->_fromID);
        $obj->setObjetID($id);
        $obj->setDeletedObjetAndSons();
      }
    }

}
