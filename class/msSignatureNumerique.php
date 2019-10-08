<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 *
 * Signature numérique sur périphérique tactile
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msSignatureNumerique
{


  private $_patientID;
  private $_fromID;

/**
 * Définir le patient concerné
 * @param int $patientID ID de l'individu
 * @return int toID
 */
    public function setPatientID($patientID)
    {
        if (msPeople::checkPeopleExist($patientID)) {
            return $this->_patientID = $patientID;
        } else {
            throw new Exception('PatientID does not exist');
        }
    }

/**
 * Définir l'auteur
 * @param int $fromID ID de l'auteur
 * @return int fromID
 */
    public function setFromID($fromID)
    {
        if (msPeople::checkPeopleExist($fromID)) {
            return $this->_fromID = $fromID;
        } else {
            throw new Exception('FromID does not exist');
        }
    }

/**
 * Obtenir la liste des documents signables
 * @return array liste des docs
 */
    public function getPossibleDocToSign() {
      $docASigner = new msData;
      if($tab=$docASigner->getDataTypesFromCatName('catModelesDocASigner', ['id','name','label', 'validationRules as onlyfor', 'validationErrorMsg as notfor'])) {
        $docASigner->applyRulesOnlyforNotforOnArray($tab, $this->_fromID);
      }
      return $tab;
    }

}
