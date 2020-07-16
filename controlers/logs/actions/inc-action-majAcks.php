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
 * Logs : mise à jour des données accusés de réception
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


if (filter_var($_POST['pratID'], FILTER_VALIDATE_INT, array('min_range'=>1))) {
    if ($date_path = date('Y/m/d', $_POST['date'])) {
        $smsLogCampaignFile = msConfiguration::getParameterValue('smsLogCampaignDirectory', ['id'=>$_POST['pratID'], 'module'=>'']).'/'.$date_path.'/RappelsRDV.json';
    }
}

if(is_file($smsLogCampaignFile)) {
  $msSMS='msSMS'.$p['config']['smsProvider'];
  if (class_exists($msSMS)) {
      $campaign = new $msSMS();
      $campaign->addAcksToLogs($smsLogCampaignFile);
  }
}

msTools::redirection('/logs/historiqueRappelsSMS/'.date('Y-m-d', $_POST['date']).'/');
