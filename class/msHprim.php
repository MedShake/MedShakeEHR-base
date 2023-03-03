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
 * Manipulations sur les données HPRIM
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */


class msHprim
{

	/**
	 * Parser le paragraphe HPRIM machine d'un texte
	 * @param  string $texte Le texte à traiter
	 * @return array        Array avec le résultat
	 */
	public static function parseSourceHprim($texte)
	{
		$lab = explode('****LAB****', $texte);
		if (isset($lab['1'])) {
			$lab = trim($lab['1']);
			if (!empty($lab)) {
				$lignes = explode("\n", $lab);
			}
			if (count($lignes) > 0) {
				$r = [];
				foreach ($lignes as $ligne) {
					$l = explode('|', $ligne);
					$l = msTools::utf8_converter($l);
					$l = array_pad($l, 13, '');
					$l = array_map('trim', $l);

					if ($l[0] == 'RES' and strlen($l[1]) > 0) {
						$r[] = array(
							'label' => $l[1],
							'labelStandard' => $l[2],
							'typeResultat' => $l[3],
							'resultat' => $l[4],
							'unite' => $l[5],
							'normaleInf' => $l[6],
							'normaleSup' => $l[7],
							'indicateurAnormal' => $l[8],
							'statutRes' => $l[9],
							'resAutreU' => $l[10],
							'normaleInfAutreU' => $l[11],
							'normalSupAutreU' => $l[12]
						);
					}
				}
			}
			return $r;
		}
	}

	/**
	 * Enregistrer chaque lignee HPRIM dans la bdd
	 * @param  array $tabRes  Array résultat de parseSourceHprim()
	 * @param  int $fromID  ID du user
	 * @param  int $toID    ID du patient concerné
	 * @param  string $date    date au format date mysql (Y-m-d)
	 * @param  int  $objetID ID de l'objet concerné (= ID du document source)
	 * @return void
	 */
	public static function saveHprim2bdd($tabRes, $fromID, $toID, $date, $objetID)
	{
		if (is_array($tabRes)) {
			foreach ($tabRes as $k => $v) {
				$v['fromID'] = $fromID;
				$v['toID'] = $toID;
				$v['date'] = $date;
				$v['objetID'] = $objetID;

				msSQL::sqlInsert('hprim', $v);
			}
		}
	}

