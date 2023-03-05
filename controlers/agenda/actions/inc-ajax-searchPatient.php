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
 * Agenda : chercher patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$a_json = array();

// Permet d'affiner les résultat sur des noms qui peuvent resembler à des
// prénoms très commun :
//   (ex : MARIE Françoise (nom + prenom) <> Marie Françoise (prénom seul))
// en donnant le prossiblité de préciser la recherche en séparrant les noms et
// prénoms par un ":". Dans le cas le nom est le premier terme et le prénom le
// second.
$split_term = explode(':', $_GET['term']);
if (count($split_term) > 1) {
	$mss = new msPeopleSearch;
	$mss->setPeopleType(['pro', 'patient']);
	$criteres = array(
		'birthname' => trim($split_term[0]),
		'lastname' => trim($split_term[0]),
		'firstname' => trim($split_term[1]),
	);
} else {
	$mss = new msPeopleSearch;
	$mss->setNameSearchMode('BnFnOrLnFn');
	$mss->setPeopleType(['pro', 'patient']);
	$criteres = array(
		'birthname' => $_GET['term'],
	);
}

$is_valid = GUMP::is_valid($criteres, [
	'birthname' => 'sqlIdentiteSearch|max_len,60',
	'lastname' => 'sqlIdentiteSearch|max_len,60',
	'firstname' => 'sqlIdentiteSearch|max_len,60',
]);
if ($is_valid !== true) {
	return;
}

$mss->setCriteresRecherche($criteres);
$mss->setColonnesRetour(['deathdate', 'identite', 'birthdate']);
$mss->setLimitNumber(20);

if ($data = msSQL::sql2tab($mss->getSql(), $mss->getSqlMarqueurs())) {

	if ($p['config']['optionGeActiverUnivTags'] == 'true') {
		$univTagsTypeID = msUnivTags::getTypeIdByName('patients');
		if (!msUnivTags::getIfTypeIsActif($univTagsTypeID)) {
			unset($univTagsTypeID);
		}
	};

	foreach ($data as $k => $v) {

		// Tag universel pour le dossier médical d'un patient
		// permet de récupérer les pastille de couleur pour les afficher dans
		// les résultat de la recherche.
		$tagParams = array();
		$tagParams['circle'] = '';
		if (!empty($univTagsTypeID)) {
			$tagParams = msUnivTags::getList($univTagsTypeID, $v['peopleID'], true);
			$tagParams['circle'] = $tagCircle = msUnivTags::getTagsCircleHtml($tagParams);
		};

		// Si le patient possède des tags le recherche pour afficher la
		// patstille dans les résultat de recherche.
		$a_json[] = array(
			'label' => trim($v['identite']) . ' ' . $v['birthdate'],
			'value' => trim($v['identite']),
			'patientID' => $v['peopleID'],
			'tagParams' => $tagParams,
		);
	}
}

header('Content-Type: application/json');
echo json_encode($a_json);
