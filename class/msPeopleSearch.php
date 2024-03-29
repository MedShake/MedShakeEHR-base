<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Recherche des individus ou des utilisateurs
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib DEMAREST Maxime  <maxime@indelog.fr>
 *
 * SQLPREPOK
 */

class msPeopleSearch
{
	private $_peopleType = ['patient'];
	private $_criteresRecherche = [];
	private $_colonnesRetour = [];
	private $_nameSearchMode = 'BnOrLn';
	private $_whereClauses = [];
	private $_marqueurs = [];
	private $_limitStart = 0;
	private $_limitNumber = 50;
	private $_restricDossiersPropres = false;
	private $_restricDossiersGroupes = false;
	private $_restricDossiersPratGroupes = false;
	private $_restricGroupesEstMembre = false;

	/**
	 * @var $_univTagsFilter	Liste des ids de tags sur lesquels filtrer les résultats
	 */
	private $_univTagsFilter = array();

	/**
	 * Définir une restriction pour ne retourner que ses propres dossiers patients
	 * @param bool $restricDossiersPropres true/false
	 */
	public function setRestricDossiersPropres($restricDossiersPropres)
	{
		if (!is_bool($restricDossiersPropres)) throw new Exception('RestricDossiersPropres is not bool');
		return $this->_restricDossiersPropres = $restricDossiersPropres;
	}

	/**
	 * Définir une restriction pour ne retourner que les dossiers créer par praticiens des groupes
	 * auxquels l'utilisateur est affilié
	 * @param bool $restricDossiersGroupes true/false
	 */
	public function setRestricDossiersGroupes($restricDossiersGroupes)
	{
		if (!is_bool($restricDossiersGroupes)) throw new Exception('RestricDossiersPropres is not bool');
		return $this->_restricDossiersGroupes = $restricDossiersGroupes;
	}

	/**
	 * Définir une restriction pour ne retourner que les dossiers praticiens affiliés aux mêmes groupes que l'utilisateur
	 * @param bool $restricDossiersPratGroupes true/false
	 */
	public function setRestricDossiersPratGroupes($restricDossiersPratGroupes)
	{
		if (!is_bool($restricDossiersPratGroupes)) throw new Exception('RestricDossiersPratGroupes is not bool');
		return $this->_restricDossiersPratGroupes = $restricDossiersPratGroupes;
	}

	/**
	 * Définir une restriction de recherche aux groupes auxquels l'utilisateur est membre
	 * @param bool $restricGroupesEstMembre true/false
	 */
	public function setRestricGroupesEstMembre($restricGroupesEstMembre)
	{
		if (!is_bool($restricGroupesEstMembre)) throw new Exception('RestricGroupesEstMembre is not bool');
		return $this->_restricGroupesEstMembre = $restricGroupesEstMembre;
	}

	/**
	 * Définir le tableau de critères de recherche
	 * @param array $criteresRecherche tableau typeName => valeur recherche
	 */
	public function setCriteresRecherche($criteresRecherche)
	{
		if (!is_array($criteresRecherche)) throw new Exception('CriteresRecherche is not an array');
		$this->_criteresRecherche = $criteresRecherche;
	}

	/**
	 * Définir les colonnes à retourner
	 * @param array $colonnesRetour tableau des typeName à retourner
	 */
	public function setColonnesRetour($colonnesRetour)
	{
		if (!is_array($colonnesRetour)) throw new Exception('ColonnesRetour is not an array');
		$this->_colonnesRetour = $colonnesRetour;
	}

	/**
	 * Définir une clause supplémentaire de la condition where
	 * @param string $whereClause clause exprimé en sql
	 */
	public function setWhereClause($whereClause)
	{
		$this->_whereClauses[] = $whereClause;
	}

	/**
	 * Ajouter des marqueurs nommés en utilisation conjointe avec setWhereClause()
	 *
	 * @param [array] $array de la forme marqueur => valeur
	 * @return void
	 */
	public function setMarqueursSup($array)
	{
		$this->_marqueurs = array_merge($this->_marqueurs, $array);
	}

