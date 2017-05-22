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
 * Compta > ajax : extraire le formulaire de réglement
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$debug='';

//template
$template="comptaSimpleReglementForm";

//identité patient
$patient = new msPeople;
$patient->setToID($_POST['patientID']);
$p['page']['patient']=$patient->getSimpleAdminDatas();

//patient id
$p['page']['patient']['id']=$_POST['patientID'];

$form = new msForm();
$form->setFormID('18');
$p['page']['form']=$form->getForm();
$form->addSubmitToForm($p['page']['form'], 'btn-warning btn-lg btn-block');

//ajout champs cachés au form
$p['page']['form']['addHidden']=array(
  'patientID'=>$_POST['patientID'],
  'objetID'=>$_POST['objetID']
);
