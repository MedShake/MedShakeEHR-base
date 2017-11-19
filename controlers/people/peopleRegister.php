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
 * people : enregistrer un individu
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';

// form number
//if (is_numeric($_POST['formID'])) {
    $formID=$_POST['formID'];
//} else {
//    die();
//}

//definition formulaire de travail
$form = new msForm();
$form->setFormIDbyName($formID);
$form->setPostdatas($_POST);
$validation=$form->getValidation();


if ($validation === false) {
    msTools::redirection('/'.$match['params']['porp'].'/create/');
} else {
    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);

    if (!isset($_POST['patientID'])) {
        $newpatient = new msPeople();
        $newpatient->setFromID($p['user']['id']);
        $newpatient->setType($match['params']['porp']);
        $patient->setToID($newpatient->createNew());
    } else {
        $patient->setToID($_POST['patientID']);
    }
    $patient->setDataset('admin');


    foreach ($_POST as $k=>$v) {
        if (($pos = strpos($k, "_")) !== false) {
            $id = substr($k, $pos+1);
        }

        if (is_numeric($id)) {
            if (!empty(trim($v))) {
                $patient->createNewObjet($id, $v);
            }
        }
    }

    unset($_SESSION['form'][$formID]);



    msTools::redirection('/patient/relations/'.$patient->getToID().'/');
}
