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
* Gestion des individus : droits
*
* @author Bertrand Boutillier <b.boutillier@gmail.com>
*/

class msPeopleDroits extends msPeople
{

/**
 * Data d'une ligne de la table people
 * @var array
 */
  private $_basicUserData;

  function __construct($toID) {
    if (!isset($toID) or !is_numeric($toID)) {
        throw new Exception('ToID is not set');
    }
    $this->setToID($toID);
    if($basicUserData = msSQL::sqlUnique("SELECT type, `rank`, CASE WHEN length(pass) = 0 THEN null ELSE 1 END as pass FROM people WHERE id='".$toID."' limit 1")) {
      $this->_basicUserData = $basicUserData;
    } else {
      throw new Exception("This people don't exist");
    }
  }

/**
 * Vérifier si le people est admin
 * @return bool true/false
 */
  public function checkIsAdmin() {
    if(isset($this->_basicUserData['rank']) and $this->_basicUserData['rank'] == 'admin') {
      return true;
    } else {
      return false;
    }
  }

/**
 * Vérifier si le people est utilisateur
 * @return bool true/false
 */
  public function checkIsUser() {
    if(empty($this->_basicUserData['pass'])) {
      return false;
    } else {
      return true;
    }
  }

/**
 * Vérifier si le people est de type destroyed
 * @return bool true/false
 */
  public function checkIsDestroyed() {
    if($this->_basicUserData['type'] == 'destroyed') {
      return true;
    } else {
      return false;
    }
  }

/**
 * Vérifier si un utilisateur pro peut voir les pièces (dossier, stockage ..) d'un autre utilisateur pro
 * @param  int $userSeeID peopleID cible
 * @return bool            true : si peut voir / false
 */
  public function checkUserCanSeePatientsUser($userSeeID) {
    if(!$this->checkIsUser()) return;
    global $p;
    if($p['config']['droitDossierPeutVoirUniquementPatientsPropres'] == 'true' and $userSeeID != $this->_toID) {
      return false;
    } elseif($p['config']['droitDossierPeutVoirUniquementPatientsGroupes'] == 'true') {
      $frat = new msPeopleRelations;
      $frat->setToID($this->_toID);
      $frat->setRelationType('relationPraticienGroupe');
      $autoID = $frat->getSiblingIDs();
      $authorisedID[] = $this->_toID;
      if(!in_array($this->_toID, $authorisedID)) {
        return false;
      } else {
        return true;
      }
    } else {
      return true;
    }

  }

/**
 * Vérifier si l'utilisateur (passé via construct toId) peut voir les datas du patient
 * @param  int $patientID patientID
 * @return bool            true/false
 */
  public function checkUserCanSeePatientData($patientID) {
    if (!isset($patientID) or !is_numeric($patientID)) {
        throw new Exception('PatientID is not numeric');
    }

    if($this->checkIsAdmin()) return true;

    global $p;

    $patientg = new msPeopleRelations;
    $patientg->setToID($patientID);

    if($p['config']['droitDossierPeutVoirUniquementPatientsPropres'] == 'true' and $patientg->getFromID() == $this->_toID) {
      return true;
    } elseif($p['config']['droitDossierPeutVoirUniquementPatientsPropres'] == 'true' and $patientg->getFromID() != $this->_toID) {
      return false;
    } elseif($p['config']['droitDossierPeutVoirUniquementPatientsGroupes'] == 'true') {
      // groupes patient
      $patientg->setRelationType('relationPatientGroupe');
      if($patientg = $patientg->getRelations()) {
        $patientg  = array_column($patientg, 'peopleID');
      }

      // groupes user
      $pratg = new msPeopleRelations;
      $pratg->setToID($this->_toID);
      $pratg->setRelationType('relationPraticienGroupe');
      if($pratg = $pratg->getRelations()) {
        $pratg  = array_column($pratg, 'peopleID');
      }

      if(!empty(array_intersect($patientg, $pratg))) {
        return true;
      } else {
        return false;
      }
    }
    return true;

  }

}
