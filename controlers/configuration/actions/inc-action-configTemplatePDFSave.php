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
 */

 //config défaut
 $p['page']['configDefaut']=Spyc::YAMLLoad('../config/config.yml');

 //utilisateurs ayant un repertoire de templates spécifique
 $specificUsers= new msPeople();
 $p['page']['templatesDirUsers']=$specificUsers->getUsersWithSpecificParam('templatesPdfFolder');

 // si user
 if (isset($_POST['userID'])) {
     msUser::applySpecificConfig($p['page']['configDefaut'], $_POST['userID']);
     $p['page']['repertoireTemplatesPDF']=$p['page']['templatesDirUsers'][$_POST['userID']]['paramValue'];
     $gotoSaveOnly='/configuration/templates-pdf/edit/'.$_POST['fichier'].'/'.$_POST['userID'].'/';
     $gotoSaveAndEnd='/configuration/templates-pdf/'.$_POST['userID'].'/';
 } else {
     $p['page']['repertoireTemplatesPDF']=$p['page']['configDefaut']['templatesPdfFolder'];
     $gotoSaveOnly='/configuration/templates-pdf/edit/'.$_POST['fichier'].'/';
     $gotoSaveAndEnd='/configuration/templates-pdf/';
 }

//construction du répertoire si besoin
msTools::checkAndBuildTargetDir($p['page']['repertoireTemplatesPDF']);

file_put_contents($p['page']['repertoireTemplatesPDF'].$_POST['fichier'], $_POST['code']);

if (isset($_POST['saveAndEnd'])) {
    $goto=$gotoSaveAndEnd;
} else {
    $goto=$gotoSaveOnly;
}
msTools::redirection($goto);
