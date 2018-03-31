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
 * Agenda : déplacer un rdv de l'agenda
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$agenda = new msAgenda();
$agenda->set_fromID($p['user']['id']);
$agenda->set_userID($match['params']['userID']);
$agenda->set_eventID($_POST['eventid']);
$agenda->setStartDate($_POST['start']);
$agenda->setEndDate($_POST['end']);
$agenda->moveEvent();

header('Content-Type: application/json');
//hook pour service externe
if (isset($p['config']['agendaService'])) {
    $hook=$p['homepath'].'controlers/services/'.$p['config']['agendaService'].'/inc-ajax-moveEvent.php';
    if (is_file($hook)) {
        $event=$agenda->getEventByID($_POST['eventid']);
        include($hook);
    }
}

echo json_encode(array("status"=>"ok"));
