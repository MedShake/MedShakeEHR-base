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
 * Logs : présente l'historique des rappels SMS
 *
 * @author           Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib 2020     Maxime   DEMAREST   <maxime@indelog.fr>
 */

$debug='';
$template="historiqueRappelsSMS";

// prats possibles
$p['page']['pratsInConfig']=msPeople::getUsersListForService('optionGeActiverRappelsRdvSMS');

// prat concerné
if(isset($_POST['pratID']) and is_numeric($_POST['pratID']) and array_key_exists($_POST['pratID'], $p['page']['pratsInConfig'])) {
  $match['params']['pratID'] = $_POST['pratID'];
} elseif(!isset($match['params']['pratID']) and !empty($p['page']['pratsInConfig'])) {
  $match['params']['pratID'] = key($p['page']['pratsInConfig']);
}

if(isset($match['params']['pratID']) and is_array($p['page']['pratsInConfig']) and array_key_exists($match['params']['pratID'], $p['page']['pratsInConfig'])) {
  $smsDaysBeforeRDV = msConfiguration::getParameterValue('smsDaysBeforeRDV', ['id'=>$match['params']['pratID'], 'module'=>'']);
  $smsLogCampaignDirectory = msConfiguration::getParameterValue('smsLogCampaignDirectory', ['id'=>$match['params']['pratID'], 'module'=>'']);
  $p['page']['selectPrat']=$match['params']['pratID'];
  $smsProvider = msConfiguration::getParameterValue('smsProvider', ['id'=>$match['params']['pratID'], 'module'=>'']);
} else {
  $smsDaysBeforeRDV = msConfiguration::getDefaultParameterValue('smsDaysBeforeRDV');
  $smsLogCampaignDirectory = msConfiguration::getDefaultParameterValue('smsLogCampaignDirectory');
  $smsProvider = msConfiguration::getDefaultParameterValue('smsProvider');
  $p['page']['selectPrat']=NULL;
}
$smsLogCampaignDirectory=msTools::setDirectoryLastSlash($smsLogCampaignDirectory);

//date concernée
if (isset($_POST['dateSel'])) {
    $date = DateTime::createFromFormat('d/m/Y', $_POST['dateSel']);
    $date=$date->format("U") ;
} elseif (isset($match['params']['date'])) {
    $date=strtotime($match['params']['date']);
} else {
    $date=time()-($smsDaysBeforeRDV*24*60*60);
}

//dates
$p['page']['dates']['emission']=$date;
$p['page']['pratID']=$match['params']['pratID'];
$p['page']['dates']['rdv']=$date+($smsDaysBeforeRDV*24*60*60);
$p['page']['dates']['precedent']=$date-(60*60*24);
$p['page']['dates']['suivant']=$date+(60*60*24);
$p['page']['dates']['smsPourAujour']=time()-($smsDaysBeforeRDV*24*60*60);
$p['page']['dates']['smsEnvoyeAujour']=time()+($smsDaysBeforeRDV*24*60*60);

$msSMS='msSMS'.$smsProvider;
if (class_exists($msSMS)) {
    $campaign = new $msSMS();
    $p['page']['data']=$campaign->getSendedCampaignData($date, $smsLogCampaignDirectory);
}
