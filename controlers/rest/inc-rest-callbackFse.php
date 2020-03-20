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
 * Rest : retour FSE par services tiers
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//compléter le chargement de la config
$p['config']=array_merge($p['config'], msConfiguration::getAllParametersForUser());

if(!isset($_GET['objetID']) or !isset($_GET['validationHash']) or !is_numeric($_GET['objetID'])) {
  http_response_code(401);
  die();
}

$paiem = new msObjet;
$paiem->setObjetID($_GET['objetID']);
$dataPaiem = $paiem->getObjetAndSons('name');

if(!isset($dataPaiem['regleDetailsActes'])) {
  http_response_code(400);
  die();
}

$validationHashExpected= md5($dataPaiem['regleDetailsActes']['registerDate'].$_GET['objetID'].$dataPaiem['regleDetailsActes']['typeID']);

if($validationHashExpected != $_GET['validationHash']) {
  http_response_code(401);
  die();
}

$paiem->setToID($dataPaiem['regleDetailsActes']['toID']);
$paiem->setFromID(msUser::getUserIdFromName($p['config']['vitaleService']));

if(!$paiem->createNewObjetByTypeName('regleFseData', $_GET['data'], $_GET['objetID'])) {
  http_response_code(400);
} else {
  http_response_code(200);
  header ('Content-Type: image/png');
  $im = @imagecreatetruecolor(3, 3);
  imagepng($im);
}
die();