	/**
	 * Définir le type de people défini dans la recherche
	 * @param array $peopleType array des types concernés
	 */
	public function setPeopleType($peopleType)
	{
		$this->_peopleType = $peopleType;
	}

	/**
	 * Définir le mode de recherche goupeBnFnOrLnFn ou goupeBnLn
	 * @param string $nameSearchMode mode de recherche
	 */
	public function setNameSearchMode($nameSearchMode)
	{
		$this->_nameSearchMode = $nameSearchMode;
	}

	/**
	 * Définir le rang du résultat où commencer
	 * @param int $limitStart rang du résultat
	 */
	public function setLimitStart($limitStart)
	{
		if (!is_numeric($limitStart)) throw new Exception('LimitStart is not numeric');
		$this->_limitStart = $limitStart;
	}

	/**
	 * Nombre de résultats à retourner
	 * @param int $limitNumber nombre de résultats
	 */
	public function setLimitNumber($limitNumber)
	{
		if (!is_numeric($limitNumber)) throw new Exception('LimitNumber is not numeric');
		$this->_limitNumber = $limitNumber;
	}

	/**
	 * Définir la liste des ids de tags sur lesquels filtrer les résultats
	 * @param	array	$tagsFilter	Liste des ID de tags sur lesquels filtrer
	 */
	public function setUnviTagsFilter(array $tagsFilter)
	{
		$this->_univTagsFilter = array_filter($tagsFilter, 'is_numeric');
	}

	/**
	 * Obtenir les marqueurs correspondant à la chaine SQL de recherche obtenue via getSql()
	 *
	 * @return array
	 */
	public function getSqlMarqueurs()
	{
		return $this->_marqueurs;
	}

	/**
	 * Obtenir la chaîne SQL de recherche
	 * @param  bool     $only_get_total     Au lieu de retourner la requête sql
	 *                                      de recherche, retourne une requête qui
	 *                                      permet d'obtenir le nombre total de
	 *                                      résultat possible.
	 * @return string                       requête sql
	 */
	public function getSql($only_get_total = false)
	{
		global $p;

		$restrictionUser = '';
		$restricPatientGroupeJoin = '';
		$restricPatientGroupeWhere = '';
		if (in_array('patient', $this->_peopleType)) {
			if ($this->_restricDossiersPropres == true) {
				$restrictionUser .= ' and p.fromID = "' . $p['user']['id'] . '"';
			} elseif ($this->_restricDossiersGroupes == true) {
				$frat = new msPeopleRelations;
				$frat->setToID($p['user']['id']);
				$frat->setRelationType('relationPraticienGroupe');
				if ($groupesUser = $frat->getRelations()) {

					$groupesUser = array_column($groupesUser, 'peopleID');
					$restricPatientGroupeWhere = " and resg.value in ('" . implode("', '", $groupesUser) . "') ";

					if ($this->_restricDossiersPropres == false and $this->_restricDossiersGroupes == true) {
						$restricPatientGroupeJoin = " left join objets_data as resg on resg.toID = p.id and resg.typeID='" . msData::getTypeIDFromName('relationID') . "' and resg.value in ('" . implode("', '", $groupesUser) . "') ";
					}
				} else {
					return $sql = 'SELECT NULL LIMIT 0';
				}
			}
		}

		if (in_array('pro', $this->_peopleType) and $this->_restricDossiersPratGroupes == true) {
			$frat = new msPeopleRelations;
			$frat->setToID($p['user']['id']);
			$frat->setRelationType('relationPraticienGroupe');
			$ids = $frat->getSiblingIDs();
			$ids[] = $p['user']['id'];
			$restrictionUser .= " and (p.id in ('" . implode("', '", $ids) . "') or p.fromID = '" . $p['user']['id'] . "')";
		}

		if (in_array('groupe', $this->_peopleType) and $this->_restricGroupesEstMembre == true) {
			$frat = new msPeopleRelations;
			$frat->setToID($p['user']['id']);
			$frat->setRelationType('relationPraticienGroupe');
			$relations = $frat->getRelations();
			if (!empty($relations)) {
				$ids = array_column($relations, 'peopleID');
				$restrictionUser .= " and p.id in ('" . implode("', '", $ids) . "')";
			} else {
				return $sql = 'SELECT NULL LIMIT 0';
			}
		}

		$orderBy = '';
		if (in_array('identite', $this->_colonnesRetour)) {
			$orderBy = 'order by trim(identite)';
		}

		if ($only_get_total) {
			$sql  = 'SELECT COUNT(p.id) FROM people as p ';
			$sql .= implode(' ', $this->_makeSqlJoin()) . ' ' . $restricPatientGroupeJoin;
			$sql .= 'WHERE p.type IN ("' . implode('", "', $this->_peopleType) . '") AND ' . implode(' AND ', $this->_makeSqlWhere()) . ' ' . $restricPatientGroupeWhere . implode(' ', $this->_whereClauses) . ' ' . $restrictionUser;
		} else {
			$sql = 'select p.type, p.id as peopleID, CASE WHEN LENGTH(TRIM(p.name)) > 0  and LENGTH(TRIM(p.pass)) > 0 THEN "isUser" ELSE "isNotUser" END as isUser,
        ' . implode(', ', $this->_makeSqlSelect()) . '
        from people as p
        ' . implode(' ', $this->_makeSqlJoin()) . ' ' . $restricPatientGroupeJoin . '
        where p.type in ("' . implode('", "', $this->_peopleType) . '") and ' . implode(' and ', $this->_makeSqlWhere()) . ' ' . $restricPatientGroupeWhere . implode(' ', $this->_whereClauses) . ' ' . $restrictionUser . ' ' . $orderBy . '
        limit ' . $this->_limitStart . ',' . $this->_limitNumber;
		}
		return $sql;
	}

