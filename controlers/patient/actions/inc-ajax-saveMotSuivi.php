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
 * Patient > action : ajouter un nouvau mot suivi
 *
 * @author 2021      DEMAREST Maxime <maxime@indelog.fr>
 */

function returnJson(bool $status, string $message, array $data = []) {
	header('Content-Type: application/json');
	header('Cache-Control: no-store');
	$ret = array(
		'status' => $status ? 'ok' : 'error',
		'message' => $message,
		'data' => $data,
	);
    print json_encode($ret);
	exit();
}

if ($p['config']['optionsDossierPatientActiverMotSuivi'] == 'false') {

	$ret_arr['statut'] = 'error';
	$ret_arr['message'] = array("L'option optionsDossierPatientActiverMotSuivi n'est pas activé pour cette utilisateur.");
	returnJson($ret_arr, "L'option optionsDossierPatientActiverMotSuivi n'est pas activé pour cette utilisateur.");

}

$tabReturn = array();
$gump = new GUMP('fr');
$fromID = $p['user']['id'];
$motSuivi = new msMotSuivi();
$data = Array();

switch ($_POST['action']) {
	case 'create':
		$gump->validation_rules(array(
			'toID' => 'required|numeric',
			'dateTime' => 'required|date,d/m/Y H:i',
			'texte' => 'required',
		));
		$gump->filter_rules(array(
			'texte' => 'trim|sanitize_string',
		));
		$validated_data = $gump->run($_POST);
		if ($validated_data) {
			try {
				$motSuivi->setTexte($validated_data['texte']);
				$motSuivi->setDateTime($validated_data['dateTime']);
				$motSuivi->create($fromID, $validated_data['toID']);
				$data['html'] = msMotSuivi::getListHtmlTab($validated_data['toID']);
				returnJson(true, 'Mot suivi crée.', $data);
			} catch (Exception $e) {
				returnJson(false, $e->getMessage());
			}
		} else {
			returnJson(false, $gump->get_readable_errors(true));
		}
		break;
	case 'update':
		$gump->validation_rules(array(
			'ID' => 'required|numeric',
			'dateTime' => 'required|date,d/m/Y H:i',
			'texte' => 'required',
		));
		$gump->filter_rules(array(
			'texte' => 'trim|sanitize_string',
		));
		$validated_data = $gump->run($_POST);
		if ($validated_data) {
			try {
				$motSuivi = new msMotSuivi;
				if (! $motSuivi->fetch($validated_data['ID'])) throw new Exception('mot suivi non existant');
				$toID = $motSuivi->getToID();
				$fromID = $motSuivi->getFromID();
				// Si l'utilisateur n'a pas le droit de modififier un mot suivi qui n'est pas le sien
				if ($p['user']['id'] != $fromID && !filter_var($p['config']['droitMotSuiviPeutModifierSuprimerDunAutre'], FILTER_VALIDATE_BOOLEAN))
					returnJson(false, 'Cette utilisateur ne peut pas modifier un mot suivi qui n\'est pas le sien');
				$motSuivi->setTexte($validated_data['texte']);
				$motSuivi->setDateTime($validated_data['dateTime']);
				$motSuivi->update();
				$data['html'] = msMotSuivi::getListHtmlTab($toID);

				returnJson(true, 'Mot suivi modifié.', $data);
			} catch (Exception $e) {
				returnJson(false, $e->getMessage());
			}
		} else {
			returnJson(false, $gump->get_readable_errors(true));
		}
		break;
	case 'delete':
		$gump->validation_rules(array('ID' => 'required|numeric'));
		$validated_data = $gump->run($_POST);
		if ($validated_data) {
			try {
				$motSuivi->fetch($validated_data['ID']);
				$toID = $motSuivi->getToID();
				$fromID = $motSuivi->getFromID();
				// Si l'utilisateur n'a pas le droit de modififier un mot suivi qui n'est pas le sien
				if ($p['user']['id'] != $fromID && !filter_var($p['config']['droitMotSuiviPeutModifierSuprimerDunAutre'], FILTER_VALIDATE_BOOLEAN))
					returnJson(false, 'Cette utilisateur ne peut pas suprimer un mot suivi qui n\'est pas le sien');
				$motSuivi->delete();
				$data['html'] = msMotSuivi::getListHtmlTab($toID);
				returnJson(true, 'Mot suivi suprimé.', $data);
			} catch (Exception $e) {
				returnJson(false, $e->getMessage());
			}
		} else {
			returnJson(false, $gump->get_readable_errors(true));
		}
		break;
	case 'list':
		$gump->validation_rules(array(
			'toID' => 'required|numeric',
			'getAll' => 'required|boolean',
		));
		$validated_data = $gump->run($_POST);
		if ($validated_data) {
			try {
				$data['html'] = msMotSuivi::getListHtmlTab($validated_data['toID'], (filter_var($validated_data['getAll'], FILTER_VALIDATE_BOOLEAN) ? -1 : 0));
				returnJson(true, 'Liste actualisé.', $data);
			} catch (Exception $e) {
				returnJson(false, $e->getMessage());
			}
		} else {
			returnJson(false, $gump->get_readable_errors(true));
		}
	default:
		returnJson(false, 'action non prise en charge');
}
