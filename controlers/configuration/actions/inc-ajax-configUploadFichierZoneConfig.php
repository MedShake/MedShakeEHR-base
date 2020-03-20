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
 * Configuration > ajax : upload par drag&drop de fichier en zone Configuration
 * (clef apicrypt, templates ...)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

$fichier=$_FILES['file'];
if(!isset($_POST['destination'])) die;
if(strpos($_POST['destination'], $homepath) !== 0) die();

//creation folder si besoin
msTools::checkAndBuildTargetDir($_POST['destination']);
if(!is_dir($_POST['destination'])) die;

$destination_file = $_POST['destination'].basename($fichier['name']);
move_uploaded_file($fichier['tmp_name'], $destination_file);

die();
