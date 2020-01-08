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
 * Public > ajax : traiter le nouveau mot de passe souhaité
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

header('Content-Type: application/json');

if($p['config']['optionGeLoginPassOnlineRecovery'] != "true") {
 die();
}

unset($_SESSION['form']['baseUserPasswordRecovery']);

if(!isset($_POST['randStringControl']) or !is_string($_POST['randStringControl'])) die();

$userID = msSQL::sqlUniqueChamp("select id from people where lastLostPassRandStr = '".msSQL::cleanVar($_POST['randStringControl'])."' and lastLostPassDate >= DATE_SUB(NOW(),INTERVAL 10 MINUTE) limit 1");
if(!is_numeric($userID) or $userID < 1) {
  $_SESSION['form']['baseUserPasswordRecovery']['validationErrorsMsg'][]="La durée de validité pour effectuer l'opération est dépassée";
  exit (json_encode(array(
    'status'=>'error',
    'msg'=>$_SESSION['form']['baseUserPasswordRecovery']['validationErrorsMsg'],
    'code'=>''
  )));
}

//construction validation rules
$form = new msFormValidation();
$form->setformIDbyName('baseUserPasswordRecovery');
$form->setPostdatas($_POST);
$form->setContextualValidationErrorsMsg(false);
$form->setContextualValidationRule('password',['required','checkPasswordLength']);
$form->setContextualValidationRule('verifPassword',['equalsfield,p_password']);
$validation=$form->getValidation();

if ($validation === false) {
 exit (json_encode(array(
   'status'=>'error',
   'msg'=>$_SESSION['form']['baseUserPasswordRecovery']['validationErrorsMsg'],
   'code'=>$_SESSION['form']['baseUserPasswordRecovery']['validationErrors']
 )));

} else {
  $user = new msUser;
  $user->setUserID($userID);
  $user->setUserNewPassword($userID, $_POST['p_password']);
  $user->setUserAccountPasswordRecoveryProcessClosed();

  exit (json_encode(array(
    'status'=>'ok',
    'msg'=>'',
    'code'=>''
  )));

}
