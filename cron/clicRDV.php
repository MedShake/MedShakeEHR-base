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
 * Cron : synchronisation des agendas clicRDV avec l'agenda intégré
 *
 * @author fr33z00 <https://www.github.com/fr33z00>
 */

// pour le configurateur de cron
if (isset($p)) {
    $p['page']['availableCrons']['clicRDV']=array(
        'task' => 'clicRDV',
        'defaults' => array('m'=>'0,15,30,45','h'=>'8-19','M'=>'*','dom'=>'*','dow'=>'1,2,3,4,5,6'),
        'description' => 'Synchronisation de l\'agenda interne avec clicRDV. Si vos rendez-vous sont pris autant par clicRDV que par un secrétariat, préférez une fréquence élevée (toutes les 5 minutes) pour limiter les risques de conflits sur les créneaux.');
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
$p['config']=yaml_parse_file($homepath.'config/config.yml');
$p['homepath']=$homepath;

/////////// SQL connexion
$mysqli=msSQL::sqlConnect();


$clicUsers=msPeople::getUsersWithSpecificParam('clicRdvUserId');
if (!is_array($clicUsers)) {
    return;
}
$clicrdv=new msClicRDV();
$startdate=date("Y-m-d H:i:s");
foreach($clicUsers as $userid=>$value) {
    $clicrdv->setUserID($userid);
    $ret=$clicrdv->syncEvents();
    if ($ret!==false and $ret!==true) {
        echo $ret."\n";
    }
}
msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'cron', 'value'=>$startdate));
