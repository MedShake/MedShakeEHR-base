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
    'setPeopleData' // Enregistrer des données patient
);

if (!in_array($m, $acceptedModes)) {
    die;
}


// Autocomplete des forms
if ($m=='getAutocompleteFormValues') {
    $type=$match['params']['type'];
    $dataset=$match['params']['dataset'];

    $dataset2database=array(
        'data_types'=>'objets_data'
    );

    $database=$dataset2database[$dataset];

    if (isset($match['params']['setTypes'])) {
        $searchTypes=explode(':', $match['params']['setTypes']);
    } else {
        $searchTypes[]=$type;
    }

    $data=msSQL::sql2tab("select distinct(value) from ".$database." where typeID in ('".implode("','", $searchTypes)."') and value like '".msSQL::cleanVar($_GET['term'])."%' ");

    echo json_encode($data);

// Enregistrer des données patient
} elseif ($m=='setPeopleData') {
    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);
    $patient->setToID($_POST['patientID']);
    if ($patient->createNewObjet($_POST['typeID'], $_POST['value'], $_POST['instance']) > 0) {
        $return['status']='ok';
        echo json_encode($return);
    } else {
        header('HTTP/1.1 401 Unauthorized');
    }
}
