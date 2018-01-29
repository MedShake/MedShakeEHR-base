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
 * Cron : rappels mails via Mailjet <https://www.mailjet.com/>
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

// pour le configurateur de cron
if (isset($p)) {
    $p['page']['availableCrons']['rappelsMails-Mailjet']=array(
        'task' => 'Mails rappel Mailjet',
        'defaults' => array('m'=>'0','h'=>'19','M'=>'*','dom'=>'*','dow'=>'0,1,2,3,4,5'),
        'description' => 'Envoi des mails de rappel Mailjet');
    return;
}

ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");
session_start();

/////////// Composer class auto-upload
require '../vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
    include '../class/' . $class . '.php';
});


/////////// Config loader
$p['config']=Spyc::YAMLLoad('../config/config.yml');

/////////// SQL connexion
$mysqli=msSQL::sqlConnect();

/**
 * Envoi du mail de rappel
 * @param  array $pa tableau des var
 * @return array     tableau de log
 */
function sendmailjet($pa)
{
    global $p;

    $msgRappel="Madame,\n\nNous vous rappelons votre RDV du ".$pa['jourRdv']." à ".$pa['heureRdv']." avec le Dr ... .\n\nNotez bien qu’aucun autre rendez-vous ne sera donné à un patient n’ayant pas honoré le premier.\n\nMerci de votre confiance,\nÀ bientôt !\n\nPS : Ceci est un mail automatique, merci de ne pas répondre.";

    $mailParams=array(
    "FromEmail"=>$p['config']['smtpFrom'],
    "FromName"=>$p['config']['smtpFromName'],
    "Subject"=>'Rappel rendez-vous Dr ... le '.$pa['jourRdv'].' à '.$pa['heureRdv'],
    "Text-part"=>$msgRappel,
    "Html-part"=>nl2br($msgRappel),
    "Recipients"=>[
      [
      "Email"=>$pa['email'],
      "Name"=>$pa['identite']
      ]
    ],
    );

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
        $pa['status'] = 'Error:' . curl_error($ch);
    } else {
        $pa['status']='problème';
        curl_close($ch);

        $result = json_decode($result, true);

        if (isset($result['Sent'][0]['MessageID'])) {
            if (is_numeric($result['Sent'][0]['MessageID'])) {
                $pa['mailTrackingID']=$result['Sent'][0]['MessageID'];
                $pa['status']='message envoyé';
            }
        }
    }
    return $pa;
}


$tsJourRDV=time()+($p['config']['mailRappelDaysBeforeRDV']*24*60*60);

$patientsList=file_get_contents('http://192.0.0.0/patientsDuJour.php?date='.date("Y-m-d", $tsJourRDV));
$patientsList=json_decode($patientsList, true);


if (is_array($patientsList)) {
    $listeID=array_column($patientsList, 'id');

    $listeEmail=msSQL::sql2tabKey("select toID, value from objets_data where toId in ('".implode("', '", $listeID)."') and typeID='".msData::getTypeIDFromName('personalEmail')."' and deleted='' and outdated='' ", 'toID', 'value');

    $date_sms=date("d/m/y", $tsJourRDV);

    $dejaInclus=[];
    foreach ($patientsList as $patient) {
        if (isset($listeEmail[$patient['id']])) {
            if (!in_array($listeEmail[$patient['id']], $dejaInclus)) {
                $detinataire=array(
                  'id'=>$patient['id'],
                  'typeCs'=>$patient['type'],
                  'jourRdv'=>$date_sms,
                  'heureRdv'=>$patient['heure'],
                  'identite'=>$patient['identite'],
                  'email'=>$listeEmail[$patient['id']]
                );
                $log[]=sendmailjet($detinataire);
            }
            $dejaInclus[]=$listeEmail[$patient['id']];
        }
    }

    //log json
    $logFileDirectory=$p['config']['mailRappelLogCampaignDirectory'].date('Y/m/d/');
    msTools::checkAndBuildTargetDir($logFileDirectory);
    file_put_contents($logFileDirectory.'RappelsRDV.json', json_encode($log));
}
