<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * Outils > action : envoyer un fax
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if(strlen(trim(str_replace(' ','',$_POST['mailToEcofaxNumber']))) != 10) {
  die('Le numéro '.$_POST['mailToEcofaxNumber'].' ne semble pas être un numéro de fax valide');
}

$detsinataireFAX=trim(str_replace(' ','',$_POST['mailToEcofaxNumber'])).'@ecofax.fr';

$mail = new PHPMailer\PHPMailer\PHPMailer;
$mail->CharSet = 'UTF-8';
//$mail->SMTPDebug = 3;
$mail->isSMTP();
$mail->Host = $p['config']['smtpHost'];
$mail->SMTPAuth = true;
$mail->Username = $p['config']['smtpUsername'];
$mail->Password = $p['config']['smtpPassword'];
if($p['config']['smtpOptions'] == 'on') {
  $mail->SMTPOptions = array(
    'ssl' => array(
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true
    )
  );
}
if(!empty($p['config']['smtpSecureType'])) $mail->SMTPSecure = $p['config']['smtpSecureType'];
$mail->Port = $p['config']['smtpPort'];


//obtenir le chemin complet de la pj
$sourceFile=$p['config']['workingDirectory'].$p['user']['id'].'/pdf2fax.pdf';

$mail->isHTML(false);
$mail->Subject = $p['config']['ecofaxMyNumber'];

$mail->setFrom($p['config']['smtpFrom']);
$mail->addAddress($detsinataireFAX);

if (is_file($sourceFile)) {
    $mail->addAttachment($sourceFile, "document.pdf");
} else {
  die("Aucun document à faxer !");
}
$mail->Body    =  'password : '.$p['config']['ecofaxPassword'];
$mail->AltBody = $mail->Body;


if (!$mail->send()) {
    echo 'Le fax n\'a pu être envoyé.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    //echo 'envoyé !';
    if($_POST['actionAfterSend'] == 'cleanDoc') {
      @unlink($sourceFile);
    }
    msTools::redirection('/outils/envoyer-fax/');
}
