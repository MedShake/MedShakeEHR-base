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
 * Patients > ajax : obtenir le listing des patients ou des pros
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 * @contrib DEMAREST Maxime  <maxime@indelog.fr>
 *
 * SQLPREPOK
 */

$debug = '';

// Si currentNbOfResults vaut plus que 0 alors c'est que nous voulons obtenir
// la suite des résultats. Dans ce cas, il faut seulement retourner le html
// pour les lignes du tableau et non le tableau en entier.
$p['page']['currentNbOfResultsDisplayed'] = filter_var($_POST['currentNbOfResultsDisplayed'], FILTER_VALIDATE_INT) ? (int) $_POST['currentNbOfResultsDisplayed'] : 0;
if ($p['page']['currentNbOfResultsDisplayed'] > 0) {
	$template = 'listing-det';
} else {
	$template = 'listing';
}

// si groupe, on vérifie que l'option générale est ON et on termine sinon
if ($_POST['porp'] == 'groupe' and $p['config']['optionGeActiverGroupes'] != 'true') {
	die();
}

// si registre, on vérifie que l'option générale est ON et on termine sinon
if ($_POST['porp'] == 'registre' and $p['config']['optionGeActiverRegistres'] != 'true') {
	die();
}

if ($_POST['porp'] == 'patient' or $_POST['porp'] == 'externe' or $_POST['porp'] == 'today') {
	$formIN = $p['config']['formFormulaireListingPatients'];

	// liste des documents pouvant être envoyés à la signature par l'utilisateur courant
	$docAsSigner = new msSignatureNumerique;
	$docAsSigner->setFromID($p['user']['id']);
	$p['page']['modelesDocASigner'] = $docAsSigner->getPossibleDocToSign();


	// intégration tags universels pour filtrer sur un patient
	if ($p['config']['optionGeActiverUnivTags'] == 'true') {
		$univTagsTypeID = msUnivTags::getTypeIdByName('patients');
		if (msUnivTags::getIfTypeIsActif($univTagsTypeID)) {
			if (!empty($_POST['univTagsFilter'])) $univTagsFilter = array_column($_POST['univTagsFilter'], 'value');
		} else {
			unset($univTagsTypeID);
		}
	}

	if ($_POST['porp'] == 'externe' && !empty($_POST['externalPatientID'])) $p['page']['externalPatientID'] = $_POST['externalPatientID'];
} elseif ($_POST['porp'] == 'pro') {
	$formIN = $p['config']['formFormulaireListingPraticiens'];

	// intégration tags universels pour filtrer sur un pro
	if ($p['config']['optionGeActiverUnivTags'] == 'true') {
		$univTagsTypeID = msUnivTags::getTypeIdByName('pros');
		if (msUnivTags::getIfTypeIsActif($univTagsTypeID)) {
			if (!empty($_POST['univTagsFilter'])) $univTagsFilter = array_column($_POST['univTagsFilter'], 'value');
		} else {
			unset($univTagsTypeID);
		}
	}
} elseif ($_POST['porp'] == 'groupe') {
	$formIN = $p['config']['formFormulaireListingGroupes'];
} elseif ($_POST['porp'] == 'registre') {
	$formIN = $p['config']['formFormulaireListingRegistres'];
} else {
	die();
}

$p['page']['porp'] = $_POST['porp'];


