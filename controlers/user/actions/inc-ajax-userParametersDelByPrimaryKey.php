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
 * Paramètres utilisateur > ajax : supprimer une entrée via la primary key
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */

$acceptedTables = array(
	'prescriptions',
	'actes',
	'prescriptions_cat',
	'actes_cat',
	'actes_base',
);

$table = $_POST['table'];
$id = $_POST['id'];
$do = false;

// conditions variables entrée script
if (!is_numeric($id) or !in_array($table, $acceptedTables)) {
	$do = false;
}
//conditions by table
elseif ($table == 'prescriptions') {
	$do = true;
	if (msSQL::sqlUniqueChamp("SELECT count(id) from objets_data where parentTypeID = :id", ['id' => $id]) == 0) {
		$do = true;
	}
} elseif ($table == 'prescriptions_cat') {
	if (msSQL::sqlUniqueChamp("SELECT count(cat) from prescriptions where cat = :id", ['id' => $id]) == 0) {
		$do = true;
	}
} elseif ($table == 'actes') {
	$do = true;
	if (msSQL::sqlUniqueChamp("SELECT count(id) from objets_data where parentTypeID = :id", ['id' => $id]) == 0) {
		$do = true;
	}
} elseif ($table == 'actes_cat') {
	if (msSQL::sqlUniqueChamp("SELECT count(cat) from actes where cat = :id", ['id' => $id]) == 0) {
		$do = true;
	}
}

// do it if you can !
if ($do === true) {
	msSQL::sqlQuery("DELETE from " . $table . " where toID = :userID and id = :id limit 1", ['userID' => $p['user']['id'], 'id' => $id]);
	$return['status'] = 'ok';
	echo json_encode($return);
} else {
	http_response_code(401);
}
