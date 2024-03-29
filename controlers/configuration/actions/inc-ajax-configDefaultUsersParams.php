<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00>
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
 * Config > action : enregistrer les paramètres globaux de configuration
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */

if (!msUser::checkUserIsAdmin()) {
	die("Erreur: vous n'êtes pas administrateur");
}

$booleans = array(
	'twigEnvironnementAutoescape',
	'twigEnvironnementCache',
	'twigDebug'
);

$toyaml = array(
	'protocol' => '',
	'host' => '',
	'urlHostSuffixe' => '',
	'webDirectory' => '',
	'stockageLocation' => '',
	'backupLocation' => '',
	'workingDirectory' => '',
	'cookieDomain' => '',
	'cookieDuration' => '',
	'fingerprint' => '',
	'sqlServeur' => '',
	'sqlBase' => '',
	'sqlUser' => '',
	'sqlPass' => '',
	'sqlVarPassword' => '',
	'templatesFolder' => '',
	'twigEnvironnementCache' => '',
	'twigEnvironnementAutoescape' => '',
	'twigDebug' => ''
);

foreach ($toyaml as $k => $v) {
	if (isset($_POST[$k])) {
		if (in_array($k, $booleans)) {
			$toyaml[$k] = $_POST[$k] === 'true' ? true : false;
		} else {
			$toyaml[$k] = $_POST[$k];
		}
		unset($_POST[$k]);
	}
}
file_put_contents($p['homepath'] . 'config/config.yml', msYAML::yamlArrayToYaml($toyaml));

$sql = "UPDATE configuration SET value = :value WHERE name = :name AND level = 'default' LIMIT 1";
$stmt = $pdo->prepare($sql);

foreach ($_POST as $name => $value) {
	if (!$stmt->execute(['name' => $name, 'value' => $value])) {
		echo json_encode(array('ko'));
		die();
	}
}

echo json_encode(array('ok'));
