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
 * Print : fonctions de remplacement si inexistantes dans la version de PHP en production
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
}

if (!function_exists('yaml_parse_file')) {
    function yaml_parse_file(string $str) {
      return  Spyc::YAMLLoad($str);
    }
}

if (!function_exists('yaml_parse')) {
    function yaml_parse(string $str) {
      return  Spyc::YAMLLoad($str);
    }
}

?>
