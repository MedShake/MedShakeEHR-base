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
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

$method=$_SERVER['REQUEST_METHOD'];
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $parameters);

switch ($match['params']['m']) {
    case 'getPatientInfo': // obtenir les infos sur le patient dans la workList
        if ($method!='GET') {
            http_response_code(405);
            die;
        }
        include 'inc-rest-getPatientInfo.php';
        break;
    case 'uploadNewDoc': // Uploader un document dans dossier patient
        if ($method!='POST') {
            http_response_code(405);
            die;
        }
        include 'inc-rest-uploadNewDoc.php';
        break;
    case 'callbackFse': // retour FSE par services tiers
        if ($method!='GET') {
            http_response_code(405);
            die;
        }
        include 'inc-rest-callbackFse.php';
        break;

}
