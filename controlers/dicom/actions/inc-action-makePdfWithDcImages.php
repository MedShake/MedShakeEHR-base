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
 * Dicom > action : création du PDF à partir des images sélectionnées
 * On utilise le template Twig rapportImagesDicom.html.twig
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

// images
foreach ($_POST['images'] as $k=>$v) {
    $p['page']['courrier']['images'][]=$p['config']['dicomWorkingDirectory'].$p['user']['id'].'/'.$_POST['dcStudyID'].'/'.$v.'.png';
}

//forger la description TXT pour le support
$nbImages=count($_POST['images']);
$txt="Pdf de ".$nbImages." images(s) de l'étude ".$_POST['dcStudyID']." du ".date("d/m/Y H:i", strtotime($dcStudyDate))." :\n";
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
    $doc->createNewObjetByTypeName('docType', 'pdf', $supportID);
    $doc->createNewObjetByTypeName('docOrigine', 'interne', $supportID);

    //titre doc
    $doc->setTitleObjet($supportID, 'pdf '.$nbImages.' images du '.date("d/m/Y H:i", strtotime($dcStudyDate)));

    //nouveau pdf
    $pdf= new msPDF();

    $pdf->setFromID($p['user']['id']);
    $pdf->setToID($_POST['patientID']);
    $pdf->setType('doc');
    $pdf->setObjetID($supportID);
    $pdf->setOptimizeWithGS(TRUE);

    $pdf->setPageHeader($pdf->makeWithTwig('base-page-headAndNoFoot.html.twig'));
    $pdf->setBodyFromPost($pdf->makeWithTwig('rapportImagesDicom.html.twig'));

    $pdf->makePDF();
    $pdf->savePDF();

    msTools::redirection('/patient/'.$_POST['patientID'].'/');
}
