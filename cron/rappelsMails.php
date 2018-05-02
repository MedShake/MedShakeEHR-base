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
 * Cron : rappels mails (smtp)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

// pour le configurateur de cron
if (isset($p)) {
    $p['page']['availableCrons']['rappelsMails']=array(
        'task' => 'Mails rappel',
        'defaults' => array('m'=>'0','h'=>'19','M'=>'*','dom'=>'*','dow'=>'0,1,2,3,4,5'),
        'description' => 'Envoi des mails de rappel');
    return;
}

ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");
session_start();

$homepath=getcwd().'/';

/////////// Composer class auto-upload
require $homepath.'vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
    global $homepath;
    include $homepath.'class/' . $class . '.php';
});


/////////// Config loader
$p['configDefault']=$p['config']=Spyc::YAMLLoad($homepath.'config/config.yml');
$p['homepath']=$homepath;

/////////// SQL connexion
$mysqli=msSQL::sqlConnect();

/**
 * Envoi du mail de rappel
 * @param  array $pa tableau des var
 * @return array     tableau de log
 */
function sendmail($pa)
{
    global $p;

    $mail = new PHPMailer\PHPMailer\PHPMailer;
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
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

    $mail->setFrom($p['config']['smtpFrom'], $p['config']['smtpFromName']);
    $mail->addAddress($pa['email'], $pa['identite']);
    $mail->Subject = 'Rappel rdv le '.$pa['jourRdv'].' à '.$pa['heureRdv'];

    $msgRappel=str_replace("#praticien", $pa['praticien'], str_replace("#jourRdv", $pa['jourRdv'], str_replace('#heureRdv', $pa['heureRdv'], $p['config']['mailRappelMessage'])));

    $mail->Body = nl2br($msgRappel);
    $mail->AltBody = $msgRappel;

    if (!$mail->send()) {
        $pa['status']=$mail->ErrorInfo;
    } else {
        $pa['status']="message envoyé";
    }
    return $pa;
}

$users=msPeople::getUsersListForService('mailRappelActiver');

foreach ($users as $userID=>$value) {
    /////////// config pour l'utilisateur concerné
    $p['config']=array_merge($p['configDefault'], msConfiguration::getAllParametersForUser($userID));


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
                      'praticien'=>$value['lastname']?:$value['birthname'],
                      'id'=>$patient['id'],
                      'typeCs'=>$patient['type'],
                      'jourRdv'=>$date_sms,
                      'heureRdv'=>$patient['heure'],
                      'identite'=>$patient['identite'],
                      'email'=>$listeEmail[$patient['id']]
                    );
                    $log[]=sendmail($detinataire);
                }
                $dejaInclus[]=$listeEmail[$patient['id']];
            }
        }

        //log json
        $logFileDirectory=$p['config']['mailRappelLogCampaignDirectory'].date('Y/m/d/');
        msTools::checkAndBuildTargetDir($logFileDirectory);
        file_put_contents($logFileDirectory.'RappelsRDV.json', json_encode($log));

    }
}
