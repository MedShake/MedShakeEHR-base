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
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template="historiqueRappelsSMS";


//date concernée
if(isset($_POST['dateSel'])) {
    $date = DateTime::createFromFormat('d/m/Y', $_POST['dateSel']);
    $date=$date->format("U") ;
} elseif(isset($match['params']['date'])) {
    $date=strtotime($match['params']['date']);
} else {
    $date=time()-($p['config']['smsDaysBeforeRDV']*24*60*60);
}

//dates
$p['page']['dates']['emission']=$date;
$p['page']['dates']['rdv']=$date+($p['config']['smsDaysBeforeRDV']*24*60*60);
$p['page']['dates']['precedent']=$date-(60*60*24);
$p['page']['dates']['suivant']=$date+(60*60*24);
$p['page']['dates']['smsPourAujour']=time()-($p['config']['smsDaysBeforeRDV']*24*60*60);
$p['page']['dates']['smsEnvoyeAujour']=time()+($p['config']['smsDaysBeforeRDV']*24*60*60);

$campaign = new msSMS();
$p['page']['data']=$campaign->getSendedCampaignData($date);
