<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * Config : editer un template utilisateur
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


 //admin uniquement
if (!msUser::checkUserIsAdmin()) {
    $template="forbidden";
    return;
}

$template="configUserTemplatesEdit";
$debug='';

$fichier=urldecode($match['params']['fichier']);
$fichier=basename($fichier);

//vérification fichier existe
$directory=$homepath.'config/userTemplates/';
if (!is_file($directory.$fichier)) {
    die("Ce fichier n'existe pas");
} else {

    //test autorisation d'écriture du dossier template
    if (is_writable($directory)) {
        $p['page']['templatesDirAutorisationEcriture'] = true;
    } else {
        $p['page']['templatesDirAutorisationEcriture'] = false;
    }

    $p['page']['fichier']['name']=$fichier;
    $p['page']['fichier']['chemin']=$directory;
    $p['page']['fichier']['code']= file_get_contents($directory.$fichier);
}
