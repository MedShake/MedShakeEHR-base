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
 * Config > action : sauver la configuration d'un agenda
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

//utilisateurs pouvant avoir un agenda
$agendaUsers= new msPeople();
$autorisedUsers=$agendaUsers->getUsersListForService('administratifPeutAvoirAgenda');

//construction du répertoire
msTools::checkAndBuildTargetDir($p['homepath'].'config/agendas/');


if($_POST['userID']>0 and in_array($_POST['userID'], array_keys($autorisedUsers))) {
    file_put_contents($p['homepath'].'config/agendas/typesRdv'.$_POST['userID'].'.yml', $_POST['configTypesRdv']);

    if(empty($_POST['configTypesRdv']))
        unlink($p['homepath'].'config/agendas/typesRdv'.$_POST['userID'].'.yml');
    if(empty($_POST['configAgendaAd'])) {
        if (is_file($p['homepath'].'config/agendas/agenda'.$_POST['userID'].'_ad.js')) {
            unlink($p['homepath'].'config/agendas/agenda'.$_POST['userID'].'_ad.js');
        }
    } else {
        file_put_contents($p['homepath'].'config/agendas/agenda'.$_POST['userID'].'_ad.js', $_POST['configAgendaAd']);
    }
    if(empty($_POST['configAgenda'])) {
        if (is_file($p['homepath'].'config/agendas/agenda'.$_POST['userID'].'.yml')) {
            unlink($p['homepath'].'config/agendas/agenda'.$_POST['userID'].'.yml');
        }
        if (is_file($p['homepath'].'config/agendas/agenda'.$_POST['userID'].'.js')) {
            unlink($p['homepath'].'config/agendas/agenda'.$_POST['userID'].'.js');
        }
    } else {
        file_put_contents($p['homepath'].'config/agendas/agenda'.$_POST['userID'].'.yml', $_POST['configAgenda']);
        $params=Spyc::YAMLLoad($p['homepath'].'config/agendas/agenda'.$_POST['userID'].'.yml');

        $js=array();
        $js[]="businessHours = [\n";
        $hiddenDays=array();
        $d=1;
        foreach(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'] as $day) {
            if (!$params[$day]['visible']) {
                $hiddenDays[]=$d;
            } elseif ($params[$day]['worked']) {
                $js[]="  {\n";
                $js[]="    dow: [".$d."],\n";
                $js[]="    start: '".$params[$day]['minTime'].":00',\n";
                $js[]="    end: '".$params[$day]['maxTime'].":00',\n";
                $js[]="  },\n";
            }
            $d++;
            $d%=7;
        }
        $js[]="];\n";

        $js[]="hiddenDays = [".implode(', ', $hiddenDays)."];\n";

        $js[]="eventSources = [{\n";
        $js[]="    url: urlBase + '/agenda/".$_POST['userID']."/ajax/getEvents/'\n";
        $js[]="  },\n";
        $js[]="  {\n";
        $js[]="    events:[\n";
        $d=1;
        foreach(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'] as $day) {
          if(isset($params[$day]['pauseStart'], $params[$day]['pauseEnd'])) {
            if ($params[$day]['pauseStart'] != $params[$day]['pauseEnd'] and !in_array($d, $hiddenDays)) {
                $js[]="      {\n";
                $js[]="        start: '".$params[$day]['pauseStart'].":00',\n";
                $js[]="        end: '".$params[$day]['pauseEnd'].":00',\n";
                $js[]="        dow: [".$d."],\n";
                $js[]="        rendering: 'background',\n";
                $js[]="        className: 'fc-nonbusiness'\n";
                $js[]="      },\n";
            }
          }
          $d++;
          $d%=7;
        }
        $js[]="    ]\n";
        $js[]="  }\n";
        $js[]="];\n";

        $js[]="minTime = '".$params['minTime'].":00';\n";
        $js[]="maxTime = '".$params['maxTime'].":00';\n";
        $js[]="slotDuration = '".$params['slotDuration'].":00';\n";
        file_put_contents($p['homepath'].'config/agendas/agenda'.$_POST['userID'].'.js', $js);
    }
}
echo json_encode("ok");
