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
  private $_toStatus='';
  private $_withID;
  private $_withIdType;
  private $_relationType;


/**
 * Définir le second peopleID concerné
 * @param int $v ID du people concerné
 * @return int withID
 */
    public function setWithID($v)
    {
      if ($this->checkPeopleExist($v)) {
        $withID = new msPeople();
        $withID->setToID($v);
        $this->_withIdType = $withID->getType();
        return $this->_withID = $v;
      } else {
        throw new Exception('WithID does not exist');
      }
    }
/**
 * Définir le statut de toID
 * @param string $v statut de toId par rapport à withID
 */
    public function setToStatus($v)
    {
      if (!is_string($v)) {
        throw new Exception('ToStatus is not valid');
      } else {
        return $this->_toStatus = $v;
      }
    }

/**
 * Définir le type de relation
 * @param string $v type de relation
 */
    public function setRelationType($v)
    {
      $relationPossibleTypes = ['relationPraticienGroupe', 'relationPatientPraticien', 'relationPatientPatient', 'relationGroupeRegistre'];
      if(!in_array($v, $relationPossibleTypes)) {
        throw new Exception('RelationType is not valid');
      } else {
        return $this->_relationType = $v;
      }
    }

/**
 * DEPRECATED Créer une relation patient patient
 * @param string $toIdStatus statut familial de l'individu identifé par _toID
 * @param int $withID     ID du 2e individu concerné
 */
    public function setRelationWithOtherPatient($toIdStatus, $withID)
    {
      return $this->setRelation('relationPatientPatient', $toStatus, $withID);
    }

/**
 * DEPRECATED Créer une relation patient praticien
 * @param string $praticienStatus statut du praticien vis à vis du patient
 * @param int $praticienID     ID du praticien
 */
    public function setRelationWithPro($praticienStatus, $praticienID)
    {
      return $this->setRelation('relationPatientPraticien', $praticienStatus, $praticienID);
    }

/**
 * Vérifier si une relation existe déjà
 * @return boolean true/false
 */
    public function checkRelationExist() {
      if (!isset($this->_toID)) {
          throw new Exception('ToID is not defined');
      }
      if (!isset($this->_withID)) {
          throw new Exception('WithID is not defined');
      }
      $typeID = msData::getTypeIDFromName('relationID');
      if($id = msSQL::sqlUniqueChamp("select id from objets_data where toID = '".$this->_toID."' and typeID='".$typeID."' and value='".$this->_withID."' and outdated='' and deleted='' limit 1")) {
        return true;
      } else {
        return false;
      }

    }

    public function checkMaxGroupeRestriction() {
      global $p;
      if($this->_relationType != 'relationPraticienGroupe') {
        return false;
      }
      if($p['config']['groupesNbMaxGroupesParPro'] < 1) {
        return false;
      }

      $typeID = msData::getTypeIDFromName('relationPraticienGroupe');
      $nb = msSQL::sqlUniqueChamp("select count(id) from objets_data where toID = '".$this->_toID."' and typeID='".$typeID."' and outdated='' and deleted='' ");
      if($nb >= $p['config']['groupesNbMaxGroupesParPro']) {
        return true;
      } else {
        return false;
      }

    }


/**
 * Définir une relation entre 2 peopleID
 * @param string $toStatus     statut s'appliquant à toID
 * @param int $withID       peopleID du sujet en relation avec toID
 */
    public function setRelation() {

      if (!isset($this->_toID)) {
          throw new Exception('ToID is not defined');
      }

      if (!isset($this->_fromID)) {
          throw new Exception('FromID is not defined');
      }

      if (!isset($this->_withID)) {
          throw new Exception('WithID is not defined');
      }

      if (!isset($this->_toStatus)) {
          throw new Exception('ToStatus is not defined');
      }

      // valider l'opération
      $toIdType = $this->getType();

      if($this->_relationType == 'relationPatientPraticien' and ($toIdType!='patient' or $this->_withIdType != 'pro')) {
        throw new Exception('Action non valide');
      }
      if($this->_relationType == 'relationPatientPatient' and ($toIdType!='patient' or $this->_withIdType != 'patient')) {
        throw new Exception('Action non valide');
      }
      if($this->_relationType == 'relationPraticienGroupe' and ($toIdType!='pro' or $this->_withIdType != 'groupe')) {
        throw new Exception('Action non valide');
      }

      //sortir les choix de relations pour valider $this->_toStatus
      $data = new msData();
      $typeID = $data->getTypeIDFromName($this->_relationType);
      $options = $data->getSelectOptionValue(array($typeID))[$typeID];
      if (!array_key_exists($this->_toStatus, $options)) {
          throw new Exception('ToStatus is not a valid choice');
      }

      if($this->_relationType == 'relationPraticienGroupe') {
        $withStatus = $this->_toStatus;
      } elseif($this->_relationType == 'relationPatientPraticien') {
        $withStatus = 'patient';
      } elseif($this->_relationType == 'relationPatientPatient') {
        $reversOptions = array_flip($options);
        $withStatus = $reversOptions[$this->_toStatus];
      } elseif($this->_relationType == 'relationGroupeRegistre') {
        $withStatus = $this->_toStatus;
      } else {
        $withStatus = $this->_toStatus;
      }

      $first = new msObjet();
      $first->setToID($this->_toID);
      $first->setFromID($this->_fromID);
      $supportID=$first->createNewObjetByTypeName('relationID', $this->_withID);
      $first->createNewObjetByTypeName($this->_relationType, $this->_toStatus, $supportID);

      $second = new msObjet();
      $second->setToID($this->_withID);
      $second->setFromID($this->_fromID);
      $supportID2=$second->createNewObjetByTypeName('relationID', $this->_toID);
      $second->createNewObjetByTypeName($this->_relationType, $withStatus, $supportID2);

      if(is_int($supportID) and is_int($supportID2)) {
        return true;
      } else {
        return false;
      }

    }

