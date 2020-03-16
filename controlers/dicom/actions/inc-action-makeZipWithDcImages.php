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
 * Dicom > action : création d'un ZIP à partir des images sélectionnées
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//patient
$patient= new msPeople();
$patient->setToID($_POST['patientID']);
$p['page']['courrier']=$patient->getSimpleAdminDatas();

// dicom
$dc = new msDicom();
$dc->setToID($_POST['patientID']);
$dc->setDcStudyID($_POST['dcStudyID']);
$data = $dc->getStudyDcData();
$dcStudyDate=$data['MainDicomTags']['StudyDate'].'T'.round($data['MainDicomTags']['StudyTime']);

//forger la description TXT pour le support
$nbImages=count($_POST['images']);
$txt="Zip de ".$nbImages." images(s) de l'étude ".$_POST['dcStudyID']." du ".date("d/m/Y H:i", strtotime($dcStudyDate))." :\n";
// images
foreach ($_POST['images'] as $k=>$v) {
    $txt.='- '.$v.".png\n";
}

// nouveau document support
$doc = new msObjet();
$doc->setFromID($p['user']['id']);
$doc->setToID($_POST['patientID']);

if ($supportID=$doc->createNewObjetByTypeName('docPorteur', $txt)) {

    //type et origine
    $doc->createNewObjetByTypeName('docType', 'zip', $supportID);
    $doc->createNewObjetByTypeName('docOrigine', 'interne', $supportID);

    //titre doc
    $doc->setTitleObjet($supportID, 'zip '.$nbImages.' images du '.date("d/m/Y H:i", strtotime($dcStudyDate)));

    //stockage
    $stockage = new msStockage();
    $stockage->setObjetID($supportID);
    $file = $stockage->getPathToDoc();
    $directory = $p['config']['stockageLocation'].$stockage->getFolder($supportID);
    msTools::checkAndBuildTargetDir($directory) ;

    //nouveau zip
    $zip = new ZipArchive();

    if ($zip->open($file, ZipArchive::CREATE)!==TRUE) {
        exit("Impossible d'ouvrir le fichier <$file>\n");
    } else {

      $i=1;
      foreach ($_POST['images'] as $k=>$v) {
        $imagepath=$p['config']['dicomWorkingDirectory'].$p['user']['id'].'/'.$_POST['dcStudyID'].'/'.$v.'.png';
        if(is_file($imagepath)) {
          $zip->addFile($imagepath, "image".$i.".png");
          $i++;
        }
      }

      $zip->close();
    }
}

msTools::redirection('/patient/'.$_POST['patientID'].'/');
