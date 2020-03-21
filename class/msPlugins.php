<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2020
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
 * Plugins
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msPlugins
{

/**
 * Obtenir une liste des plugins installés
 * @return array array pluginName=>pluginName
 */
  public static function getInstalledPluginsNames() {
    return msSQL::sql2tabKey("SELECT name FROM `system` WHERE groupe='plugin' order by name", "name", "name");
  }

/**
 * Obtenir une liste des plugins et versions
 * @return array k=>['plugin','version']
 */
  public static function getInstalledPluginsNamesAndVersions() {
    return msSQL::sql2tab("SELECT name, value AS version FROM `system` WHERE groupe='plugin' order by name");
  }

/**
 * Obtenir une liste des versions des plugins
 * @return array plugin=>'version'
 */
  public static function getInstalledPluginsVersions() {
    if($r = msSQL::sql2tabKey("SELECT name, value AS version FROM `system` WHERE groupe='plugin'", "name", "version")) {
      return $r;
    } else {
      return [];
    }
  }

/**
 * Obtenir les infos génériques sur un plugin à partir du fichier aboutPlugin*Plugin*.yml
 * @param  string $name nom du plugin
 * @return array       paramètres extraits
 */
  public static function getPluginInfosGen($name) {
      global $p;
      $file=$p['homepath'].'config/plugins/'.$name.'/aboutPlugin'.ucfirst($name).'.yml';
      if(is_file($file)) {
        return Spyc::YAMLLoad($file);
      }
      return [];
  }

}
