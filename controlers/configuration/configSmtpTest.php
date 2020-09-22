<?php

/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2020
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
 * Config : faire un test des paramètre SMTP
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//admin uniquement
if (!msUser::checkUserIsAdmin()) {
   $template="forbidden";
   return;
}

$people = new msPeople;
$people->setToID($p['user']['id']);
$peopleData = $people->getSimpleAdminDatasByName(['personalEmail', 'profesionnalEmail']);


if(empty($peopleData)) die("Aucune adresse email connue sur votre compte utilisateur, envoi de mail impossible.");

$mail = new PHPMailer\PHPMailer\PHPMailer;
$mail->CharSet = 'UTF-8';
$mail->SMTPDebug = 2;
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


$mail->isHTML(false);
$mail->Subject = 'Test serveur SMPT de '.$p['config']['designAppName'];

$mail->setFrom($p['config']['smtpFrom'], $p['config']['smtpFromName']);
if(isset($peopleData['personalEmail'])) $mail->addAddress($peopleData['personalEmail']);
if(isset($peopleData['profesionnalEmail'])) $mail->addAddress($peopleData['profesionnalEmail']);

$mail->Body    =  nl2br("Test du serveur SMTP concluant !");
$mail->AltBody = $mail->Body;


if (!$mail->send()) {
    echo 'Le message n\'a pu être envoyé.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'envoyé !';
  }
