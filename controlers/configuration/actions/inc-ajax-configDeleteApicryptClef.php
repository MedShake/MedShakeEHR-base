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
 * Configuration > ajax : suppression d'une clef apicrypt
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

//utilisateurs ayant un repertoire de clefs spécifiques
$apicryptUsers= new msPeople();
$p['page']['apicryptClefsUsers']=$apicryptUsers->getUsersWithSpecificParam('apicryptCheminVersClefs');

// détermination du répertoire
if (isset($p['page']['apicryptClefsUsers'][$_POST['userID']])) {
    $directory=$p['page']['apicryptClefsUsers'][$_POST['userID']]['paramValue'];
} else {
    $directory=$p['page']['configDefaut']['apicryptCheminVersClefs'];
}

if (is_file($directory.'Clefs/'.$_POST['file'])) {
    if (unlink($directory.'Clefs/'.$_POST['file'])) {
        echo json_encode(array('ok'));
    } else {
        header("HTTP/1.0 404 Not Found");
    }
} else {
    header("HTTP/1.0 404 Not Found");
}