	/**
	 * Tenter de trouver si un message contient un entête HPRIM valide a moyens
	 * de plusieurs test.
	 * @param  $array   Tableau contenant les entêtes hprim d'un message
	 *                  obtenus avec self::getHprimHeaderData()
	 * @return bool     `true` si ça resemble à une entête hprim, sinon `false`
	 * @see    self::getHprimHeaderData()
	 */
	public static function checkIfValidHprimHeaderData(array $hprim_data)
	{
		/**
		 * Ce système est une ébauche...
		 * Chaque test permet d'ajouteur et de retirer un certain nombre de
		 * point sur un score total qui permet de définir si un message contient
		 * une entête hprim ou non.
		 */
		$score = 0;
		$score_min  = 10;
		// Les score pour les tests sont évaluer au doit mouillé et devrons
		// sûrement être ajustés.

		// Test si le paramètre codePatient est présent
		if (!empty($hprim_data['codePatient']) || $hprim_data['codePatient'] != '.') {
			// Et que le code patient resemble à un code
			preg_match('/(^[A-z0-9-_\/]*$)/', $hprim_data['codePatient'], $matches);
			$score += (!empty($matches)) ? 3 : -1;
		}
		//var_dump('[codePatient] ' . $hprim_data['codePatient'] . ': ' . $score);

		// TEST Si les nom et prénon sont présent
		$score += (!empty($hprim_data['nom'])) ? +1 : -1;
		//var_dump('[nom] ' . $hprim_data['nom'] . ': ' . $score);
		$score += (!empty($hprim_data['prenom'])) ? +1 : -1;
		//var_dump('[prenom] ' . $hprim_data['prenom'] . ': ' . $score);


		// Test si le code postal ressemble à un code postal si il est présent
		// TODO : Attention, seul les codes postaux français sont pris en compte
		//        (d'ou le score de 1)
		if (!empty($hprim_data['cp']) || $hprim_data['cp'] != '.') {
			preg_match('/(^[0-9]{1}[1-9]{1}[0-9]{3}$)/', $hprim_data['cp'], $matches);
			$score += (!empty($matches)) ? 1 : -1;
		}
		//var_dump('[cp] ' . $hprim_data['cp'] . ' : ' . $score);

		// Test si la date de naissance est saisis et si c'est le cas, test is
		// elle est vallide
		if (!empty($hprim_data['ddn']) || $hprim_data['ddn'] != '.') {
			// TODO : Est-il sûre que la date soit toujours au format 'd/m/Y' ?
			$test_date = DateTime::createFromFormat('d/m/Y', $hprim_data['ddn']);
			if ($test_date && $test_date->format('d/m/Y') == $hprim_data['ddn']) {
				$score += 8;
			} else {
				$score -= 8;
			}
		}
		//var_dump('[ddn] ' . $hprim_data['ddn'] . ' : ' . $score);


		// Si il y a un nss saisis, test si celluis ci possède un format valide
		if (!empty($hprim_data['nss']) || $hprim_data['nss'] != '.') {
			preg_match('/(^[123478]{1}[0-9]{2}[01]{1}[0-9]{1}[0-9]{1}[0-9AB]{1}[0-9]{6}[0-9]{0,2}$)/', $hprim_data['nss'], $matches);
			$score += (!empty($matches)) ? 3 : -3;
		}
		//var_dump('[nss] ' . $hprim_data['nss'] . ' : ' . $score);

		// Test si la date d'expédition du dossier est présente est si c'est le
		// cas, test si elle est valide.
		if (!empty($hprim_data['dateDossier']) || $hprim_data['dateDossier'] != '.') {
			// TODO : Est-il sûre que la date soit toujours au format 'd/m/Y' ?
			$test_date = DateTime::createFromFormat('d/m/Y', $hprim_data['dateDossier']);
			if ($test_date && $test_date->format('d/m/Y') == $hprim_data['dateDossier']) {
				$score += 4;
			} else {
				$score -= 4;
			}
		}
		//var_dump('[dateDossier] ' . $hprim_data['dateDossier'] . ' : ' . $score);

		//var_dump($score);
		return ($score >= $score_min) ? true : false;
	}

	/**
	 * Parser en-tête HPRIM d'un fichier txt
	 * @param  string $file fichier avec chemin complet
	 * @return array       Tableau de résultat
	 */
	public static function getHprimHeaderData($file)
	{
		$file = fopen("$file", "r");
		$count = "0";

		while ($count < 13) {
			$count++;
			switch ($count) {
				case "1":
					$d['codePatient'] = substr(fgets($file), 0, 10);
					break;

				case "2":
					$d['nom'] = substr(fgets($file), 0, 50);
					break;

				case "3":
					$d['prenom'] = substr(fgets($file), 0, 50);
					break;

				case "4":
					$d['adresse1'] = fgets($file);
					if (!empty($d['adresse1']) and is_numeric($d['adresse1'][0])) {
						$explo = explode(' ', $d['adresse1'], 2);
						$d['streetNumber'] = $explo[0];
						$d['street'] = $explo[1];
					}
					break;

				case "5":
					$d['adresse2'] = fgets($file);
					break;

				case "6":
					$line6 = fgets($file);
					$d['cp'] = substr($line6, 0, 5);
					$d['ville'] = substr($line6, 5);
					break;

				case "7":
					$d['ddn'] = trim(fgets($file));
					$test_date = DateTime::createFromFormat('Y-m-d', $d['ddn']);
					// Si date fournis au format Y-m-d, la convertis au format d/m/Y
					if ($test_date && $test_date->format('Y-m-d') == $d['ddn']) {
						$d['ddn'] = $test_date->format('d/m/Y');
					}

					break;

				case "8":
					$d['nss'] = fgets($file);
					if (!empty($d['nss'])) {
						if ($d['nss'][0] == 1) {
							$d['administrativeGenderCode'] = 'M';
						} elseif ($d['nss'][0] == 2) {
							$d['administrativeGenderCode'] = 'F';
						} else {
							$d['administrativeGenderCode'] = 'U';
						}
					} else {
						$d['administrativeGenderCode'] = 'U';
					}
					break;

				case "9":
					$d['numDossier'] = fgets($file);
					break;

				case "10":
					$d['dateDossier'] = trim(substr(fgets($file), 0, 15));
					$test_date = DateTime::createFromFormat('Y-m-d', $d['dateDossier']);
					// Si date fournis au format Y-m-d, la convertis au format d/m/Y
					if ($test_date && $test_date->format('Y-m-d') == $d['dateDossier']) {
						$d['dateDossier'] = $test_date->format('d/m/Y');
					}

					break;

				case "11":
					$line11 = fgets($file);
					$d['codeExp'] = substr($line11, 0, 10);
					$d['expediteur'] = substr($line11, 10);
					break;

				case "12":
					$line12 = fgets($file);
					$d['codeDest'] = substr($line12, 0, 10);
					$d['destinataire'] = substr($line12, 10);
					break;
			}
		}
		$d = array_map('trim', $d);

		return $d;
	}

