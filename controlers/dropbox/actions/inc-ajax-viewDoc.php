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
 * Dropbox > ajax : voir un doc
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */
$debug='';
$template = 'dropboxViewDoc';

if($p['config']['optionGeActiverDropbox'] != 'true') die;
if(!isset($_POST['box']) or !isset($_POST['filename'])) die;
if(!is_string($_POST['box']) or !is_string($_POST['filename'])) die;

$dropbox = new msDropbox;
$dropbox->setCurrentBoxId($_POST['box']);
$p['page']['boxParams'] = $dropbox->getAllBoxesParametersCurrentUser()[$_POST['box']];
$p['page']['boxParams']['id'] = $_POST['box'];

if($dropbox->checkFileIsInCurrentBox($_POST['filename'])) {
  $dropbox->setCurrentFilename($_POST['filename']);
  $p['page']['dataFromFilename']=$dropbox->getDataFromFilename();
  $p['page']['patientsPossibles'] = $dropbox->getPossiblePatients();
  $p['page']['fileData'] = $dropbox->getCurrentFileData();
  $p['page']['fileData']['filename'] = $_POST['filename'];
} else {
  die("Ce fichier n'existe pas dans la boite de dépôt");
}
