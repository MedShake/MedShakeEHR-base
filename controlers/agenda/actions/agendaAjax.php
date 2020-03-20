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
 * Agenda : les requêtes ajax
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';
$m=$match['params']['m'];


$acceptedModes=array(
    'getEvents', // Obtenir le json des events
    'delEvent', // effacer un rdv
    'moveEvent', // déplacer un rdv
    'searchPatient', //chercher patient
    'getPatientAdminData', //obetnir les data patient
    'setNewRdv', // ajouter ou updater un rdv
    'synchronizeEvents', // synchroniser les événements (internes et externes)
    'setEventPasVenu', // marquer rendez-vous non honoré / honoré
    'getHistoriquePatient', // obtenir l'historique de rendez-vous d'un patient
    'setEventEnAttente', // marquer patient en salle d'attente
);

if (!in_array($m, $acceptedModes)) {
    die;
} else {
  include('inc-ajax-'.$m.'.php');
}
