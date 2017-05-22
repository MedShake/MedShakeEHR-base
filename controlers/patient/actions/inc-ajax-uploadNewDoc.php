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
 * Patient > ajax : upload par drag&drop d'un nouveau document dans le dossier patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$fichier=$_FILES['file'];
$mimetype=msTools::getmimetype($fichier['tmp_name']);
if ($mimetype=='application/pdf') {
    $ext='pdf';
} elseif ($mimetype=='text/plain') {
    $ext='txt';
}

if (isset($ext)) {
    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);
    $patient->setToID($_POST['patientID']);

    //si txt alors on chope le contenu
    if($ext=='txt') $corps=msInbox::getMessageBody($fichier['tmp_name']); else $corps='';

    //support
    $supportID=$patient->createNewObjet(184,  $corps);

    //non fonctionnel car non supporté par l'uploader ...
    // if(isset($_POST['titre'])) {
    //   echo $_POST['titre'];
    //   msObjet::setTitleObjet($supportID, $_POST['titre']);
    // }

    //nom original
    $patient->createNewObjet(185, $fichier['name'], $supportID);
    //type
    $patient->createNewObjet(183, $ext, $supportID);

    //folder
    $folder=msStockage::getFolder($supportID);

    //creation folder si besoin
    msTools::checkAndBuildTargetDir($p['config']['stockageLocation']. $folder.'/');

    $destination_file= $p['config']['stockageLocation']. $folder.'/'.$supportID.'.'.$ext;
    move_uploaded_file($fichier['tmp_name'], $destination_file);
}

die();
