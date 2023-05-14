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
 *
 * Interogation du modèle de données : catégories
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */

class msDataCat
{


	const COL_TABLE_DATA_CAT = array("id", "groupe", "name", "label", "description", "type", "fromID", "creationDate");


	/**
	 * Obtenir le cat ID à partir du cat name
	 * @param  string $name nom du type
	 * @return int     catID
	 */
	public static function getCatIDFromName($name)
	{
		return msSQL::sqlUniqueChamp("select id from data_cat where name = :name ", ['name' => $name]);
	}

	/**
	 * Obtenir le cat name à partir du cat ID
	 * @param  int $id de la catégorie
	 * @return string     name
	 */
	public static function getCatNameFromCatID($id)
	{
		if (!is_numeric($id)) throw new Exception('ID is not numeric');
		return msSQL::sqlUniqueChamp("select name from data_cat where id = :id ", ['id' => $id]);
	}

	/**
	 * Obtenir le cat label à partir du cat ID
	 * @param  int $id de la catégorie
	 * @return string     label
	 */
	public static function getCatLabelFromCatID($id)
	{
		if (!is_numeric($id)) throw new Exception('ID is not numeric');
		return msSQL::sqlUniqueChamp("select label from data_cat where id = :id ", ['id' => $id]);
	}

	/**
	 * Obtenir une liste des catégories correspondant au(x) groupe(s)
	 * @param  array  $groupe  tableau des groupes concernés
	 * @param  array  $cols    champs à retourner
	 * @param  string $orderBy ordonner par
	 * @return array          tableau
	 */
	public static function getCatListFromGroupe($groupe = ['*'], $cols = ['*'], $orderBy = 'label')
	{
		if ($cols != ['*']) {
			if (!msSQL::sqlValidQueryCols($cols, self::COL_TABLE_DATA_CAT)) {
				throw new Exception('nom de colonne non valide');
			}
		}

		if (!msSQL::sqlIsValidOrderByString($orderBy)) throw new Exception('orderBy not valid');

		if (!empty($groupe)) {
			$sqlImplode = msSQL::sqlGetTagsForWhereIn($groupe, 'groupe');
		}

		return msSQL::sql2tabKey("select id, " . implode(', ', $cols) . " from data_cat where groupe in (" . $sqlImplode['in'] . ") order by " . $orderBy, 'id', '', $sqlImplode['execute']);
	}
}
