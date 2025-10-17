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
 * Config : installer le script PHP Adminer
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib Michaël Val
 */

if (!msUser::checkUserIsAdmin()) {
	die("Erreur: vous n'êtes pas administrateur ou autorisé à effectuer cette action");
}

$output = $homepath . 'public_html/bddEdit.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.adminer.org/latest.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$data = curl_exec($ch);

if (curl_errno($ch)) {
    throw new Exception('Erreur Curl: ' . curl_error($ch));
}
curl_close($ch);

file_put_contents($output, $data);
msTools::redirection('/configuration/');
