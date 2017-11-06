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
if(is_file($p['config']['webDirectory'].'agendasConfigurations/configTypesRdv'.$match['params']['userID'].'.yml')) {
  $p['page']['typeRdv']=Spyc::YAMLLoad($p['config']['webDirectory'].'agendasConfigurations/configTypesRdv'.$match['params']['userID'].'.yml');
} else {
  $p['page']['typeRdv']=array(
    '[C]'=> array(
      'descriptif'=>'Consultation',
      'backgroundColor'=>'#2196f3',
      'borderColor'=>'#1e88e5',
      'duree'=>15
    )
  );
}

//formulaire prise rdv
$formPriseRdv = new msForm();
$p['page']['formNumber']=$formPriseRdv->setFormIDbyName('baseAgendaPriseRDV');
$formPriseRdv->setTypeForNameInForm('byName');
$p['page']['formPriseRdv']=$formPriseRdv->getForm();
