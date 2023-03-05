<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Transmissions : chercher patient concerné
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$a_json = array();

$mss = new msPeopleSearch;
$mss->setNameSearchMode('BnFnOrLnFn');
$mss->setPeopleType(['pro', 'patient']);
$criteres = array(
	'birthname' => $_GET['term'],
);

$is_valid = GUMP::is_valid($criteres, [
	'birthname' => 'sqlIdentiteSearch|max_len,60',
]);
if ($is_valid !== true) {
	return;
}

$mss->setCriteresRecherche($criteres);
$mss->setColonnesRetour(['deathdate', 'identite', 'birthdate']);
$mss->setLimitNumber(20);
if ($data = msSQL::sql2tab($mss->getSql(), $mss->getSqlMarqueurs())) {

	foreach ($data as $k => $v) {
		$a_json[] = array(
			'label' => trim($v['identite']) . ' ' . $v['birthdate'],
			'value' => trim($v['identite']),
			'patientID' => $v['peopleID'],
		);
	}
}

header('Content-Type: application/json');
echo json_encode($a_json);
