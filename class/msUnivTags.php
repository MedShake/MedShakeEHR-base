<?php
/*
 * This file is part of MedShakeEHR.
 * http://www.medshake.net
 *
 * Copyright (c) 2021     DEMAREST Maxime <maxime@indelog.fr>
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
 * @author	DEMAREST Maxime <maxime@indelog.fr>
 * @brief	Class pour gérer les tags universels.
 * @details	Les tags universels permettent de disposer d'une fonction
 *			d'étiquetage commune à tous les éléments de MedShakeEHR la plus
 *			indépendante possible du fonctionnement des éléments tagués
 *			permettant de grouper facilement ces éléments ainsi que de
 *			filtrer des résultats de recherches sur ceux-ci.
 *			Chaque type d'élément pouvant être tagué est identifié à
 *			l'aide de type de tags défini dans la table indiqué par la
 *			constante de class `TABLE_TYPE`. Par exemple un type de tag
 *			peut être défini pour correspondre aux patients alors qu'un
 *			autre sera définit pour à un pro ou encore à un rendez-vous de
 *			l'agenda. Chaque type de tags peut être indépendamment activé
 *			ou désactivé. Chaque type de tag possède ses propres
 *			droits indiqués par les champs suivants de la table des types de
 *			tags :
 *				`droitCreSup` =>	Pour déterminer si un utilisateur peut
 *									créer, supprimer ou modifier un tag pour
 *									un type donné.
 *				`droitAjoRet` =>	Pour déterminer si un utilisateur peut
 *									ajouter ou retirer un tag sur un élément.
 *			Chaque droit indiqué doit correspondre à un élément de type "Droit"
 *			dans les paramètres de configuration.
 *			Les tags sont liés à un élément via la table décrite dans la
 *			constante de class `TABLE_JOIN`. La liaison se fait entre l'id
 *			du tag et l'id de l'objet. La différenciation entre les
 *			différents types d'objet lié se fait à l'aide de l'id du type de
 *			tag.
 *
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */

class msUnivTags
{


	/**
	 * @const TABLE_TAGS Nom de la table pour le stockage des tags universel.
	 */
	const TABLE_TAGS = 'univtags_tag';
	/**
	 * @const TABLE_TYPE Nom de la table utilisé pour la description des
	 *		  types de tags universel.
	 */
	const TABLE_TYPE = 'univtags_type';
	/**
	 * @const TABLE_JOIN Nom de la table utilisé pour stoquer la
	 *		  corespondant d'un tag avec un élément.
	 */
	const TABLE_JOIN = 'univtags_join';

	/**
	 * @var $_id Indentifiant unique du tag.
	 */
	private $_id;

	/**
	 * @var $_typeID Id du type de tag.
	 */
	private $_typeID;

	/**
	 * @var $_typeName Nom du type de tag.
	 */
	private $_typeName;

	/**
	 * @var $_name Nom du tag.
	 */
	private $_name;

	/**
	 * @var $_description Description du tag.
	 */
	private $_description;

	/**
	 * @var $_color Couleur du tag au format hexadécimal (#FFFFFF).
	 */
	private $_color;

	/**
	 * @var $_droitCreSup Nom du droit dans la configuration Medshake pour
	 *                    créer et supprimer les tags en fonction leurs type.
	 */
	private $_droitCreSup;

	/**
	 * @var $_droitAjoRet Nom du droit dans la configuration Medshake pour
	 *                    ajouter et retier un tag sur un élément en fonction
	 *                    de son type.
	 */
	private $_droitAjoRet;

