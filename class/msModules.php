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
 * Modules
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib Michaël Val
 *
 * SQLPREPOK
 */

class msModules
{

	/**
	 * Obtenir une liste des modules installés
	 * @return array array moduleName=>moduleName
	 */
	public static function getInstalledModulesNames()
	{
		return msSQL::sql2tabKey("SELECT name FROM `system` WHERE groupe='module' order by name", "name", "name");
	}

	/**
	 * Obtenir une liste des modules et versions
	 * @return array k=>['module','version']
	 */
	public static function getInstalledModulesNamesAndVersions()
	{
		return msSQL::sql2tab("SELECT name, value AS version FROM `system` WHERE groupe='module'");
	}

	/**
	 * Obtenir une liste des versions des modules
	 * @return array module => 'version'
	 */
	public static function getInstalledModulesVersions()
	{
		if ($r = msSQL::sql2tabKey("SELECT name, value AS version FROM `system` WHERE groupe='module' ", "name", "version")) {
			return $r;
		} else {
			return [];
		}
	}

	/**
	 * Obtenir les infos génériques sur un module à partir du fichier aboutMod*Module*.yml
	 * @param  string $name nom cours du module
	 * @return array       paramètres extraits
	 */
	public static function getModuleInfosGen($name)
	{
		global $p;
		$file = $p['homepath'] . 'aboutMod' . ucfirst($name) . '.yml';
		if (is_file($file)) {
			return msYAML::yamlFileRead($file);
		}
		return [];
	}

    public static function getLatestVersionFromGitHub($name)
    {
        // Obtenir les infos du module
        $moduleInfo = self::getModuleInfosGen($name);
        if (empty($moduleInfo['sources'])) {
            return null; // Retourne null si l'URL du dépôt n'est pas définie
        }

        // Construire l'URL de l'API GitHub
        $repoUrlParts = explode('/', trim($moduleInfo['sources'], '/'));
        if (count($repoUrlParts) < 2) {
            return null; // Retourne null si l'URL n'est pas valide
        }
        $url = "https://api.github.com/repos/" . $repoUrlParts[3] . "/" . $repoUrlParts[4] . "/releases/latest";

        // Initialiser cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0'); // Nécessaire pour l'API GitHub

        // Exécuter la requête
        $response = curl_exec($ch);
        curl_close($ch);

        // Vérifier si la réponse est valide
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['tag_name'])) {
                return $data['tag_name']; // Retourne la dernière version
            }
        }

		 var_dump($data);

        return null; // Retourne null en cas d'erreur
    }


}
