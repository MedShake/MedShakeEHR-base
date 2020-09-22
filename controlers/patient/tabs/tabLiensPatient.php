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
 * Relations d'un patient avec les autres et avec les praticiens
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template="inc-patientPeopleRelations";

$patient = new msPeople();
$patient->setToID($match['params']['patientID']);
$p['page']['patient']['id'] = $match['params']['patientID'];

//sortir les choix de relations patient<->prat
$data = new msData();
$typeID = $data->getTypeIDFromName('relationPatientPraticien');
$options = $data->getSelectOptionValue(array($typeID));
$p['page']['preRelationPatientPrat']['formValues']=$options[$typeID];

//sortir les choix de relations patient<->patient
$data = new msData();
$typeID = $data->getTypeIDFromName('relationPatientPatient');
$options = $data->getSelectOptionValue(array($typeID));
foreach($options[$typeID] as $k=>$v) {
  $p['page']['preRelationPatientPatient']['formValues'][$k]=$k;
}

//formulaire de création praticien en modal
$formPro = new msForm();
$formPro->setFormIDbyName($p['config']['formFormulaireNouveauPraticien']);
if (isset($_SESSION['form'][$p['config']['formFormulaireNouveauPraticien']]['formValues'])) {
    $formPro->setPrevalues($_SESSION['form'][$p['config']['formFormulaireNouveauPraticien']]['formValues']);
}

//si jeux de valeurs normées présents
if(is_file($p['homepath'].'ressources/JDV/JDV_J01-XdsAuthorSpecialty-CI-SIS.xml')) {
  $codes = msExternalData::getJdvDataFromXml('JDV_J01-XdsAuthorSpecialty-CI-SIS.xml');
  $optionsInject['PSCodeProSpe']=['Z'=>''] + array_column($codes, 'displayName', 'code');
}
if(is_file($p['homepath'].'ressources/JDV/JDV_J02-HealthcareFacilityTypeCode_CI-SIS.xml')) {
  $codes = msExternalData::getJdvDataFromXml('JDV_J02-HealthcareFacilityTypeCode_CI-SIS.xml');
  $optionsInject['PSCodeStructureExercice']=['Z'=>''] + array_column($codes, 'displayName', 'code');
}
if(!empty($optionsInject)) $formPro->setOptionsForSelect($optionsInject);

$p['page']['form']=$formPro->getForm();
$p['page']['formJavascript'][$p['config']['formFormulaireNouveauPraticien']]=$formPro->getFormJavascript();
//ajout champs cachés au form
$p['page']['form']['addHidden']=array(
  'actAsAjax'=>'true',
  'porp'=>'pro'
);
//modifier action pour url ajax
$p['page']['form']['global']['formAction']='/people/actions/peopleRegister/';
