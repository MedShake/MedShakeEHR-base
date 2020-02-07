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
 * Système
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msSystem
{

/**
 * Obtenir le statut système en base
 * @return string normal / maintenance
 */
  public static function getSystemState() {
    return msSQL::sqlUniqueChamp("SELECT value FROM system WHERE name='state' and groupe = 'system' limit 1");
  }

/**
 * Obtenir le nombre de pro utilisateurs
 * @return int nombre de pros utilisateurs 
 */
  public static function getProUserCount() {
    return (int) msSQL::sqlUniqueChamp("SELECT COUNT(*) FROM people WHERE type='pro' AND name!=''");
  }
}