	/**
	 * Fabriquer les paramètres SQL select pour la requète finale
	 * @return array
	 */
	private function _makeSqlSelect()
	{
		if (in_array('ageCalcule', $this->_colonnesRetour) and !in_array('birthdate', $this->_colonnesRetour)) {
			$this->_colonnesRetour[] = 'birthdate';
		}

		$name2type = new msData();
		$name2typeID = $name2type->getTypeIDsFromName($this->_colonnesRetour);

		if (in_array('identite', $this->_colonnesRetour)) {
			$name2typeID = array_merge($name2typeID, $name2type->getTypeIDsFromName(['lastname', 'birthname', 'firstname']));
		}

		$sp = [];
		foreach ($this->_colonnesRetour as $v) {

			if ($v == 'identite') {
				$sp[] = 'CASE WHEN d' . $name2typeID['lastname'] . '.value !="" and d' . $name2typeID['birthname'] . '.value !="" THEN concat(COALESCE(d' . $name2typeID['lastname'] . '.value,""), " ", COALESCE(d' . $name2typeID['firstname'] . '.value,""), " (", COALESCE(d' . $name2typeID['birthname'] . '.value,"") ,")")
				WHEN d' . $name2typeID['birthname'] . '.value !="" THEN concat(COALESCE(d' . $name2typeID['birthname'] . '.value,""), " ", COALESCE(d' . $name2typeID['firstname'] . '.value,""))
				WHEN d' . $name2typeID['lastname'] . '.value !="" THEN concat(COALESCE(d' . $name2typeID['lastname'] . '.value,""), " ", COALESCE(d' . $name2typeID['firstname'] . '.value,""))
				ELSE concat("(inconnu) ", COALESCE(d' . $name2typeID['firstname'] . '.value,""))
				END as identite';
			} elseif ($v == 'ageCalcule') {
				if (isset($name2typeID[$v])) $sp[] = '
				CASE WHEN TIMESTAMPDIFF(YEAR, STR_TO_DATE(d' . $name2typeID['birthdate'] . '.value, "%d/%m/%Y"), CURDATE()) >= 1
				THEN
				CONCAT(TIMESTAMPDIFF(YEAR, STR_TO_DATE(d' . $name2typeID['birthdate'] . '.value, "%d/%m/%Y"), CURDATE()), IF(TIMESTAMPDIFF(YEAR, STR_TO_DATE(d' . $name2typeID['birthdate'] . '.value, "%d/%m/%Y"), CURDATE()) > 1, " ans", " an") )
				WHEN TIMESTAMPDIFF(DAY, STR_TO_DATE(d' . $name2typeID['birthdate'] . '.value, "%d/%m/%Y"), CURDATE()) <= 31
				THEN
				CONCAT(TIMESTAMPDIFF(DAY, STR_TO_DATE(d' . $name2typeID['birthdate'] . '.value, "%d/%m/%Y"), CURDATE()), " jours")
				ELSE
				CONCAT(TIMESTAMPDIFF(MONTH, STR_TO_DATE(d' . $name2typeID['birthdate'] . '.value, "%d/%m/%Y"), CURDATE()), " mois")
				END as ' . $v;
			} else {
				if (isset($name2typeID[$v])) {
					$sp[] = 'd' . $name2typeID[$v] . '.value as ' . $v;
				}
			}
		}
		return array_filter($sp);
	}

