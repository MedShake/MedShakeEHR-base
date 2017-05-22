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
 * soit en mode patient -> formulaire n°1
 * soit en mode pro -> formualire n°7
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template="peopleEdit";

$p['page']['porp']=$match['params']['porp'];

if ($p['page']['porp']=='patient') {
    $p['page']['formNumber']='1';
} elseif ($p['page']['porp']=='pro') {
    $p['page']['formNumber']='7';
}

$patient = new msPeople();
$patient->setToID($match['params']['patient']);
$p['page']['patient']=$patient->getSimpleAdminDatas();
$p['page']['patient']['id']=$match['params']['patient'];

$formpatient = new msForm();
$formpatient->setFormID($p['page']['formNumber']);
$formpatient->setPrevalues($p['page']['patient']);
$p['page']['form']=$formpatient->getForm();

//ajout au form
$p['page']['form']['addHidden']=array(
  'patientID'=>$match['params']['patient']
);
