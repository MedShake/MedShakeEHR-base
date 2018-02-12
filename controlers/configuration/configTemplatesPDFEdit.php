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
 * Config : editer un template de production de PDF
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */


 //admin uniquement
 if (!msUser::checkUserIsAdmin()) {
     $template="forbidden";
 } else {
     $template="configTemplatesPDFEdit";
     $debug='';

     $fichier=urldecode($match['params']['fichier']);

     //config défaut
     $p['page']['configDefaut']=$p['configDefaut'];

     //utilisateurs ayant un repertoire de templates spécifique
     $specificUsers= new msPeople();
     $p['page']['templatesDirUsers']=$specificUsers->getUsersWithSpecificParam('templatesPdfFolder');

     // si user
     if (isset($match['params']['userID'])) {
         $p['page']['selectUser']=$match['params']['userID'];
         msUser::applySpecificConfig($p['page']['configDefaut'], $p['page']['selectUser']);
         $p['page']['repertoireTemplatesPDF']=$p['page']['templatesDirUsers'][$match['params']['userID']]['paramValue'];

         $proprio = new msPeople();
         $proprio->setToID($match['params']['userID']);
         $p['page']['fichier']['proprio']=$proprio->getSimpleAdminDatas();
     } else {
         $p['page']['repertoireTemplatesPDF']=$p['page']['configDefaut']['templatesPdfFolder'];
     }

     //vérification fichier existe
     if (!is_file($p['page']['repertoireTemplatesPDF'].$fichier)) {
         die("Ce fichier n'existe pas");
     } else {

         //test autorisation d'écriture du dossier template
         if (is_writable($p['page']['repertoireTemplatesPDF'])) {
             $p['page']['templatesDirAutorisationEcriture'] = true;
         } else {
             $p['page']['templatesDirAutorisationEcriture'] = false;
         }

         $p['page']['fichier']['name']=$fichier;
         $p['page']['fichier']['chemin']=$p['page']['repertoireTemplatesPDF'];
         $p['page']['fichier']['code']= file_get_contents($p['page']['repertoireTemplatesPDF'].$fichier);
     }
 }
