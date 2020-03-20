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
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

// si pas de fichier à supprimer
if (!isset($_POST['file'])) {
    die;
}

$fichier=basename($_POST['file']);

// détermination du répertoire
$user=array('id'=>$_POST['userID'], 'module'=>'');
$directory=msConfiguration::getParameterValue('templatesPdfFolder', $user);

if (is_file($directory.'/'.$fichier)) {
    if (unlink($directory.'/'.$fichier)) {
        echo json_encode(array('ok'));
    } else {
        http_response_code(404);
    }
} else {
    http_response_code(404);
}
