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
 * Modules
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msModules
{

/**
 * Obtenir une liste des modules installés
 * @return array array moduleName=>moduleName
 */
  public static function getInstalledModulesNames() {
    return msSQL::sql2tabKey("SELECT name FROM system WHERE groupe='module' order by name", "name", "name");
  }

/**
 * Obtenir une liste des modules et versions
 * @return array k=>['module','version']
 */
  public static function getInstalledModulesNamesAndVersions() {
    return msSQL::sql2tab("SELECT name, value AS version FROM system WHERE groupe='module'");
  }
}
