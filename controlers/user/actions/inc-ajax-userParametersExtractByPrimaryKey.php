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
 * Paramètres utilisateur > ajax : extraire une entrée via la primary key
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$acceptedTables=array(
    'prescriptions',
    'prescriptions_cat',
    'actes',
    'actes_cat',
    'actes_base',
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
        header('HTTP/1.1 401 Unauthorized');
    }
} else {
    header('HTTP/1.1 401 Unauthorized');
}
