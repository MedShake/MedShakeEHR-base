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
    'setPeopleDataByTypeName', // Enregistrer des données patient par nom du type de donnée
    'mailTracking', // Retourner les infos de tracking d'un mail
    'getAutocompleteCodeNgapOrCcamData' // Retourner les infos sur un acte NGAP ou CCAM
);

if (!in_array($m, $acceptedModes)) {
    die;
} else {
    include('inc-ajax-'.$m.'.php');
}