/**
 * Retirer une relation entre 2 peopleID
 * @param int $withID 2e peopleID concerné
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
      if ($id=msSQL::sqlUniqueChamp("select id from objets_data where typeID='".$typeID."' and toID='".$withID."' and value='".$this->_toID."' and deleted='' limit 1")) {
        $obj = new msObjet;
        $obj->setFromID($this->_fromID);
        $obj->setObjetID($id);
        $obj->setDeletedObjetAndSons();
      }
    }

/**
 * Obtenir pour le peopleID concerné les relations du type demandé
 * @param  string $relationType     type de relation
 * @param  array  $dataComp         typeName à retourner
 * @param  array  $dataCompNotEmpty typeName qui ne doivent pas être vide
 * @return array                   tableau des infos sur peopleID
 */
    public function getRelations($relationType, $dataComp=[], $dataCompNotEmpty=[])
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }


        $generateIdentityTags = false;
        if(in_array('identite', $dataComp)) {
          if(!in_array('birthdate', $dataComp)) $dataComp[]='birthdate';
          if(!in_array('lastname', $dataComp)) $dataComp[]='lastname';
          if(!in_array('birthname', $dataComp)) $dataComp[]='birthname';
          if(!in_array('firstname', $dataComp)) $dataComp[]='firstname';
          if(!in_array('administrativeGenderCode', $dataComp)) $dataComp[]='administrativeGenderCode';

          if (($key = array_search('identite', $dataComp)) !== false) {
            unset($dataComp[$key]);
          }
          $generateIdentityTags = true;
        }

        if(in_array('ageCalcule', $dataComp)) {
          if(!in_array('birthdate', $dataComp)) $dataComp[]='birthdate';
        }

        $data = new msData();
        $name2typeID = $data->getTypeIDsFromName(array_merge(['relationID', $relationType], $dataComp));

        $champsSql=[];
        $tablesSql=[];
        $groupBy=[];
        $notEmpty=[];
        if(!empty($dataComp)) {
          foreach($dataComp as $k=>$v) {
            if(key_exists($v,$name2typeID)) {
              if($v == 'ageCalcule') {
                $champsSql[] = ',
                CASE WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(co'.$name2typeID['birthdate'].'.value, "%d/%m/%Y"), CURDATE()) >= 1
                THEN
                  CONCAT(TIMESTAMPDIFF(YEAR, STR_TO_DATE(co'.$name2typeID['birthdate'].'.value, "%d/%m/%Y"), CURDATE()), IF(TIMESTAMPDIFF(YEAR, STR_TO_DATE(co'.$name2typeID['birthdate'].'.value, "%d/%m/%Y"), CURDATE()) > 1, " ans", " an") )
                WHEN TIMESTAMPDIFF(DAY, STR_TO_DATE(co'.$name2typeID['birthdate'].'.value, "%d/%m/%Y"), CURDATE()) <= 31
                THEN
                  CONCAT(TIMESTAMPDIFF(DAY, STR_TO_DATE(co'.$name2typeID['birthdate'].'.value, "%d/%m/%Y"), CURDATE()), " jours")
                ELSE
                  CONCAT(TIMESTAMPDIFF(MONTH, STR_TO_DATE(co'.$name2typeID['birthdate'].'.value, "%d/%m/%Y"), CURDATE()), " mois")
                END as '.$v;
              } else {
                $champsSql[] = ', co'.$name2typeID[$v].'.value as '.$v;
              }
              $tablesSql[] = " left join objets_data as co".$name2typeID[$v]." on co".$name2typeID[$v].".toID=o.value and co".$name2typeID[$v].".typeID='".$name2typeID[$v]."' and co".$name2typeID[$v].".outdated='' and co".$name2typeID[$v].".deleted='' ";
              $groupBy[]= ', co'.$name2typeID[$v].'.id';

              if(in_array($v,$dataCompNotEmpty)) {
                $notEmpty[]=' and co'.$name2typeID[$v].'.value is not null';
              }
            }
          }
        }

        // tris par défaut, l'ordre peut être remanié a posteriori
        $orderBy = '';
        if($relationType == 'relationPraticienGroupe') {
        } elseif($relationType == 'relationPatientPraticien') {
          $orderBy = "order by typeRelation ='MTD' desc, trim(concat(lastname,birthname,firstname))";
        } elseif($relationType == 'relationPatientPatient') {
          $orderBy = "order by STR_TO_DATE(co".$name2typeID['birthdate'].".value, '%d/%m/%Y') desc, trim(concat(lastname,birthname,firstname))";
        }

        $relations = [];
        if($relations =  msSQL::sql2tab("select o.value as peopleID, c.value as typeRelation ".implode(" ", $champsSql)."
        from objets_data as o
        inner join objets_data as c on c.instance=o.id and c.typeID='".$name2typeID[$relationType]."'
        ".implode(" ", $tablesSql)."
        where o.toID='".$this->_toID."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated='' ".implode("", $notEmpty)."
        group by o.value, c.id ".implode("", $groupBy).' '.$orderBy )) {

          $typeID = $data->getTypeIDFromName($relationType);
          $options = $data->getSelectOptionValue(array($typeID))[$typeID];
          foreach($relations as $k=>$v) {
            if(isset($options[$relations[$k]['typeRelation']])) {
              $relations[$k]['typeRelationTxt']=$options[$relations[$k]['typeRelation']];
            }
            if($generateIdentityTags) {
              $relations[$k] = array_merge($relations[$k], msCourrier::getIdentiteTags($relations[$k]));
            }
          }
        }
        return (array)$relations;
    }


}