	/**
	 * Vérifier si un tag ne possède pas déjà un nom pour un même typeId
	 * (le name doit être unique pour un typeID donnée).
	 * @return	bool	`true` si le nom existe autrement `false`
	 */
	private function checkTypeNameExist()
	{
		if (empty($this->_typeID)) throw new Exception(__METHOD__ . ' : $this->_typeID non définit');
		if (empty($this->_name)) throw new Exception(__METHOD__ . ' : $this->_name non définit');

		$sql = 'SELECT `id` FROM ' . self::TABLE_TAGS . ' WHERE `name` = :name AND `typeID` = :typeID';
		$marqueurs = ['name' => $this->_name, 'typeID' => $this->_typeID];

		// Si le tag possède une id, on update, if faut donc exclure son propre
		// nom de la liste.
		if (!empty($this->_id)) {
			$sql .= ' AND `id` != ' . $this->_id;
			$marqueurs['thisID'] = $this->_id;
		}
		$resql = msSQL::sqlQuery($sql, $marqueurs);
		if (empty($resql)) throw new Exception(__METHOD__ . ' : erreur sql sur la vérification de l\'exisance du typeID');
		return (count($resql->fetchAll(PDO::FETCH_ASSOC)) > 0);
	}

	/**
	 * Céer un nouveau TAG en fonction des propriétés définies.
	 * @return Array	Tableau des propriétés du tag nouvellement créé.
	 */
	public function create()
	{
		if (empty($this->_typeID)) throw new Exception(__METHOD__ . ' : $this->_typeID non définit');
		if (empty($this->_name)) throw new Exception(__METHOD__ . ' : $this->_name non définit');
		if (empty($this->_description)) throw new Exception(__METHOD__ . ' : $this->_description non définit');
		if (empty($this->_color)) throw new Exception(__METHOD__ . ' : $this->_color non définit');

		// Vérifie si aucun autre tag commporte le name avec ce typeID
		if ($this->checkTypeNameExist()) throw new Exception('Une étiquette portant déjà ce nom existe pour ce type.');

		$data = array(
			'typeID' => $this->_typeID,
			'name' => $this->_name,
			'description' => $this->_description,
			'color' => $this->_color,
		);
		$res = msSQL::sqlInsert(self::TABLE_TAGS, $data);
		if (!$res) throw new Exception(__METHOD__ . ' : échec insersion SQL');
		return (int) $res;
	}

	/**
	 * Met à jour un tags en fonction de ses propriétés.
	 * @return	Array	Nouvelle propriété du tag.
	 */
	public function update()
	{
		if (empty($this->_id)) throw new Exception(__METHOD__ . ' : $this->_id non définit');

		// Vérifie si aucun autre tag commporte le name avec ce typeID
		if ($this->checkTypeNameExist()) throw new Exception('Une étiquette portant déjà ce nom existe pour ce type.');

		$data = array(
			'name' => $this->_name,
			'description' => $this->_description,
			'color' => $this->_color,
		);

		$setFields = array_map(function ($k, $v) {
			return $k . ' = :' . $k;
		}, array_keys($data), $data);

		$marqueurs = $data;
		$marqueurs['id'] = $this->_id;

		$sql = 'UPDATE ' . self::TABLE_TAGS . ' SET ' . implode(',', $setFields) . ' WHERE id = :id';
		$resql = msSQL::sqlQuery($sql, $marqueurs);

		if (empty($resql)) throw new Exception(__METHOD__ . ' : erreur sql update');
		return $resql;
	}

	/**
	 * Supprime le tag instancié.
	 * @return	void
	 */
	public function delete()
	{
		if (empty($this->_id)) throw new Exception(__METHOD__ . ' : $this->_id non définit');

		$sql = 'DELETE FROM ' . self::TABLE_JOIN . ' WHERE tagID = :id';
		$resql = msSQL::sqlQuery($sql, ['id' => $this->_id]);
		if (!$resql) throw new Exception(__METHOD__ . ' : erreur sql supression liaison tags');

		$sql = 'DELETE FROM ' . self::TABLE_TAGS . ' WHERE id = :id';
		$resql = msSQL::sqlQuery($sql, ['id' => $this->_id]);
		if (empty($resql)) throw new Exception(__METHOD__ . ' : erreur sql supression tags');
		unset($this->_id);
		return true;
	}

