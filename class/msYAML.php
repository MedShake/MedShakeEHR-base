<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2023
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
 * Gestion des données YAML
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 */

class msYAML
{

	/**
	 * Obtenir un array à partir d'une chaine YAML
	 *
	 * @param string $string
	 * @param string $pos
	 * @return array
	 */
	public static function yamlYamlToArray($string, $pos = "0")
	{
		return yaml_parse($string, $pos);
	}

	/**
	 * Obtenir du YAML à partir d'un array
	 *
	 * @param array $array
	 * @param boolean $removeHeader
	 * @return string
	 */
	public static function yamlArrayToYaml($array, $removeHeader = false)
	{
		return yaml_emit($array, YAML_UTF8_ENCODING, YAML_LN_BREAK);
	}

	/**
	 * Lire un fichier YAML
	 *
	 * @param string $file chemin complet et fichier à lire
	 * @param string $pos
	 * @return array
	 */
	public static function yamlFileRead($file, $pos = "0")
	{
		return yaml_parse_file($file, $pos);
	}

	/**
	 * Ecrire un fichier YAML
	 *
	 * @param string $file chemin complet du fichier
	 * @param array $data array des datas à écrire
	 * @return bool
	 */
	public static function yamlFileWrite($file, $data)
	{
		return yaml_emit_file($file, $data, YAML_UTF8_ENCODING, YAML_LN_BREAK);
	}
}
