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
 * soit en mode patient -> formulaire baseNewPatient
 * soit en mode pro -> formualire baseNewPro
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';
$template="peopleEdit";

$p['page']['porp']=$match['params']['porp'];

if ($p['page']['porp']=='patient') {
    $p['page']['formIN']='baseNewPatient';
} elseif ($p['page']['porp']=='pro') {
    $p['page']['formIN']='baseNewPro';
}

$patient = new msPeople();
$patient->setToID($match['params']['patient']);
$p['page']['patient']=$patient->getSimpleAdminDatas();
$p['page']['patient']['id']=$match['params']['patient'];

$formpatient = new msForm();
$formpatient->setFormIDbyName($p['page']['formIN']);
$formpatient->setPrevalues($p['page']['patient']);
$p['page']['form']=$formpatient->getForm();

//ajout au form
$p['page']['form']['addHidden']=array(
  'patientID'=>$match['params']['patient']
);
