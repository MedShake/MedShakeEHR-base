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


$formIN=$_POST['formIN'];

//construc validation rules
$form = new msForm();
$form->setformIDbyName($formIN);
$form->setPostdatas($_POST);
$validation=$form->getValidation();

if (msSQL::sqlUniqueChamp("SELECT COUNT(*) FROM people WHERE type='pro'") != "0") {
    msTools::redirRoute('userLogIn');
} else if ($validation === false) {
    unset($_SESSION['form'][$formIN]);
    $_SESSION['form'][$formIN]['validationErrorsMsg'][]='Veillez à bien remplir l\'identifiant et les deux champs de mot de passe.';
    msTools::redirRoute('userLogInFirst');
} else if ($_POST['p_password'] != $_POST['p_verifPassword']) {
    unset($_SESSION['form'][$formIN]);
    $_SESSION['form'][$formIN]['validationErrorsMsg'][]='Veillez à bien remplir les deux champs de mot de passe de façon identique.';
    msTools::redirRoute('userLogInFirst');
} else {
    $data=array(
        'name' => $_POST['p_username'],
        'type' => 'pro',
        'rank' => 'admin',
        'module' => 'base',
        'registerDate' => date("Y/m/d H:i:s"),
        'fromID' => 1
    );
    if($id=msSQL::sqlInsert('people', $data)) {
      msUser::setUserNewPassword($id, $_POST['p_password']);
      $obj= new msObjet();
      $obj->setToID($id);
      $obj->setFromID(1);
      $obj->createNewObjetByTypeName('firstname', $_POST['p_username']);
      $obj->createNewObjetByTypeName('lastname', 'ADMIN');
    }
    $user = new msUser();
    if (!$user->checkLogin($_POST['p_username'], $_POST['p_password'])) {
        unset($_SESSION['form'][$formIN]);
        $message='Un problème est survenu lors de la création de l\'utilisateur.';
        if (!in_array($message, $_SESSION['form'][$formIN]['validationErrorsMsg'])) {
            $_SESSION['form'][$formIN]['validationErrorsMsg'][]=$message;
        }
        $validation = false;
    }

    //do login
    if ($validation != false) {
        $user-> doLogin();
        unset($_SESSION['form'][$formIN]);
        msTools::redirRoute('configDefaultParams');
    } else {
        msTools::redirRoute('userLogIn');
    }
}
