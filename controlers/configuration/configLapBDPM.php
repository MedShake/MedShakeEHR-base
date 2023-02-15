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
 * Config : LAP => afficher les données de la BDPM si présente
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */

//admin uniquement
if (!msUser::checkUserIsAdmin() or $p['config']['theriaqueMode'] != 'BDPM') {
	$template = "forbidden";
} else {
	$template = "configLapBDPM";

	if ($bdpm = yaml_parse_file($homepath . 'config/bdpm/configBdpm.yml')) {

		$dirRessourcesBdpm = $homepath . 'ressources/bdpm/';

		$dataParsing = msSQL::sql2tabKey("select * from bdpm_updates", 'fileName', 'fileLastParse');

		foreach ($bdpm['dataBdpm'] as $table => $v) {
			$p['page']['dataBdpm'][$table] = array(
				'file' => $v['file'],
				'filePath' => $dirRessourcesBdpm . $v['file'],
				'filePresence' => file_exists($dirRessourcesBdpm . $v['file']),
				'fileLastParsing' =>  $dataParsing[$v['file']]
			);
		}
	}
}
