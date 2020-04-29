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
 * Renvoyer un fichier stocké dans le navigateur
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';

$fichier=new msStockage();
$fichier->setObjetID($match['params']['fichierID']);
if ($fichier->testDocExist()) {

    //vérifier les droits
    $droits = new msPeopleDroits($p['user']['id']);
    if(!$droits->checkUserCanSeePatientData($fichier->getToID())) {
      $template="forbidden";
      return;
    }

    $mimetype=msTools::getmimetype($fichier->getPathToDoc());
    header("Content-type: ".$mimetype);
    if($mimetype == 'text/plain') {
      $content=file_get_contents($fichier->getPathToDoc());
      if (!mb_detect_encoding($content, 'utf-8', true)) {
        header('Content-Type: '.$mimetype.'; charset=iso-8859-1');
      }
    }
    readfile($fichier->getPathToDoc());
} else {
    die("Ce document n'existe pas.");
}
