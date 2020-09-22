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
  private $_returnedPeopleTypes=[];


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
      $relationPossibleTypes = ['relationPraticienGroupe', 'relationPatientPraticien', 'relationPatientPatient', 'relationGroupeRegistre', 'relationRegistrePraticien', 'relationRegistrePatient', 'relationPatientGroupe'];
      if(!in_array($v, $relationPossibleTypes)) {
        throw new Exception('RelationType is not valid');
      } else {
        return $this->_relationType = $v;
      }
    }

/**
 * Définir les types de people à retourner
 * @param array $returnedPeopleType array des types de people à retourner
 */
    public function setReturnedPeopleTypes($returnedPeopleType) {
      if(!is_array($returnedPeopleType)) {
        throw new Exception('ReturnedPeopleType is not valid');
      }
      $this->_returnedPeopleTypes = $returnedPeopleType;
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

/**
 * Vérifier s'il existe une restriction liée au nombre maximal de groupes attribué à un pro
 * @return bool true (si restriction atteinte)/false
 */
    public function checkMaxGroupeRestriction() {
      global $p;
      if($this->_relationType != 'relationPraticienGroupe') {
        return false;
      }
      if($p['config']['groupesNbMaxGroupesParPro'] < 1) {
        return false;
      }
      if($this->getType() == 'pro') {
        $pratID = $this->_toID;
      } elseif($this->getType() == 'groupe') {
        $pratID = $this->_withID;
      } else {
        return false;
      }

      $typeID = msData::getTypeIDFromName('relationPraticienGroupe');
      $nb = msSQL::sqlUniqueChamp("select count(id) from objets_data where toID = '".$pratID."' and typeID='".$typeID."' and outdated='' and deleted='' ");
      if($nb >= $p['config']['groupesNbMaxGroupesParPro']) {
        return true;
      } else {
        return false;
      }

    }

    private function _checkActionValidity() {
      global $p;

      $toIdType = $this->getType();

      if($this->_relationType == 'relationPatientPraticien' and (($toIdType!='patient' and $toIdType!='pro') or $this->_withIdType != 'pro')) {
        return false;
      }

      elseif($this->_relationType == 'relationPatientPatient' and ($toIdType!='patient' or $this->_withIdType != 'patient')) {
        return false;
      }

      elseif($this->_relationType == 'relationPraticienGroupe') {
        if ($toIdType == 'pro' and $this->_withIdType != 'groupe') {
          return false;
        } elseif ($toIdType == 'groupe' and $this->_withIdType != 'pro') {
          return false;
        }
        else {
          $checkAdminGroup = new msPeopleRelationsDroits;
          if($toIdType == 'groupe') {
            $checkAdminGroup->setToID($this->_toID);
          } elseif($this->_withIdType == 'groupe') {
            $checkAdminGroup->setToID($this->_withID);
          }
          $checkAdminGroup = $checkAdminGroup->getCurrentUserStatusInGroup();
          if($checkAdminGroup != 'admin' and $p['user']['rank'] != 'admin') {
            return false;
          }
        }
      }

      elseif($this->_relationType == 'relationPraticienRegistre' and (($toIdType!='pro' or $this->_withIdType != 'registre') or $p['config']['droitRegistrePeutGererAdministrateurs'] != 'true')) {
        return false;
      }

      elseif($this->_relationType == 'relationRegistrePatient' and ($toIdType!='registre' or $this->_withIdType != 'patient') ) {
        return false;
      }

      elseif($this->_relationType == 'relationPatientGroupe' and ($toIdType!='patient' or $this->_withIdType != 'groupe') ) {
        return false;
      }

      elseif($this->_relationType == 'relationGroupeRegistre') {
        if ($p['config']['droitRegistrePeutGererGroupes'] != 'true') {
          return false;
        }
      }

      return true;
    }

/**
 * Définir une relation entre 2 peopleID
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
      if(!$this->_checkActionValidity()) {
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
 */
    public function setRelationDeleted()
    {
      if (!is_numeric($this->_toID)) {
          throw new Exception('ToID is not numeric');
      }

      if (!is_numeric($this->_fromID)) {
          throw new Exception('FromID is not numeric');
      }

      if (!is_numeric($this->_withID)) {
          throw new Exception('WithID is not numeric');
      }

      $this->_relationType = $this->getPeopleRelationType();

      // valider l'opération
      if(!$this->_checkActionValidity()) {
        return false;
      }

      $typeID = msData::getTypeIDFromName('relationID');

      // sup relation
      if ($id=msSQL::sqlUniqueChamp("select id from objets_data where typeID='".$typeID."' and toID='".$this->_toID."' and value='".$this->_withID."' and deleted='' limit 1")) {
        $obj = new msObjet;
        $obj->setFromID($this->_fromID);
        $obj->setObjetID($id);
        $obj->setDeletedObjetAndSons();
      }

      // sup relation réciproque
      if ($id=msSQL::sqlUniqueChamp("select id from objets_data where typeID='".$typeID."' and toID='".$this->_withID."' and value='".$this->_toID."' and deleted='' limit 1")) {
        $obj = new msObjet;
        $obj->setFromID($this->_fromID);
        $obj->setObjetID($id);
        $obj->setDeletedObjetAndSons();
      }

      return true;
    }

/**
 * Obtenir pour le peopleID concerné les relations du type demandé
 * @param  array  $dataComp         typeName à retourner
 * @param  array  $dataCompNotEmpty typeName qui ne doivent pas être vide
 * @param  array  $relationValues   value caractérisant la relation (membre, admin, inclus ...)
 * @return array                   tableau des infos sur peopleID
 */
    public function getRelations($dataComp=[], $dataCompNotEmpty=[], $relationValues=[])
    {
        global $p;

        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }

        if (!isset($this->_relationType)) {
            throw new Exception('RelationType is not defined');
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
        $name2typeID = $data->getTypeIDsFromName(array_merge(['relationID', $this->_relationType], $dataComp));

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
        if($this->_relationType == 'relationPraticienGroupe') {
        } elseif($this->_relationType == 'relationPatientPraticien') {
          $orderBy = "order by typeRelation ='MTD' desc, trim(concat(lastname,birthname,firstname))";
        } elseif($this->_relationType == 'relationPatientPatient') {
          $orderBy = "order by STR_TO_DATE(co".$name2typeID['birthdate'].".value, '%d/%m/%Y') desc, trim(concat(lastname,birthname,firstname))";
        }

        // complément pour type de people strict à retourner
        if(!empty($this->_returnedPeopleTypes)) {
          $strictTable = " inner join people as p on p.id = o.value and p.type in ('".implode("', '", $this->_returnedPeopleTypes)."' )";
        } else {
          $strictTable = '';
        }

        // gestion de la value caractérisant la relation
        if(!empty($relationValues)) {
          $relationValues = msSQL::cleanArray($relationValues);
          $whereRelationValues = " and c.value in ('".implode("', '", $relationValues)."') ";
        } else {
          $whereRelationValues = " ";
        }

        $relations = [];

        if($relations =  msSQL::sql2tab("select o.value as peopleID, o.fromID as createBy, u2.value as currentUserStatus, c.value as typeRelation, '".$p['user']['rank']."' as currentUserRank
        ".implode(" ", $champsSql)."
        from objets_data as o
        ".$strictTable."
        inner join objets_data as c on c.instance=o.id and c.typeID='".$name2typeID[$this->_relationType]."'

        left join objets_data as u on u.value=o.value and u.toID = '".$p['user']['id']."' and u.typeID='".$name2typeID['relationID']."' and u.deleted='' and u.outdated=''
        left join objets_data as u2 on u2.instance=u.id and u2.typeID='".$name2typeID[$this->_relationType]."' and u2.deleted='' and u2.outdated=''

        ".implode(" ", $tablesSql)."
        where o.toID='".$this->_toID."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated='' ".implode("", $notEmpty).$whereRelationValues."
        group by o.value, c.id, u.id, u2.id ".implode("", $groupBy).' '.$orderBy )) {

          $typeID = $data->getTypeIDFromName($this->_relationType);
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

/**
 * Obtenir la liste des peopleID de la fratrie
 * Exemple : pour un pro, donne la liste des pros associés dans l'ensemble des groupes
 * auxquels le pro est intégré
 * @return array peopleIDs
 */
    public function getSiblingIDs() {

      if (!is_numeric($this->_toID)) {
          throw new Exception('ToID is not numeric');
      }

      if (!isset($this->_relationType)) {
          throw new Exception('RelationType is not defined');
      }

      $in = new msPeopleRelations;
      $in->setToID($this->_toID);
      $in->setRelationType($this->_relationType);
      $inIds=array_column($in->getRelations(),'peopleID');

      $r=[];
      foreach($inIds as $id) {
        $in->setToID($id);
        $tab = array_column($in->getRelations(),'peopleID');
        $r = array_merge($r, $tab);
      }

      return array_unique($r);

    }

/**
 * Obtenir le type de relation (data type name) entre 2 people
 * @return string name du data type
 */
    public function getPeopleRelationType() {

      if (!isset($this->_toID)) {
          throw new Exception('ToID is not defined');
      }

      if (!isset($this->_withID)) {
          throw new Exception('WithID is not defined');
      }

      $data = new msData();
      $name2typeID = $data->getTypeIDsFromName(['relationID']);

      $typeRelation = msSql::sqlUniqueChamp("select d.name as typeRelation
      from objets_data as o
      inner join objets_data as c on c.instance=o.id
      left join data_types as d on d.id=c.typeID
      where o.toID='".$this->_toID."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated='' and o.value='".$this->_withID."'
      limit 1");
      if(!$typeRelation) {
        return null;
      } else {
        return $typeRelation;
      }

    }

/**
 * Obtenir la (value) valeur d'une relation
 * @return string value de la relation
 */
    public function getPeopleRelationValue() {

      if (!isset($this->_toID)) {
          throw new Exception('ToID is not defined');
      }

      if (!isset($this->_withID)) {
          throw new Exception('WithID is not defined');
      }

      $data = new msData();
      $name2typeID = $data->getTypeIDsFromName(['relationID']);

      $typeRelation = msSql::sqlUniqueChamp("select c.value
      from objets_data as o
      inner join objets_data as c on c.instance=o.id
      where o.toID='".$this->_toID."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated='' and o.value='".$this->_withID."'
      limit 1");
      if(!$typeRelation) {
        return null;
      } else {
        return $typeRelation;
      }

    }

/**
 * Obtenir les datas sur une relation
 * @return array data relation
 */
    public function getPeopleRelationData() {

      if (!isset($this->_toID)) {
          throw new Exception('ToID is not defined');
      }

      if (!isset($this->_withID)) {
          throw new Exception('WithID is not defined');
      }

      $data = new msData();
      $name2typeID = $data->getTypeIDsFromName(['relationID','lastname', 'birthname','firstname']);

      $typeRelation = msSql::sqlUnique("select c.*, CASE WHEN ln.value != '' THEN concat(fn.value , ' ' , ln.value) ELSE concat(fn.value , ' ' , bn.value) END as fromIdentite
      from objets_data as o
      inner join objets_data as c on c.instance=o.id

      left join objets_data as ln on ln.toID=c.fromID and ln.typeID='".$name2typeID['lastname']."' and ln.outdated='' and ln.deleted=''
      left join objets_data as bn on bn.toID=c.fromID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      left join objets_data as fn on fn.toID=c.fromID and fn.typeID='".$name2typeID['firstname']."' and fn.outdated='' and fn.deleted=''

      where o.toID='".$this->_toID."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated='' and o.value='".$this->_withID."'
      limit 1");
      if(!$typeRelation) {
        return null;
      } else {
        return $typeRelation;
      }

    }

}
