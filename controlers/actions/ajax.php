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
// Utiliser pour faciliter le debugage avec xdebug
//header('Content-Type: text/html');

$m=$match['params']['m'];

$acceptedModes=array(
    'getAutocompleteFormValues', // Autocomplete des forms
    'getAutocompleteLinkType', // Autocomplete plus évolué
    'setPeopleData', // Enregistrer des données patient
    'setPeopleDataByTypeName', // Enregistrer des données patient par nom du type de donnée
    'mailTracking', // Retourner les infos de tracking d'un mail
    'getAutocompleteCodeNgapOrCcamData', // Retourner les infos sur un acte NGAP ou CCAM
    'getCpsVitaleData', // Obtenir les infos de la carte Vitale
    'getCpsVitaleDataRappro', // Obtenir les infos de la carte Vitale rapprochées aux ID patients
    'makeClick2Call', // lancer un appel click2call
    'getPatientsOfTheDay', // obtenir le html pour le menu patients of the day
    'getBareCodeGenerator', // obtenir les codes bare généré
);

if (!in_array($m, $acceptedModes)) {
    die;
} else {
    include('inc-ajax-'.$m.'.php');
}
