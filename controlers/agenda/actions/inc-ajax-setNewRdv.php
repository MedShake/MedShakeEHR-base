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
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

if (!isset($_POST['patientID'])) {
    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);

    $newpatient = new msPeople();
    $newpatient->setFromID($p['user']['id']);
    $newpatient->setType('patient');

    $patient->setToID($_POST['patientID']=$newpatient->createNew());

    foreach ($_POST as $k=>$v) {
        if (array_search($k, ['eventID', 'userID', 'eventStartID', 'eventEndID', 'start', 'end', 'motif', 'type'])!==FALSE) {
            continue;
        }
        if (($pos = strpos($k, "_")) !== false) {
            $in = substr($k, $pos+1);
            if (isset($in)) {
                if (!empty(trim($v)) and !empty(trim($in))) {
                    $patient->createNewObjetByTypeName($in, $v);
                }
            }
        }
    }
}

$agenda = new msAgenda();
if (isset($_POST['eventID']) and $_POST['eventID']>0) {
    $agenda->set_eventID($_POST['eventID']);
}
$agenda->set_userID($match['params']['userID']);
$agenda->set_patientID($_POST['patientID']);
$agenda->set_fromID($p['user']['id']);
$agenda->setStartDate($_POST['start']);
$agenda->setEndDate($_POST['end']);
$agenda->set_motif($_POST['motif']);
$agenda->set_type($_POST['type']);
$event=$agenda->addOrUpdateRdv();

header('Content-Type: application/json');
//hook pour service externe
if (isset($p['config']['agendaService'])) {
    $hook=$p['homepath'].'controlers/services/'.$p['config']['agendaService'].'/inc-ajax-setNewRdv.php';
    if (is_file($hook)) {
        include($hook);
    }
}
echo json_encode($event);
