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
 * Requêtes AJAX > autocomplete des forms, version simple
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */

$type = $match['params']['type'];

if (isset($match['params']['setTypes'])) {
	$searchTypes = explode(':', $match['params']['setTypes']);
} else {
	$searchTypes[] = $type;
}
if (!empty($searchTypes)) {
	$sqlImplode = msSQL::sqlGetTagsForWhereIn($searchTypes, 'typeID');
	$marqueurs = $sqlImplode['execute'];
	$marqueurs['term'] = $_GET['term'] . '%';

	$data = msSQL::sql2tab("SELECT distinct(value) from objets_data where typeID in (" . $sqlImplode['in'] . ") and value like :term ", $marqueurs);
} else {
	$data = null;
}

echo json_encode($data);
