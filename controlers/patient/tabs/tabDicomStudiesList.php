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
 * Dicom : voir les études correspondant au patient
 * (action via Orthanc, cf class msDicom)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 if(!is_numeric($match['params']['patientID'])) die;
 $template='inc-patientDicomStudiesList';

 $dc = new msDicom();
 $dc->setToID($match['params']['patientID']);
 $p['page']['studiesDcData']=$dc->getAllStudiesFromPatientDcData();
 if (!isset($p['page']['studiesDcData']['HttpError'])) {

   //on complète les data dicom avec un datetime facilement exploitable
   if(isset($p['page']['studiesDcData'])) {
     foreach ($p['page']['studiesDcData'] as $k=>$v) {
         $p['page']['studiesDcData'][$k]['Datetime'] =  $v['MainDicomTags']['StudyDate'].'T'.round($v['MainDicomTags']['StudyTime']);
     }
   }

   //on cherche les examens EHR qui peuvent être attachés.
   if ($d=msSQL::sql2tabKey("select value, instance from objets_data where typeID='".msData::getTypeIDFromName('dicomStudyID')."' and toID='".$match['params']['patientID']."' ", 'instance', 'value')) {
       foreach ($d as $k=>$v) {
           $ob = new msObjet();
           $ob->setObjetID($k);
           $p['page']['studiesDcDataRapro'][$v]=$ob->getCompleteObjetDataByID();
       }
   }
 } else {
     unset($p['page']['studiesDcData']);
 }
