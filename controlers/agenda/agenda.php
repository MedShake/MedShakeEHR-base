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

//paramètres de l'agenda
if(is_file($p['config']['webDirectory'].'agendasConfigurations/configAgenda'.$match['params']['userID'].'.js')) {
  $p['page']['configAgenda']=file_get_contents($p['config']['webDirectory'].'agendasConfigurations/configAgenda'.$match['params']['userID'].'.js');
}

// types de rendez-vous
$p['page']['typeRdv']=msAgenda::getRdvTypes($match['params']['userID']);

//formulaire prise rdv
$formPriseRdv = new msForm();
$formPriseRdv->setFormIDbyName($p['page']['formIN']='baseAgendaPriseRDV');
$formPriseRdv->setTypeForNameInForm('byName');
$p['page']['formPriseRdv']=$formPriseRdv->getForm();

//formulaire nouveau patient
$formpatient = new msForm();
$formpatient->setFormIDbyName('baseNewPatient');
if (isset($_SESSION['form']['baseNewPatient']['formValues'])) {
    $formpatient->setPrevalues($_SESSION['form']['baseNewPatient']['formValues']);
}
$p['page']['formNewPatient']=$formpatient->getForm();
//ajout champs cachés au form
$p['page']['formNewPatient']['addHidden']=array(
  'actAsAjax'=>'true',
  'porp'=>'patient'
);
//modifier action pour url ajax
$p['page']['formNewPatient']['global']['formAction']='/people/actions/peopleRegister/';

