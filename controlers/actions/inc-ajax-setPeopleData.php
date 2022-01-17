<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
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
 * Requêtes AJAX > enregistrement des données patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

//vérifier les droits utilisateur
$droits = new msPeopleDroits($p['user']['id']);
if(!$droits->checkUserCanSeePatientData($_POST['patientID'])) {
 http_response_code(401);
 die();
}

$patient = new msObjet();
$patient->setFromID($p['user']['id']);
$patient->setToID($_POST['patientID']);
if ($patient->createNewObjet($_POST['typeID'], $_POST['value'], $_POST['instance']) > 0) {
   $return['status']='ok';
   echo json_encode($return);
} else {
   http_response_code(401);
}
