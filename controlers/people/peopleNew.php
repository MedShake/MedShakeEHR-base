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
 * people :  créer un individus
 * soit en mode patient -> formulaire $p['config']['formFormulaireNouveauPatient']
 * soit en mode pro -> formulaire $p['config']['formFormulaireNouveauPraticien']
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';
$template="peopleNew";

$p['page']['porp']=$match['params']['porp'];

if ($p['page']['porp']=='patient') {
  $p['page']['formIN']=$p['config']['formFormulaireNouveauPatient'];
} elseif ($p['page']['porp']=='pro' and $p['config']['droitDossierPeutCreerPraticien'] == 'true') {
  $p['page']['formIN']=$p['config']['formFormulaireNouveauPraticien'];
} elseif ($p['page']['porp']=='groupe' and $p['config']['droitGroupePeutCreerGroupe'] == 'true') {
  $p['page']['formIN']=$p['config']['formFormulaireNouveauGroupe'];
} elseif ($p['page']['porp']=='registre' and $p['config']['droitRegistrePeutCreerRegistre'] == 'true') {
  $p['page']['formIN']=$p['config']['formFormulaireNouveauRegistre'];
} else {
  $template="forbidden";
  return;
}

if($template != "forbidden") {
  $formpatient = new msForm();
  $formpatient->setFormIDbyName($p['page']['formIN']);
  if (isset($_SESSION['form'][$p['page']['formIN']]['formValues'])) {
      $formpatient->setPrevalues($_SESSION['form'][$p['page']['formIN']]['formValues']);
  } elseif (isset($_POST)) {
      $formpatient->setPrevalues($_POST);
  }

  //si formulaire pro
  if ($p['page']['porp']=='pro') {

    //si jeux de valeurs normées présents
    if(is_file($p['homepath'].'ressources/JDV/JDV_J01-XdsAuthorSpecialty-CI-SIS.xml')) {
      $codes = msExternalData::getJdvDataFromXml('JDV_J01-XdsAuthorSpecialty-CI-SIS.xml');
      $optionsInject['PSCodeProSpe']=['Z'=>''] + array_column($codes, 'displayName', 'code');
    }

    if(is_file($p['homepath'].'ressources/JDV/JDV_J02-HealthcareFacilityTypeCode_CI-SIS.xml')) {
      $codes = msExternalData::getJdvDataFromXml('JDV_J02-HealthcareFacilityTypeCode_CI-SIS.xml');
      $optionsInject['PSCodeStructureExercice']=['Z'=>''] + array_column($codes, 'displayName', 'code');
    }
    if(!empty($optionsInject)) $formpatient->setOptionsForSelect($optionsInject);
  }

  $p['page']['form']=$formpatient->getForm();
  $formpatient->addHiddenInput($p['page']['form'],['peopleType'=>$p['page']['porp']]);
  $p['page']['formJavascript'][$p['page']['formIN']]=$formpatient->getFormJavascript();
  $formpatient->addSubmitToForm($p['page']['form'], 'btn-primary btn-block');
}
