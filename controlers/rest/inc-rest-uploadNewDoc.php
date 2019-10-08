<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00>
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
 * RESTful : recevoir des documents
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

$user=new msUser();
if (!isset($_SERVER['PHP_AUTH_USER']) or !isset($_SERVER['PHP_AUTH_PW']) or !$user->checkLogin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
   http_response_code(401);
   die;
}
$userID=msSQL::sqlUniqueChamp("select id from people where name='".msSQL::cleanVar($_SERVER['PHP_AUTH_USER'])."'");

if ((!array_key_exists('timestamp', $parameters) or $parameters['timestamp'] < date_sub(new Datetime(), new Dateinterval("PT0H15M0S"))->format("Y-m-d H:i:s"))) {
   http_response_code(401);
   die;
}

if (!is_array($parameters) or !array_key_exists('filename', $parameters)) {
    http_response_code(400);
    die;
}
$filename=basename($parameters['filename']);
$titre=array_key_exists('title', $parameters)?$parameters['title']:$filename;

$fichier=tempnam($p['config']['workingDirectory'], "file");
file_put_contents($fichier, file_get_contents("php://input"));

$mimetype=msTools::getmimetype($fichier);
$acceptedtypes=array(
    'application/pdf'=>'pdf',
    'text/plain'=>'txt',
    'image/png'=>'png',
    'image/jpeg'=>'jpg'
    );
if (!array_key_exists($mimetype, $acceptedtypes)) {
    unlink($fichier);
    http_response_code(403);
    die;
}

$ext=$acceptedtypes[$mimetype];

// récupérer data prat & patient
$job=json_decode(file_get_contents($p['config']['workingDirectory'].$userID.'/workList.json'), true);

$obj = new msObjet();
$obj->setFromID($job['prat']['pratID']);
$obj->setToID($job['patient']['id']);


$supportID=$obj->createNewObjetByTypeName('docPorteur', '');

//nom original
$obj->createNewObjetByTypeName('docOriginalName', $filename, $supportID);
//type
$obj->createNewObjetByTypeName('docType', $ext, $supportID);
//titre
$obj->setTitleObjet($supportID, $titre);

//folder
$folder=msStockage::getFolder($supportID);

//creation folder si besoin
msTools::checkAndBuildTargetDir($p['config']['stockageLocation']. $folder.'/');

$destination_file=$p['config']['stockageLocation']. $folder.'/'.$supportID.'.'.$ext;
rename($fichier, $destination_file);

http_response_code(201);
die;
