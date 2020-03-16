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
 * Dicom : voir toutes les données d'une études (images + data SR)
 * (action via Orthanc, cf class msDicom)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if(!is_numeric($match['params']['patientID'])) die;
$debug='';
$template='inc-patientDicomStudyView';

//le patient
$patient = new msPeople();
$patient->setToID($match['params']['patientID']);
$p['page']['patient']['id']=$match['params']['patientID'];
$p['page']['patient']['administrativeDatas']=$patient->getAdministrativesDatas();
$p['page']['patient']['administrativeDatas'][8]['age']=$patient->getAge();

//l'examen
$dc = new msDicomSR();
$dc->setToID($match['params']['patientID']);
$dc->setDcStudyID($_POST['param']['dcStudyID']);
$p['page']['studyDcData'] = $dc->getStudyDcData();

if (count($p['page']['studyDcData']) > 0) {
    $p['page']['studyDcData']['Datetime'] =  $p['page']['studyDcData']['MainDicomTags']['StudyDate'].'T'.round($p['page']['studyDcData']['MainDicomTags']['StudyTime']);
    $p['page']['imagesPath'] = $dc->getAllImagesFromStudy();


    //Données du SR via le XML
    $dc->getSRinstanceFromStudy();
    $p['page']['studyDcDataSRFull']=$dc->getSrData();

    //on cherche les examens EHR qui peuvent être attachés.
    if ($d=msSQL::sqlUniqueChamp("select instance from objets_data where typeID='".msData::getTypeIDFromName('dicomStudyID')."' and toID='".$match['params']['patientID']."' and value='".msSQL::cleanVar($_POST['param']['dcStudyID'])."' ")) {
        $ob = new msObjet();
        $ob->setObjetID($d);
        $p['page']['studyDcDataRapro']=$ob->getCompleteObjetDataByID();
    }
} else {
    die("Cette page n'existe pas");
}
