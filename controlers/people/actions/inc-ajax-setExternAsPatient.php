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
 * People : ajax > relier un externe à un patient
 *
 * @author fr33z00 <https://www.github.com/fr33z00>
 */


if($_POST['externID']<1) die;
if($_POST['patientID']<1) die;

$obj=new msObjet();
$obj->setToID($_POST['externID']);
$obj->setFromID($p['user']['id']);
$obj->createNewObjetByTypeName('relationExternePatient', $_POST['patientID']);

echo json_encode(array('ok'));
