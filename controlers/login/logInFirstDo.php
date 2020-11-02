<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00>
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
 * Premier utilisateur
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

unset($_SESSION['form'][$_POST['formIN']]);

if (msSQL::sqlUniqueChamp("SELECT COUNT(*) FROM `people` WHERE `type`='pro'") != "0") {
  msTools::redirRoute('userLogIn');
}

//construc validation rules
$form = new msFormValidation();
$form->setformIDbyName($_POST['formIN']);
$form->setPostdatas($_POST);
$form->setContextualValidationErrorsMsg(false);
$form->setContextualValidationRule('username',['alpha_dash','max_len,25']);
$form->setContextualValidationRule('password',['checkPasswordLength']);
$form->setContextualValidationRule('verifPassword',['equalsfield,p_password']);
$validation=$form->getValidation();

if ($validation === false) {
	msTools::redirRoute('userLogInFirst');
} else {
	// compléter la config par défaut
	$p['config'] = array_merge($p['config'], msConfiguration::getAllParametersForUser());

	if(isset($p['config']['optionGeLoginCreationDefaultModule']) and !empty($p['config']['optionGeLoginCreationDefaultModule'])) {
		$defaultModule = $p['config']['optionGeLoginCreationDefaultModule'];
	} else {
		$defaultModule = 'base';
	}

    $data=array(
        'name' => $_POST['p_username'],
        'type' => 'pro',
        'rank' => 'admin',
        'module' => $defaultModule,
        'registerDate' => date("Y/m/d H:i:s"),
        'fromID' => 1
    );
    if($id=msSQL::sqlInsert('people', $data)) {
      msUser::setUserNewPassword($id, $_POST['p_password']);
      $obj= new msObjet();
      $obj->setToID($id);
      $obj->setFromID(1);
      $obj->createNewObjetByTypeName('firstname', $_POST['p_username']);
      $obj->createNewObjetByTypeName('birthname', 'ADMIN');
    }
    $user = new msUser();
    if (!$user->checkLogin($_POST['p_username'], $_POST['p_password'])) {
        unset($_SESSION['form'][$_POST['formIN']]);
        $message='Un problème est survenu lors de la création de l\'utilisateur.';
        if (!in_array($message, $_SESSION['form'][$_POST['formIN']]['validationErrorsMsg'])) {
            $_SESSION['form'][$_POST['formIN']]['validationErrorsMsg'][]=$message;
        }
        $validation = false;
    }

    //do login
    if ($validation != false) {
        $user-> doLogin();
        unset($_SESSION['form'][$_POST['formIN']]);
        msTools::redirRoute('configDefaultParams');
    } else {
        msTools::redirRoute('userLogIn');
    }
}
