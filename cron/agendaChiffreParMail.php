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
 * Cron : envoyer les agendas futurs chiffrés (GPG) par mail
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

// pour le configurateur de cron
if (isset($p)) {
    $p['page']['availableCrons']['agendaChiffreParMail']=array(
        'task' => 'Agenda de secours',
        'defaults' => array('m'=>'0','h'=>'23','M'=>'*','dom'=>'*','dow'=>'0,1,2,3,4,5'),
        'description' => 'Envoi de son agenda futur chiffré GPG à l\'utilisateur, par mail');
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
$p['configDefault']=$p['config']=yaml_parse_file($homepath.'config/config.yml');
$p['homepath']=$homepath;

/////////// SQL connexion
$mysqli=msSQL::sqlConnect();


$users=msPeople::getUsersListForService('agendaEnvoyerChiffreParMail');

foreach ($users as $userID=>$value) {
    /////////// config pour l'utilisateur concerné
    $p['config']=array_merge($p['configDefault'], msConfiguration::getAllParametersForUser(['id'=>$userID]));

    if(empty($p['config']['agendaEnvoyerChiffreTo'])) continue;

    $ag = new msAgenda;
    $ag->set_userID($userID);
    $ag->setStartDate(date('Y-m-d 00:00:00'));
    $ag->setEndDate(date('Y-m-d 00:00:00',(time()+60*60*24*365*20)));
    $agenda = $ag->getAgendaInFlatHumanTxt();

    if(empty($agenda)) continue;

    $gpg = new msGnupg;
    $gpg->setPeopleID($userID);
    $blocGPG = $gpg->chiffrerTexte($agenda);

    if(empty($blocGPG)) continue;

    $tempfile=$p['config']['workingDirectory'].$userID.'/agendaSecours.txt.gpg';
    msTools::checkAndBuildTargetDir($p['config']['workingDirectory'].$userID);
    file_put_contents($tempfile, $blocGPG);

    $send = new msSend;
    $send->setSendType('ns');
    $send->setSendService($p['config']['smtpTracking']);
    $send->setTo(explode(',', $p['config']['agendaEnvoyerChiffreTo']));
    $send->setFrom($p['config']['smtpFrom']);
    $send->setFromName($p['config']['smtpFromName']);
    $send->setSubject("Agenda de secours au ".date("d/m/Y H:i:s"));
    $send->setAttachments($tempfile);
    $send->setAttachmentsBaseName('agendaDeSecours');
    $send->setBody("Agenda de secours au ".date("d/m/Y H:i:s"));
    $send->send();

    unlink($tempfile);

}
