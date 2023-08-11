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
 * Compléments pour la validation des data avec GUMP
 * <https://github.com/Wixel/GUMP>
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */



GUMP::add_validator("identite", function ($field, $input, $param = NULL) {
	if (empty($input[$field])) return TRUE;
	$find = preg_match('/^([a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\-\'\ ])+$/i', $input[$field]);
	if ($find != '1') return FALSE;
	else return TRUE;
}, 'Le champ {field} a une mauvaise syntaxe');

GUMP::add_validator("mobilphone", function ($field, $input, $param = NULL) {
	if (empty($input[$field])) return TRUE;
	$find = preg_match('/^0[6-7]{1}(([0-9]{2}){4})|((\s[0-9]{2}){4})|((\xc2\xa0[0-9]{2}){4})|((-[0-9]{2}){4})$/i', $input[$field]);
	if ($find != '1') return FALSE;
	else return TRUE;
}, 'Le champ {field} n\'est pas un numéro de téléphone mobile valide');

GUMP::add_validator("phone", function ($field, $input, $param = NULL) {
	if (empty($input[$field])) return TRUE;
	$find = preg_match('/^0[1-6]{1}(([0-9]{2}){4})|((\s[0-9]{2}){4})|((\xc2\xa0[0-9]{2}){4})|((-[0-9]{2}){4})$/i', $input[$field]);
	if ($find != '1') return FALSE;
	else return TRUE;
}, 'Le champ {field} n\'est pas un numéro de téléphone valide');

GUMP::add_validator("genericPhone", function ($field, $input, $param = NULL) {
	if (empty($input[$field])) return TRUE;
	$find = preg_match('/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.\xc2\xa0.-]*\d{2}){4}$/mix', $input[$field]);
	if ($find != '1') return FALSE;
	else return TRUE;
}, 'Le champ {field} n\'est pas un numéro de téléphone international valide');

GUMP::add_validator("presence_bdd", function ($field, $input, $param = NULL) {
	if (empty($input[$field])) return TRUE;
	if (!msSQL::sqlVerifyTableExist($param[0])) {
		throw new Exception("La table demandée n'existe pas");
	}
	if (!msSQL::sqlValidQueryCols([$field], msSQL::sqlGetColumnNames($param[0]))) {
		throw new Exception("La colonne demandée n'existe pas dans la table");
	}
	if (msSQL::sqlUniqueChamp("SELECT " . $field . " from " . $param[0] . " where " . $field . " = :field limit 1", ['field' => $input[$field]])) return FALSE;
}, 'Le champ {field} contient une valeur déjà utilisée');

GUMP::add_validator("validedate", function ($field, $input, $param = NULL) {
	msTools::validateDate($input[$field], $param[0]);
}, 'Le champ {field} ne contient pas une date valide');

GUMP::add_validator("checkPasswordValidity", function ($field, $input, $param = NULL) {
	if (empty($input[$field])) return FALSE;
	$checkLogin = new msUser;
	return $checkLogin->checkLoginByUserID($param[0], $input[$field]);
}, 'Le champ {field} n\'est pas correct');

GUMP::add_validator("checkPasswordLength", function ($field, $input, $param = NULL) {
	if (empty($input[$field])) return FALSE;
	if (mb_strlen($input[$field]) < PASSWORDLENGTH) return FALSE;
	return TRUE;
}, 'Le champ {field} doit avoir un nombre de caract&#232;res de ' . PASSWORDLENGTH . ' ou plus');

GUMP::add_validator("checkNoName", function ($field, $input, $param = NULL) {
	if (empty($input['p_birthname']) and empty($input['p_lastname'])) return FALSE;
	return TRUE;
}, 'Le champ Nom de naissance et Nom d\'usage ne peuvent être vides en même temps');

GUMP::add_validator("checkUniqueUsername", function ($field, $input, $param = NULL) {
	if (empty($input[$field])) return TRUE;
	if (msSQL::sqlUniqueChamp("SELECT name from people where name = :name limit 1", ['name' => $input[$field]])) return FALSE;
}, 'Ce nom d\'utilisateur est déjà existant');

GUMP::add_validator("checkNotAllEmpty", function ($field, $input, $param = NULL) {
	if (!empty($input[$field])) return TRUE;
	$params = explode(';', $param[0]);
	if (!empty($params)) {
		foreach ($params as $pa) {
			if (!empty($input['p_' . $pa])) return TRUE;
		}
	}
	return FALSE;
}, 'Le champ {field} ne peut être vide en même temps que certains autres');

GUMP::add_validator("max_numeric_current_year", function ($field, $input, $param = NULL) {
	return $input[$field] <= date('Y');
}, 'Le champ {field} ne peut être supérieur à l\'année en cours');

GUMP::add_validator("sqlIdentiteSearch", function ($field, $input, $param = NULL) {
	if (empty($input[$field])) return TRUE;
	$find = preg_match('/^([a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\'\-%_\ ])+$/i', $input[$field]);
	if ($find != '1') return FALSE;
	else return TRUE;
}, 'Le champ {field} a une mauvaise syntaxe');

GUMP::add_validator("sqlSearch", function ($field, $input, $param = NULL) {
	if (empty($input[$field])) return TRUE;
	$find = preg_match('/^([0-9a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ\'\-%_\ ])+$/i', $input[$field]);
	if ($find != '1') return FALSE;
	else return TRUE;
}, 'Le champ {field} a une mauvaise syntaxe');

GUMP::add_validator("alpha_numeric_colon", function ($field, $input, $param = NULL) {
	if (empty($input[$field])) return TRUE;
	$find = preg_match('/^([0-9a-zÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ:])+$/i', $input[$field]);
	if ($find != '1') return FALSE;
	else return TRUE;
}, 'Le champ {field} a une mauvaise syntaxe');