if ($form = msForm::getFormUniqueRawField($formIN, 'yamlStructure')) {
	$form = Spyc::YAMLLoad($form);

	//all type
	$col = count($form);
	$listeTypes = array();
	$p['page']['outputTableHead'] = array();

	for ($i = 1; $i <= $col; $i++) {
		if (isset($form['col' . $i]['bloc'])) {
			foreach ($form['col' . $i]['bloc'] as $v) {
				$el = explode(',', $v);
				if (is_numeric($el[0])) {
					$name = msData::getNameFromTypeID($el[0]);
					$listeTypes[$name] = $el[0];
				} else {
					$typeID = msData::getTypeIDFromName($el[0]);
					$listeTypes[$el[0]] = $typeID;
					$el[0] = $typeID;
				}
			}
		}
	}
	$listeTypes = array_unique($listeTypes);

	$mss = new msPeopleSearch;

	if ($_POST['porp'] == 'today') {
		$agenda = new msAgenda();
		if ($p['config']['agendaNumberForPatientsOfTheDay']) {
			$agenda->set_userID($p['config']['agendaNumberForPatientsOfTheDay']);
		} else {
			$agenda->set_userID($p['user']['id']);
		}
		$todays = $agenda->getPatientsOfTheDay();
		if (count($todays)) {
			$sqlImplodeCountToday = msSQL::sqlGetTagsForWhereIn(array_column($todays, 'id'), 'todayID');
			$mss->setMarqueursSup($sqlImplodeCountToday['execute']);
			$mss->setWhereClause(" and p.id in (" . $sqlImplodeCountToday['in'] . ") ");
		} else {
			return;
		}
	}


	//patient ou pro en fonction
	if ($_POST['porp'] == 'registre') {
		$mss->setPeopleType(['registre']);
	} elseif ($_POST['porp'] == 'groupe') {
		$mss->setPeopleType(['groupe']);
	} elseif ($_POST['porp'] == 'pro') {
		$mss->setPeopleType(['pro']);
	} elseif ($_POST['porp'] == 'today') {
		$mss->setPeopleType(['pro', 'patient', 'externe']);
		$sqlImplodeCountToday = msSQL::sqlGetTagsForWhereIn(array_column($todays, 'id'), 'todayID');
		$p['page']['extToInt'] = msSQL::sql2tabKey("SELECT od.toID, od.value
		FROM objets_data AS od left join data_types AS dt
		ON od.typeID=dt.id AND od.outdated='' AND od.deleted=''
		WHERE dt.name='relationExternePatient' and od.toID in (" . $sqlImplodeCountToday['in'] . ")", 'toID', 'value', $sqlImplodeCountToday['execute']);
	} elseif (array_key_exists('PraticienPeutEtrePatient', $p['config']) and $p['config']['PraticienPeutEtrePatient'] == 'true') {
		$mss->setPeopleType(['pro', 'patient', 'externe']);
	} else {
		$mss->setPeopleType(['patient', 'externe']);
	}

	//restrictions sur retours
	if ($_POST['porp'] == 'patient' and $p['config']['droitDossierPeutVoirUniquementPatientsPropres'] == 'true') {
		$mss->setRestricDossiersPropres(true);
	} elseif (in_array($_POST['porp'], ['patient', 'pro']) and $p['config']['droitDossierPeutVoirUniquementPatientsGroupes'] == 'true') {
		$mss->setRestricDossiersGroupes(true);
	}

	if (in_array($_POST['porp'], ['pro']) and $p['config']['droitDossierPeutVoirUniquementPraticiensGroupes'] == 'true') {
		$mss->setRestricDossiersPratGroupes(true);
	}

	if ($p['user']['rank'] != 'admin' and $p['config']['droitGroupePeutVoirTousGroupes'] != 'true') {
		$mss->setRestricGroupesEstMembre(true);
	}

	// retrictions forcées sur retours sur l'UI
	if ($_POST['patientsPropres'] == 'true') {
		$mss->setRestricDossiersPropres(true);
	}

	// critères
	if ($_POST['porp'] == 'registre') {
		$criteres = array(
			'registryname' => $_POST['d2'] . '%',
		);
	} elseif ($_POST['porp'] == 'groupe') {
		$criteres = array(
			'groupname' => $_POST['d2'] . '%',
		);
	} else {
		$criteres = array(
			'firstname' => $_POST['d3'],
			'lastname' => $_POST['d2'],
			'birthname' => $_POST['d2']
		);
	}
	if (!empty($_POST['autreCritVal'])) {
		$criteres[$_POST['autreCrit']] = $_POST['autreCritVal'];
	}
	$mss->setCriteresRecherche($criteres);

	$colRetour = array_merge(['deathdate'], array_keys($listeTypes));
	$mss->setColonnesRetour($colRetour);

	// on sort les label correspondant au val si champs select/radio
	$dataGet = new msData;
	$selectConversions = $dataGet->getSelectOptionValueByTypeName($colRetour);

	// si des id de tags pour filtrer la recherche sont présents on les ajoute à la recherche
	if (!empty($univTagsFilter)) $mss->setUnviTagsFilter($univTagsFilter);

	// offet des résultat si on affiche la suite du listing
	$mss->setLimitStart($p['page']['currentNbOfResultsDisplayed']);

	// Obtenir le nombre total de résultat potentiel (utilisé pour la
	// pagination du tableau).
	$p['page']['sqlForTotal'] = $mss->getSql(true);
	$data = msSQL::sqlUniqueChamp($p['page']['sqlForTotal'], $mss->getSqlMarqueurs());
	$p['page']['totalNbOfResultsDisplayed'] = $data ? $data : 0;

	$p['page']['sqlString'] = $mss->getSql();
	if ($data = msSQL::sql2tabKey($p['page']['sqlString'], 'peopleID', '', $mss->getSqlMarqueurs())) {
		for ($i = 1; $i <= $col; $i++) {
			if (isset($form['col' . $i]['bloc'])) {
				foreach ($form['col' . $i]['bloc'] as $v) {
					if (!isset($p['config']['click2callService']) or empty($p['config']['click2callService'])) {
						$v = str_replace(',click2call', '', $v);
					}
					$el = explode(',', $v);
					$id = $el[0];

					//col number for type
					$modele[$id] = $i;
					//separator
					if (isset($form['col' . $i]['blocseparator'])) {
						$separator[$i] = $form['col' . $i]['blocseparator'];
					} else {
						$separator[$i] = ' ';
					}
					//class
					if (count($el) > 0) {
						$classadd[$id] = implode(' ', $el);
					}
				}
			}
			$p['page']['outputTableHead'][$i] = $form['col' . $i]['head'];
		}

		foreach ($data as $k => $v) {
			$row[$k] = array();
			foreach ($v as $l => $w) {
				if (isset($selectConversions[$l][$w])) $w = $selectConversions[$l][$w];
				if (empty($w)) {
					if (isset($modele[$l])) $row[$k][$modele[$l]][] = '';
				} elseif (isset($modele[$l])) {
					if (isset($classadd[$l])) {
						$row[$k][$modele[$l]][] = '<span class="' . $classadd[$l] . '">' . $w . '</span>';
					} else {
						$row[$k][$modele[$l]][] = $w;
					}
				}
			}
			// patient dcd
			if (trim($v['deathdate']) !== '') {
				$data[$v['peopleID']]['type'] = 'dcd';
			}
		}

		foreach ($row as $patientID => $v) {
			foreach ($v as $k => $q) {
				$p['page']['outputTableRow'][$patientID][] = implode($separator[$k], array_filter($q));
				$p['page']['outputType'][$patientID]['type'] = $data[$patientID]['type'];
				$p['page']['outputType'][$patientID]['isUser'] = $data[$patientID]['isUser'];
			}
		}

		// Si les tags universels sont actifs, ajoute une colone sur le tableau afin de voir les tags attachés
		if (!empty($univTagsTypeID) && !empty($p['page']['outputTableHead'])) {
			$p['page']['outputTableHead'][] = 'Étiquettes';

			foreach ($p['page']['outputTableRow'] as $k => $v) {
				$univTagsList = msUnivTags::getList($univTagsTypeID, $k, true);
				$p['page']['outputTableRow'][$k][] = '<span>' . msUnivTags::getTagsCircleHtml($univTagsList) . '</span>';
			}
		}
	}
}
