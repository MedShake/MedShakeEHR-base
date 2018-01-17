<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://www.github.com/fr33z00>
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
 * user : les requête ajax
 *
 * @author fr33z00 <https://www.github.com/fr33z00>
 */


$debug='';
$m=$match['params']['m'];

$acceptedModes=array(
    'updateGroups', // Récupérer la liste des groupes de clicRDV
    'updateCals' // Récupérer la liste des agendas d'un groupe de clicRDV
);

if (!in_array($m, $acceptedModes)) {
    die;
}


// Récupérer la liste des groupes de clicRDV
if ($m=='updateGroups') {
    include('inc-ajax-updateGroups.php');
} elseif ($m=='updateCals') {
    include('inc-ajax-updateCals.php');
}


