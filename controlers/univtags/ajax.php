<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2021      DEMAREST Maxime <maxime@indelog.fr>
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
 * Config : ajax pour gestion des tags universels
 *
 * @author DEMAREST Maxime <maxime@indelog.fr>
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

$action=$match['params']['a'];
$gump = new GUMP('fr');

switch ($action) {

	case 'toggleTypeActif':
		// Seul un administrateur peut désactiver un type de tag
		if (!msUser::checkUserIsAdmin()) returnJson(false, 'Erreur: vous n\'êtes pas administrateur ou autorisé à effectuer cette action');
		$validator = new Gump('fr');
		$gump->validation_rules(array(
			'typeID' => 'required|numeric',
			'actif' => 'required|boolean',
		));
		$data = $gump->run($_POST);
		if ($data) {
			try {
				$newState = msUnivTags::setTypeActif($data['typeID'], filter_var($data['actif'], FILTER_VALIDATE_BOOLEAN));
				returnJson(true, 'Type de tag universel avec ID='.$data['typeID'].' '.($newState ? 'activé' : 'désactivé').'.', array('actif'=>$newState));
			} catch (Exception $e) {
				returnJson(false, $e->getMessage());
			}
		} else {
			returnJson(false, $gump->get_readable_errors(true));
		}
		break;

	case 'newTag':
		$gump->validation_rules(array(
			'name' => 'required|max_len,64',
			'description' => 'required|max_len,256',
			'color' => 'required|regex,/^#[0-9A-Fa-f]{6}$/',
			'typeID' => 'required|numeric',
			'toID' => 'required|numeric',
			'contexte' => 'required|alpha',
		));
		$gump->filter_rules(array(
			'name' => 'trim|sanitize_string',
			'description' => 'trim|sanitize_string',
		));
		$data = $gump->run($_POST);
		if ($data) {
			try {
				if (! msUnivTags::checkTypeDroitCreSup($data['typeID'])) throw new Exception('L\'utilisateur n\'est pas autorisé à effectuer cette action.');
				$univTag = new msUnivTags();
				$newTagTypeID = $univTag->setTypeID($data['typeID']);
				$newTagName = $univTag->setName($data['name']);
				$newTagDescription = $univTag->setDescription($data['description']);
				$newTagColor = $univTag->setColor($data['color']);
				$newTagID = $univTag->create();
				$retData = array(
					'id' => $newTagID,
					'name' => $newTagName,
					'description' => $newTagDescription,
					'color' => $newTagColor,
					'typeID' => $newTagTypeID,
					'toID' => $data['toID'],
					'tagsListHtml' => msUnivTags::getListHtml($newTagTypeID, $data['toID'], $data['contexte']),
				);
				returnJson(true, 'Tag crée', $retData);
			} catch (Exception $e) {
				returnJson(false, $e->getMessage());
			}
		} else {
			returnJson(false, $gump->get_readable_errors(true));
		}
		break;

	case 'editTag':
		$gump->validation_rules(array(
			'id' => 'required|numeric',
			'toID' => 'required|numeric',
			'name' => 'required|max_len,64',
			'description' => 'required|max_len,256',
			'color' => 'required|regex,/^#[0-9A-Fa-f]{6}$/',
			'contexte' => 'required|alpha',
		));
		$gump->filter_rules(array(
			'name' => 'trim|sanitize_string',
			'description' => 'trim|sanitize_string',
		));
		$data = $gump->run($_POST);
		if ($data) {
			try {
				$univTag = new msUnivTags();
				$univTag->fetch($data['id']);
				if (! $univTag->checkDroitCreSup()) throw new Exception('L\'utilisateur n\'est pas autorisé à effectuer cette action.');
				$newTagName = $univTag->setName($data['name']);
				$newTagDescription = $univTag->setDescription($data['description']);
				$newTagColor = $univTag->setColor($data['color']);
				$univTag->update();
				$retData = array(
					'id' => $data['id'],
					'name' => $newTagName,
					'description' => $newTagDescription,
					'color' => $newTagColor,
					'typeID' => $univTag->getTypeID(),
					'toID' => $data['toID'],
					'tagsListHtml' => msUnivTags::getListHtml($univTag->getTypeID(), $data['toID'], $data['contexte']),
				);
				returnJson(true, 'Tag modifié', $retData);
			} catch (Exception $e) {
				returnJson(false, $e->getMessage());
			}
		} else {
			returnJson(false, $gump->get_readable_errors(true));
		}
		break;

	case 'delTag':
		$gump->validation_rules(array(
			'id' => 'required|numeric',
			'toID' => 'required|numeric',
			'contexte' => 'required|alpha',
		));
		$data = $gump->run($_POST);
		if ($data) {
			try {
				$univTag = new msUnivTags();
				$univTag->fetch($data['id']);
				if (! $univTag->checkDroitCreSup()) throw new Exception('L\'utilisateur n\'est pas autorisé à effectuer cette action.');
				$typeID = $univTag->getTypeID();
				$univTag->delete();
				$retData = array(
					'typeID' => $typeID,
					'toID' => $data['toID'],
					'tagsListHtml' => msUnivTags::getListHtml($typeID, $data['toID'], $data['contexte']),
				);
				returnJson(true, 'Tag suprimé', $retData);
			} catch (Exception $e) {
				returnJson(false, $e->getMessage());
			}
		} else {
			returnJson(false, $gump->get_readable_errors(true));
		}
		break;

	case 'getTagsList':
		$gump->validation_rules(array(
			'typeID' => 'required|numeric',
			'toID' => 'required|numeric',
			'contexte' => 'required|alpha',
		));
		$data = $gump->run($_GET);
		if ($data) {
			try {
				$retData = array(
					'typeID' => $data['typeID'],
					'toID' => $data['toID'],
					'contexte' => $data['contexte'],
					'tagsListHtml' => msUnivTags::getListHtml($data['typeID'], $data['toID'], $data['contexte']),
				);
				returnJson(true, 'Liste actualisé', $retData);
			} catch (Exception $e) {
				returnJson(false, $e->getMessage());
			}
		} else {
			returnJson(false, $gump->get_readable_errors(true));
		}
		break;

	case 'getModalTag':
		$gump->validation_rules(array(
			'typeID' => 'required|numeric',
			'tagID' => 'required|numeric',
			'toID' => 'required|numeric',
			'contexte' => 'required|alpha',
		));
		$data = $gump->run($_GET);
		if ($data) {
			try {
				$modalHTML = msUnivTags::getModalHtml($data['typeID'], $data['tagID'], $data['toID'], $data['contexte']);
				$retData = array(
					'typeID' => $data['typeID'],
					'tagID' => $data['tagID'],
					'toID' => $data['toID'],
					'contexte' => $data['toID'],
					'modalHTML' => $modalHTML,
				);
				returnJson(true, 'Modal UnivTag obtenu', $retData);
			} catch (Exception $e) {
				returnJson(false, $e->getMessage());
			}
		} else {
			returnJson(false, $gump->get_readable_errors(true));
		}
		break;

	case 'getSelectForm':
		$gump->validation_rules(array(
			'typeID' => 'required|numeric',
			'toID' => 'required|numeric',
		));
		$data = $gump->run($_GET);
		if ($data) {
			try {
				$selectFormHTML = msUnivTags::getSelectFormHtml($data['typeID'], $data['toID']);
				$retData = array(
					'toID' => $data['toID'],
					'selectFormHTML' => $selectFormHTML,
				);
				returnJson(true, 'Formulaire de séléction obtenus', $retData);
			} catch (Exception $e) {
				returnJson(false, $e->getMessage());
			}
		} else {
			returnJson(false, $gump->get_readable_errors(true));
		}
		break;

	case 'setTagTo':
		$gump->validation_rules(array(
			'toID' => 'required|numeric',
			'tagID' => 'required|numeric',
		));
		$data = $gump->run($_POST);
		if ($data) {
			try {
				$univTag = new msUnivTags();
				$univTag->fetch($data['tagID']);
				if (!$univTag->checkDroitAjoRet()) throw new Exception('L\'utilisateur n\'est pas autorisé à effectuer cette action');
				$toID = $univTag->setTagTo($data['toID']);
				$retData = array(
					'typeID' => $univTag->getTypeID(),
					'tagIDs' => $univTag->getID(),
					'toID' => $toID,
				);
				returnJson(true, 'Tags attaché', $retData);
			} catch (Exception $e) {
				returnJson(false, $e->getMessage());
			}
		} else {
			returnJson(false, $gump->get_readable_errors(true));
		}
		break;

	case 'removeTagTo':
		$gump->validation_rules(array(
			'toID' => 'required|numeric',
			'tagID' => 'required|numeric',
		));
		$data = $gump->run($_POST);
		if ($data) {
			try {
				$univTag = new msUnivTags();
				$univTag->fetch($data['tagID']);
				if (!$univTag->checkDroitAjoRet()) throw new Exception('L\'utilisateur n\'est pas autorisé à effectuer cette action');
				$toID = $univTag->removeTagTo($data['toID']);
				$retData = array(
					'typeID' => $univTag->getTypeID(),
					'tagIDs' => $univTag->getID(),
					'toID' => $toID,
				);
				returnJson(true, 'Étiquette retirée', $retData);
			} catch (Exception $e) {
				returnJson(false, $e->getMessage());
			}
		} else {
			returnJson(false, $gump->get_readable_errors(true));
		}
		break;

	default:
		returnJson(false, 'action non prise en charge');
}
