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
 * enregistrement des paramètres d'agenda utilisateur
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

//construction du répertoire
msTools::checkAndBuildTargetDir($p['homepath'].'config/agendas/');

$params=array('Lundi'=>array(), 'Mardi'=>array(), 'Mercredi'=>array(), 'Jeudi'=>array(), 'Vendredi'=>array(), 'Samedi'=>array(), 'Dimanche'=>array());
$js=array();
$js[]="businessHours = [\n";
$hiddenDays=[];
$day=1;
foreach($params as $k=>$v) {
    if(!isset($_POST['workOn_'.$k])) $_POST['workOn_'.$k]=false;
    if(!isset($_POST['visible_'.$k])) $_POST['visible_'.$k]=false;

    $params[$k]=array('worked'=>$_POST['workOn_'.$k], 'visible'=>$_POST['visible_'.$k], 'minTime'=> $_POST['minTime_'.$k], 'maxTime'=> $_POST['maxTime_'.$k]);
    $js[]="  {\n";
    $js[]="    dow: [".$day."],\n";
    $js[]="    start: '".$_POST['minTime_'.$k].":00',\n";
    $js[]="    end: '".$_POST['maxTime_'.$k].":00',\n";
    $js[]="  },\n";
    if ($_POST['visible_'.$k]!=true) {
        $hiddenDays[]=$day;
    }
    $day++;
    $day%=7;
}
$js[]="];\n";

$js[]="hiddenDays = [".implode(', ', $hiddenDays)."];\n";

$js[]="eventSources = [{\n";
$js[]="    url: urlBase + '/agenda/".$p['user']['id']."/ajax/getEvents/'\n";
$js[]="  },\n";
$js[]="  {\n";
$js[]="    events:[\n";
$day=1;
foreach($params as $k=>$v) {
    $params[$k]['pauseStart']=$_POST['pauseStart_'.$k];
    $params[$k]['pauseEnd']=$_POST['pauseEnd_'.$k];
    if ($_POST['pauseStart_'.$k] != $_POST['pauseEnd_'.$k] and !in_array($day, $hiddenDays)) {
        $js[]="      {\n";
        $js[]="        start: '".$_POST['pauseStart_'.$k].":00',\n";
        $js[]="        end: '".$_POST['pauseEnd_'.$k].":00',\n";
        $js[]="        dow: [".$day."],\n";
        $js[]="        rendering: 'background',\n";
        $js[]="        className: 'fc-nonbusiness'\n";
        $js[]="      },\n";
    }
    $day++;
    $day%=7;
}
$js[]="    ]\n";
$js[]="  }\n";
$js[]="];\n";

$params['minTime']=$_POST['minTime'];
$js[]="minTime = '".$params['minTime'].":00';\n";
$params['maxTime']=$_POST['maxTime'];
$js[]="maxTime = '".$params['maxTime'].":00';\n";
$params['slotDuration']=$_POST['slotDuration'];
$js[]="slotDuration = '".$params['slotDuration'].":00';\n";

file_put_contents($p['homepath'].'config/agendas/agenda'.$p['user']['id'].'.yml', Spyc::YAMLDump($params, false, 0, true));

if(file_put_contents($p['homepath'].'config/agendas/agenda'.$p['user']['id'].'.js', $js)) {
  header('Content-Type: application/json');
  exit(json_encode(array('status'=>'success')));
} else {
  header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
  exit();
}
