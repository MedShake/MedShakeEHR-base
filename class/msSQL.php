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
      if(!empty($p['config']['sqlServeur'])) {
        $mysqli = new mysqli($p['config']['sqlServeur'], $p['config']['sqlUser'], $p['config']['sqlPass'], $p['config']['sqlBase']);
      } elseif(!empty($_SERVER['RDS_HOSTNAME'])) {
        $mysqli = new mysqli($_SERVER['RDS_HOSTNAME'], $_SERVER['RDS_USERNAME'], $_SERVER['RDS_PASSWORD'], $_SERVER['RDS_DB_NAME'], $_SERVER['RDS_PORT']);
      }
      $mysqli->set_charset("utf8");
      if (mysqli_connect_errno()) {
          die('Echec de connexion à la base de données');
      } else {
          $mysqli->query('SELECT @password:="'.$mysqli->real_escape_string($p['config']['sqlVarPassword']).'"');
          if(isset($p['config']['sqlTimeZone'])) $mysqli->query("SET time_zone = '".$p['config']['sqlTimeZone']."'");
          return $mysqli;
      }
  }


/**
 * Nettoyer une variable avant insertion en bdd
 * @param  string $var variable
 * @return string      variable échappée
 */
  public static function cleanVar($var)
  {
      global $mysqli;
      $var=$mysqli->real_escape_string(trim($var));
      return $var;
  }

/**
 * Nettoyer un array avant utilisation sur string SQL
 * @param  array $var array
 * @return string      variable échappée
 */
  public static function cleanArray($array)
  {
      array_map(function($v){
        global $mysqli;
        $mysqli->real_escape_string(trim($v));
      }, $array);
      return $array;
  }

/**
 * Fonction query de base
 * @param  string $sql commande SQL
 * @return resource      résultat mysql
 */
  public static function sqlQuery($sql)
  {
      global $mysqli;
      $query=$mysqli->query($sql);
      if ($mysqli->connect_errno) {
          return null;
      } else {
          return $query;
      }
  }

/**
 * Sortir un champ unique d'une ligne unique
 * @param  string $sql commande SQL
 * @return string      valeur du champ unique demandé
 */
  public static function sqlUniqueChamp($sql)
  {
      $query=self::sqlQuery($sql);
      if ($query and mysqli_num_rows($query)==1) {
          $query->data_seek(0);
          $row = $query->fetch_row();
          return $row[0];
      } else {
          return null;
      }
  }

/**
 * Sortir une ligne unique en ArrayAccess
 * @param  string $sql commande SQL
 * @return array      array
 */
  public static function sqlUnique($sql)
  {
      $query=self::sqlQuery($sql);
      if ($query and mysqli_num_rows($query)==1) {
          $query->data_seek(0);
          return $query->fetch_array(MYSQLI_ASSOC);
      } else {
          return null;
      }
  }

/**
 * Sortir des lignes en array
 * @param  string $sql commande SQL
 * @return array      array
 */
  public static function sql2tab($sql)
  {
      $query=self::sqlQuery($sql);
      if ($query and mysqli_num_rows($query)>0) {
          while ($row=$query->fetch_array(MYSQLI_ASSOC)) {
              if ($row) {
                  $result[]=$row;
              }
          };
          return $result;
      } else {
          return null;
      }
  }

/**
 * Sortir un array avec en key le champ mysql sépcifié et l'éventuelle unique value
 * @param  string $sql   commande SQL
 * @param  string $key   colonne qui servira de clef
 * @param  string $value colonne qui servira de value
 * @return array        Array key => value
 */
  public static function sql2tabKey($sql, $key, $value='')
  {
      if ($tab=self::sql2tab($sql)) {
          foreach ($tab as $k=>$v) {
              if ($value) {
                  $returntab[$v[$key]]=$v[$value];
              } else {
                  $returntab[$v[$key]]=$v;
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
 * @return array      array 0=> 1=> ...
 */
  public static function sql2tabSimple($sql)
  {
      $query=self::sqlQuery($sql);
      if ($query and mysqli_num_rows($query)>0) {
          while ($row=$query->fetch_array(MYSQLI_NUM)) {
              if ($row) {
                  $result[]=$row[0];
              }
          };
          return $result;
      } else {
          return null;
      }
  }

/**
 * Insérer dans une table ou mettre à jour
 * Processus basé sur l'existence ou non d'une clef primaire dans la commande SQL
 *
 * @param  string  $table     table concernée
 * @param  array  $data      array champ => valeur à injecter
 * @param  bool $trashHTML deprecated
 * @return int|bool             last insert id ou false
 */
  public static function sqlInsert($table, $data, $trashHTML=true)
  {
      global $mysqli;
      foreach ($data as $key=>$val) {
          $key=self::cleanVar($key);
          $val=html_entity_decode($val, ENT_QUOTES | ENT_HTML5, "UTF-8");
          if ($trashHTML==true) {
              $val=self::cleanVar($val);
          } else {
              $val=$mysqli->real_escape_string(trim($val));
          }
          $cols[]=$key;
          $valeurs[]='\''.$val.'\'';
		  $dupli[]='`'.$key.'`=VALUES(`'.$key.'`)';
      }
      if (self::sqlQuery("insert into `".self::cleanVar($table)."` (`".implode('`, `', $cols)."`) values (".implode(',', $valeurs).") ON DUPLICATE KEY UPDATE ".implode(', ', $dupli)." ;")) {
          return $mysqli->insert_id;
      } else {
          return false;
      }
  }

/**
 * Obtenir un tableau des différentes valeurs d'un champ enum
 * @param  string $table nom de la table
 * @param  string $field nom du champ
 * @return array        tableau des valeurs
 */
  public static function sqlEnumList($table, $field) {
    if($row = self::sqlUnique("SHOW FIELDS FROM `".self::cleanVar($table)."` where Field = '".self::cleanVar($field)."'")) {
      preg_match('#^enum\((.*?)\)$#ism', $row['Type'], $matches);
      $enum = str_getcsv($matches[1], ",", "'");
      return $enum;
    } else {
      return [];
    }
  }


}