	/**
	 * Récupére les propriétés d'un tag avec son ID.
	 * @param	int		$id		ID du tag.
	 * @return	Array			Tableau avec les propriétés du tag.
	 */
	public function fetch(int $id)
	{
		$sql  = 'SELECT tag.id AS id, tag.name AS name, tag.description AS description, tag.color AS color, type.id AS typeID, type.name AS typeName, type.droitCreSup AS droitCreSup, type.droitAjoRet AS droitAjoRet';
		$sql .=	' FROM ' . self::TABLE_TAGS . ' AS tag LEFT JOIN ' . self::TABLE_TYPE . ' AS type ON type.id = tag.typeID';
		$sql .= ' WHERE tag.id = :id';

		$resql = msSQL::sqlUnique($sql, ['id' => $id]);
		if (empty($resql)) throw new Exception(__METHOD__ . ' : échec select sql');

		$this->_id = $resql['id'];
		$this->_name = $resql['name'];
		$this->_description = $resql['description'];
		$this->_color = $resql['color'];
		$this->_typeID = $resql['typeID'];
		$this->_typeName = $resql['typeName'];
		$this->_droitCreSup = $resql['droitCreSup'];
		$this->_droitAjoRet = $resql['droitAjoRet'];

		return $resql;
	}

	/**
	 * Défini le typeID du tag.
	 * @param	int		$typeID		ID du type.
	 * @return	int					Type ID défini.
	 */
	public function setTypeID(int $typeID)
	{
		// test si le type existe
		if (!self::checkTypeExist($typeID)) throw new Exception(__METHOD__ . ' : typeID=' . $typeID . ' inexistant');
		$this->_typeID = $typeID;
		return $this->_typeID;
	}

	/**
	 * Défini le nom du tag.
	 * @param	string		$name		Nom du tag.
	 * @return	string					Le nom du tag défini.
	 */
	public function setName(string $name)
	{
		if (empty($name)) throw new Exception(__METHOD__ . ' : $name ne doit pas être vide.');
		$this->_name = trim(strip_tags($name));
		return $this->_name;
	}

	/**
	 * Défini la description du tag.
	 * @param	string		$description	Description du tag.
	 * @return	string						La description du tag définit.
	 */
	public function setDescription(string $description)
	{
		if (empty($description)) throw new Exception(__METHOD__ . ' : $description ne doit pas être vide.');
		$this->_description = trim(strip_tags($description));
		return $this->_description;
	}

	/**
	 * Défini la couleur du tag.
	 * @param	string		$color		Couleur au format héxadécimal
	 *									(#FFFFFF).
	 * @return	string					La couleur défini.
	 */
	public function setColor(string $color)
	{
		if (!filter_var($color, FILTER_VALIDATE_REGEXP, array('options' => ["regexp" => '/^#[0-9A-Fa-f]{6}$/']))) {
			throw new Exception(__METHOD__ . ' : couleur=' . $color . ' n\'est pas dans le format de coleur attendus (/^#[0-9A-Fa-f]{6}$/)');
		}
		$this->_color = strtoupper($color);
		return $this->_color;
	}

	/**
	 * Retourne l'id d'un tag.
	 * @return	int			ID du tag.
	 */
	public function getID()
	{
		if (empty($this->_id)) throw new Exception(__METHOD__ . ' : $this->_id non définit');
		return $this->_id;
	}

	/**
	 * Retroune le typeID du tag.
	 * @return	int			typID du tag.
	 */
	public function getTypeID()
	{
		if (empty($this->_typeID)) throw new Exception(__METHOD__ . ' : $this->_typeID non définit');
		return $this->_typeID;
	}

	/**
	 * Retourne le nom du tag.
	 * @return	string		name du tag.
	 */
	public function getName()
	{
		if (empty($this->_name)) throw new Exception(__METHOD__ . ' : $this->_name non définit');
		return $this->_name;
	}

	/**
	 * Retroune la description du tag.
	 * @return	string		description du tag.
	 */
	public function getDescription()
	{
		if (empty($this->_description)) throw new Exception(__METHOD__ . ' : $this->_description non définit');
		return $this->_description;
	}

