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
 * Patient > ajax : préparer le LAP externe
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

header('Content-Type: application/json');

if (!is_numeric($_POST['patientID'])) die;

$classLapExt = 'msLapExt' . ucfirst($p['config']['utiliserLapExterneName']);

if (method_exists($classLapExt, 'prepareLapExterne')) {
	$lapExt = new $classLapExt;
	$lapExt->setPatientID($_POST['patientID']);
	$lapExt->prepareLapExterne();

	exit(json_encode(array(
		'statut' => 'ok'
	)));
} else {
	die();
}
