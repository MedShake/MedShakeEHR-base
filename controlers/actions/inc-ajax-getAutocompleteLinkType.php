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
 * Requêtes AJAX > autocomplete des forms, version complexe
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */

$valeurs = array(
	'type' => $match['params']['type'],
	'term' => $_GET['term'],
	'setTypes' => $match['params']['setTypes']
);

$is_valid = GUMP::is_valid($valeurs, [
	'type' => 'required|alpha_numeric',
	'term' => 'required|sqlSearch',
	'setTypes' => 'alpha_numeric_colon'
]);
if ($is_valid !== true) {
	return;
}

$data = new msData();
$name2typeId = $data->getTypeIDsFromName([$match['params']['type']]);
$type = $name2typeId[$match['params']['type']];

if (!is_numeric($type)) {
	return;
}

if (isset($match['params']['setTypes'])) {
	$searchTypes = $data->getTypeIDsFromName(explode(':', $match['params']['setTypes']));
	foreach ($searchTypes as $v) {
		if (is_numeric($v)) $concatValue[] = " COALESCE(d" . $v . ".value, '')";
	}
} else {
	$searchTypes[] = $type;
}

$joinleft = [];
$concat = [];
$groupby = array('label');
if (isset($match['params']['linkedTypes'])) {
	$originalOrderLabel = explode(':', $match['params']['linkedTypes']);
	$linkedTypes = $data->getTypeIDsFromName($originalOrderLabel);

	foreach ($linkedTypes as $k => $v) {
		if (is_numeric($v)) {
			$sel[] = " d" . $v . ".value as " . $k;
			$concatLabel[$k] = " COALESCE(d" . $v . ".value, '')";
			$joinleft[] = " left join objets_data as d" . $v . " on do.toID = d" . $v . ".toID and d" . $v . ".typeID='" . $v . "' and d" . $v . ".outdated='' and d" . $v . ".deleted='' ";
			$groupby[] = 'd' . $v . '.value';
		}
	}
}
// remettre dans l'ordre original de l'url
if (!empty($concatLabel)) {
	$concatLabel = array_replace(array_flip($originalOrderLabel), $concatLabel);
}

$sqlImplode = msSQL::sqlGetTagsForWhereIn($searchTypes, 'st');
$marqueurs = array_merge($sqlImplode['execute'], ['term' => '%' . $_GET['term'] . '%']);

$sql = "SELECT trim(concat(" . implode(', " ",', $concatValue) . ")) as value, trim(concat(" . implode(', " ",', $concatLabel) . ")) as label, " . implode(",", $sel) . "
from objets_data as do
" . implode(" ", $joinleft) . "
where do.typeID in (" . $sqlImplode['in'] . ") and trim(concat(" . implode(', " ",', $concatLabel) . ")) like :term
and d" . $type . ".value is not null
group by " . implode(",", $groupby) . " limit 25";

$data = msSQL::sql2tab($sql, $marqueurs);

echo json_encode($data);
