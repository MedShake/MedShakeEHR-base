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
 * Gestion des individus : effacer
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */

class msPeopleDestroy extends msPeople
{

	/**
	 * Raison de blocage destruction
	 * @var array
	 */
	private $_blockingReasons = [];
	/**
	 * Autorisation à détruire
	 * @var bool
	 */
	private $_autorisationDestroy = false;

	/**
	 * Obtenir l'autorisation de destruction
	 * @return bool true/false
	 */
	public function getDestroyAutorisation()
	{
		global $p;

		// autorisation générale
		if ($p['config']['optionGeDestructionDataDossierPatient'] != 'true') {
			$this->_blockingReasons[] = "La destruction de dossiers est impossible";
			return $this->_autorisationDestroy = false;
		}

		// autorisation de l'acteur
		$acteur = new msPeopleDroits($this->_fromID);
		if (!$acteur->checkIsUser()) $this->_blockingReasons[] = "L'acteur n'est pas utilisateur";
		if (!$acteur->checkIsAdmin()) $this->_blockingReasons[] = "L'acteur n'est pas administrateur";

		// possibilité du dossier
		$dossier = new msPeopleDroits($this->_toID);
		if ($dossier->checkIsUser()) $this->_blockingReasons[] = "Le dossier à détruire est celui d'un utilisateur";
		if ($dossier->checkIsAdmin()) $this->_blockingReasons[] = "Le dossier à détruire est celui d'un administrateur";
		if ($dossier->checkIsDestroyed()) $this->_blockingReasons[] = "Le dossier est déjà détruit";

		if (empty($this->_blockingReasons)) {
			return $this->_autorisationDestroy = true;
		} else {
			return $this->_autorisationDestroy = false;
		}
	}

	/**
	 * Obtenir les raisons de blocage de destruction
	 * @return array raisons du blocage
	 */
	public function getBlockingReasons()
	{
		return $this->_blockingReasons;
	}

	/**
	 * Détruire un dossier
	 * @return void
	 */
	public function destroyPeopleData()
	{
		if (!is_numeric($this->_toID)) {
			throw new Exception('ToID is not numeric');
		}
		if ($this->_autorisationDestroy != true) {
			throw new Exception('Pas d\'autorisation de destruction');
		}

		$this->_agenda();
		$this->_hprim();
		$this->_inbox();
		$this->_objets();
		$this->_destroyRelations();
		$this->_printed();
		$this->_transmissions();
		$this->_people();
	}

	/**
	 * Destruction des data agenda
	 * @return void
	 */
	private function _agenda()
	{
		if ($rdv = msSQL::sql2tabSimple("SELECT id from agenda where patientid = :patientid", ['patientid' => $this->_toID])) {
			$sqlImplode = msSQL::sqlGetTagsForWhereIn($rdv, 'rdv');
			msSQL::sqlQuery("DELETE from agenda_changelog where eventID in (" . $sqlImplode['in'] . ")", $sqlImplode['execute']);
		}
		msSQL::sqlQuery("DELETE from agenda where patientid = :patientid", ['patientid' => $this->_toID]);
	}

	/**
	 * Destruction des data hprim
	 * @return void
	 */
	private function _hprim()
	{
		msSQL::sqlQuery("DELETE from hprim where toID = :toID", ['toID' => $this->_toID]);
	}

	/**
	 * Destruction des data inbox et des fichiers archivés
	 * @return void
	 */
	private function _inbox()
	{
		global $p;
		if ($msgs = msSQL::sql2tab("SELECT txtFileName, YEAR(txtDatetime) as annee from inbox where assoToID = :assoToID", ['assoToID' => $this->_toID])) {
			foreach ($msgs as $msg) {
				$globStr = $p['config']['apicryptCheminArchivesInbox'] . '*/*{' . $msg['annee'] . ',' . ($msg['annee'] + 1) . '}/*/*/' . pathinfo($msg['txtFileName'],  PATHINFO_FILENAME) . '.*';
				if ($archives = glob($globStr, GLOB_BRACE)) {
					foreach ($archives as $archive) {
						if (is_file($archive)) {
							unlink($archive);
						} elseif (is_dir($archive)) {
							msTools::rmdir_recursive($archive);
						}
					}
				}
			}
		}
		msSQL::sqlQuery("DELETE from inbox where assoToID = :assoToID ", ['assoToID' => $this->_toID]);
	}