	/**
	 * Fabriquer les paramètres SQL left join pour la requête finale
	 * @return array
	 */
	private function _makeSqlJoin()
	{
		$tab = array_unique(array_merge(array_keys($this->_criteresRecherche), $this->_colonnesRetour));

		if (in_array('identite', $this->_colonnesRetour)) {
			if (!in_array('lastname', $tab)) $tab[] = 'lastname';
			if (!in_array('birthname', $tab)) $tab[] = 'birthname';
			if (!in_array('firstname', $tab)) $tab[] = 'firstname';
		}

		$name2typeID = new msData();
		$name2typeID = $name2typeID->getTypeIDsFromName($tab);

		foreach ($tab as $v) {
			if (isset($name2typeID[$v])) $sp[$name2typeID[$v]] = 'left join objets_data as d' . $name2typeID[$v] . ' on d' . $name2typeID[$v] . '.toID=p.id and d' . $name2typeID[$v] . '.typeID=' . $name2typeID[$v] . ' and d' . $name2typeID[$v] . '.outdated=\'\' and d' . $name2typeID[$v] . '.deleted=\'\'';
		}

		return $sp;
	}

	/**
	 * Fabriquer les paramètres SQL where pour la requête finale
	 * @return array conditions where
	 */
	private function _makeSqlWhere()
	{
		$name2type = new msData();
		$name2typeID = $name2type->getTypeIDsFromName(array_keys($this->_criteresRecherche));

		if (in_array('identite', $this->_colonnesRetour)) {
			$name2typeID = array_merge($name2typeID, $name2type->getTypeIDsFromName(['lastname', 'birthname', 'firstname']));
			if (!array_key_exists('lastname', $this->_criteresRecherche)) $this->_criteresRecherche['lastname'] = '';
			if (!array_key_exists('birthname', $this->_criteresRecherche)) $this->_criteresRecherche['birthname'] = '';
			if (!array_key_exists('firstname', $this->_criteresRecherche)) $this->_criteresRecherche['firstname'] = '';
		}

		foreach ($this->_criteresRecherche as $k => $v) {
			if (in_array($k, ['birthname', 'lastname']) and $this->_nameSearchMode == 'BnFnOrLnFn') {
				$this->_marqueurs['critBirthname'] = "%" . $this->_criteresRecherche['birthname'] . "%";

				$sp['where'][0] = "(concat(d" . $name2typeID['birthname'] . ".value, ' ', d" . $name2typeID['firstname'] . ".value) like :critBirthname or concat(d" . $name2typeID['lastname'] . ".value, ' ', d" . $name2typeID['firstname'] . ".value) like :critBirthname ) ";
			} elseif (in_array($k, ['birthname', 'lastname']) and $this->_nameSearchMode == 'BnOrLn') {
				$this->_marqueurs['critBirthname'] = $this->_criteresRecherche['birthname'] . "%";
				$this->_marqueurs['critLastname'] = $this->_criteresRecherche['lastname'] . "%";

				$sp['where'][0] = "((d" . $name2typeID['lastname'] . ".value like :critLastname and d" . $name2typeID['lastname'] . ".outdated='') or (d" . $name2typeID['birthname'] . ".value like :critBirthname and d" . $name2typeID['birthname'] . ".outdated='') ) ";
			} else {
				if ($v != null and $k) {
					$this->_marqueurs['crit' . $name2typeID[$k]] = $v . '%';
					$sp['where'][$name2typeID[$k]] = "d" . $name2typeID[$k] . ".value like :crit" . $name2typeID[$k];
				}
			}
		}

		// Ajout du filtre sur les tags universels
		if (!empty($this->_univTagsFilter)) {
			$sqlImplodeTags = msSQL::sqlGetTagsForWhereIn($this->_univTagsFilter, 'tagID');
			$this->_marqueurs = array_merge($this->_marqueurs, $sqlImplodeTags['execute']);
			$this->_marqueurs['nbTotalTags'] = count($this->_univTagsFilter);

			$sp['where'][] .= 'p.id IN (SELECT DISTINCT toID FROM univtags_join AS uj LEFT JOIN univtags_tag AS ut ON ut.id = uj.tagID WHERE tagID in (' . $sqlImplodeTags['in'] . ') GROUP BY toID HAVING(COUNT(tagID)) = :nbTotalTags ) ';
		}
		return $sp['where'];
	}

