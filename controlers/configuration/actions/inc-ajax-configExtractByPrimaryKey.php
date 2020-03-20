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
 * Config > ajax : extraire une entrée via la primary key
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

$acceptedTables=array(
    'data_types',
    'data_cat',
    'forms_cat',
    'prescriptions',
    'prescriptions_cat',
    'actes',
    'actes_cat',
    'actes_base',
    'dicomTags'
);

$do=true;
$table=msSQL::cleanVar($_POST['table']);
$id=msSQL::cleanVar($_POST['id']);
if (!is_numeric($id) or !in_array($table, $acceptedTables)) {
    $do=false;
}

if ($do) {
    if ($data=msSQL::sqlUnique("select * from $table where id = '$id' limit 1")) {
        echo json_encode($data);
    } else {
        http_response_code(401);
    }
} else {
    http_response_code(401);
}
