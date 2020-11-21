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
if ($match['params']['porp']=='pro' and $p['config']['droitDossierPeutCreerPraticien']!='true' and $_POST['patientID']!=$p['user']['id']) {
  die("Action interdite");
} elseif ($match['params']['porp']=='registre' and $p['config']['droitRegistrePeutCreerRegistre'] != 'true') {
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
    $objet = new msObjet();
    $objet->setFromID($p['user']['id']);

    if (!isset($_POST['patientID'])) {
        $newpatient = new msPeople();
        $newpatient->setFromID($p['user']['id']);
        $newpatient->setType($match['params']['porp']);
        $objet->setToID($newpatient->createNew());

        //création de l'exportID
        if($p['config']['optionGeCreationAutoPeopleExportID'] == 'true') {
          $newpatient->setPeopleExportID();
        }

    } else {
        $objet->setToID($_POST['patientID']);
        $patient = new msPeople();
        $patient->setToID($_POST['patientID']);
    }

    foreach ($_POST as $k=>$v) {
        if (($pos = strpos($k, "_")) !== false) {
            $in = substr($k, $pos+1);
            if (isset($in)) {
                if (!empty(trim($v)) and !empty(trim($in))) {
                    $objet->createNewObjetByTypeName($in, $v);
                } elseif (isset($_POST['patientID']) and empty(trim($v)) and !empty(trim($in))) {
                    if(isset($patient->getSimpleAdminDatasByName([$in])[$in])) {
                      $objet->createNewObjetByTypeName($in, $v);
                    }
                }
            }
        }
    }

    // ajout des groupes du prat créateur au patient nouvellement créé
    if(!isset($_POST['patientID']) and $p['config']['optionGeActiverGroupes'] == 'true' and $p['config']['groupesAutoAttachProGroupsToPatient'] == 'true' and $match['params']['porp']=='patient') {
      $relationPratGroups = new msPeopleRelations;
      $relationPratGroups->setToID($p['user']['id']);
      $relationPratGroups->setRelationType('relationPraticienGroupe');

      foreach($relationPratGroups->getRelations() as $rela) {
        $relation = new msPeopleRelations;
        $relation->setToID($objet->getToID());
        $relation->setToStatus('membre');
        $relation->setFromID($p['user']['id']);
        $relation->setWithID($rela['peopleID']);
        $relation->setRelationType('relationPatientGroupe');
        if(!$relation->checkRelationExist()) {
          $relation->setRelation();
        }
      }
    }

    unset($_SESSION['form'][$formIN]);

    if ($actAsAjax) {
        echo json_encode(array('status'=>'ok', 'toID'=>$objet->getToID()));
    } else {
        if ($match['params']['porp']=='registre') {
            msTools::redirection('/registre/'.$objet->getToID().'/');
        } elseif ($match['params']['porp']=='groupe') {
            msTools::redirection('/groupe/'.$objet->getToID().'/');
        } elseif ($match['params']['porp']=='pro') {
            msTools::redirection('/pro/'.$objet->getToID().'/');
        } elseif($p['config']['optionGePatientOuvrirApresCreation'] == 'liens') {
            msTools::redirection('/patient/relations/'.$objet->getToID().'/');
        } else {
            msTools::redirection('/patient/'.$objet->getToID().'/');
        }
    }
}
