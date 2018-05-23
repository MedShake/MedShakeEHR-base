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
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

if(!isset($_FILES['file']) or !is_numeric($_POST['patientID'])) die;

$fichier=$_FILES['file'];
$mimetype=msTools::getmimetype($fichier['tmp_name']);
$acceptedtypes=array(
    'application/pdf'=>'pdf',
    'text/plain'=>'txt',
    'image/png'=>'png',
    'image/jpeg'=>'jpg'
    );
if (array_key_exists($mimetype, $acceptedtypes)) {
    $ext=$acceptedtypes[$mimetype];

    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);
    $patient->setToID($_POST['patientID']);

    //si txt alors on chope le contenu
    if($ext=='txt') $corps=msInbox::getMessageBody($fichier['tmp_name']); else $corps='';

    //support
    $supportID=$patient->createNewObjetByTypeName('docPorteur',  $corps);

    //non fonctionnel car non supporté par l'uploader ...
    // if(isset($_POST['titre'])) {
    //   echo $_POST['titre'];
    //   msObjet::setTitleObjet($supportID, $_POST['titre']);
    // }

    //nom original
    $patient->createNewObjetByTypeName('docOriginalName', $fichier['name'], $supportID);
    //type
    $patient->createNewObjetByTypeName('docType', $ext, $supportID);
    //titre
    $patient->setTitleObjet($supportID, $fichier['name']);
    //folder
    $folder=msStockage::getFolder($supportID);

    //creation folder si besoin
    msTools::checkAndBuildTargetDir($p['config']['stockageLocation']. $folder.'/');

    $destination_file= $p['config']['stockageLocation']. $folder.'/'.$supportID.'.'.$ext;
    move_uploaded_file($fichier['tmp_name'], $destination_file);
}

die();