	/**
	 * Destruction des data objet et des documents associés
	 * @return void
	 */
	private function _objets()
	{

		$data = new msData();
		$porteursOrdoIds = array_column($data->getDataTypesFromCatName('porteursOrdo', ['id']), 'id');
		$porteursReglementIds = array_column($data->getDataTypesFromCatName('porteursReglement', ['id']), 'id');
		$name2typeID = $data->getTypeIDsFromName(['mailPorteur', 'docPorteur', 'lapOrdonnance']);

		$sqlImplodeOrdo = msSQL::sqlGetTagsForWhereIn($porteursOrdoIds, 'ordo');
		$sqlImplodeReg = msSQL::sqlGetTagsForWhereIn($porteursReglementIds, 'reg');

		$marqueurs = array_merge($name2typeID, $sqlImplodeOrdo['execute'], $sqlImplodeReg['execute']);
		$marqueurs['toID'] = $this->_toID;

		$stock = new msStockage;
		if ($objets = msSQL::sql2tabSimple("SELECT p.id
		from objets_data as p
		left join data_types as t on p.typeID=t.id
		where (t.groupe in ('typeCS', 'courrier')
		or (t.groupe = 'doc' and  t.id = :docPorteur )
		or (t.groupe = 'ordo' and  t.id in (" . $sqlImplodeOrdo['in'] . "))
		or (t.groupe = 'ordo' and  t.id = :lapOrdonnance )
		or (t.groupe = 'reglement' and  t.id in (" . $sqlImplodeReg['in'] . "))
		or (t.groupe='mail' and t.id = :mailPorteur and p.instance = '0'))
		and p.toID = :toID", $marqueurs)) {
			foreach ($objets as $objet) {
				$stock->setObjetID($objet);
				$stock->deleteDoc();
			}
		}
		msSQL::sqlQuery("DELETE from objets_data where toID = :toID ", ['toID' => $this->_toID]);
	}

	/**
	 * Destruction des impressions
	 * @return void
	 */
	private function _printed()
	{
		msSQL::sqlQuery("DELETE from printed where toID = :toID ", ['toID' => $this->_toID]);
	}

	/**
	 * Destruction des transmissions
	 * @return void
	 */
	private function _transmissions()
	{
		if ($trans = msSQL::sql2tabSimple("SELECT id from transmissions where aboutID = :aboutID", ['aboutID' => $this->_toID])) {
			foreach ($trans as $tran) {
				msSQL::sqlQuery("DELETE from transmissions_to where sujetID = :tran ", ['tran' => $tran]);
				msSQL::sqlQuery("DELETE from transmissions where sujetID = :tran or id = :tran ", ['tran' => $tran]);
			}
		}
	}

	/**
	 * Destruction du people (marquage destroyed et log dans marqueur)
	 * @return void
	 */
	private function _people()
	{
		global $p;
		$data = array(
			'id' => $this->_toID,
			'type' => 'destroyed',
		);
		msSQL::sqlInsert('people', $data);

		$value = array(
			'auteurID' => $p['user']['id'],
			'ip' => $_SERVER['REMOTE_ADDR'],
			'date' => date('Y-m-d H-i-s')
		);
		$value = Spyc::YAMLDump($value);

		$marqueur = new msObjet();
		$marqueur->setFromID($p['user']['id']);
		$marqueur->setToID($this->_toID);
		$marqueur->createNewObjetByTypeName('administratifMarqueurDestruction', $value);
	}

	/**
	 * Détruire les relations (enregistrements croisés)
	 * @return void
	 */
	private function _destroyRelations()
	{
		if ($porteurRelationID = msData::getTypeIDFromName('relationID')) {
			if ($ids = msSQL::sql2tabSimple("SELECT id from objets_data where typeID = :porteurRelationID and value = :toID", ['porteurRelationID' => $porteurRelationID, 'toID' => $this->_toID])) {
				foreach ($ids as $id) {
					msSQL::sqlQuery("DELETE from objets_data where id = :id or instance = :id", ['id' => $id]);
				}
			}
		}
	}
}
