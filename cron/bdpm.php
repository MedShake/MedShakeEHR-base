<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2023
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
 * Cron : met à jour les fichiers de la Base de données publique des médicaments
 *
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib Michaël Val

 */

// pour le configurateur de cron
if (isset($p)) {
	$p['page']['availableCrons']['bdpm'] = array(
		'task' => 'BDPM',
		'defaults' => array('m' => '0', 'h' => '02', 'M' => '*', 'dom' => '*', 'dow' => '*'),
		'description' => 'Met à jour les data de la Base de données publique des médicaments'
	);
	return;
}


ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");
session_start();

if (!empty($homepath = getenv("MEDSHAKEEHRPATH"))) $homepath = getenv("MEDSHAKEEHRPATH");
else $homepath = preg_replace("#cron$#", '', __DIR__);

/////////// Composer class auto-upload
require $homepath . 'vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
	global $homepath;
	include $homepath . 'class/' . $class . '.php';
});

/////////// Config loader
$p['configDefault'] = $p['config'] = msYAML::yamlFileRead($homepath . 'config/config.yml');
$p['homepath'] = $homepath;


/////////// SQL connexion
$pdo = msSQL::sqlConnect();

///// Data à récupérer
$bdpm = msYAML::yamlFileRead($homepath . 'config/bdpm/configBdpm.yml');

$destiRessource = $homepath . 'ressources/bdpm/';
msTools::checkAndBuildTargetDir($destiRessource, 0755);

foreach ($bdpm['dataBdpm'] as $table => $v) {
	$file = '/tmp/' . $v['file'];
	@unlink($file);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $v['url']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	$data = curl_exec($ch);
	
	if (curl_errno($ch)) {
		throw new Exception('Erreur Curl: ' . curl_error($ch));
	}
	curl_close($ch);
	
	// Supprime les lignes vides
	$data = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $data);
	file_put_contents($file, $data);
	if (!msSQL::sqlVerifyTableExist($table)) {
		throw new Exception("La table n'existe pas en base");
	}
	if (trim($v['finligne']) != null) {
		throw new Exception("Fin de ligne invalide");
	}
	if (msSQL::sqlQuery("LOAD DATA INFILE :file REPLACE INTO TABLE `" . $table . "` CHARACTER SET LATIN1 FIELDS TERMINATED BY \"\t\" LINES TERMINATED BY \"" . $v['finligne'] . "\";", ['file' => $file])) {
		$copyDest = $homepath . 'ressources/bdpm/' . $v['file'];
		@unlink($copyDest);
		copy($file, $copyDest);
		msSQL::sqlInsert('bdpm_updates', ['fileName' => $v['file'], 'fileLastParse' => date("Y-m-d H:i:s")]);
	}
}
