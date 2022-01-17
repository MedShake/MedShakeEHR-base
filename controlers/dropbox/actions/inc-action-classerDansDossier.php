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

if(!is_numeric($_POST['patientID'])) die;
if($p['config']['optionGeActiverDropbox'] != 'true') die;
if(!isset($_POST['box']) or !isset($_POST['filename'])) die;
if(!is_string($_POST['box']) or !is_string($_POST['filename'])) die;

$dropbox = new msDropbox;
$dropbox->setCurrentBoxId($_POST['box']);
$p['page']['boxParams'] = $dropbox->getAllBoxesParametersCurrentUser()[$_POST['box']];

if($dropbox->checkFileIsInCurrentBox($_POST['filename'])) {
  $dropbox->setCurrentFilename($_POST['filename']);
  $p['page']['fileData'] = $dropbox->getCurrentFileData();

  //source
  $source=$p['page']['fileData']['fullpath'];

  //support
  $patient = new msObjet();
  $patient->setFromID($p['user']['id']);
  $patient->setToID($_POST['patientID']);
  $supportID=$patient->createNewObjetByTypeName('docPorteur', '');

  //ajout d'un titre
  if (isset($_POST['titre']) and !empty($_POST['titre'])) {
      msObjet::setTitleObjet($supportID, $_POST['titre']);
  }

  //nom original
  $patient->createNewObjetByTypeName('docOriginalName', $_POST['filename'], $supportID);
  //type
  $patient->createNewObjetByTypeName('docType', $p['page']['fileData']['ext'], $supportID);

  //folder
  $folder=msStockage::getFolder($supportID);

  //creation folder si besoin
  msTools::checkAndBuildTargetDir($p['config']['stockageLocation']. $folder.'/');

  $destination = $p['config']['stockageLocation']. $folder.'/'.$supportID.'.'.$p['page']['fileData']['ext'];
  if($p['page']['fileData']['ext']=='txt') {
    msTools::convertPlainTextFileToUtf8($source, $destination);
  } elseif(msTools::commandExist('gs') and $p['page']['fileData']['ext']=='pdf') {
    msPDF::optimizeWithGS($source, $destination);
  } else {
    copy($source, $destination);
  }

  unlink($source);

  if($p['page']['boxParams']['endTarget'] == 'patient') {
    msTools::redirection('/patient/'.$_POST['patientID'].'/');
  } else {
    msTools::redirection('/dropbox/#'.$_POST['box']);
  }

} else {
  die("Ce fichier n'existe pas dans la boite de dépôt");
}
