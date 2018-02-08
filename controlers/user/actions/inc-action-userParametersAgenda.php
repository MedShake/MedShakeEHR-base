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
 */

//construction du répertoire
msTools::checkAndBuildTargetDir($p['config']['webDirectory'].'agendasConfigurations/');

$params=array('Lundi'=>array(), 'Mardi'=>array(), 'Mercredi'=>array(), 'Jeudi'=>array(), 'Vendredi'=>array(), 'Samedi'=>array(), 'Dimanche'=>array());
$js=array();
$js[]="var businessHours = [\n";
$hiddenDays=[];
$day=1;
foreach($params as $k=>$v) {
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

$js[]="var hiddenDays = [".implode(', ', $hiddenDays)."];\n";

$js[]="var eventSources = [{\n";
$js[]="    url: urlBase + '/agenda/".$p['user']['id']."/ajax/getEvents/'\n";
$js[]="  },\n";
$js[]="  {\n";
$js[]="    events:[\n";
$day=1;
foreach($params as $k=>$v) {
    $params[$k]['pauseStart']=$_POST['pauseStart_'.$k];
    $params[$k]['pauseEnd']=$_POST['pauseEnd_'.$k];
    if ($_POST['pauseStart_'.$k] != $_POST['pauseStop_'.$k] and !in_array($k, $hiddenDays)) {
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
$js[]="var minTime = '".$params['minTime'].":00';\n";
$params['maxTime']=$_POST['maxTime'];
$js[]="var maxTime = '".$params['maxTime'].":00';\n";
$params['slotDuration']=$_POST['slotDuration'];
$js[]="var slotDuration = '".$params['slotDuration'].":00';\n";

file_put_contents('../config/configAgenda'.$p['user']['id'].'.yml', Spyc::YAMLDump($params, false, 0, true));
file_put_contents($p['config']['webDirectory'].'agendasConfigurations/configAgenda'.$p['user']['id'].'.js', $js);

msTools::redirRoute('userParameters');
