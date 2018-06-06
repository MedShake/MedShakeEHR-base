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
 * Phonecapture : recevoir les images en provenance de la page
 * de cpature spécifique dédiée aux smartphone
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$type = isset($_POST['pngBase64']) ? "png" : isset($_POST['jpgBase64']) ? "jpeg" : false;
if ($type !== false) {

    // récupérer data prat & patient
    $p['page']=json_decode(file_get_contents($p['config']['workingDirectory'].$p['user']['id'].'/workList.json'), true);

    //Si dossier ouvert à changer on coupe tout
    if($_POST['dicomPatientID'] != $p['page']['patient']['dicomPatientID']) {
      echo json_encode(array('status'=>'badDicomPatientID'));
      die();
    }

    // Vérification répertoire de travail
    msTools::checkAndBuildTargetDir($p['config']['workingDirectory'].$p['user']['id'].'/');

    $jpegFile=$p['config']['workingDirectory'].$p['user']['id'].'/dicomCreateJPG.jpg';
    //Récupérer l'image et sauver
    if ($type == "png") {
      $image = imagecreatefrompng($_POST['pngBase64']);
      imagejpeg($image, $jpegFile, 100);
      imagedestroy($image);
    } else {
      $image = fopen($jpegFile, 'wb');
      fwrite($image, base64_decode(substr($_POST['jpgBase64'], strpos($_POST['jpgBase64'], ",")+1)));
      fflush($image);
      fclose($image);
    }

    if ($p['page']['saveAs']=='doc') {
        $patient = new msObjet();
        $patient->setFromID($p['page']['prat']['pratID']);
        $patient->setToID($p['page']['patient']['id']);

        $supportID=$patient->createNewObjetByTypeName('docPorteur', '');

        //nom original
        $patient->createNewObjetByTypeName('docOriginalName', 'phonecapture_'.(date('d-m-Y_H:i:s')).'.jpg', $supportID);
        //type
        $patient->createNewObjetByTypeName('docType', 'jpg', $supportID);

        //folder
        $folder=msStockage::getFolder($supportID);

        //creation folder si besoin
        msTools::checkAndBuildTargetDir($p['config']['stockageLocation']. $folder.'/');

        $destination_file= $p['config']['stockageLocation']. $folder.'/'.$supportID.'.jpg';
        rename($jpegFile, $destination_file);
    } elseif ($p['page']['saveAs']=='dicom') {

        // compléter les data
        $p['page']['StudyInstanceUID']='1.7.12.9.3.11.'.$p['page']['prat']['pratID'].'.'.$p['page']['patient']['dicomPatientID'].'.'.date('Ymd');
        $p['page']['SeriesInstanceUID']='1.7.12.9.3.11.'.$p['page']['prat']['pratID'].'.'.$p['page']['patient']['dicomPatientID'].'.'.date('Ymd').'.'.date('d');

        // déterminer le numéro de l'instance
        $orthancSeriesID = $p['page']['patient']['dicomPatientID'].'|'.$p['page']['StudyInstanceUID'].'|'.$p['page']['SeriesInstanceUID'];
        $dicom = new msDicom();
        $dicom->setDcSerieID($dicom->constructIdOrthanc($orthancSeriesID));
        $p['page']['instanceNumber']= $dicom->getNumberInstancesInSeries() + 1;


        //générer le texte du template
        $getHtml = new msGetHtml();
        $getHtml->set_template('dicomCreateDCM');
        $fichierDicomTXT = $getHtml->genererHtml();
        $fichierDicomTXT = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $fichierDicomTXT);

        //définition fichiers et chemins
        $templateForDCM=$p['config']['workingDirectory'].$p['user']['id'].'/dicomCreateDCM.txt';
        $dcmBaseFile=$p['config']['workingDirectory'].$p['user']['id']."/dicomCreateDCM.dcm";
        $dcmFinalFile=$p['config']['workingDirectory'].$p['user']['id']."/dicomInstanceDCM.dcm";

        //créer le fichier de template
        file_put_contents($templateForDCM, $fichierDicomTXT);
        // transformer le template en DCM
        exec("dump2dcm ".$templateForDCM." ".$dcmBaseFile);
        // injecter le jpeg dans le DCM
        exec("img2dcm ".$jpegFile." ".$dcmFinalFile." -df " . $dcmBaseFile . " -stf " . $dcmBaseFile . " -sef " . $dcmBaseFile);
        // transférer à Orthanc
        if(strpos($p['config']['dicomHost'], '@') > 0) {
          $sendToHost = explode('@', $p['config']['dicomHost']);
          $sendToHost = $sendToHost[1];
        } else {
          $sendToHost = $p['config']['dicomHost'];
        }
        exec("storescu -aec ORTHANC ".$sendToHost." 4242 ".$dcmFinalFile."  --propose-jpeg8");

        @unlink($jpegFile);
        @unlink($templateForDCM);
        @unlink($dcmBaseFile);
        @unlink($dcmFinalFile);
    }
    echo json_encode(array('status'=>'ok'));
}
