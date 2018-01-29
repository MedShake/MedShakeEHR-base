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
 * Agenda : ajouter / éditer un rdv dans l'agenda
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$event = new msAgenda();
if ($_POST['eventID']>0) {
    $event->set_eventID($_POST['eventID']);
}
$event->set_userID($match['params']['userID']);
$event->set_patientID($_POST['patientID']);
$event->set_fromID($p['user']['id']);
$event->setStartDate($_POST['start']);
$event->setEndDate($_POST['end']);
$event->set_motif($_POST['motif']);
$event->set_type($_POST['type']);
$dataEvent=$event->addOrUpdateRdv();

//hook pour service externe
if (iset($p['config']['agendaService'])) {
    $hook=$p['config']['homeDirectory'].'controlers/services/'.$p['config']['agendaService'].'/inc-ajax-setNewRdv.php';
    if (is_file($hook)) {
        include($hook);
    }
}

header('Content-Type: application/json');
echo json_encode($dataEvent);