	/**
	 * Obtenir la liste des utilisateurs
	 * @param  string $orderBy paramètres order by
	 * @return array
	 */
	public static function getUsersList($orderBy = '')
	{
		if (!empty($orderBy) and msSQL::sqlIsValidOrderByString($orderBy)) {
			$orderBy = $orderBy;
		} else {
			$orderBy = 'pp.id';
		}
		$name2typeID = new msData();
		$marqueurs = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'birthname']);

		return msSQL::sql2tab("SELECT pp.id, pp.name, pp.`rank`, pp.module, p.value as prenom, CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom
		from people as pp
		left join objets_data as n on n.toID=pp.id and n.typeID = :lastname and n.outdated='' and n.deleted=''
		left join objets_data as bn on bn.toID=pp.id and bn.typeID = :birthname and bn.outdated='' and bn.deleted=''
		left join objets_data as p on p.toID=pp.id and p.typeID = :firstname and p.outdated=''  and p.deleted=''
		where pp.name!='' and pp.pass!='' order by " . $orderBy, $marqueurs);
	}

	/**
	 * Obtenir l'ID d'un service à partir de son nom
	 * @param  string $name name du service
	 * @return int       ID du service
	 */
	public static function getServiceID($name)
	{
		return msSQL::sqlUniqueChamp("SELECT `id` from `people` where `name` = :name and `type` = 'service' limit 1", ['name' => $name]);
	}

	/**
	 * Méthode alternative de recherche de patient avec retour immédiat
	 * @return array retour people
	 */
	public function getSimpleSearchPeople()
	{
		$data = array_map('trim', $this->_criteresRecherche);

		$name2typeID = new msData();
		$name2typeID = $name2typeID->getTypeIDsFromName(array_keys($this->_criteresRecherche));
		$res = [];
		$final = [];

		foreach ($data as $k => $v) {
			if (!empty($v)) {
				if ($res = msSQL::sql2tabSimple("SELECT toID from objets_data where typeID = :typeID and value like :val and outdated='' and deleted=''", ['val' => $v, 'typeID' => $name2typeID[$k]])) {
					$final = array_merge($final, $res);
				}
			}
		}

		$final = array_count_values($final);
		arsort($final);
		$final = array_slice($final, $this->_limitStart, $this->_limitNumber, true);

		foreach ($final as $k => $v) {
			if ($v >= 1) {
				$patient = new msPeople();
				$patient->setToID($k);
				$peopleType = $patient->getType();
				if (in_array($peopleType, $this->_peopleType)) {
					$final[$k] = $patient->getSimpleAdminDatasByName($this->_colonnesRetour);
					$final[$k]['patientType'] = $peopleType;
					$final[$k]['nbOccurence'] = $v;
					$final[$k]['id'] = $k;
				} else {
					unset($final[$k]);
				}
			} else {
				unset($final[$k]);
			}
		}

		return array_values($final);
	}
}
