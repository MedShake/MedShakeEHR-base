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
 * Gestion des mot de suivi
 *
 * @author 2021      DEMAREST Maxime <maxime@indelog.fr>
 * @contrib 2022	 Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msMotSuivi {

    // Table de la base de donné utilisé par la class
    const TABLE = 'motsuivi';

    /**
     * @var	intx			$_id			ID en base du mot suivi
     */
    private $_id;

    /**
     * @var	int				$_fromID		ID de l'utilisteur enregistrant le mot suivi
     */
    private $_fromID;

    /**
     * @var	int				$_toID			ID de l'individus pour le quel est attaché le mot suivi
     */
    private $_toID;

    /**
     * @var	DateTime		$_dateTime		Date et heure du mot suivi
     */
    private $_dateTime;

    /**
     * @var	string			$_texte			Texte du mot suivi
     */
    protected $_texte;

    /**
     * Créer un nouvau mot suivi
     * @return int ID
     */
    public function create(int $fromID, int $toID)
    {
        if(! msPeople::checkPeopleExist($fromID)) throw new Exception('fromID not exist');
        if(! msPeople::checkPeopleExist($toID)) throw new Exception('toID not exist');
        if (empty($this->_dateTime)) throw new Exception('_dateTime non définit');
        if (empty($this->_texte)) throw new Exception('_texte non définit');

        $data = array(
            'fromID' => $fromID,
            'toID' => $toID,
            'dateTime' => $this->_dateTime->format('Y-m-d H:i:s'),
            'texte' => $this->_texte,
        );

        return (int) msSQL::sqlInsert(self::TABLE, $data);
    }

    /**
     * Mettre à jours le texte et la date d'un mot suivi
     * @return mysql_result|bool
     */
    public function update()
    {
        if (empty($this->_id)) throw new Exception('_id non définit');
        if (empty($this->_dateTime)) throw new Exception('_dateTime non définit');
        if (empty($this->_texte)) throw new Exception('_texte non définit');

        $data = array(
            'fromID' => $this->_fromID,
            'dateTime' => $this->_dateTime->format('Y-m-d H:i:s'),
            'texte' => $this->_texte,
        );

        $setFields = array_map(function($k,$v) {global $mysqli; return $k.' = "'.$mysqli->real_escape_string($v).'"';}, array_keys($data), $data);
        $sql = 'UPDATE '.self::TABLE.' SET '.implode(',', $setFields).' WHERE id = '.$this->_id;
        $res = msSQL::sqlQuery($sql);
        if (is_null($res)) throw new Exception('Échec sql update');
        return $res;
    }

    /**
     * Supprimer un mot suivi
     * @return mysqli_result
     */
    public function delete()
    {
        if (empty($this->_id)) throw new Exception('_id non définit');
        $sql = 'DELETE FROM '.self::TABLE.' WHERE `id` = '.$this->_id;
        $res = msSQL::sqlQuery($sql);
        if (is_null($res)) throw new Exception('Échec sql delete');
        return $res;
    }

    /**
     * Retrouver un mot suivi en base de données
     * @param	int		id		identifiant du mot suivi à retourner
     * @return	bool			true si le mot est trouvé sinon false
     */
    public function fetch(int $id) {
        $sql = 'SELECT * FROM '.self::TABLE.' WHERE id = '.$id;
        $res = msSQL::sql2tab($sql);

        // array vide = id non trouvé
        if (empty($res)) return false;

        $this->_id = (int) $res[0]['id'];
        $this->_toID = (int) $res[0]['toID'];
        $this->_fromID = (int) $res[0]['fromID'];
        $this->_texte = (string) $res[0]['texte'];
        $this->_dateTime = new DateTime($res[0]['dateTime']);
        return true;
    }

    /**
     * Retourner l'id créateur
     * @return int
     */
    public function getFromID() {
        if (empty($this->_fromID)) throw new Exception('_fromID non défini');
        return $this->_fromID;
    }

    /**
     * Retourner l'id l'individu pour le quel est attaché le mot suivi
     * @return int
     */
    public function getToID() {
        if (empty($this->_toID)) throw new Exception('_toID non défini');
        return $this->_toID;
    }

    /**
     * Retourner le texte d'un mot suivi
     * @return string
     */
    public function getTexte() {
        if (empty($this->_texte)) throw new Exception('_texte non défini');
        return $this->_texte;
    }

    /**
     * Retourne la date et l'heure du mot suivi sous forme de chaine de caractère au format 'd/m/y H:i'
     * @return string
     */
    public function getDateTime() {
        if (empty($this->_dateTime)) throw new Exception('_dateTime non défini');
        return $this->_dateTime->format('d/m/y H:i');
    }

    /**
     * Définir le texte du mot de suivi
     * @param	string	$texte		Contenu du mot de suivi
     * @return	string				Texte du mot suivi filté
     */
    public function setTexte(string $texte) {
        // Chaine de texte limité à 255 caractères (stockage en base VARCHAR(255))
        $this->_texte = substr(trim(filter_var($texte, FILTER_SANITIZE_STRING)), 0, 255);
        return true;
    }

    /**
     * Définir la date et l'heure du mot de suivi
     * @param	string		$stringDateTime	Date et heure du mot suivi au format 'd/m/y H:i'
     * @return	DateTime					L'heure et la date de création du mot suivi
     */
    public function setDateTime(string $stringDateTime) {
        $dateTime = DateTime::createFromFormat('d/m/Y H:i', $stringDateTime);
        if ($dateTime) {
            $this->_dateTime = $dateTime;
            return $dateTime;
        } else {
            throw new Exception('mauvais format de date et heure');
        }
    }

    /**
     * Obtenir un array des mot de suivi du patient
     * @param	int		$toID		Id de l'individu pour lequel retourner la
     *								liste de mot suivi.
     * @param	string	$nb_elem	Nombre de ligne à retrourner, si 0 c'est la
     *                              valeur du paramètre de configuration
     *                              optionsDossierPatientNbMotSuiviAfficher
     *                              qui sera utilisés si c'est un nombre négatif
     *                              toutes les lignes seront retournées.
     * @return	Array				Tableau contenant la liste des mot de suivi ou null si aucun mot trouvé
     */
    public static function getList(int $toID, int $nb_elem = 0) {
        global $p;
        if (! msPeople::checkPeopleExist($toID)) throw new Exception('individu non existant');
        $dataTypeIDs = msData::getTypeIDsFromName(array('lastname', 'birthname', 'firstname'));
        if ($nb_elem === 0) $nb_elem = (int) $p['config']['optionsDossierPatientNbMotSuiviAfficher'];

        $sql  = 'SELECT ms.id AS ID, ms.fromID AS fromID, DATE_FORMAT(ms.dateTime, \'%d/%m/%Y %H:%i\') AS dateTime, ms.texte AS texte, ln.value AS lastname, bn.value AS birstname, fn.value AS firstname';
        $sql .= ' FROM '.self::TABLE.' AS ms';
        $sql .= ' LEFT JOIN `objets_data` AS ln ON ln.toID = ms.fromID AND ln.typeID = '.$dataTypeIDs['lastname'].' AND ln.outdated = \'\' AND ln.deleted = \'\'';
        $sql .= ' LEFT JOIN `objets_data` AS bn ON bn.toID = ms.fromID AND bn.typeID = '.$dataTypeIDs['birthname'].' AND bn.outdated = \'\' AND bn.deleted = \'\'';
        $sql .= ' LEFT JOIN `objets_data` AS fn ON fn.toID = ms.fromID AND fn.typeID = '.$dataTypeIDs['firstname'].' AND fn.outdated = \'\' AND fn.deleted = \'\'';
        $sql .= ' WHERE ms.toID = '.$toID.' ORDER BY ms.dateTime DESC, id DESC';
        if ($nb_elem >= 0) $sql .= ' LIMIT '.$nb_elem;

        $res = msSQL::sql2tab($sql);
        return $res;
    }

    /**
     * Retourne le html pour le contenus du tableau de la liste des mot suivi.
     * Utilisé pour actualisé le contenus du tableau avec une requette ajax.
     * @param	int		$toID		Id de l'individu pour lequel retourner la
     *								liste de mot suivi.
     * @param	string	$nb_elem	Nombre de ligne à retrourner, si 0 c'est la
     *                              valeur du paramètre de configuration
     *                              optionsDossierPatientNbMotSuiviAfficher
     *                              qui sera utilisés si c'est un nombre négatif
     *                              toutes les lignes seront retournées.
     * @return	Array				Tableau contenant la liste des mot de suivi
     */
    public static function getListHtmlTab(int $toID, int $nb_elem = 0) {
        global $p;
        if (empty($p['page'])) $p['page'] = array();
        $lignes = self::getList($toID, $nb_elem);
        if(is_countable($lignes)) {$nb_lignes = count($lignes);} else {$nb_lignes=0;};
        $total = self::getNbTotal($toID);
        $see_all = ($nb_lignes >= $total);
        $nb_restant = ($total - $nb_lignes);
        $nb_prochain = (int) $p['config']['optionsDossierPatientNbMotSuiviAfficher'];
        $nb_precedent = $nb_lignes + $nb_prochain;
        if ($nb_restant < $nb_prochain) {
            $text_afficher_suivant = 'Afficher tout';
        } else {
            $text_afficher_suivant = 'Afficher les ' . $nb_prochain . ' précédents';
        }
        $p['page']['motSuivi'] = array();
        $p['page']['motSuivi']['lignes'] = $lignes;
        $p['page']['motSuivi']['nbLignes'] = $nb_lignes;
        $p['page']['motSuivi']['total'] = $total;
        $p['page']['motSuivi']['seeAll'] = $see_all;
        $p['page']['motSuivi']['nbProchain'] = $nb_prochain;
        $p['page']['motSuivi']['nbPrecedent'] = $nb_precedent;
        $p['page']['motSuivi']['textAfficherSuivant'] = $text_afficher_suivant;
        $getHtml = new msGetHtml();
        $getHtml->set_template('patientMotSuiviTable');
        return $getHtml->genererHtml();
    }

    /**
     * Obtenir le nombre total de mot de suivis pour le dossier patient.
     * @param	int		$toID		Id de l'individu pour lequel retourner la
     *								liste de mot suivi.
     * @return	int				    Nombre total de mot de suivis.
     */
    public static function getNbTotal(int $toID) {
        global $p;
        $sql  = 'SELECT COUNT(toID) as total FROM `motsuivi` WHERE toID = ' . $toID . ' GROUP BY `toID`';
        $total = (int) msSQL::sqlUniqueChamp($sql);
        return $total;
    }
}
