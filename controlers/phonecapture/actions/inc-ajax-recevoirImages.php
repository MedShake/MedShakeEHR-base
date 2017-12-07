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
 */

if ($_POST['pngBase64']) {

    // Vérification répertoire de travail
    msTools::checkAndBuildTargetDir($p['config']['workingDirectory'].$p['user']['id'].'/');

    //Récupérer l'image et sauver
    $jpegFile=$p['config']['workingDirectory'].$p['user']['id'].'/dicomCreateJPG.jpg';
    $image = imagecreatefrompng($_POST['pngBase64']);
    imagejpeg($image, $jpegFile, 100);
    imagedestroy($image);

    // récupérer data prat & patient
    $p['page']=json_decode(file_get_contents($p['config']['workingDirectory'].$p['user']['id'].'/workList.json'), true);

    // compléter les data
    $p['page']['StudyInstanceUID']='1.7.12.9.3.11.'.$p['page']['prat']['pratID'].'.'.$p['page']['patient']['dicomPatientID'].'.'.date('Ymd');
    $p['page']['SeriesInstanceUID']='1.7.12.9.3.11.'.$p['page']['prat']['pratID'].'.'.$p['page']['patient']['dicomPatientID'].'.'.date('Ymd').'.'.date('d');

    // déterminer le numéro de l'instance
    $orthancSeriesID = $p['page']['patient']['dicomPatientID'].'|'.$p['page']['StudyInstanceUID'].'|'.$p['page']['SeriesInstanceUID'];
    $dicom = new msDicom();
    $dicom->setDcSerieID($dicom->constructIdOrthanc($orthancSeriesID));
    $p['page']['instanceNumber']= $dicom->getNumberInstancesInSeries() + 1;


    // les variables d'environnement twig
    if (isset($p['config']['twigEnvironnementCache'])) {
        $twigEnvironment['cache']=$p['config']['twigEnvironnementCache'];
    } else {
        $twigEnvironment['cache']=false;
    }
    if (isset($p['config']['twigEnvironnementAutoescape'])) {
        $twigEnvironment['autoescape']=$p['config']['twigEnvironnementAutoescape'];
    } else {
        $twigEnvironment['autoescape']=false;
    }

    $loaderPDF = new Twig_Loader_Filesystem($p['config']['templatesBaseFolder']);
    $twigPDF = new Twig_Environment($loaderPDF, $twigEnvironment);
    $twigPDF->getExtension('Twig_Extension_Core')->setDateFormat('d/m/Y', '%d days');
    $twigPDF->getExtension('Twig_Extension_Core')->setTimezone('Europe/Paris');

    //générer le texte du template
    $fichierDicomTXT = $twigPDF->render('dicomCreateDCM.html.twig', $p);
    $fichierDicomTXT = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $fichierDicomTXT);

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
    exec("storescu -aec ORTHANC ".$p['config']['dicomHost']." 4242 ".$dcmFinalFile."  --propose-jpeg8");

    @unlink($jpegFile);
    @unlink($templateForDCM);
    @unlink($dcmBaseFile);
    @unlink($dcmFinalFile);

    echo json_encode(array('status'=>'ok'));
}
