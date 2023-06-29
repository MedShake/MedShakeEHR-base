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
 *
 * Les termes et conditions de la présente licence GPL v3 ne s’appliquent pas aux composants APICRYPT, fournis par la société APICEM SARL, APICEM Développement ou l’association APICEM, qui restent la propriété exclusive desdites entités. Le code source des composants APICRYPT ne saurait être distribué dans le cadre de la licence du logiciel MedShakeEHR.
 * La réutilisation du code source du logiciel MedShakeEHR à quelques fins que ce soit nécessitera pour le responsable de développements de prendre contact avec la société APICEM SARL afin de procéder à l’établissement d’un contrat de partenariat ainsi qu’à des tests de validité de l’intégration des composants APICRYPT.
 * Le logiciel issu de cette réutilisation ne peut en effet prétendre être « compatible APICRYPT » sans avoir effectué ces démarches préalables et la société APICEM SARL ne saurait être tenue responsable d’éventuels problèmes de réceptions, de traitements ou d’envois de messages au travers de ce logiciel.
 *
 */

/**
 * Méthodes pour l'installation de MedShakeEHR
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


class msInstall
{

	/**
	 * affiche l'aide pour la ligne de commande
	 */
	public static function print_help()
	{
		echo <<< EOT
		Script d'installation en mode ligne de commande pour MedShakeEHR

		Utilisation:
		php ./install.php -R <rootuser> -P <rootpass> -s <sqlhost> -d <database>
							-u <sqluser> -o <sqluserhost> -p <sqlpass> -r <protocol>
							-D <domain> [ -f <urlsuffix> ] [ -S <storpath> ]
							[ -B <backpath> ] [ -n <numport> ]
		php ./install.php -N -u <sqluser> -p <sqlpass> -r <protocol> -D <domain>
							[ -f <urlsuffix> ] [ -S <storpath> ] [ -B <backpath> ]
							[ -n <numport> ]

		-h|--help                           Afficher cette aide
		-R|--sqlrootid    <rootuser>        Nom d'utilisateur root MySQL
		-P|--sqlrootpw    <rootpass>        Mot de passe utilisateur root MySQL
		-N|--sqlnocreatdb                   Ne pas créer la base de données MySQL
		-s|--sqlserver    <sqlhost>         IP du Server MySQL
		-d|--database     <database>        Nom de la base de données MySQL pour
											MedShakeEHR
		-u|--sqluser      <sqluser>         Nom d'utilisateur MySQL pour
											MedShakeEHR
											(seulement si créé à l'avance)
		-o|--sqluserhost  <sqluserhost>     Hôte(s) autorisé(s) pour l'utilisateur
											MySQL MedShakeEHR
		-p|--sqlpass      <sqlpass>         Mot de passe utilisateur MySQL
											pour MedshakeEHR
											(seulement si crée à l'avance)
		-r|--protocol     <protocol>        Protocole utilisé pour la connexion
											MedShakeEHR (http|https)
		-D|--domain       <domain>          Nom de domaine utilisé pour accéder à
											MedShakeEHR ('localhost' par défaut)
		-n|--port         <numport>         Préciser port du serveur web (si différent
											de 80 ou 443)
		-f|--suffix       <urlsuffix>       Suffix url (installation sous dossier web)
											('http' par défaut)
		-S|--storage      <storpath>        Chemin du dossier de stockage
											('stockage' par défaut)
		-B|--backup       <backpath>        Chemin du dossier de sauvegarde
											('backup' par défaut)
		\n
		EOT;
	}

	/**
	 * lecture de arguments de la ligne de commande
	 * @return array paramètres d'installation
	 */
	public static function read_args()
	{
		global $argv;
		$arrParam = array();
		while (!empty($argv)) {
			switch ($argv[0]) {

				case '-R':
				case '--sqlrootid':
					array_shift($argv);
					$arrParam['sqlRootId'] = array_shift($argv);
					break;

				case '-P':
				case '--sqlrootpw':
					array_shift($argv);
					$arrParam['sqlRootPwd'] = array_shift($argv);
					break;

				case '-N':
				case '--sqlnocreatdb':
					array_shift($argv);
					$arrParam['sqlNotCreatDb'] = true;
					break;

				case '-s':
				case '--sqlserver':
					array_shift($argv);
					$arrParam['sqlServeur'] = array_shift($argv);
					break;

				case '-d':
				case '--database':
					array_shift($argv);
					$arrParam['sqlBase'] = array_shift($argv);
					break;

				case '-u':
				case '--sqluser':
					array_shift($argv);
					$arrParam['sqlUser'] = array_shift($argv);
					break;

				case '-o':
				case '--sqluserhost':
					array_shift($argv);
					$arrParam['sqlUserHost'] = array_shift($argv);
					break;

				case '-p':
				case '--sqlpass':
					array_shift($argv);
					$arrParam['sqlPass'] = array_shift($argv);
					break;

				case '-r':
				case '--protocol':
					array_shift($argv);
					$arrParam['protocol'] = array_shift($argv) . '://';
					break;

				case '-D':
				case '--domain':
					array_shift($argv);
					$arrParam['host'] = array_shift($argv);
					break;

				case '-n':
				case '--port':
					array_shift($argv);
					$arrParam['port'] = array_shift($argv);
					break;

				case '-f':
				case '--suffix':
					array_shift($argv);
					$arrParam['urlHostSuffixe'] = array_shift($argv);
					break;

				case '-S':
				case '--storage':
					array_shift($argv);
					$arrParam['stockageLocation'] = array_shift($argv);
					break;

				case '-B':
				case '--backup':
					array_shift($argv);
					$arrParam['backupLocation'] = array_shift($argv);
					break;

				case '-h':
				case '--help':
					self::print_help();
					exit(0);
					break;

				default:
					echo 'Paramètre non reconus ' . $argv[0] . "\n\n";
					self::print_help();
					exit(1);
					break;
			}
		}
		return $arrParam;
	}

	/**
	 * récupère les paramètres d'installation postés par le formulaire
	 * @return array paramètres d'installation
	 */
	public static function get_post()
	{
		$arrParam = array();
		foreach ($_POST as $k => $v) {
			switch ($k) {
				case 'sqlNotCreatDb':
					$arrParam['sqlNotCreatDb'] = true;
					break;
				default:
					$arrParam[$k] = $v;
			}
		}
		return $arrParam;
	}

	/**
	 * vérifier la configuration et créer la base si besoin
	 * @return boolean true if OK, false if KO
	 */
	public static function check_and_create_base_config()
	{
		global $conf, $homepath;
		// Ne pas créer la base de données s'il est précisé qu'on l'a créée en amont
		if (empty($conf['sqlNotCreatDb'])) {

			if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $conf['sqlBase'])) {
				echo "Le nom de base de données n'est pas valide.";
				return false;
			}

			try {
				$pdo = new PDO("mysql:host=" . $conf['sqlServeur'], $conf['sqlRootId'], $conf['sqlRootPwd']);

				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$pdo->exec("SET NAMES utf8");

				$stmt = $pdo->prepare("CREATE USER IF NOT EXISTS :user@:host IDENTIFIED BY :pass");
				$stmt->bindParam(':user', $conf['sqlUser']);
				$stmt->bindParam(':pass', $conf['sqlPass']);
				$stmt->bindParam(':host', $conf['sqlUserHost']);
				$stmt->execute();

				$stmt = $pdo->prepare("CREATE DATABASE IF NOT EXISTS " . $conf['sqlBase'] . " CHARACTER SET = 'utf8mb4'");
				$stmt->execute();

				$stmt = $pdo->prepare("GRANT ALL PRIVILEGES ON " . $conf['sqlBase'] . ".* TO :user@:host");
				$stmt->bindParam(':user', $conf['sqlUser']);
				$stmt->bindParam(':host', $conf['sqlUserHost']);
				$stmt->execute();

				$pdo = null;
			} catch (PDOException $e) {
				echo "Echec de connexion à la base de données.\nVérifiez l'utilisateur et le mot de passe root.\n" . $e->getMessage() . "\n";
				return false;
			}
		} else { // Verifier si la base et l'utilisateur medshake existent
			try {
				$pdo = new PDO("mysql:host=" . $conf['sqlServeur'] . ";dbname=" . $conf['sqlBase'], $conf['sqlUser'], $conf['sqlPass']);
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$pdo = null;
			} catch (PDOException $e) {
				echo "Echec de connexion à la base de données.\nVérifiez vos paramètres de connexion.\n" . $e->getMessage() . "\n";
				return false;
			}
		}

		if (!is_dir($conf['backupLocation'])) {
			if (mkdir($conf['backupLocation'], 0770, true) === false) {
				echo ("Echec lors de la création du dossier " . $conf['backupLocation'] . "<br>Vérifiez que " . get_current_user() . " a les droits d'écriture vers ce chemin.\n");
				return false;
			}
		}
		if (!is_dir($conf['stockageLocation'])) {
			if (mkdir($conf['stockageLocation'], 0770, true) === false) {
				echo ("Echec lors de la création du dossier " . $_POST['stockageLocation'] . "<br>Vérifiez que " . get_current_user() . " a les droits d'écriture vers ce chemin.\n");
				return false;
			}
		}

		if (file_put_contents($homepath . 'config/config.yml', msYAML::yamlArrayToYaml($conf)) === false) {
			echo ("Echec lors de l'écriture du fichier de configuration.\n Vérifiez que " . get_current_user() . " a les droits d'écriture sur le dossier " . $homepath . "config/\n");
			return false;
		}

		return true;
	}

	/**
	 * vérifier les paramètres de la configuration
	 * @configParam   array      Paramètre de configuration pour l'installateur
	 * @return        array      Tableau de message d'erreur
	 */
	// TODO completer cette fonction avec les divers cas pouvant poser problème à l'installation
	public static function check_config_param($params)
	{
		$errMsgs = array();
		// Check protocol
		if (empty($params['protocol']))
			$errMsgs['protocol'] = "Protocol non fournis.";
		elseif ($params['protocol'] <> 'http://' && $params['protocol'] <> 'https://')
			$errMsgs['protocol'] = "Le protocol doit être http ou https.";

		// Paramètres de création de la base de données
		if (!empty($params['sqlNotCreatDb']) && (!empty($params['sqlRootId']) || !empty($params['sqlRootPwd']))) {
			$errMsgs['creatAndNotCreatDB'] = "Ne pas fournir les identifants root pour la création de la base de donnée si l'option pour ne pas la créer est activé.";
		} elseif (empty($params['sqlNotCreatDb']) && (empty($params['sqlRootId']) || empty($params['sqlRootPwd']))) {
			if (empty($params['sqlRootId'])) $errMsgs['sqlRootId'] = "Root id pour la création de la base de donnée absent.";
			if (empty($params['sqlRootPwd'])) $errMsgs['sqlRootPwd'] = "Mot de passe root pour la création de la base de donnée absent.";
		}

		if (empty($params['sqlBase'])) $errMsgs['sqlBase'] = "Nom de base de donnée SQL absent";
		if (empty($params['sqlUser'])) $errMsgs['sqlUser'] = "Nom d'utilisateur SQL absent";
		if (empty($params['sqlUserHost'])) $errMsgs['sqlUserHost'] = "Hote du nom d'utilisateur SQL absent";
		if (empty($params['sqlPass'])) $errMsgs['sqlPass'] = "Mot de passe utilisateur SQL absent";
		if (empty($params['sqlServeur'])) $errMsgs['sqlServeur'] = "Server SQL absent";

		return $errMsgs;
	}
}
