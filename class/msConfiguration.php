<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00>
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
 * Gestion des paramètres de configuration de MedShakeEHR au niveau général / module / user
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msConfiguration
{
    private static $_usersParams;

////////////////// NIVEAU DEFAULT \\\\\\\\\\\\\\\\\\\

/**
 * Obtenir tous les paramètres de configuration par défaut
 * @return array tableau des paramètres
 */
    public static function getDefaultParameters() {
        return msSQL::sql2tab("SELECT cat, type, name, value, description FROM configuration WHERE level='default'");
    }

/**
 * Obtenir un paramètre de configuration par défaut
 * @param  string $name nom du paramètre
 * @return string       valeur du paramètre
 */
    public static function getDefaultParameterValue($name) {
        return msSQL::sqlUniqueChamp("SELECT value FROM configuration WHERE name='".$name."' AND level='default'");
    }

////////////////// NIVEAU MODULE \\\\\\\\\\\\\\\\\\\

public static function getModuleDefaultParameters($module) {
    return msSQL::sql2tab("SELECT cat, type, name, value, description FROM configuration WHERE level='module' and module='".$module."'");
}

////////////////// NIVEAU USER \\\\\\\\\\\\\\\\\\\

/**
 * Obtenir la liste des paramètres sustituables (non déjà subtitués) pour un user
 * @param  array $user $user
 * @return array       tableau des paramètres
 */
    public static function listAvailableParameters($user) {
        $all=msSQL::sql2tabKey("SELECT cat, name, type, description FROM configuration WHERE level='default' ORDER BY cat, name", 'name');
        self::$_usersParams=self::getUserParamaters($user['id']);
        if (!is_array(self::$_usersParams)) {
            return $all;
        }
        return array_filter($all, function($key){return !array_key_exists($key, self::$_usersParams);}, ARRAY_FILTER_USE_KEY);
    }

/**
 * Obtenir les paramètres de configuration pour un user
 * @param  array $user $user
 * @return array       tableau des paramètres
 */
    public static function getAllParametersForUser($user=array('id'=>'', 'module'=>'')) {
        $defaultParams=msSQL::sql2tabKey("SELECT name, value FROM configuration WHERE level='default'", 'name','value');
        if (isset($user['module'])) {
            $moduleParams=msSQL::sql2tabKey("SELECT name, value FROM configuration WHERE level='module' AND module='".$user['module']."'", 'name', 'value');
        }
        if (isset($user['id'])) {
            $userParams=msSQL::sql2tabKey("SELECT name, value FROM configuration WHERE level='user' AND toID='".$user['id']."'", 'name', 'value');
        }
        if (!isset($userParams) or !is_array($userParams))
          $userParams=array();
        if (!isset($moduleParams) or !is_array($moduleParams))
          $moduleParams=array();
        if (!is_array($defaultParams))
          $defaultParams=array();
        return array_replace($defaultParams, $moduleParams, $userParams);
    }

/**
 * Obtenir les paramètres de configuration d'une catégorie pour un user
 * NOTE : si l'id du user est '', les valeurs du module sont retournées
 *        si user n'est pas défini, les valeurs par défaut sont retournées
 * @param  string $cat  nom de la catégorie
 * @param  array  $user user
 * @return array       tableau des paramètres
 */
    public static function getCatParametersForUser($cat, $user=array('id'=>'','module'=>'')) {
        $defaultParams=msSQL::sql2tabKey("SELECT name, value FROM configuration WHERE level='default' AND cat='".$cat."'", 'name', 'value');
        $moduleParams=msSQL::sql2tabKey("SELECT name, value FROM configuration
            WHERE level='module' AND module='".$user['module']."' AND name IN ('".implode("','", array_keys($defaultParams))."')", 'name', 'value');
        $userParams=msSQL::sql2tabKey("SELECT name, value FROM configuration
            WHERE level='user' AND toID='".$user['id']."' AND name IN ('".implode("','", array_keys($defaultParams))."')", 'name', 'value');
        if (!is_array($userParams))
          $userParams=array();
        if (!is_array($moduleParams))
          $moduleParams=array();
        if (!is_array($defaultParams))
          $defaultParams=array();
        return array_replace($defaultParams, $moduleParams, $userParams);
    }

/**
 * Obtenir les paramètres niveau user d'un user
 * @param  int $userID id du user
 * @return array         tableau des paramètres
 */
    public static function getUserParamaters($userID) {
        $userParams=msSQL::sql2tabKey("SELECT name, value FROM configuration WHERE level='user' AND toID='".$userID."'", 'name');
        if (!is_array($userParams)) {
            return array();
        }
        $catTypeDefault=msSQL::sql2tabKey("SELECT name, cat, type, value FROM configuration
            WHERE level='default' AND name IN ('".implode("','",array_keys($userParams))."')", 'name');
        foreach ($userParams as $k=>$v) {
            $userParams[$k]['type']=$catTypeDefault[$k]['type'];
            $userParams[$k]['cat']=$catTypeDefault[$k]['cat'];
            $userParams[$k]['default']=$catTypeDefault[$k]['value'];
            if (strpos(strtolower($k), 'password')!==false and $v['value']!='') {
                $userParams[$k]['value']=msSQL::sqlUniqueChamp("SELECT CONVERT(AES_DECRYPT(UNHEX('".$v['value']."'),@password), CHAR)");
                $userParams[$k]['type']='password';
            } elseif ($catTypeDefault[$k]['type']==='true/false') {
                $userParams[$k]['type']='checkbox';
            }
        }
        return $userParams;
    }

/**
 * Obtenir un paramètre de configuration pour un user, éventuellement à la valeur fixée par le module ou par défaut
 * NOTE : si l'id du user est '', c'est la valeur du module qui est retournée,
 *        et si user n'est pas défini, la valeur par défaut est retournée
 * @param  string $name nom du paramètre
 * @param  array  $user user
 * @return string       valeur du paramètre
 */
    public static function getParameterValue($name, $user=array('id'=>'','module'=>'')) {
        if (strpos(strtolower($name), 'password')!==false) {
            $param=msSQL::sql2tabKey("SELECT level, CONVERT(AES_DECRYPT(UNHEX(value),@password), CHAR) AS value FROM configuration WHERE name='".$name."' AND
                ((level='user' AND toID='".$user['id']."') OR (level='module' AND module='".$user['module']."') OR level='default')", 'level');
        } else {
            $param=msSQL::sql2tabKey("SELECT level, value FROM configuration WHERE name='".$name."' AND
                ((level='user' AND toID='".$user['id']."') OR (level='module' AND module='".$user['module']."') OR level='default')", 'level');
        }
        if (!is_array($param))
            return NULL;
        if (array_key_exists('user', $param))
            return $param['user']['value'];
        if (array_key_exists('module', $param))
            return $param['module']['value'];
        if (array_key_exists('default', $param))
            return $param['default']['value'];
        return NULL;
    }

/**
 * Obtenir un paramètre de configuration pour un user, s'il existe au niveau user
 * @param  string $name   nom du paramètre
 * @param  int $userID user ID
 * @return string         valeur du paramètre
 */
    public static function getUserParameterValue($name, $userID) {
        if (strpos(strtolower($name), 'password')!==false) {
        return msSQL::sqlUniqueChamp("SELECT CONVERT(AES_DECRYPT(UNHEX(value),@password), CHAR) FROM configuration
            WHERE name='".$name."' AND level='user' AND toID='".$userID."'");
        }
        return msSQL::sqlUniqueChamp("SELECT value FROM configuration WHERE name='".$name."' AND level='user' AND toID='".$userID."'");
    }

/**
 * Obtenir la liste des catégories des paramètres
 * @return array tableau avec clef = cat "sanitizée"
 */
    public static function getListOfParametersCat() {
      if($all=msSQL::sql2tab("SELECT distinct(cat) as cat FROM configuration WHERE level='default' ORDER BY cat")) {
        foreach($all as $v) {
          $tab[msTools::sanitizeFilename($v['cat'])]=$v['cat'];
        }
        return $tab;
      }
    }


/**
 * fixer (+ créer) un paramètre de configuration pour un user
 * @param string  $name   nom du paramètres
 * @param string $value  valeur du paramètre
 * @param int $userID id du USER
 * @return bool true (succès) / false
 */
    public static function setUserParameterValue($name, $value, $userID) {
        if (strpos(strtolower($name), 'password')!==false and $value!='') {
            return msSQL::sqlQuery("INSERT INTO configuration (name, level, toID, value) VALUES ('".msSQL::cleanVar($name)."', 'user', '".msSQL::cleanVar($userID)."', HEX(AES_ENCRYPT('".$value."',@password)))
            ON DUPLICATE KEY UPDATE value=HEX(AES_ENCRYPT('".$value."',@password))");
        }

        return msSQL::sqlQuery("INSERT INTO configuration (name, level, toID, value) VALUES ('".msSQL::cleanVar($name)."', 'user', '".msSQL::cleanVar($userID)."', '".msSQL::cleanVar($value)."')
        ON DUPLICATE KEY UPDATE value='".$value."'");
    }
}
