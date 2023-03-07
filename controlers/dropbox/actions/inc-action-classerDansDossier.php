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
 * Dropbox > action : classer dans dossier
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!is_numeric($_POST['patientID'])) die;
if ($p['config']['optionGeActiverDropbox'] != 'true') die;
if (!isset($_POST['box']) or !isset($_POST['filename'])) die;
if (!is_string($_POST['box']) or !is_string($_POST['filename'])) die;

$dropbox = new msDropbox;
if ($endTarget = $dropbox->rangerDropboxDocDansDossier($_POST['patientID'], $_POST['box'], $_POST['filename'], $_POST['titre'])) {
	if (!is_bool($endTarget) and $endTarget == 'patient') {
		msTools::redirection('/patient/' . $_POST['patientID'] . '/');
	} else {
		msTools::redirection('/dropbox/#' . $_POST['box']);
	}
} else {
	die("Il existe un problème bloquant");
}
