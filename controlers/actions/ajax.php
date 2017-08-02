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
 * Requêtes AJAX utiles sur l'ensemble du site
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


header('Content-Type: application/json');

$m=$match['params']['m'];

$acceptedModes=array(
    'getAutocompleteFormValues', // Autocomplete des forms
    'getAutocompleteLinkType', // Autocomplete plus évolué
    'setPeopleData', // Enregistrer des données patient
    'mailTracking' // Retourner les infos de tracking d'un mail
);

if (!in_array($m, $acceptedModes)) {
    die;
}


// Autocomplete des forms - version simple
if ($m=='getAutocompleteFormValues') {
    include('inc-ajax-getAutocompleteFormValues.php');
}
// Autocomplete des forms - version complexe
elseif ($m=='getAutocompleteLinkType') {
    include('inc-ajax-getAutocompleteLinkType.php');
}
// Enregistrer des données patient
elseif ($m=='setPeopleData') {
    include('inc-ajax-setPeopleData.php');
}
// Retourner les infos de tracking d'un mail
elseif ($m=='mailTracking') {
    include('inc-ajax-mailTracking.php');
}
