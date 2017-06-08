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
 * Patient > action : envoyer un mail apicrypt
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$mail = new PHPMailer;
$mail->CharSet = 'iso-8859-1';
//$mail->SMTPDebug = 3;
$mail->isSMTP();
$mail->Host = $p['config']['apicryptSmtpHost'];
$mail->SMTPAuth = true;
$mail->Username = $p['config']['apicryptUtilisateur'];
$mail->Password = $p['config']['apicryptPopPass'];
$mail->Port = $p['config']['apicryptSmtpPort'];

//obtenir le chemin complet de la pj
if (isset($_POST['objetID'])) {
    $doc = new msStockage;
    $doc->setObjetID($_POST['objetID']);
    $sourceFile=$doc->getPathToDoc();
    $ext=$doc->getFileExtOfDoc();
}

$mail->isHTML(false);
$mail->Subject = $_POST['p_112'];

$mail->setFrom($_POST['p_109']);
$mail->addAddress($_POST['p_179']);

$hprimData = new msPeople();
$hprimData->setToID($_POST['patientID']);
$hprimData=$hprimData->getSimpleAdminDatas();

if (!isset($hprimData['180'])) {
    $hprimData['180']='';
}

$texte=$_POST['patientID']."\n";
$texte.=strtoupper($hprimData['2'])."\n"; //nom
$texte.=$hprimData['3']."\n"; //prenom
$texte.="\n"; //adresse 1
$texte.="\n"; //adresse 2
$texte.="\n"; //ville
$texte.=$hprimData['8']."\n"; //naissance
$texte.=str_replace(' ', '', $hprimData['180'])."\n";
$texte.=$_POST['patientID']."\n"; //num de dossier
$texte.="\n"; //date dossier
$texte.='.         '.' '.strstr($p['config']['apicryptAdresse'], '@', true)."\n"; //code expediteur expediteur
$texte.='.         '.' ';
strstr($_POST['p_179'], '@', true)."\n"; //code desti destinataire
$texte.="\n".trim($_POST['p_111']);
$texte.="\n".'****FIN****'."\n";
$texte.='****FINFICHIER****'."\n";
$mail->Body = msApicrypt::crypterCorps($texte, $_POST['p_179']);

if (is_file($sourceFile)) {
    msApicrypt::crypterPJ($sourceFile, $_POST['p_179'], $_POST['objetID'].'.'.$ext);
    $mail->addAttachment($p['config']['apicryptCheminFichierC'].$p['user']['id'].'/'.$_POST['objetID'].'.pdf.apz', "document.".$ext.".apz");
}


if (!$mail->send()) {
    echo 'Le message n\'a pu être envoyé.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    //echo 'envoyé !';

    //clean
    unlink($p['config']['apicryptCheminFichierC'].$p['user']['id'].'/'.$_POST['objetID'].'.pdf.apz');

    //logs
    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);
    $patient->setToID($_POST['patientID']);

    //support (avec PJ ou sans)
    if(isset( $_POST['objetID'])) $supportID=$patient->createNewObjet(177, '', $_POST['objetID']);
    else $supportID=$patient->createNewObjet(177, '');

    //from
    $patient->createNewObjet(109, $_POST['p_109'], $supportID);
    //to
    if (isset($_POST['p_110'])) {
        $patient->createNewObjet(110, $_POST['p_110'], $supportID);
    }
    if (isset($_POST['p_179'])) {
        $patient->createNewObjet(179, $_POST['p_179'], $supportID);
    }
    //sujet
    $patient->createNewObjet(112, $_POST['p_112'], $supportID);
    //message
    $patient->createNewObjet(111, $_POST['p_111'], $supportID);
    //pj ID
    if (isset($_POST['objetID'])) $patient->createNewObjet(178, $_POST['objetID'], $supportID);


    msTools::redirection('/patient/'.$_POST['patientID'].'/');
}
