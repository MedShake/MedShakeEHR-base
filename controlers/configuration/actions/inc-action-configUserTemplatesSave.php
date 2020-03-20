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
 * Config > action : sauver un template utilisateur dans un fichier
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

$directory=$homepath.'config/userTemplates/';
$fichier=basename($_POST['fichier'],'.yml');
$fichier=$fichier.'.yml';

$gotoSaveOnly='/configuration/user-templates/edit/'.$fichier.'/';
$gotoSaveAndEnd='/configuration/user-templates/';

//construction du répertoire si besoin
msTools::checkAndBuildTargetDir($directory);

file_put_contents($directory.$fichier, $_POST['code']);

if (isset($_POST['saveAndEnd'])) {
    $goto=$gotoSaveAndEnd;
} else {
    $goto=$gotoSaveOnly;
}
msTools::redirection($goto);
