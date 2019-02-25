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
 * Patient > action : envoyer un mail via Mailjet <https://www.mailjet.com/>
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$mailParams=array(
  "FromEmail"=>$_POST['mailFrom'],
  "FromName"=>$p['config']['smtpFromName'],
  "Subject"=>$_POST['mailSujet'],
  "Text-part"=>$_POST['mailBody'],
  "Html-part"=>nl2br($_POST['mailBody']),
  "Recipients"=>[
    [
    "Email"=>$_POST['mailTo']
    ]
  ],
);


if (isset($_POST['objetID'])) {
    $doc = new msStockage;
    $doc->setObjetID($_POST['objetID']);
    $sourceFile=$doc->getPathToDoc();
    $ext=$doc->getFileExtOfDoc();
    $mime=msTools::getmimetype($sourceFile);
    $finalname="document.".$ext;
    $contenu=file_get_contents($sourceFile);
    if (!mb_detect_encoding($contenu, 'utf-8', true) and $mime == 'text/plain') {
        $contenu = utf8_encode($contenu);
    }
    $contenu=base64_encode($contenu);

    $mailParams['Attachments']=[
      [
        'Content-type' => $mime,
        'Filename' => $finalname,
        'content' => $contenu
      ]
    ];
}

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3/send");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mailParams));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_USERPWD, $p['config']['smtpUsername'] . ":" . $p['config']['smtpPassword']);

$headers = array();
$headers[] = "Content-Type: application/json";
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    curl_close($ch);

    $result = json_decode($result, true);

    if (isset($result['Sent'][0]['MessageID'])) {
        if (is_numeric($result['Sent'][0]['MessageID'])) {

            //logs
            $patient = new msObjet();
                  $patient->setFromID($p['user']['id']);
                  $patient->setToID($_POST['patientID']);

            //support (avec PJ ou sans)
            if (isset($_POST['objetID'])) {
                $supportID=$patient->createNewObjetByTypeName('mailPorteur', '', $_POST['objetID']);
            } else {
                $supportID=$patient->createNewObjetByTypeName('mailPorteur', '');
            }

            //trackingID
            $patient->createNewObjetByTypeName('mailTrackingID', $result['Sent'][0]['MessageID'], $supportID);

            //from
            $patient->createNewObjetByTypeName('mailFrom', $_POST['mailFrom'], $supportID);
            //to
            $patient->createNewObjetByTypeName('mailTo', $_POST['mailTo'], $supportID);
            //sujet
            $patient->createNewObjetByTypeName('mailSujet', $_POST['mailSujet'], $supportID);
            //message
            $patient->createNewObjetByTypeName('mailBody', $_POST['mailBody'], $supportID);
            //pj ID
            if (isset($_POST['objetID'])) {
                $patient->createNewObjetByTypeName('mailPJ1', $_POST['objetID'], $supportID);
            }

             msTools::redirection('/patient/'.$_POST['patientID'].'/');
        } else {
            echo "Il semble y avoir un problème. Merci de vérifier dans l'historique d'envoi des mails pour savoir si celui ci est parti ou non !";
        }
    } else {
        echo "Il semble y avoir un problème. Merci de vérifier dans l'historique d'envoi des mails pour savoir si celui ci est parti ou non !";
    }
}
