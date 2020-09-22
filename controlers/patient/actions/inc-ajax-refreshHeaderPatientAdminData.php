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
 * Patient > ajax : générer le header du dossier patient (infos administratives)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$template="inc-ajax-patientAdminData";

//le patient
$patient = new msPeopleRelations();
$patient->setToID($_POST['patientID']);

//vérifier les droits
$droits = new msPeopleDroits($p['user']['id']);
if(!$droits->checkUserCanSeePatientData($_POST['patientID'])) {
  $template="forbidden";
  return;
}

$p['page']['patient']['id']=$_POST['patientID'];
$p['page']['patient']['administrativeDatas']=$patient->getAdministrativesDatas();
$p['page']['patient']['administrativeDatas']['birthdate']['ageFormats']=$patient->getAgeFormats();
$p['page']['patient']['administrativeDatas']['birthdate']['age']=$patient->getAge();
if(isset($p['page']['patient']['administrativeDatas']['deathdate'])) {
  if(msTools::validateDate($p['page']['patient']['administrativeDatas']['deathdate']['value'], "d/m/Y")) {
    $p['page']['patient']['administrativeDatas']['deathAge']=$patient->getDeathAge();
  }
}

//les correspondants et liens familiaux
$patient->setRelationType('relationPatientPraticien');
$patient->setReturnedPeopleTypes(['pro']);
$p['page']['correspondants']=$patient->getRelations(['identite','titre','emailApicrypt', 'faxPro', 'profesionnalEmail', 'telPro', 'telPro2', 'mobilePhonePro']);
