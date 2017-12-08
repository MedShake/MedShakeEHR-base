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
 * Pivot central des pages de capture dicom par smartphone
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 $debug='';
 $template="phonecapture";

//vérification des cookies
if ($p['user']['id']==null) {
    $p['page']['error'][]="Vous n'êtes pas identifié pour PhoneCapture ! Flasher le QR code accessible par votre menu utilisateur une fois identifié dans MedShakeEHR sur votre ordinateur";
    $p['page']['errorReloadButton']=false;
} else {
    $p['page']['errorReloadButton']=true;

    //récupération info prat et patient

    $jsonFile=$p['config']['workingDirectory'].$p['user']['id'].'/workList.json';

    if (is_file($jsonFile)) {
      $p['page']['data']=json_decode(file_get_contents($jsonFile), true);
      if (!is_array($p['page']['data'])) {
          $p['page']['error'][]="Aucun dossier patient valide ne semble ouvert pour le moment. Ouvrez un dossier sur votre ordinateur et rechargez la page !";
      }
    } else {
      $p['page']['error'][]="Aucun dossier patient ne semble ouvert pour le moment. Ouvrez un dossier sur votre ordinateur et rechargez la page !";
    }
}

//template si erreur
if (isset($p['page']['error'])) {
    $template="phonecaptureError";
}
