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
 * Public > ajax : générer le PDF comme document dans le dossier après signature
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$signature=$_POST['signatureSvg'];
$signPeriphName=$_POST['signPeriphName'];

$svg=$p['config']['workingDirectory'].$signPeriphName.'signature.svg';
$png=$p['config']['workingDirectory'].$signPeriphName.'signature.png';


if (is_file($p['config']['workingDirectory'].'signData-'.$signPeriphName.'.txt')) {
    $data=Spyc::YAMLLoad($p['config']['workingDirectory'].'signData-'.$signPeriphName.'.txt');
} else {
    $data['patientID']=null;
}

if ($signature and is_numeric($data['patientID'])) {
    file_put_contents($svg, $signature[1]);
    exec('convert '.$svg.' '.$png);

    // $im = new Imagick();
    // $im->readImageBlob($signature[1]);
    // $im->setImageFormat("png");
    // $im->thumbnailImage($maxwidth, 0);
    // $im->writeImage($p['config']['workingDirectory'].'signature.png');
    // $im->clear();
    // $im->destroy();

    //Data patient
    $courrier = new msCourrier();
    $courrier->setPatientID($data['patientID']);
    if(isset($data['objetID'])) {
      $courrier->setObjetID($data['objetID']);
      $p['page']['courrier']=$courrier->getDataByObjetID();
    } elseif (is_numeric($data['patientID'])) {
      $courrier->setFromID($data['fromID']);
      $p['page']['courrier']=$courrier->getCourrierData();
    }

    $pdf= new msPDF();
    $pdfCorps = $pdf->makeWithTwig($data['template'].'.html.twig');
    $signIMG = '<img src="'.$png.'" style="height : 50pt" />';
    $pdfCorps = str_replace('<!-- signatureIMG -->', $signIMG, $pdfCorps);
    $pdfCorps = str_replace('class="tailleFont"', 'style="font-size : 9pt;"', $pdfCorps);

    if(!isset($data['objetID'])) {

      // nouvel objet support
      $doc = new msObjet();
      $doc->setFromID($data['fromID']);
      $doc->setToID($data['patientID']);

      if ($supportID=$doc->createNewObjetByTypeName('docPorteur', '')) {
        //titre
        $doc->setTitleObjet($supportID, $data['label']);

        //type
        $doc->createNewObjetByTypeName('docType', 'pdf', $supportID);
        $doc->createNewObjetByTypeName('docOrigine', 'interne', $supportID);

        $pdf->setFromID($data['fromID']);
        $pdf->setToID($data['patientID']);
        $pdf->setType('doc');
        $pdf->setObjetID($supportID);

        $pdf->setPageHeader('');
        $pdf->setPageFooter('');
        $pdf->setBodyFromPost($pdfCorps);

        $pdf->makePDF();
        $pdf->savePDF();
      }
    } elseif(is_numeric($data['objetID'])) {
      $pdf->setObjetID($data['objetID']);
      $pdf->setPageHeader('');
      $pdf->setPageFooter('');
      $pdf->setBodyFromPost($pdfCorps);
      $pdf->makePDFfromObjetID();
      $pdf->savePDF();
    }

}

unlink($svg);
unlink($png);
unlink($p['config']['workingDirectory'].'signData-'.$signPeriphName.'.txt');
