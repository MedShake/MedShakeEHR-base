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
 * Patients > ajax : marquer un dossier comme à nouveau utilisable
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */

if (is_numeric($_POST['patientID'])) {


	// création d'un marqueur pour sauvegarde de l'info
	// on place en valeur le type du dossier + motif converti en yaml

	$administratifMarqueurSuppressionID = msData::getTypeIDFromName('administratifMarqueurSuppression');
	$value = msSQL::sqlUniqueChamp("SELECT value from objets_data where toID = :toID and typeID = :administratifMarqueurSuppression order by id desc limit 1", ['toID' => $_POST['patientID'], 'administratifMarqueurSuppression' => $administratifMarqueurSuppressionID]);
	$value = Spyc::YAMLLoad($value);

	// on marque le dossier dans people
	$data = array(
		'id' => $_POST['patientID'],
		'type' => $value['typeDossier']
	);
	msSQL::sqlInsert('people', $data);

	// on marque deleted/outdated le marqueur
	msSQL::sqlQuery("UPDATE objets_data set outdated='y', deleted='y', deletedByID = :userID where toID = :patientID and typeID = :administratifMarqueurSuppressionID", ['userID' => $p['user']['id'], 'patientID' => $_POST['patientID'], 'administratifMarqueurSuppressionID' => $administratifMarqueurSuppressionID]);
}
