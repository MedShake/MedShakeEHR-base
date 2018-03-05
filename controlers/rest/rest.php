<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://www.github.com/fr33z00>
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
 * Pivot pour accès RESTful
 *
 * @author fr33z00 <https://www.github.com/fr33z00>
 */

$user=new msUser();
if (!isset($_SERVER['PHP_AUTH_USER']) or !isset($_SERVER['PHP_AUTH_PW']) or !$user->checkLogin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
    header('HTTP/1.1 401 Unauthorized');
    die;
}
$userID=msSQL::sqlUniqueChamp("select id from people where name='".msSQL::cleanVar($_SERVER['PHP_AUTH_USER'])."'");

$method=$_SERVER['REQUEST_METHOD'];
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $parameters);

if (($method=='POST' or $method=='PUT') and (!array_key_exists('timestamp', $parameters) or $parameters['timestamp'] < date_sub(new Datetime(), new Dateinterval("PT0H15M0S"))->format("Y-m-d H:i:s"))) {
    header('HTTP/1.1 401 Unauthorized');
    die;
}


switch ($match['params']['m']) {
    case 'getPatientInfo': // obtenir les infos sur le patient dans la workList
        if ($method!='GET') {
            header('HTTP/1.1 405 Method Not Allowed');
            die;
        }
        include 'inc-rest-getPatientInfo.php';
        break;
    case 'uploadNewDoc': // Uploader un document dans dossier patient
        if ($method!='POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            die;
        }
        include 'inc-rest-uploadNewDoc.php';
        break;
    default: 
        header('HTTP/1.1 404 Not Found');
        die;
}

