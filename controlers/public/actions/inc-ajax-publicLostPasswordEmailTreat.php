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
 * Public > ajax : traiter l'email de recouvrement de password
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if($p['config']['optionGeLoginPassOnlineRecovery'] != "true") {
 die();
}

if(!isset($_POST['email']) or empty($_POST['email']) or !is_string($_POST['email'])) die();

$email = $_POST['email'];

// recherche de l'email et de l'utilisateur correspondant.
$name2typeID = new msData();
$name2typeID = $name2typeID->getTypeIDsFromName(['profesionnalEmail', 'personalEmail']);

if($people = (array)msSQL::sql2tabSimple("select toID from objets_data where value = '".msSQL::cleanVar($email)."' and typeID in ('".implode("', '", $name2typeID)."') and outdated ='' and deleted ='' ")) {

  // on trie pour ne garder que les utilisateur actifs
  foreach ($people as $k=>$userID) {
    $droits = new msPeopleDroits($userID);
    if(!$droits->checkIsUser() or $droits->checkIsDestroyed()) unset($people[$k]);
  }

  // on évite le cas où l'email est attribuée à plusieurs users
  if(count($people) == 1) {
    sort($people);
    $userID = $people[0];
    $user = new msUser;
    $user->setUserID($userID);
    if($user->setUserAccountToNewPasswordRecoveryProcess()) {
      $user->mailUserPasswordRecoveryProcess($email);
    }
    //echo "ok";
  }

  die();
}
