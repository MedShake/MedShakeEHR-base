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
 * Fonctions MySQL
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 *
 * SQLPREPOK
 */

class msSQL
{

	/**
	 * Se connecter à la base
	 * @return resource connexion
	 */
	public static function sqlConnect()
	{
		global $p;
		if (!empty($p['config']['sqlServeur'])) {
			$pdo = new PDO('mysql:host=' . $p['config']['sqlServeur'] . ';dbname=' . $p['config']['sqlBase'] . ';charset=utf8', $p['config']['sqlUser'], $p['config']['sqlPass']);
		} elseif (!empty($_SERVER['RDS_HOSTNAME'])) {
			$pdo = new PDO('mysql:host=' . $_SERVER['RDS_HOSTNAME'] . ';port=' . $_SERVER['RDS_PORT'] . ';dbname=' . $_SERVER['RDS_DB_NAME'] . ';charset=utf8', $_SERVER['RDS_USERNAME'], $_SERVER['RDS_PASSWORD']);
		}
		if (!$pdo) {
			die('Echec de connexion à la base de données');
		} else {
			$request = $pdo->prepare('SELECT @password:= :sqlVarPassword ');
			$request->execute(['sqlVarPassword' => $p['config']['sqlVarPassword']]);

			if (isset($p['config']['sqlTimeZone'])) {
				$request = $pdo->prepare('SET time_zone = :sqlTimeZone ');
				$request->execute(['sqlTimeZone' => $p['config']['sqlTimeZone']]);
			}
			return $pdo;
		}
	}

	/**
	 * Nettoyer une variable avant insertion en bdd
	 * @param  string $var variable
	 * @return string      variable échappée SANS quotes périphériques
	 */
	public static function cleanVar($var)
	{
		global $pdo;
		$var = $pdo->quote(trim($var));
		$var = trim($var, "'");
		return $var;
	}

	/**
	 * Nettoyer un array avant utilisation sur string SQL
	 * @param  array $var array
	 * @return string      variable échappée SANS quotes périphériques
	 */
	public static function cleanArray(array $array): array
	{
		array_map(function ($v) {
			global $pdo;
			trim($pdo->quote(trim($v)), "'");
		}, $array);
		return $array;
	}

	/**
	 * Fonction query de base
	 * @param  string $sql commande SQL
	 * @param  array $data marqueurs nommés
	 * @return resource      résultat mysql
	 */
	public static function sqlQuery($sql, $data = [])
	{
		global $pdo;
		$request = $pdo->prepare($sql);
		if ($request->execute($data)) {
			return $request;
		} else {
			return null;
		}
	}

	/**
	 * Sortir un champ unique d'une ligne unique
	 * @param  string $sql commande SQL
	 * @param  array $data marqueurs nommés
	 * @return string      valeur du champ unique demandé
	 */
	public static function sqlUniqueChamp($sql, $data = [])
	{
		$request = self::sqlQuery($sql, $data);
		if ($request and $request->rowCount() === 1) {
			$row = $request->fetch();
			return $row[0];
		} else {
			return null;
		}
	}

	/**
	 * Sortir une ligne unique en Array
	 * @param  string $sql commande SQL
	 * @param  array $data marqueurs nommés
	 * @return array      array
	 */
	public static function sqlUnique($sql, $data = [])
	{
		$request = self::sqlQuery($sql, $data);
		if ($request and $request->rowCount() === 1) {
			return $request->fetch(PDO::FETCH_ASSOC);
		} else {
			return null;
		}
	}

	/**
	 * Sortir des lignes en array
	 * @param  string $sql commande SQL
	 * @param  array $data marqueurs nommés
	 * @return array      array
	 */
	public static function sql2tab($sql, $data = [])
	{
		$request = self::sqlQuery($sql, $data);
		if ($request and $request->rowCount() > 0) {
			return $request->fetchAll(PDO::FETCH_ASSOC);
		} else {
			return null;
		}
	}

	/**
	 * Sortir un array avec en key le champ mysql spécifié et l'éventuelle unique value
	 * @param  string $sql   commande SQL
	 * @param  string $key   colonne qui servira de clef
	 * @param  string $value colonne qui servira de value
	 * @param  array $data marqueurs nommés
	 * @return array        Array key => value
	 */
	public static function sql2tabKey($sql, $key, $value = '', $data = [])
	{
		if ($tab = self::sql2tab($sql, $data)) {
			foreach ($tab as $v) {
				if ($value) {
					$returntab[$v[$key]] = $v[$value];
				} else {
					$returntab[$v[$key]] = $v;
				}
			}
			return $returntab;
		} else {
			return false;
		}
	}

	/**
	 * Retourner un simple array avec clef numérique ascendante
	 * @param  string $sql commande SQL
	 * @param  array $data marqueurs nommés
	 * @return array      array 0=> 1=> ...
	 */
	public static function sql2tabSimple($sql, $data = [])
	{
		$request = self::sqlQuery($sql, $data);
		if ($request and $request->rowCount() > 0) {
			while ($row = $request->fetch(PDO::FETCH_NUM)) {
				if ($row) {
					$result[] = $row[0];
				}
			};
			return $result;
		} else {
			return null;
		}
	}

