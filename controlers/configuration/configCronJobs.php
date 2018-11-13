<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00>
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
 * Config : gérer les tâches planifiées
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

$template='configCronJobs';
$debug='';

if (stristr(PHP_OS, 'WIN')) {
    die("Impossible de configurer des tâches programmées sous WINDOWS");
}
$p['page']['availableCrons']=array();

$crons=scandir($p['homepath'].'cron/');
if (!$crons or !is_array($crons) or count($crons)==2) {
    return;
}
$crons=array_splice($crons, 2);

foreach ($crons as $cron) {
  if(is_file($p['homepath'].'cron/'.$cron)) include $p['homepath'].'cron/'.$cron;
}
exec("crontab -l", $installedCrons);
if (!is_array($installedCrons)) {
    return;
}

foreach($installedCrons as $line) {
    if (!preg_match('#([-*,0-9]+) ([-*,0-9]+) ([-*,0-9]+) ([-*,0-9]+) ([-*,0-9]+) cd '.$p['homepath'].' && php -f cron\/(.*)\.php#', trim($line), $matches)) {
        continue;
    }
    if (array_key_exists($matches[6], $p['page']['availableCrons'])) {
        $p['page']['availableCrons'][$matches[6]]['values']=array('m'=>$matches[1],'h'=>$matches[2],'M'=>$matches[3],'dom'=>$matches[4],'dow'=>$matches[5]);
    }
}
