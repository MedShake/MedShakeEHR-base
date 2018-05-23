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
 * People : ajax > ajouter une relation patient <-> patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

 //sortir les choix de relations patient<->patient pour faire un reverse tab
 $data = new msData();
 $typeID = $data->getTypeIDFromName('relationPatientPatient');
 $options = $data->getSelectOptionValue(array($typeID));
 foreach($options[$typeID] as $k=>$v) {
   $reversTab[$k]=$v;
 }


// patientPrin -> patient
$patient = new msObjet();
$patient->setToID($_POST['patientID']);
$patient->setFromID($p['user']['id']);
$supportID=$patient->createNewObjetByTypeName('relationID', $_POST['patient2ID']);
$patient->createNewObjetByTypeName('relationPatientPatient', $_POST['preRelationPatientPatient'], $supportID);

// patient -> patientPrin
$patient2 = new msObjet();
$patient2->setToID($_POST['patient2ID']);
$patient2->setFromID($p['user']['id']);
$supportID=$patient2->createNewObjetByTypeName('relationID', $_POST['patientID']);
$patient2->createNewObjetByTypeName('relationPatientPatient', $reversTab[$_POST['preRelationPatientPatient']], $supportID);

echo json_encode(array('ok'));
