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
 * Fabriquer un PDF à partir d'une version sauvegardée en table printed
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
if(!is_numeric($match['params']['versionID'])) die();
if(!is_numeric($match['params']['patient'])) die();

if ($contenuFinalPDF=msSQL::sqlUniqueChamp("select value from printed where id='".$match['params']['versionID']."' and toID='".$match['params']['patient']."' limit 1")) {

  $doc = new msPDF;
  $doc->setContenuFinalPDF($contenuFinalPDF);
  $doc->showPDF();
  die();

}
