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
 * Cron : rappels SMS via allMySMS <https://www.allmysms.com/>
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

// pour le configurateur de cron
if (isset($p)) {
    $p['page']['availableCrons']['rappelsSMS-allMySMS']=array(
        'task' => 'Rappels SMS',
        'defaults' => array('m'=>'0','h'=>'19','M'=>'*','dom'=>'*','dow'=>'0,1,2,3,4,5'),
        'description' => 'Envoi des SMS de rappel via allMySMS');
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

$users=msPeople::getUsersListForService('smsRappelActiver');

foreach ($users as $userID=>$value) {
    /////////// config pour l'utilisateur concerné
    $p['config']=array_merge($p['configDefault'], msConfiguration::getAllParametersForUser($userID));

    $tsJourRDV=time()+($p['config']['smsDaysBeforeRDV']*24*60*60);

    $campaignSMS = new msSMSallMySMS();

    $campaignSMS->set_campaign_name("RappelsRDV".date('Ymd', $tsJourRDV));
    $campaignSMS->set_message(str_replace("#praticien", $value['lastname']?:$value['birthname'], str_replace("#jourRdv", "#param_1#", str_replace('#heureRdv', "#param_2#", $p['config']['smsRappelMessage']))));
    $campaignSMS->set_tpoa(iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', str_replace("#praticien", $value['lastname']?:$value['birthname'], $p['config']['smsTpoa'])));

    $patientsList=file_get_contents('http://192.0.0.0/patientsDuJour.php?date='.date("Y-m-d", $tsJourRDV));
    $patientsList=json_decode($patientsList, true);

    $campaignSMS->set_addData4log(array('patientsList'=>$patientsList, 'tsJourdRDV'=>$tsJourRDV));

    if (is_array($patientsList)) {
        $listeID=array_column($patientsList, 'id');

        $listeTel=msSQL::sql2tabKey("select toID, value from objets_data where toId in ('".implode("', '", $listeID)."') and typeID='".msData::getTypeIDFromName('mobilePhone')."' and deleted='' and outdated='' ", 'toID', 'value');

        $date_sms=date("d/m/y", $tsJourRDV);

        $numDejaInclus=[];
        foreach ($patientsList as $patient) {
            if (isset($listeTel[$patient['id']])) {
                $telNumber=str_ireplace(array(' ', '/', '.'), '', $listeTel[$patient['id']]);
                if (!in_array($telNumber, $numDejaInclus)) {
                    $campaignSMS->ajoutDestinataire($telNumber, array('PARAM_1'=>$date_sms , 'PARAM_2'=>$patient['heure']));
                }
                $numDejaInclus[]=$telNumber;
            }
        }

        $campaignSMS->sendCampaign();
        $campaignSMS->set_filename4log('RappelsRDV.json');
        $campaignSMS->logCampaign();
        $campaignSMS->logCreditsRestants();
    }
}
