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
 * People : ajax > retirer une relation patient <-> praticien
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


if($_POST['ID1']<1) die;
if($_POST['ID2']<1) die;

$typeID = msData::getTypeIDFromName('relationID');
if($typeID<1) die;


// patient -> praticien/patient
if ($id=msSQL::sqlUniqueChamp("select id from objets_data where typeID='".$typeID."' and toID='".$_POST['ID1']."' and value='".$_POST['ID2']."' and deleted='' limit 1")) {
    if($id>0) msSQL::sqlQuery("update objets_data set deleted='y', updateDate='".date("Y/m/d H:i:s")."' where id='".$id."' or instance='".$id."'");

}


// praticien/patient -> patient
if ($id=msSQL::sqlUniqueChamp("select id from objets_data where typeID='".$typeID."' and toID='".$_POST['ID2']."' and value='".$_POST['ID1']."' and deleted='' limit 1")) {
    if($id>0) msSQL::sqlQuery("update objets_data set deleted='y', updateDate='".date("Y/m/d H:i:s")."' where id='".$id."' or instance='".$id."'");
}

echo json_encode(array('ok'));
