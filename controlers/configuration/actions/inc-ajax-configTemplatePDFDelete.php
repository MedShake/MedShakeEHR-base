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
 * Configuration > ajax : suppression d'un template pour impression pdf
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//accès par admin uniquement
if (!msUser::checkUserIsAdmin()) {
    die;
}
// si pas de fichier à supprimer
if (!isset($_POST['file'])) {
    die;
}

//config défaut
$p['page']['configDefaut']=Spyc::YAMLLoad('../config/config.yml');

//utilisateurs ayant un repertoire de temmplate spécifique
$specificUsers= new msPeople();
$p['page']['specificDirTemplatesUsers']=$specificUsers->getUsersWithSpecificParam('templatesPdfFolder');

// détermination du répertoire
if (isset($p['page']['specificDirTemplatesUsers'][$_POST['userID']])) {
    $directory=$p['page']['specificDirTemplatesUsers'][$_POST['userID']]['paramValue'];
} else {
    $directory=$p['page']['configDefaut']['templatesPdfFolder'];
}

if (is_file($directory.'/'.$_POST['file'])) {
    if (unlink($directory.'/'.$_POST['file'])) {
        echo json_encode(array('ok'));
    } else {
        header("HTTP/1.0 404 Not Found");
    }
} else {
    header("HTTP/1.0 404 Not Found");
}
