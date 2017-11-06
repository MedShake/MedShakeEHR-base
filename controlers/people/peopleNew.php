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
 * soit en mode patient -> formulaire baseNewPatient
 * soit en mode pro -> formualire baseNewPro
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template="peopleNew";

$p['page']['porp']=$match['params']['porp'];

if ($p['page']['porp']=='patient') {
    $p['page']['formInternalName']='baseNewPatient';
} elseif ($p['page']['porp']=='pro') {
    $p['page']['formInternalName']='baseNewPro';
}


$formpatient = new msForm();
$p['page']['formNumber']=$formpatient->setFormIDbyName($p['page']['formInternalName']);
if (isset($_SESSION['form'][$p['page']['formNumber']]['formValues'])) {
    $formpatient->setPrevalues($_SESSION['form'][$p['page']['formNumber']]['formValues']);
}
$p['page']['form']=$formpatient->getForm();
$formpatient->addSubmitToForm($p['page']['form'], 'btn-primary');
