<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Config : exécuter les script upgrade à la demande
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */
 ini_set('display_errors', 1);
 //admin uniquement
 if (!msUser::checkUserIsAdmin()) {
     $template="forbidden";
 } else {
   if(isset($match['params']['module'], $match['params']['script'])) {
     $filepath= $homepath.'upgrade/'.$match['params']['module'].'/'.$match['params']['script'].'.php';

     if(is_file($filepath)) {
       echo 'Inclusion de : '.$filepath.'<br>';
       include($filepath);
       echo '<br>Terminé';
     } else {
       echo 'Ce fichier n\'existe pas';
     }

   }
 }
