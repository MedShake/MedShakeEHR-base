<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2020
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
 * Dropbox > ajax : supprimer un doc sans le classer
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */
 if($p['config']['dropboxActiver'] != 'true') die;
 if(!isset($_POST['box']) or !isset($_POST['filename'])) die;
 if(!is_string($_POST['box']) or !is_string($_POST['filename'])) die;

 $dropbox = new msDropbox;
 $dropbox->setCurrentBoxId($_POST['box']);
 $p['page']['boxParams'] = $dropbox->getAllBoxesParametersCurrentUser()[$_POST['box']];

 if($dropbox->checkFileIsInCurrentBox($_POST['filename'])) {
   $dropbox->setCurrentFilename($_POST['filename']);
   $p['page']['fileData'] = $dropbox->getCurrentFileData();

   if(unlink($p['page']['fileData']['fullpath'])) {
     exit(json_encode(['status'=>'ok']));
   } else {
     exit(json_encode(['status'=>'ko']));
   }
 }
exit(json_encode(['status'=>'ko']));
