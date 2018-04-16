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
 * LAP : ajax > ajouter une allergie à un petient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

header('Content-Type: application/json');
 $patient = new msObjet();
 $patient->setFromID($p['user']['id']);
 $patient->setToID($_POST['patientID']);

 if ($supportID=$patient->createNewObjetByTypeName('allergieCodeTheriaque', $_POST['codeAller'], $_POST['parentID'])) {
     $patient->createNewObjetByTypeName('allergieLibelleTheriaque', $_POST['libelleAller'], $supportID);
     echo json_encode(array(
       "statut"=>'ok',
       "libelle"=>$_POST['libelleAller'],
       "code"=>$_POST['codeAller']
     ));
 } else {
     echo json_encode(array("statut"=>'error'));
 }
