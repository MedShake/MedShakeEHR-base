<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * people : ajax > détruire un dossier
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template='peopleDestroyed';

$gump=new GUMP('fr');
$_POST = $gump->sanitize($_POST);
$gump->validation_rules(array(
  'peopleID'=> 'required|numeric',
  'p_password'=> 'required|checkPasswordValidity,'.$p['user']['id'],
));

$gump->filter_rules(array(
  'id'=> 'trim',
  'p_password'=> 'trim',
));

$gump->set_field_name("p_password", "Mot de passe");

$validated_data = $gump->run($_POST);

if ($validated_data === false) {
  $return['status']='failed';
  $errors = $gump->get_errors_array();
  $return['msg']=$errors;
  $return['code']=array_keys($errors);
} else {

  $peopleDel = new msPeopleDestroy();
  $peopleDel->setToID($_POST['peopleID']);
  $peopleDel->setFromID($p['user']['id']);
  if($peopleDel->getDestroyAutorisation()) {
    $peopleDel->destroyPeopleData();
    $return['status']='ok';
  } else {
    $return['status']='failed';
    $return['msg'] = $peopleDel->getBlockingReasons();
  }
}

exit(json_encode($return));
