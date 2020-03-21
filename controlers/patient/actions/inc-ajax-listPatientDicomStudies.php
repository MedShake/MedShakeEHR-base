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
 * Patient > ajax : lister les dernières études dicom du patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 if(!is_numeric($_POST['patientID'])) die;

 $dc = new msDicom();
 $dc->setToID($_POST['patientID']);
 $p['page']['studiesDcData']=$dc->getAllStudiesFromPatientDcData();
 if (!isset($p['page']['studiesDcData']['HttpError'])) {

   //on cherche les examens EHR qui peuvent être attachés.
   if ($d=msSQL::sql2tabKey("select value, instance from objets_data where typeID='".msData::getTypeIDFromName('dicomStudyID')."' and toID='".$_POST['patientID']."' ", 'instance', 'value')) {
       foreach ($d as $k=>$v) {
           $ob = new msObjet();
           $ob->setObjetID($k);
           $p['page']['studiesDcDataRapro'][$v]=$ob->getCompleteObjetDataByID();
       }
   }

   //on complète les data dicom avec un datetime facilement exploitable et on rapproche de la liste de l'EHR
   foreach ($p['page']['studiesDcData'] as $k=>$v) {
       $p['page']['studiesDcData'][$k]['Datetime'] =  $v['MainDicomTags']['StudyDate'].'T'.round($v['MainDicomTags']['StudyTime']);
       if(isset($p['page']['studiesDcDataRapro'][$v['ID']])) $p['page']['studiesDcData'][$k]['ehr'] = $p['page']['studiesDcDataRapro'][$v['ID']];
   }

 } else {
     unset($p['page']['studiesDcData']);
 }

header('Content-Type: application/json');

echo json_encode($p['page']['studiesDcData']);

die();