	/**
	 * Obtenir la liste des patients correspondant aux datas HPRIM
	 * @param  array $hprimData Data HPRIM
	 * @param int $patientID ID du patient
	 * @return array            Array des patients possibles
	 */
	public static function getPossiblePatients($hprimData, $patientID = '')
	{
		$hprimData = array_map('trim', $hprimData);
		$nom = $ddn = $nss = $cp = array('' => '');

		if (is_numeric($patientID)) {

			$final[$patientID] = 2;
		} else {
			$name2typeID = new msData();
			$name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'birthdate', 'birthname', 'postalCodePerso', 'nss']);

			//le nom de famille
			$nom = msSQL::sql2tabSimple("SELECT toID from objets_data where typeID in (:lastname , :birthname ) and value like :valeur and outdated='' and deleted='' ", ['lastname' => $name2typeID['lastname'], 'birthname' => $name2typeID['birthname'], 'valeur' => $hprimData['nom']]);
			//le prenom
			$prenom = msSQL::sql2tabSimple("SELECT toID from objets_data where typeID = :firstname and value like :valeur and outdated='' and deleted=''", ['firstname' => $name2typeID['firstname'], 'valeur' => $hprimData['prenom']]);
			//la ddn
			$ddn = msSQL::sql2tabSimple("SELECT toID from objets_data where typeID = :birthdate and value = :valeur and outdated='' and deleted=''", ['birthdate' => $name2typeID['birthdate'], 'valeur' => $hprimData['ddn']]);
			//n secu
			$nss = msSQL::sql2tabSimple("SELECT toID from objets_data where typeID = :nss and value = :valeur and outdated='' and deleted=''", ['nss' => $name2typeID['nss'], 'valeur' => $hprimData['nss']]);
			//code postal
			$cp = msSQL::sql2tabSimple("SELECT toID from objets_data where typeID = :postalCodePerso and value = :valeur and outdated='' and deleted=''", ['postalCodePerso' => $name2typeID['postalCodePerso'], 'valeur' => $hprimData['cp']]);


			$final = array();
			if (is_array($nom)) {
				$final = array_merge($final, $nom);
			}
			if (is_array($prenom)) {
				$final = array_merge($final, $prenom);
			}
			if (is_array($ddn)) {
				$final = array_merge($final, $ddn);
			}
			if (is_array($nss)) {
				$final = array_merge($final, $nss);
			}
			if (is_array($cp)) {
				$final = array_merge($final, $cp);
			}

			$final = array_count_values($final);

			arsort($final);

			$final = array_slice($final, 0, 5, true);
		}

		foreach ($final as $k => $v) {
			if ($v > 1) {
				$patient = new msPeople();
				$patient->setToID($k);
				$final[$k] = $patient->getSimpleAdminDatasByName();
				$final[$k]['patientType'] = $patient->getType();
				$final[$k]['nbOccurence'] = $v;
				$final[$k]['id'] = $k;
			} else {
				unset($final[$k]);
			}
		}

		return array_values($final);
	}
}
