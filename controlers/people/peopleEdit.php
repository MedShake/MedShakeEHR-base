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
 * people : editer les données d'un individus
 * soit en mode patient -> formulaire $p['config']['formFormulaireNouveauPatient']
 * soit en mode pro -> formulaire $p['config']['formFormulaireNouveauPraticien']
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';


$p['page']['porp']=$match['params']['porp'];

$patient = new msPeople();
$patient->setToID($match['params']['patient']);

if(!in_array($patient->getType(), ['patient', 'pro'])) {
  $template = "404";
  return;
}

if ($p['page']['porp']=='patient') {
    $template="patientEdit";
    $p['page']['formIN']=$p['config']['formFormulaireNouveauPatient'];

    //vérifier les droits
    if($p['config']['droitDossierPeutVoirTousPatients'] != 'true' and $patient->getFromID()!=$p['user']['id']) {
      $template="forbidden";
      return;
    }
} elseif ($p['page']['porp']=='pro') {
    $template="proEdit";
    $p['page']['formIN']=$p['config']['formFormulaireNouveauPraticien'];

    //vérifier les droits
    if($p['config']['droitDossierPeutCreerPraticien'] != 'true' and $match['params']['patient']!=$p['user']['id']) {
      $template="forbidden";
      return;
    }
}

$p['page']['patient']=$patient->getSimpleAdminDatasByName();
$p['page']['patient']['id']=$match['params']['patient'];

$formpatient = new msForm();
$formpatient->setFormIDbyName($p['page']['formIN']);
$formpatient->setPrevalues($p['page']['patient']);

//si formulaire pro
if ($p['page']['porp']=='pro') {

  //si jeux de valeurs normées présents
  if(is_file($p['homepath'].'ressources/JDV/JDV_J01-XdsAuthorSpecialty-CI-SIS.xml')) {
    $codes = msExternalData::getJdvDataFromXml('JDV_J01-XdsAuthorSpecialty-CI-SIS.xml');
    $optionsInject['PSCodeProSpe']=array_column($codes, 'displayName', 'code');
    $optionsInject['PSCodeProSpe']=[''=>'Autre valeur : cliquer le stylo pour éditer']+$optionsInject['PSCodeProSpe'];
  }

  if(is_file($p['homepath'].'ressources/JDV/JDV_J02-HealthcareFacilityTypeCode_CI-SIS.xml')) {
    $codes = msExternalData::getJdvDataFromXml('JDV_J02-HealthcareFacilityTypeCode_CI-SIS.xml');
    $optionsInject['PSCodeStructureExercice']=array_column($codes, 'displayName', 'code');
    $optionsInject['PSCodeStructureExercice']=[''=>'Autre valeur : cliquer le stylo pour éditer']+$optionsInject['PSCodeStructureExercice'];
  }
  if(!empty($optionsInject)) $formpatient->setOptionsForSelect($optionsInject);
}

$p['page']['form']=$formpatient->getForm();
$p['page']['formJavascript'][$p['page']['formIN']]=$formpatient->getFormJavascript();
//ajout au form
$p['page']['form']['addHidden']=array(
  'patientID'=>$match['params']['patient']
);

// Formulaire complémentaire
$p['page']['formIN2']='basePeopleComplement';
$formpatient2 = new msForm();
$formpatient2->setFormIDbyName($p['page']['formIN2']);
$formpatient2->setPrevalues($p['page']['patient']);
$p['page']['form2']=$formpatient2->getForm();
$p['page']['formJavascript'][$p['page']['formIN2']]=$formpatient2->getFormJavascript();
//ajout au form
$p['page']['form2']['addHidden']=array(
  'patientID'=>$match['params']['patient']
);
