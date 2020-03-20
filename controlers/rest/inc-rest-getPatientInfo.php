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
 * Rest : envoyer les informations sur le patient courant
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


// récupérer data prat & patient
$data=json_decode(file_get_contents($p['config']['workingDirectory'].$userID.'/workList.json'), true);

if(!is_array($data)) {
    http_response_code(204);
    die;
}

header('Content-Type: application/json');
die (json_encode($data));
