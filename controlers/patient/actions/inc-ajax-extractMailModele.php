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
 * Patient > ajax : obtenir le modèle de mail
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$mm=new msData();
if($mm=$mm->getDataType($_POST['modeleID'], ['id','formValues'])) {

  $courrier=new msCourrier();
  if(is_numeric($_POST['objetID'])) {
    $courrier->setObjetID($_POST['objetID']);
    $dataCourrier['tag']=$courrier->getDataByObjetID();
  } elseif(is_numeric($_POST['patientID'])) {
    $courrier->setPatientID($_POST['patientID']);
    $dataCourrier['tag']=$courrier->getCourrierData();
  }

  $texte = msGetHtml::genererHtmlFromString($mm['formValues'], $dataCourrier);

  exit($texte);
}
