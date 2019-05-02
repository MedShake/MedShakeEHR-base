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
 * Config > action : sauver un template PDF dans un fichier
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

//utilisateurs ayant un repertoire de templates spécifique
$p['page']['templatesDirUsers']=msPeople::getUsersWithSpecificParam('templatesPdfFolder');

$user=array('id'=>$_POST['userID'], 'module'=>'');
$directory=msConfiguration::getParameterValue('templatesPdfFolder', $user);

$fichier=basename($_POST['fichier'],'.html.twig');
$fichier=$fichier.'.html.twig';

// si user
if (is_numeric($_POST['userID'])) {
    $gotoSaveOnly='/configuration/templates-pdf/edit/'.$fichier.'/'.$_POST['userID'].'/';
    $gotoSaveAndEnd='/configuration/templates-pdf/'.$_POST['userID'].'/';
} else {
    $gotoSaveOnly='/configuration/templates-pdf/edit/'.$fichier.'/';
    $gotoSaveAndEnd='/configuration/templates-pdf/';
}

//construction du répertoire si besoin
msTools::checkAndBuildTargetDir($directory);

file_put_contents($directory.$fichier, $_POST['code']);

if (isset($_POST['saveAndEnd'])) {
    $goto=$gotoSaveAndEnd;
} else {
    $goto=$gotoSaveOnly;
}
msTools::redirection($goto);