	/**
	 * Obtenir un tableau des différentes valeurs d'un champ enum
	 * @param  string $table nom de la table
	 * @param  string $field nom du champ
	 * @return array        tableau des valeurs
	 */
	public static function sqlEnumList($table, $field)
	{
		if (!self::sqlVerifyTableExist($table)) {
			throw new Exception('Cette table n\'existe pas');
		}
		if ($row = self::sqlUnique("SHOW FIELDS FROM `" . $table . "` where Field = :field ", ['field' => $field])) {
			preg_match('#^enum\((.*?)\)$#ism', $row['Type'], $matches);
			$enum = str_getcsv($matches[1], ",", "'");
			return $enum;
		} else {
			return [];
		}
	}

	/**
	 * Vérifier si une table existe en base
	 *
	 * @param string $table nom de la table à vérifier
	 * @return bool
	 */
	public static function sqlVerifyTableExist($table)
	{
		global $pdo;
		$stmt = $pdo->query("SHOW TABLES");
		$table_names = array();
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$table_names[] = $row[0];
		}
		if (in_array($table, $table_names)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Obtenir le nom des colonnes d'une table
	 *
	 * @param string $table
	 * @return array tableau des nom des colonnes de $table
	 */
	public static function sqlGetColumnNames($table)
	{
		$sql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = :tableName";
		return self::sql2tabSimple($sql, ['tableName' => $table]);
	}

	/**
	 * Vérifier qu'un tableau de noms de colonne est bien valide (syntaxe "as" comprise)
	 *
	 * @param array $queryCols colonnes demandées (syntaxe nomValide as nouveauNom supportée)
	 * @param array $colsInTable colonnes connue de la table
	 * @return bool
	 */
	public static function sqlValidQueryCols($queryCols, $colsInTable)
	{
		foreach ($queryCols as $value) {
			if ((is_string($value) and (in_array($value, $colsInTable)) or preg_match('#^(' . implode('|', $colsInTable) . ')\s+as\s+[a-zA-Z_][a-zA-Z0-9_]*$#i', $value))) {
				continue;
			} else {
				return false;
			}
		}
		return true;
	}

	/**
	 * Obtenir les éléments pour la requête préparée avec clause WHERE ... IN
	 *
	 * @param array $data data qui vont passer dans la clause where ... in (...)
	 * @param string $prefix prefix à appliquer aux tags à générer
	 * @return array in => la chaine à placer dans IN(), execute => le tableau des marqueurs à merger
	 */
	public static function sqlGetTagsForWhereIn($data, $prefix = 'tag')
	{

		if (!empty($data)) {
			$executeArray = [];
			foreach ($data as $k => $v) {
				$executeArray[$prefix . $k] = $v;
			}

			$tagsInString = ':' . implode(', :', array_keys($executeArray));
			return ['execute' => $executeArray, 'in' => $tagsInString];
		}
		return ['execute' => [], 'in' => "''"];
	}

	/**
	 * Vérifier si une chaine est une possible clause valide pour ORDER BY
	 *
	 * @param string $orderByString
	 * @return bool
	 */
	public static function sqlIsValidOrderByString($orderByString)
	{
		if (empty($orderByString)) return true;

		$regex = "/^[a-zA-Z0-9_,\s\.]+$/";
		if (!preg_match($regex, $orderByString)) {
			return false;
		}

		$dangerous_keywords = array("SELECT", "DROP", "TRUNCATE", "DELETE", "UPDATE", "INSERT");
		foreach ($dangerous_keywords as $keyword) {
			if (stripos($orderByString, $keyword) !== false) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Insérer dans une table ou mettre à jour
	 * Processus basé sur l'existence ou non d'une clef primaire dans la commande SQL
	 *
	 * @param  string  $table     table concernée
	 * @param  array  $data      array champ => valeur à injecter
	 * @return int|bool             last insert id ou false
	 */
	public static function sqlInsert($table, $data)
	{
		global $pdo;
		if (!self::sqlVerifyTableExist($table)) {
			throw new Exception('Cette table n\'existe pas');
		}

		if (!empty(array_diff(array_keys($data), self::sqlGetColumnNames($table)))) {
			throw new Exception('Nom de colonne invalide pour la table ' . $table);
		}

		$i = 0;
		foreach ($data as $key => $val) {
			$val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, "UTF-8");

			$cols[] = $key;
			$marqueurs['mar' . $i] = $val;
			$dupli[] = '`' . $key . '`=VALUES(`' . $key . '`)';
			$i++;
		}

		$sql = "insert into `" . $table . "` (`" . implode('`, `', $cols) . "`) values (:" . implode(', :', array_keys($marqueurs)) . ") ON DUPLICATE KEY UPDATE " . implode(', ', $dupli) . " ;";

		$stmt = $pdo->prepare($sql);
		if ($stmt->execute($marqueurs)) {
			return (int)$pdo->lastInsertId();
		} else {
			return false;
		}
	}
}
