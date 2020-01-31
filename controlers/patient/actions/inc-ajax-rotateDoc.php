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
 * Dossier patient > action : faire une rotation du document image depuis aperçu historique
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$fichier=new msStockage();
$fichier->setObjetID($_POST['fichierID']);
if ($fichier->testDocExist()) {
  $source = $fichier->getPathToDoc();
  $mimetype=msTools::getmimetype($source);
  if(explode('/', $mimetype)[0] == 'image') {

    if(isset($_POST['direction']) and $_POST['direction'] == 'left' ) {
      msImageTools::rotate90($source, $source, 'left');
    } else {
      msImageTools::rotate90($source, $source);
    }
  }
  exit();

}