	/**
	 * Retoure la couleur en héxadécimal du TAG.
	 * @return	string		couleur du tag en héxadécimal (#FFFFFF).
	 */
	public function getColor()
	{
		if (empty($this->_color)) throw new Exception(__METHOD__ . ' : $this->_color non définit');
		return $this->_color;
	}

	/**
	 * Obtenir un liste de tags pour un type et un possesseur.
	 * @param	int		$typeID		Id du type de tag pour lequel la liste.
	 *								doit être récupérée.
	 * @param	int		$toID		Id du possesseur du tag. Dépend du typeID.
	 *								Si `0` la liste générale pour le type est
	 *								obtenue.
	 * @param	bool	$onlyTo		Si vrai uniquement les tags du possesseur
	 *								selon son type id sont retourné, autrement
	 *								tous les tags pour le typeID sont retournés.
	 * @return  array				Tableau où chaque élément représente un tag
	 *								et ses propriétés. Les propriétés retournées
	 *								pour chaque tag sont :
	 *									'id' =>				Id du tag.
	 *									'name' =>			Nom du tag.
	 *									'description' =>	Description du tag.
	 *									'color' =>			Couleur hexadécimale
	 *														du tag.
	 *									'typeID' =>			ID du type de tag.
	 *									'typeName' =>		Nom du type de tag.
	 *									'toID' =>			ID du propriétaire
	 *														du tag relative à
	 *														son type. Sera `null`
	 *														si le paramètre $toID
	 *														vaut `0`.
	 *									'textcolor' =>		Couleur pour le texte
	 *														du tag (noire ou blanc)
	 *														dépendante de la couleur
	 *														du tag.
	 */
	public static function getList(int $typeID, int $toID, bool $onlyTo  = false)
	{
		if (!self::checkTypeExist($typeID)) throw new Exception(__METHOD__ . ' : type non existant typeID=' . $typeID);

		$sql  = 'SELECT tag.id AS id, tag.name AS name, tag.description AS description, tag.color AS color, type.id AS typeID, type.name AS typeName, uj.toID AS toID';
		$sql .=	' FROM ' . self::TABLE_TAGS . ' AS tag LEFT JOIN ' . self::TABLE_TYPE . ' AS type ON type.id = tag.typeID';
		$sql .= ' LEFT JOIN ' . self::TABLE_JOIN . ' AS uj ON uj.tagID = tag.id AND uj.toID = :toID';
		$sql .= ' WHERE tag.typeID = :typeID';
		if ($toID > 0 && $onlyTo) $sql .= ' AND uj.toID = :toID';
		$sql .= ' ORDER BY tag.id ASC';

		$res = msSQL::sql2tab($sql, ['toID' => $toID, 'typeID' => $typeID]);

		if (is_array($res) && count($res) > 0) {
			for ($i = 0; $i < count($res); $i++) {
				$res[$i]['textcolor'] = self::tagTextColor($res[$i]['color']);
			}
		}
		return (is_null($res)) ? array() : $res;
	}

	/**
	 * Tag un élément
	 * @param	int		$toID		ID de l'élément sur lequel ajouter le tag.
	 * @return	int					ID de l'élément sur lequel à été ajouté
	 *								le tag.
	 */
	public function setTagTo(int $toID)
	{
		if (empty($this->_id)) throw new Exception(__METHOD__ . ' : $this->_id non définit');
		$data = array(
			'tagID' => $this->_id,
			'toID' => $toID,
		);
		$res = msSQL::sqlInsert(self::TABLE_JOIN, $data);
		if (empty($res)) throw new Exception(__METHOD__ . ' : Échec de l\'insertion SQL');
		return $toID;
	}

	/**
	 * Retirer le tag d'un élément
	 * @param	int		$toID		ID de l'élément sur lequel retirer le tag.
	 * @return	int					ID de l'élément sur lequel le tag à été
	 *								retiré.
	 */
	public function removeTagTo(int $toID)
	{
		if (empty($this->_id)) throw new Exception(__METHOD__ . ' : $this->_id non définit');
		$sql = 'DELETE FROM ' . self::TABLE_JOIN . ' WHERE `tagID` = :tagID AND `toID` = :toID';

		msSQL::sqlQuery($sql, ['tagID' => $this->_id, 'toID' => $toID]);
		return $toID;
	}

