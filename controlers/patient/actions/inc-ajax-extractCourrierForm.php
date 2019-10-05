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
 * Patient > ajax : extraire l'éditeur de courrier
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */


 $debug='';

 //template
 $template="patientCourrierForm";

 //patient
 $p['page']['patient']['id']=$_POST['patientID'];

 $p['page']['printType']='courrier';

 $courrier = new msCourrier();
 $courrier->setModeleIDByName($_POST['modele']);
 $modeleID=$courrier->getModeleID();
 $courrier->setPatientID($_POST['patientID']);
 $courrier->setModule($p['user']['module']);

 if (isset($_POST['objetID'])) {
     $dataform = new msObjet();
     $dataform->setObjetID($_POST['objetID']);
     $dataform=$dataform->getObjetDataByID(['value']);
     $p['page']['courrier']['pre']=msTools::unbbcodifier($dataform['value']);
 } elseif (isset($_POST['modele'])) {

     $p['page']['courrier']=$courrier->getCourrierData();

     $data=new msData();
     if ($printModel=$data->getDataType($modeleID, ['formValues'], ['formValues'])) {
         $p['page']['courrier']['printModel']=$printModel['formValues'].'.html.twig';
     } else {
         $p['page']['courrier']['printModel']='defaut.html.twig';
     }
 } else {
     $p['page']['courrier']['printModel']='defaut.html.twig';
 }

 $p['page']['courrier']['actionForm']="/makepdf/".$_POST['patientID']."/".$p['page']['printType'].'/';
 if (isset($modeleID)) {
     $p['page']['courrier']['actionForm'].=$modeleID.'/';
 }
 if (isset($_POST['objetID'])) {
     $p['page']['courrier']['actionForm'].=$_POST['objetID'].'/';
 }
