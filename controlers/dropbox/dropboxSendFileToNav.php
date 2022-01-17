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
 * Dropbox : renvoyer fichier dans navigateur
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';

if($p['config']['optionGeActiverDropbox'] != 'true') {
  die();
}

$dropbox = new msDropbox;

if($p['config']['optionGeActiverDropbox'] != 'true') die;
if(!isset($match['params']['box']) or !isset($match['params']['filename'])) die;

$dropbox = new msDropbox;
$dropbox->setCurrentBoxId($match['params']['box']);
$dropbox->getAllBoxesParametersCurrentUser()[$match['params']['box']];
$dropbox->setCurrentFilename($match['params']['filename']);
if($dropbox->checkFileIsInCurrentBox($match['params']['filename'])) {
  $fileData = $dropbox->getCurrentFileData();
  $mimetype=msTools::getmimetype($fileData['fullpath']);
  header("Content-type: ".$mimetype);
  if($mimetype == 'text/plain') {
    $content=file_get_contents($fileData['fullpath']);
    if (!mb_detect_encoding($content, 'utf-8', true)) {
      header('Content-Type: '.$mimetype.'; charset=iso-8859-1');
    }
  }

  readfile($fileData['fullpath']);
} else {
  die;
}
