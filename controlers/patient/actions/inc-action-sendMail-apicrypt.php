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


$mail = new PHPMailer\PHPMailer\PHPMailer;
$mail->CharSet = 'iso-8859-1';
//$mail->SMTPDebug = 3;
$mail->isSMTP();
$mail->Host = $p['config']['apicryptSmtpHost'];
$mail->SMTPAuth = true;
$mail->Username = $p['config']['apicryptUtilisateur'];
$mail->Password = $p['config']['apicryptPopPassword'];
$mail->Port = $p['config']['apicryptSmtpPort'];

//obtenir le chemin complet de la pj
if (isset($_POST['objetID'])) {
    $doc = new msStockage;
    $doc->setObjetID($_POST['objetID']);
    $sourceFile=$doc->getPathToDoc();
    $ext=$doc->getFileExtOfDoc();
} else {
  $sourceFile='';
}

$mail->isHTML(false);
$mail->Subject = $_POST['mailSujet'];

$mail->setFrom($_POST['mailFrom']);
$mail->addAddress($_POST['mailToApicrypt']);

$hprimData = new msPeople();
$hprimData->setToID($_POST['patientID']);
$hprimData=$hprimData->getSimpleAdminDatasByName();
$hprimData=array_filter($hprimData);

if (!isset($hprimData['nss'])) {
    $hprimData['nss']='';
}

$texte=$_POST['patientID']."\n";
if(isset($hprimData['lastname'])) {
  $texte.=strtoupper($hprimData['lastname'])."\n"; //nom d'usage
} else {
  $texte.=strtoupper($hprimData['birthname'])."\n"; //nom de naissance
}
$texte.=$hprimData['firstname']."\n"; //prenom
$texte.="\n"; //adresse 1
$texte.="\n"; //adresse 2
$texte.="\n"; //ville
$texte.=$hprimData['birthdate']."\n"; //naissance
$texte.=str_replace(' ', '', $hprimData['nss'])."\n";
$texte.=$_POST['patientID']."\n"; //num de dossier
$texte.="\n"; //date dossier
$texte.='.         '.' '.strstr($p['config']['apicryptAdresse'], '@', true)."\n"; //code expediteur expediteur
$texte.='.         '.' '.strstr($_POST['mailToApicrypt'], '@', true)."\n"; //code desti destinataire
$texte.="\n".trim($_POST['mailBody']);
$texte.="\n".'****FIN****'."\n";
$texte.='****FINFICHIER****'."\n";
$mail->Body = msApicrypt::crypterCorps($texte, $_POST['mailToApicrypt']);

if (is_file($sourceFile)) {
    msApicrypt::crypterPJ($sourceFile, $_POST['mailToApicrypt'], $_POST['objetID'].'.'.$ext);
    $mail->addAttachment($p['config']['apicryptCheminFichierC'].$p['user']['id'].'/'.$_POST['objetID'].'.'.$ext.'.apz', "document.".$ext.".apz");
}


if (!$mail->send()) {
    echo 'Le message n\'a pu être envoyé.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
    //clean
    @unlink($p['config']['apicryptCheminFichierC'].$p['user']['id'].'/'.$_POST['objetID'].'.'.$ext.'.apz');
} else {
    //echo 'envoyé !';

    //clean
    @unlink($p['config']['apicryptCheminFichierC'].$p['user']['id'].'/'.$_POST['objetID'].'.'.$ext.'.apz');

    //logs
    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);
    $patient->setToID($_POST['patientID']);

    //support (avec PJ ou sans)
    if(isset( $_POST['objetID'])) $supportID=$patient->createNewObjetByTypeName('mailPorteur', '', $_POST['objetID']);
    else $supportID=$patient->createNewObjetByTypeName('mailPorteur', '');

    //from
    $patient->createNewObjetByTypeName('mailFrom', $_POST['mailFrom'], $supportID);
    //to
    $patient->createNewObjetByTypeName('mailToApicrypt', $_POST['mailToApicrypt'], $supportID);
    //sujet
    $patient->createNewObjetByTypeName('mailSujet', $_POST['mailSujet'], $supportID);
    //message
    $patient->createNewObjetByTypeName('mailBody', $_POST['mailBody'], $supportID);
    //pj ID
    if (isset($_POST['objetID'])) $patient->createNewObjetByTypeName('mailPJ1', $_POST['objetID'], $supportID);

    msTools::redirection('/patient/'.$_POST['patientID'].'/');
}
