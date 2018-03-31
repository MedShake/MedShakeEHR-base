<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://www.github.com/fr33z00>
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
 * Cron : sauvegarde de la base
 *
 * @author fr33z00 <https://www.github.com/fr33z00>
 */

// pour le configurateur de cron
if (isset($p)) {
    $p['page']['availableCrons']['baseBackup']=array(
        'task' => 'baseBackup',
        'defaults' => array('m'=>'0','h'=>'22','M'=>'*','dom'=>'*','dow'=>'*'),
        'description' => 'Sauvegarde de la base de données. Sont conservées les sauvegardes des 7 derniers jours, la première des 12 derniers mois, la première de chaque année');
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
$p['config']=Spyc::YAMLLoad($homepath.'config/config.yml');
$p['homepath']=$homepath;

$today=date('Y-m-d');
exec('mysqldump -u '.$p['config']['sqlUser'].' -p'.$p['config']['sqlPass'].' '.$p['config']['sqlBase'].' > '.$p['config']['backupLocation'].$p['config']['sqlBase'].'_'.$today.'.sql');

$dumpsList=scandir($p['config']['backupLocation']);

$firstDayExists=false;
$firstDayOfMonth=date('Y-m-01');
$lastWeek=date('Y-m-d', strtotime("-1 week"));
$firstOfLastYear=date('Y-m-01', strtotime("-1 year"));
foreach ($dumpsList as $dump) {
    if (!preg_match('/'.$p['config']['sqlBase'].'_([0-9]+)-([0-9]+)-([0-9]+)\.sql/', $dump, $matches)) {
        continue;
    }
    if (($matches[1].'-'.$matches[2].'-'.$matches[3])==$firstDayOfMonth) {
        $firstDayExists=true;
    }
    if ((($matches[1].'-'.$matches[2].'-'.$matches[3])<$lastWeek and $matches[3]!='01') or
    (($matches[1].'-'.$matches[2].'-'.$matches[3])<$firstOfLastYear and $matches[2]!='01')) {
        unlink($p['config']['backupLocation'].$dump);
    }
}
if (!$firstDayExists) {
    copy($p['config']['backupLocation'].$p['config']['sqlBase'].'_'.$today.'.sql', $p['config']['backupLocation'].$p['config']['sqlBase'].'_'.$firstDayOfMonth.'.sql');
}
