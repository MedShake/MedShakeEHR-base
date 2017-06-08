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
 * Patient > action : envoyer un mail pour ecofax OVH
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$detsinataireFAX=trim(str_replace(' ','',$_POST['p_481'])).'@ecofax.fr';

$mail = new PHPMailer;
$mail->CharSet = 'UTF-8';
//$mail->SMTPDebug = 3;
$mail->isSMTP();
$mail->Host = $p['config']['smtpHost'];
$mail->SMTPAuth = true;
$mail->Username = $p['config']['smtpUsername'];
$mail->Password = $p['config']['smtpPassword'];
$mail->SMTPSecure = 'ssl';
$mail->Port = $p['config']['smtpPort'];


//obtenir le chemin complet de la pj
if (isset($_POST['objetID'])) {
    $doc = new msStockage;
    $doc->setObjetID($_POST['objetID']);
    $sourceFile=$doc->getPathToDoc();
    $ext=$doc->getFileExtOfDoc();
}


$mail->isHTML(false);
$mail->Subject = $p['config']['ecofaxMyNumber'];

$mail->setFrom($p['config']['smtpFrom']);
$mail->addAddress($detsinataireFAX);

if (is_file($sourceFile)) {
    $mail->addAttachment($sourceFile, "document.".$ext);
}
$mail->Body    =  'password : '.$p['config']['ecofaxPass'];
$mail->AltBody = $mail->Body;


if (!$mail->send()) {
    echo 'Le message n\'a pu être envoyé.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    //echo 'envoyé !';

    //logs
    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);
    $patient->setToID($_POST['patientID']);

    //support (avec PJ ou sans)
    if (isset($_POST['objetID'])) {
        $supportID=$patient->createNewObjet(177, '', $_POST['objetID']);
    } else {
        $supportID=$patient->createNewObjet(177, '');
    }

    //from
    $patient->createNewObjet(109, $p['config']['smtpFrom'], $supportID);
    //to
    $patient->createNewObjet(110, $detsinataireFAX, $supportID);
    //sujet
    $patient->createNewObjet(112, $p['config']['ecofaxMyNumber'], $supportID);
    //numero destinataire
    $patient->createNewObjet(481, $_POST['p_481'], $supportID);
    //pj ID
    if (isset($_POST['objetID'])) {
        $patient->createNewObjet(178, $_POST['objetID'], $supportID);
    }

    msTools::redirection('/patient/'.$_POST['patientID'].'/');
}
