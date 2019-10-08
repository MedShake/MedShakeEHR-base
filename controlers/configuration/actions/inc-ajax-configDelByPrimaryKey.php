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
 * Config > ajax : supprimer une entrée via la primary key
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

$acceptedTables=array(
    'data_cat',
    'data_types',
    'forms_cat',
    'prescriptions',
    'actes',
    'prescriptions_cat',
    'actes_cat',
    'actes_base',
    'dicomTags'
);

$table=msSQL::cleanVar($_POST['table']);
$id=msSQL::cleanVar($_POST['id']);
if (!is_numeric($id) or !in_array($table, $acceptedTables)) {
    http_response_code(401);
    die();
}

//conditions by table
$do=false;
if ($table=='data_cat') {
    if (msSQL::sqlUniqueChamp("select count(cat) from data_types where cat='$id'")==0) {
        $do=true;
    }
} elseif ($table=='forms_cat') {
    if (msSQL::sqlUniqueChamp("select count(cat) from forms where cat='$id'")==0) {
        $do=true;
    }
} elseif ($table=='data_types') {
    if (msSQL::sqlUniqueChamp("select count(id) from objets_data where typeID='$id'")==0) {
        $do=true;
    }
} elseif ($table=='prescriptions') {
    $do=true;
    if (msSQL::sqlUniqueChamp("select count(id) from objets_data where parentTypeID='$id'")==0) {
        $do=true;
    }
} elseif ($table=='prescriptions_cat') {
    if (msSQL::sqlUniqueChamp("select count(cat) from prescriptions where cat='$id'")==0) {
        $do=true;
    }
} elseif ($table=='actes') {
    $do=true;
    if (msSQL::sqlUniqueChamp("select count(id) from objets_data where parentTypeID='$id'")==0) {
        $do=true;
    }
} elseif ($table=='actes_cat') {
    if (msSQL::sqlUniqueChamp("select count(cat) from actes where cat='$id'")==0) {
        $do=true;
    }
} elseif ($table=='dicomTags') {

    $do=true;

} elseif ($table=='actes_base') {
  $code=msSQL::sqlUniqueChamp("select code from actes_base where id = '".$id."' limit 1 ");
  if (msSQL::sqlUniqueChamp("select count(id) from actes where details like '%$code:%'")==0) {
      $do=true;
  }
}

// do it if you can !
if ($do) {
    msSQL::sqlQuery("delete from $table where id = '$id' limit 1");
    $return['status']='ok';
    echo json_encode($return);
} else {
    http_response_code(401);
}
