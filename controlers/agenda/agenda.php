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
 * Agenda
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';
$template="agenda";

// userID
if(isset($match['params']['userID'])) $p['page']['userID']=$match['params']['userID'];

// date d'ouverture
$p['page']['dateToGo']=date('Y-m-d');
if(isset($match['params']['dateToGo'])) {
  if(msTools::validateDate($match['params']['dateToGo'], $format = 'Ymd')) {
    $p['page']['dateToGo'] = DateTime::createFromFormat('Ymd', $match['params']['dateToGo'])->format('Y-m-d');
  }
}

//paramètres de l'agenda
if(is_file($p['homepath'].'config/agendas/agenda'.$match['params']['userID'].'.js')) {
  $p['page']['configAgenda']=file_get_contents($p['homepath'].'config/agendas/agenda'.$match['params']['userID'].'.js');
  if(is_file($p['homepath'].'config/agendas/agenda'.$match['params']['userID'].'_ad.js')) {
    $p['page']['configAgenda'].=file_get_contents($p['homepath'].'config/agendas/agenda'.$match['params']['userID'].'_ad.js');
  }
} else {
  $p['page']['configAgenda']=file_get_contents($p['homepath'].'config/agendas/agendaDefault.js');
}

// types de rendez-vous
$typesRdv = new msAgenda;
$typesRdv->set_userID($match['params']['userID']);
$p['page']['typeRdv']=$typesRdv->getRdvTypes();

//formulaire prise rdv
$formPriseRdv = new msForm();
$formPriseRdv->setFormIDbyName($p['page']['formIN']='baseAgendaPriseRDV');
$formPriseRdv->setTypeForNameInForm('byName');
$p['page']['formPriseRdv']=$formPriseRdv->getForm();

//formulaire nouveau patient
$formpatient = new msForm();
$formpatient->setFormIDbyName('baseModalNewPatient');
if (isset($_SESSION['form']['baseModalNewPatient']['formValues'])) {
    $formpatient->setPrevalues($_SESSION['form']['baseModalNewPatient']['formValues']);
}
$p['page']['formNewPatient']=$formpatient->getForm();
//modifier action pour url ajax
$p['page']['formNewPatient']['global']['formAction']='/people/actions/peopleRegister/';

//sortir les choix de relations patient<->prat
$data = new msData();
$typeID = $data->getTypeIDFromName('relationPatientPraticien');
$options = $data->getSelectOptionValue(array($typeID));
$p['page']['preRelationPatientPrat']['formValues']=$options[$typeID];
