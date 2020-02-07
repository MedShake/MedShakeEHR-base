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
 * people : action > enregistrer un individu
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';

$formIN=$_POST['formIN'];

if (!isset($_POST['actAsAjax'])) {
    $actAsAjax=false;
} else {
    $actAsAjax=$_POST['actAsAjax'];
    $match['params']['porp']=$_POST['porp'];
}

// vérification des droits
if ($match['params']['porp']=='pro' and $p['config']['droitDossierPeutCreerPraticien']!='true') {
  die("Action interdite");
}

//definition formulaire de travail
$form = new msFormValidation();
$form->setFormIDbyName($formIN);
$form->setPostdatas($_POST);
$form->setContextualValidationErrorsMsg(false);
$form->setContextualValidationRule('birthname',['checkNoName']);


if ($match['params']['porp']=='pro' and !$actAsAjax) {

  //si jeux de valeurs normées présents
  if(is_file($p['homepath'].'ressources/JDV/JDV_J01-XdsAuthorSpecialty-CI-SIS.xml')) {
    $codes = msExternalData::getJdvDataFromXml('JDV_J01-XdsAuthorSpecialty-CI-SIS.xml');
    $optionsInject['PSCodeProSpe']=['Z'=>''] + array_column($codes, 'displayName', 'code');
  }

  if(is_file($p['homepath'].'ressources/JDV/JDV_J02-HealthcareFacilityTypeCode_CI-SIS.xml')) {
    $codes = msExternalData::getJdvDataFromXml('JDV_J02-HealthcareFacilityTypeCode_CI-SIS.xml');
    $optionsInject['PSCodeStructureExercice']=['Z'=>''] + array_column($codes, 'displayName', 'code');
  }
  if(!empty($optionsInject)) $form->setOptionsForSelect($optionsInject);
}
$validation=$form->getValidation();


if ($validation === false) {
    if ($actAsAjax) {
        echo json_encode(array(
        'status'=>'error',
        'msg'=>$_SESSION['form'][$formIN]['validationErrorsMsg'],
        'code'=>$_SESSION['form'][$formIN]['validationErrors']
      ));
    } else {
        if (isset($_POST['patientID'])) {
          msTools::redirection('/'.$match['params']['porp'].'/edit/'.$_POST['patientID'].'/');
        } else {
          msTools::redirection('/'.$match['params']['porp'].'/create/');
        }
    }
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

    foreach ($_POST as $k=>$v) {
        if (($pos = strpos($k, "_")) !== false) {
            $in = substr($k, $pos+1);
            if (isset($in)) {
                if (!empty(trim($v)) and !empty(trim($in))) {
                    $patient->createNewObjetByTypeName($in, $v);
                }
            }
        }
    }

    unset($_SESSION['form'][$formIN]);

    if ($actAsAjax) {
        echo json_encode(array('status'=>'ok', 'toID'=>$patient->getToID()));
    } else {
        if ($match['params']['porp']=='registre') {
            msTools::redirection('/registre/'.$patient->getToID().'/');
        } elseif ($match['params']['porp']=='groupe') {
            msTools::redirection('/groupe/'.$patient->getToID().'/');
        } elseif ($match['params']['porp']=='pro') {
            msTools::redirection('/pro/'.$patient->getToID().'/');
        } elseif($p['config']['optionGePatientOuvrirApresCreation'] == 'liens') {
            msTools::redirection('/patient/relations/'.$patient->getToID().'/');
        } else {
            msTools::redirection('/patient/'.$patient->getToID().'/');
        }
    }
}
