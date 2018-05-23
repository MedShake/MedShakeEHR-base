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
 * People : les requêtes ajax
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

header('Content-Type: application/json');

$debug='';
$m=$match['params']['m'];
$acceptedModes=array(
    'getRelationsPraticiens', // autocomplete : obtenir le json des praticiens
    'addRelationPatientPraticien', //Ajouter une reelation patient <-> praticien
    'removeRelationPatient', // Retirer une reelation patient
    'getRelationsPatientPraticiensTab', // Obtenir le tableau de relations patient <-> praticiens
    'getRelationsPatients', //Autocomplete : obtenir le json des patients
    'addRelationPatientPatient', //Ajouter une reelation patient <-> patient
    'getRelationsPatientPatientsTab', // Obtenir le tableau de relations patient <-> patients
    'setExternAsPatient', //relier un externe à un patient
    'setExternAsNewPatient' //transformer un externe en patient
);

if (!in_array($m, $acceptedModes)) {
    die;
}


// Autocomplete : obtenir le json des praticiens
if ($m=='getRelationsPraticiens') {
    include('inc-ajax-getRelationsPraticiens.php');
}
// Ajouter une reelation patient <-> praticien
elseif ($m=='addRelationPatientPraticien') {
    include('inc-ajax-addRelationPatientPraticien.php');
}
// Obtenir le tableau de relations patient <-> praticiens
elseif ($m=='getRelationsPatientPraticiensTab') {
    include('inc-ajax-getRelationsPatientPraticiensTab.php');
}
// Retirer une reelation du patient 
elseif ($m=='removeRelationPatient') {
    include('inc-ajax-removeRelationPatient.php');
}


// Autocomplete : obtenir le json des patients
elseif ($m=='getRelationsPatients') {
    include('inc-ajax-getRelationsPatients.php');
}
// Autocomplete : obtenir le json des patients
elseif ($m=='addRelationPatientPatient') {
    include('inc-ajax-addRelationPatientPatient.php');
}
// Obtenir le tableau de relations patient <-> patients
elseif ($m=='getRelationsPatientPatientsTab') {
    include('inc-ajax-getRelationsPatientPatientsTab.php');
}

//relier un externe à un patient
elseif ($m=='setExternAsPatient') {
    include('inc-ajax-setExternAsPatient.php');
}
elseif ($m=='setExternAsNewPatient') {
    include('inc-ajax-setExternAsNewPatient.php');
}