	/**
	 * Retourne si l'utilisateur peut modifier ou supprimer le tag (dépendament
	 * du paramètre de configuraton défini pour ce type de tag).
	 * @return	bool	`true` si l'utilisateur peut modifier ou supprimer le tag
	 *					`false` si non.
	 */
	public function checkDroitCreSup()
	{
		if (empty($this->_id)) throw new Exception(__METHOD__ . ' : $this->_id non définit');
		global $p;
		return filter_var($p['config'][$this->_droitCreSup], FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Retourne si l'utilisateur peut ajouter ou retirer le tag (dépendament
	 * du paramètre de configuraton défini pour ce type de tag) d'un élément.
	 * @return	bool	`true` si l'utilisateur peut ajouter ou retirer le tag
	 *					d'un élément, `false` si non.
	 */
	public function checkDroitAjoRet()
	{
		if (empty($this->_id)) throw new Exception(__METHOD__ . ' : $this->_id non définit');
		global $p;
		return filter_var($p['config'][$this->_droitAjoRet], FILTER_VALIDATE_BOOLEAN);
	}

	/********************************
	 * Méthodes de gestion des types
	 *******************************/

	/**
	 * Obtenir la liste des types de tags.
	 * @param	bool	$onlyActif		Si vrai, seulement la liste des types
	 *									actifs sera retournée.
	 * @return	array					Tableau avec la liste des types de tags.
	 *									Chaque élément du tableau comporte
	 *									les propriétés suivantes :
	 *										'id'			=> ID du type.
	 *										'description'	=> Description du type.
	 *										'actif'			=> 1 si actif autrement 0
	 *										'droitCreeSup'	=> nom du paramètre de
	 *														   configuration déterminant
	 *														   si un utilisateur peut
	 *														   créer, supprimer, modifier
	 *														   un tag de ce type.
	 *										'droitAjoRet'	=> nom du paramètre de
	 *														   configuration déterminant
	 *														   si un utilisateur peut
	 *														   ajouter ou retirer
	 *														   un tag de ce type.
	 */
	public static function getTypeList($onlyActif = false)
	{
		$sql = 'SELECT `id`, `name`, `description`, `actif`, `droitCreSup`, `droitAjoRet` FROM ' . self::TABLE_TYPE;
		if ($onlyActif) $sql .= ' WHERE `actif`';
		$res = msSQL::sql2tab($sql);
		return $res;
	}

	/**
	 * Obtenir le nom d'un type de tag avec son ID.
	 * @param	int		$typeID		ID du type de tag.
	 * @return	string				Nom du type de tag.
	 */
	public static function getTypeNameById(int $typeID)
	{
		$sql = 'SELECT `name` FROM ' . self::TABLE_TYPE . ' WHERE `id` = :typeID';
		$res = msSQL::sqlUniqueChamp($sql, ['typeID' => $typeID]);
		if (is_null($res)) throw new Exception(__METHOD__ . ' : aucun type avec id=' . $typeID);
		return $res;
	}

	/**
	 * Obtenir l'id d'un type de tag avec son nom.
	 * @param	string		$typeName	Nome du type.
	 * @return	int						ID du type.
	 */
	public static function getTypeIdByName(string $typeName)
	{
		$sql = 'SELECT `id` FROM ' . self::TABLE_TYPE . ' WHERE `name` = :typeName';
		$res = msSQL::sqlUniqueChamp($sql, ['typeName' => $typeName]);
		if (is_null($res)) throw new Exception(__METHOD__ . ' : aucun type avec name="' . $typeName . '"');
		return (int) $res;
	}

	/**
	 * Vérifier si un type de tag existe.
	 * @param	int		$typeID		ID du type.
	 * @return	bool				`true` si le type existe autrement `false`.
	 */
	public static function checkTypeExist(int $typeID)
	{
		$sql = 'SELECT `id` FROM ' . self::TABLE_TYPE . ' WHERE `id` = :id';
		$resql = msSQL::sqlQuery($sql, ['id' => $typeID]);
		if (empty($resql)) throw new Exception(__METHOD__ . ' : erreur sql');
		return filter_var(count($resql->fetchAll(PDO::FETCH_ASSOC)), FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Vérifier si type de tag existe.
	 * @param	int		$typeID		ID du type.
	 * @return	boot				`true` si existe autrement `false`.
	 */
	public static function getIfTypeIsActif(int $typeID)
	{
		if (!self::checkTypeExist($typeID)) throw new Exception(__METHOD__ . ' : Type de tag non existant');
		$sql = 'SELECT `actif` FROM ' . self::TABLE_TYPE . ' WHERE `id` = :id';
		$res = msSQL::sqlUniqueChamp($sql, ['id' => $typeID]);
		if (is_null($res)) throw new Exception(__METHOD__ . ' : erreur requette sql.');
		return filter_var($res, FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Activer ou désactiver un type de tag.
	 * @param	int		$typeID		L'ID du type.
	 * @param	bool	$actif		`true` pour activer le type, `false` pour
	 *								le désactiver.
	 * @return	bool				Le nouvel état du type.
	 */
	public static function setTypeActif(int $typeID, bool $actif)
	{
		if (!self::checkTypeExist($typeID)) throw new Exception(__METHOD__ . ' : Type de tag non existant');
		$sql = 'UPDATE ' . self::TABLE_TYPE . ' SET `actif` = :actif WHERE `id` = :id';
		$resql = msSQL::sqlQuery($sql, ['actif' => (int)$actif, 'id' => $typeID]);
		if (empty($resql)) throw new Exception(__METHOD__ . ' : erreur sql');
		return $actif;
	}

	/**
	 * Vérifie si l'utilisateur peut créer, modifier ou supprimer les tags du
	 * type dont l'ID est fourni en argument.
	 * @param	int		$typeID		ID du type.
	 * @return	bool				`true` si l'utilisatuer peut, autrement `false`.
	 */
	public static function checkTypeDroitCreSup(int $typeID)
	{
		if (!self::checkTypeExist($typeID)) throw new Exception(__METHOD__ . ' : Type de tag non existant');
		global $p;
		$sql = 'SELECT `droitCreSup` FROM ' . self::TABLE_TYPE . ' WHERE `id` = :id';
		$res = msSQL::sqlUniqueChamp($sql, ["id" => $typeID]);
		if (is_null($res)) throw new Exception(__METHOD__ . ' : erreur requette sql.');
		return filter_var($p['config'][$res], FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Vérifie si l'utilisateur peut ajouter ou retirer des tags du type dont
	 * l'ID est fourni en argument.
	 * @param	int		$typeID		ID du type.
	 * @return	bool				`true` si l'utilisateur peut, autrement `false`.
	 */
	public static function checkTypeDroitAjoRet(int $typeID)
	{
		if (!self::checkTypeExist($typeID)) throw new Exception(__METHOD__ . ' : Type de tag non existant');
		global $p;
		$sql = 'SELECT `droitAjoRet` FROM ' . self::TABLE_TYPE . ' WHERE `id` = :id';
		$res = msSQL::sqlUniqueChamp($sql, ["id" => $typeID]);
		if (is_null($res)) throw new Exception(__METHOD__ . ' : erreur requette sql.');
		return filter_var($p['config'][$res], FILTER_VALIDATE_BOOLEAN);
	}

	/*************************************************************************
	 * Méthode de rendu de contenus HTML
	 ************************************************************************/

	/**
	 * Retoune le HTML d'une liste de tag selon un type, un propriétaire et un
	 * contexte.
	 * @param	int		$typeID		ID du type pour lequel la liste doit être
	 *								obtenus.
	 * @param	int		$toID		Propriétaire des tags de la liste. `0` si
	 *								la liste est obtenue dans un contexte général.
	 * @param	string	$context	Contexte pour lequel générer la liste de
	 *								tag. Peut être l'un des cas suivant :
	 *									'config'	:
	 *										Page de configuration globale des
	 *										tags universels. $toID doit valoir
	 *										`0` dans se contexte. La liste de tag
	 *										généré comporte le bouton permettant
	 *										de créer de nouveau tag pour le type
	 *										est affiché ainsi que le bouton pour
	 *										éditer chaque tag individuellement.
	 *									'show'		:
	 *										Sur la fiche d'un élément, permet
	 *										d'afficher la liste des tags liés.
	 *										$toID doit valoir l'id du propriétaire.
	 *										Seuls les tags appartenant au propriétaire
	 *										sont affichés.
	 *										propriétaire
	 *									'select'	:
	 *										Sur la fiche d'un élément quand la
	 *										sélection de tags est activée, permet
	 *										de sélectionner les tags à ajouter ou
	 *										retirer sur l'élément. $toID doit
	 *										valoir l'id du propriétaire. Tout
	 *										les tags du type en question sont
	 *										affichés. Le bouton de création de
	 *										nouveau tag est afiché ainsi que le
	 *										bouton pour éditer chaque tag
	 *										individuellement et la checkbox
	 *										permettant d'ajouter ou de retirer
	 *										le tag sur un élément.
	 *									'search'	:
	 *										Forumlaire de recherche relatif au
	 *										type, permet de filtrer les résultats
	 *										de la recherche en fonction des tags
	 *										sélectionnés. $toID doit valoir `0`.
	 *										La chekck box permettant de sélectionner
	 *										les tags sur les quel filtrer la recherche
	 *										est affiché.
	 *	@return		string			HTML avec la liste des tags.
	 */
	public static function getListHtml(int $typeID, int $toID, string $contexte)
	{
		global $p;
		if (empty($p['page'])) $p['page'] = array();
		if (empty($p['page']['univTags'])) $p['page']['univTags'] = array();
		$p['page']['univTags']['typeID'] = $typeID;
		$p['page']['univTags']['toID'] = $toID;
		switch ($contexte) {
			case 'config':
				if ($toID > 0) throw new Exception(__METHOD__ . ' : $toID doit valoir 0 sur le contexte "config"');
				$p['page']['univTags']['showEditBtn'] = true;
				$p['page']['univTags']['showNewBtn'] = true;
				$p['page']['univTags']['showSelectSetTo'] = false;
				$p['page']['univTags']['showSelectFilterSearch'] = false;
				$onlyTo = false;
				break;
			case 'show':
				if ($toID < 1) throw new Exception(__METHOD__ . ' : $toID ne peut valloir 0 sur le contexte "show"');
				$p['page']['univTags']['showEditBtn'] = false;
				$p['page']['univTags']['showNewBtn'] = false;
				$p['page']['univTags']['showSelectSetTo'] = false;
				$p['page']['univTags']['showSelectFilterSearch'] = false;
				$onlyTo = true;
				break;
			case 'select':
				if ($toID < 1) throw new Exception(__METHOD__ . ' : $toID ne peut valloir 0 sur le contexte "select"');
				$p['page']['univTags']['showEditBtn'] = self::checkTypeDroitCreSup($typeID);
				$p['page']['univTags']['showNewBtn'] = self::checkTypeDroitCreSup($typeID);
				$p['page']['univTags']['showSelectSetTo'] = self::checkTypeDroitAjoRet($typeID);
				$p['page']['univTags']['showSelectFilterSearch'] = false;
				$onlyTo = false;
				break;
			case 'search':
				if ($toID > 0) throw new Exception(__METHOD__ . ' : $toID doit valoir 0 sur le contexte "search"');
				$p['page']['univTags']['showEditBtn'] = false;
				$p['page']['univTags']['showNewBtn'] = false;
				$p['page']['univTags']['showSelectSetTo'] = false;
				$p['page']['univTags']['showSelectFilterSearch'] = true;
				$onlyTo = false;
				break;
			default:
				throw new Exception(__METHOD__ . ' : contexte=' . $contexte . ' inconus');
		}
		$p['page']['univTags']['contexte'] = $contexte;
		$p['page']['univTags']['tagListe'] = msUnivTags::getList($typeID, $toID, $onlyTo);
		$getHtml = new msGetHtml();
		$getHtml->set_template('unviTagsTagListe');
		return $getHtml->genererHtml();
	}

	/**
	 * Retourne le HTML pour la modal permettant de créer ou éditer un tag.
	 * @param	int		$typeID		ID du type de tag.
	 * @param	int		$tagID		ID du tag à éditer, si c'est un création
	 *								doit valoir `0`.
	 * @param	int		$toID		Si la modal est appelée depuis la fiche d'un
	 *								élément doit avoir l'id de cet élément.
	 *								Si la modal est appelée depuis un contexte
	 *								général comme la page de configuration doit
	 *								valoir `0`. Permet de faire suive le
	 *								propriétaire du tag à la validation du
	 *								formulaire pour générer la liste actualisée
	 *								adéquate.
	 * @param	string	$contexte	Contexte dans lequel la modal est appelée.
	 *								Doit correspondre aux contextes de la méthode
	 *								`getListHtml()`. Permet de faire suive le
	 *								contexte à la validation du formulaire pour
	 *								générer la liste actualisée adéquate.
	 */
	public static function getModalHtml(int $typeID, int $tagID, int $toID, $contexte)
	{
		global $p;
		if (empty($p['page'])) $p['page'] = array();
		if (empty($p['page']['univTags'])) $p['page']['univTags'] = array();
		$p['page']['univTags']['typeID'] = $typeID;
		$p['page']['univTags']['tagID'] = $tagID;
		$p['page']['univTags']['toID'] = $toID;
		$p['page']['univTags']['contexte'] = $contexte;
		$p['page']['univTags']['typeName'] = self::getTypeNameById($typeID);
		// Si le tagID est supérieur à 0 fetch le tag pour récupérer ses propriété
		if ($tagID > 0) {
			$tag = new self;
			$tagProp = $tag->fetch($tagID);
			$p['page']['univTags']['tagProp'] = $tagProp;
		}
		$p['page']['univTags']['typeDroitCreSup'] = self::checkTypeDroitCreSup($typeID);
		$getHtml = new msGetHtml();
		$getHtml->set_template('univTagsTagModal');
		return $getHtml->genererHtml();
	}

	/**
	 * Obtenir le HTML générant une liste de pastilles colorées afin d'avoir
	 * une vue compacte des tags atacĥés à un élément.
	 * @param	array	$list	Array de tags attachés à l'élément. Doit
	 *							corespondre à ce que produit la méthode
	 *							`getList()`.
	 * @return	string			HTML.
	 */
	public static function getTagsCircleHtml(array $list)
	{
		$ret = '';
		// @TODO check list content
		if (!empty($list)) {
			foreach ($list as $tag) {
				$ret .= '<span title="' . $tag['name'] . ' : ' . $tag['description'] . '" data-typeID="' . $tag['typeID'] . '" data-toID="' . $tag['toID'] . '">';
				$ret .= '<svg height="22" width="22">';
				$ret .= '<circle cx="11" cy="11" r="10" stroke="#B3B3B3" stroke-width="1" fill="' . $tag['color'] . '" />';
				$ret .= '</svg>';
				$ret .= '</span>';
			}
		}
		return $ret;
	}

	/********************************
	 * Méthodes outils
	 *******************************/

	/**
	 * Retourne la couleur noire ou blache en héxadécimal selon la
	 * luminosité de la couleur héxadécimale passée en argument.
	 * @param	string		$color	Couleur à analyser au format héxadécimal
	 *								(#FFFFFF).
	 * @return	string				Couleur noire ou blanche en héxadécimal.
	 */
	private static function tagTextColor(string $color)
	{
		$r = hexdec(substr($color, 1, 2));
		$b = hexdec(substr($color, 3, 2));
		$g = hexdec(substr($color, 5, 2));
		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		$l = ($max + $min) / 2;
		if ($l > 120) return '#000000';
		else return '#FFFFFF';
	}
}
