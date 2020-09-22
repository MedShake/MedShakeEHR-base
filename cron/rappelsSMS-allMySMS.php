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

if (!empty($homepath=getenv("MEDSHAKEEHRPATH"))) $homepath=getenv("MEDSHAKEEHRPATH");
else $homepath=preg_replace("#cron$#", '', __DIR__);

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

$users=msPeople::getUsersListForService('optionGeActiverRappelsRdvSMS');

foreach ($users as $userID=>$value) {
    /////////// config pour l'utilisateur concerné
    $p['config']=array_merge($p['configDefault'], msConfiguration::getAllParametersForUser(['id'=>$userID]));

	if (! empty($p['config']['smsTypeRdvPourRappel']))
		$smsTypeRdvPourRappel = explode(',', $p['config']['smsTypeRdvPourRappel']);

    $tsJourRDV=time()+($p['config']['smsDaysBeforeRDV']*24*60*60);

    $campaignSMS = new msSMSallMySMS();

    $campaignSMS->set_campaign_name("RappelsRDV".date('Ymd', $tsJourRDV));
    $campaignSMS->set_message(str_replace("#praticien", $value, str_replace("#jourRdv", "#param1#", str_replace('#heureRdv', "#param2#", $p['config']['smsRappelMessage']))));
    $campaignSMS->set_tpoa(iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', str_replace("#praticien", $value, $p['config']['smsTpoa'])));

    // Si fonctionnement avec source externe, adapter l'url
    // $patientsList=file_get_contents('http://192.0.0.0/patientsDuJour.php?date='.date("Y-m-d", $tsJourRDV));
    // $patientsList=json_decode($patientsList, true);

    // source agenda interne
    $events = new msAgenda();
    $events->set_userID($userID);
    $patientsList=$events->getPatientsForDate(date("Y-m-d", $tsJourRDV));

	// Si smsTypeRdvPourRappel est défini et que le rdv n'est pas dans la liste des rdv pour appel retirer le patient de la liste
	if (! empty($smsTypeRdvPourRappel) && is_array($patientsList)) {
		$nbPatients = count($patientsList);
		for ($i=0 ; $i<$nbPatients ; $i++) {
			if (! in_array($patientsList[$i]['type'], $smsTypeRdvPourRappel)) unset($patientsList[$i]);
		}
	}

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
                    $campaignSMS->ajoutDestinataire($telNumber, array('param1'=>$date_sms , 'param2'=>$patient['heure']));
                }
                $numDejaInclus[]=$telNumber;
            }
        }

        $campaignSMS->set_filename4log('RappelsRDV.json');
        $campaignSMS->set_timestamp4log(time());
        openlog('MedShakeEHR', LOG_PID | LOG_PERROR, LOG_LOCAL0);
        syslog(LOG_INFO, 'Evoie du rappel de rendez vous sms pour la campagne : '.$campaignSMS->get_fullpath4log());
        $resu = $campaignSMS->sendCampaign();
        if (!empty($resu)) {
            $campaignSMS->logCampaign();
            $campaignSMS->logCreditsRestants();
        } else {
            syslog(LOG_WARNING, $campaignSMS->get_fullpath4log().' exite, la campagne sms ne sera pas re-expedier une seconde fois');
        }
        closelog();
    }
}
