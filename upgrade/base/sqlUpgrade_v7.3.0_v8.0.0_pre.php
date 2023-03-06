<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * Passage de version 7.3.0 à version 8.0.0
 *
 * On traite le contenu YAML pour le normaliser, les fonctions natives PHP
 * étant moins tolérantes que la librairie antérieurement utilisée.
 *
 * On prétraite les tables SQL concenées pour éviter les erreurs de PDO liées
 * aux colonnes qui n'ont pas de contenu par défaut.
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

// Actes
msSQL::sqlQuery("ALTER TABLE `actes` CHANGE `label` `label` VARCHAR(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL; ");
msSQL::sqlQuery("ALTER TABLE `actes` CHANGE `details` `details` TEXT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL; ");
msSQL::sqlQuery("ALTER TABLE `actes` CHANGE `fromID` `fromID` SMALLINT(5) UNSIGNED NULL DEFAULT NULL; ");

if ($tabData = msSQL::sql2tabKey("SELECT id, details FROM `actes` WHERE details != '';", 'id', 'details')) {
	foreach ($tabData as $id => $value) {
		if (@yaml_parse($value) === false) {
			$value = Spyc::YAMLLoad($value);
		} else {
			$value = yaml_parse($value);
		}
		msSQL::sqlInsert('actes', ['id' => $id, 'details' => yaml_emit($value, YAML_UTF8_ENCODING, YAML_LN_BREAK)]);
	}
}

// Actes bases
msSQL::sqlQuery("ALTER TABLE `actes_base` CHANGE `code` `code` VARCHAR(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL; ");

if ($tabData = msSQL::sql2tabKey("SELECT id, dataYaml FROM `actes_base` WHERE dataYaml != '';", 'id', 'dataYaml')) {
	foreach ($tabData as $id => $value) {
		if (@yaml_parse($value) === false) {
			$value = Spyc::YAMLLoad($value);
		} else {
			$value = yaml_parse($value);
		}
		msSQL::sqlInsert('actes_base', ['id' => $id, 'dataYaml' => yaml_emit($value, YAML_UTF8_ENCODING, YAML_LN_BREAK)]);
	}
}

// Configuration
msSQL::sqlQuery("ALTER TABLE `configuration` CHANGE `name` `name` VARCHAR(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL; ");

if ($tabData = msSQL::sql2tabKey("SELECT id, `value` FROM `configuration` WHERE name in ('designTopMenuSections', 'dropboxOptions')", 'id', 'value')) {
	foreach ($tabData as $id => $value) {
		if (@yaml_parse($value) === false) {
			$value = Spyc::YAMLLoad($value);
		} else {
			$value = yaml_parse($value);
		}
		msSQL::sqlInsert('configuration', ['id' => $id, 'value' => yaml_emit($value, YAML_UTF8_ENCODING, YAML_LN_BREAK)]);
	}
}

// Data types de type select et radio
msSQL::sqlQuery("ALTER TABLE `data_types` CHANGE `cat` `cat` SMALLINT(5) UNSIGNED NULL DEFAULT NULL");
msSQL::sqlQuery("ALTER TABLE `data_types` CHANGE `name` `name` VARCHAR(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL");

if ($tabData = msSQL::sql2tabKey("SELECT id, formValues FROM `data_types` WHERE formType in ('select', 'radio') and formValues != ''", 'id', 'formValues')) {
	foreach ($tabData as $id => $value) {
		if (@yaml_parse($value) === false) {
			$value = Spyc::YAMLLoad($value);
		} else {
			$value = yaml_parse($value);
		}
		msSQL::sqlInsert('data_types', ['id' => $id, 'formValues' => yaml_emit($value, YAML_UTF8_ENCODING, YAML_LN_BREAK)]);
	}
}

// Forms
msSQL::sqlQuery("ALTER TABLE `forms` CHANGE `internalName` `internalName` VARCHAR(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL;");
msSQL::sqlQuery("ALTER TABLE `forms` CHANGE `name` `name` VARCHAR(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL;");
msSQL::sqlQuery("ALTER TABLE `forms` CHANGE `description` `description` VARCHAR(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL;");

if ($tabData = msSQL::sql2tabKey("SELECT id, yamlStructure FROM `forms` WHERE yamlStructure !=''", 'id', 'yamlStructure')) {
	foreach ($tabData as $id => $value) {
		if (@yaml_parse($value) === false) {
			$value = Spyc::YAMLLoad($value);
		} else {
			$value = yaml_parse($value);
		}
		msSQL::sqlInsert('forms', ['id' => $id, 'yamlStructure' => yaml_emit($value, YAML_UTF8_ENCODING, YAML_LN_BREAK)]);
	}
}

if ($tabData = msSQL::sql2tabKey("SELECT id, cda FROM `forms` WHERE cda !=''", 'id', 'cda')) {
	foreach ($tabData as $id => $value) {
		if (@yaml_parse($value) === false) {
			$value = Spyc::YAMLLoad($value);
		} else {
			$value = yaml_parse($value);
		}
		msSQL::sqlInsert('forms', ['id' => $id, 'cda' => yaml_emit($value, YAML_UTF8_ENCODING, YAML_LN_BREAK)]);
	}
}

if ($tabData = msSQL::sql2tabKey("SELECT id, options FROM `forms` WHERE options !=''", 'id', 'options')) {
	foreach ($tabData as $id => $value) {
		if (@yaml_parse($value) === false) {
			$value = Spyc::YAMLLoad($value);
		} else {
			$value = yaml_parse($value);
		}
		msSQL::sqlInsert('forms', ['id' => $id, 'options' => yaml_emit($value, YAML_UTF8_ENCODING, YAML_LN_BREAK)]);
	}
}
