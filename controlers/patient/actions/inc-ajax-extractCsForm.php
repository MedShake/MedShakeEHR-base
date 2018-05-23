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


//template
$template="patientCsForm";
$formIN = $p['page']['formIN'] = $_POST['formIN'];

if(!isset($_POST['csID'])) {
  $_POST['csID']=msSQL::sqlUniqueChamp("select id from data_types where groupe='typecs' and formValues='".$_POST['formIN']."' limit 1");
}

//infos sur le form
$formInfos = new msData();
$p['page']['formInfos'] = $formInfos->getDataType($_POST['csID'], array('label'));

//formulaire
$form = new msForm();
$form->setFormIDbyName($formIN);

//chargement des values si demandé
if (isset($_POST['prevalues'])) {
    if ($_POST['prevalues']=='yes') {
        $form->setInstance($_POST['objetID']);
        $form->getPrevaluesForPatient($_POST['patientID']);
    }
}
$p['page']['form']=$form->getForm();
if($_POST['mode'] == 'update' or $_POST['mode'] == 'create' ) $form->addSubmitToForm($p['page']['form'], 'btn-warning btn-lg btn-block');

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
