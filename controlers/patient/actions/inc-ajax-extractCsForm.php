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
 * Patient > ajax : obtenir le formulaire de consultation demandé
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';

//template
$template="patientCsForm";
$formIN = $p['page']['formIN'] = $_POST['formIN'];

if(!isset($_POST['csID'])) {
  $_POST['csID']=msSQL::sqlUniqueChamp("select id from data_types where groupe='typecs' and formValues='".msSQL::cleanVar($_POST['formIN'])."' limit 1");
}

// formulaire
$formInfos = new msData();
$p['page']['formInfos'] = $formInfos->getDataType($_POST['csID'], array('label'));

// module du formulaire
$formModule = new msForm;
$p['page']['formInfos']['module']=$formModule->getFormUniqueRawField($formIN, 'module');

// class du module étendant potentiellement msForm
$class='msMod'.ucfirst($p['page']['formInfos']['module']).'Forms';
$method_pre='doPreGetForm_'.$formIN;
$method_post='doPostGetForm_'.$formIN;

// instancier la bonne class pour travailler sur le form
if(class_exists($class)) {
  $form = new $class;
} else {
  $form = new msForm();
}

$form->setFormIDbyName($formIN);

//chargement des values si demandé
if (isset($_POST['prevalues'])) {
    if ($_POST['prevalues']=='yes') {
        $form->setInstance($_POST['objetID']);
        $form->setTypesSupForPrevaluesExtraction(['codeTechniqueExamen']);
        $form->getPrevaluesForPatient($_POST['patientID']);
    }
}

// méthode sépcifique au module et form : pre
if(method_exists($class,$method_pre)) {
  $form->$method_pre();
}

$p['page']['form']=$form->getForm();

if($_POST['mode'] == 'update' or $_POST['mode'] == 'create' ) $form->addSubmitToForm($p['page']['form'], 'btn-warning btn-lg btn-block');

// retrait des options d'un champ select qui doit prévenir le duplication
if($_POST['mode'] == 'create' ) {
  $typePreventDupl = $form->getFormOptions();
  if(isset($typePreventDupl['typeToPreventDuplicate'])) {
    $objDup = new msObjet;
    $objDup->setToID($_POST['patientID']);
    $optionsDejaUtilisees = $objDup->getDataTypePatientActiveValues($typePreventDupl['typeToPreventDuplicate']);
    $form->removeOptionInSelectForm($p['page']['form'], $typePreventDupl['typeToPreventDuplicate'], $optionsDejaUtilisees);
  }
}

//ajout champs cachés au form
$p['page']['form']['addHidden']=array(
  'patientID'=>$_POST['patientID'],
  'parentID'=>$_POST['parentID'],
  'csID'=>$_POST['csID'],
  'mode'=>$_POST['mode']
);
if (isset($_POST['objetID'])) {
    $p['page']['form']['addHidden']['objetID']=$_POST['objetID'];
}

$p['page']['formJavascript']=$form->getFormJavascript();


// méthode spécifique au module et form : post
if(method_exists($class,$method_post)) {
  $form->$method_post();
}
