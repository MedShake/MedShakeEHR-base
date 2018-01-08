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
 */

unset($_SESSION['formErreursReadable'], $_SESSION['formErreurs'], $_SESSION['formValues']);

$formIN=$_POST['formIN'];

//construc validation rules
$form = new msForm();
$form->setformIDbyName($formIN);
$form->setPostdatas($_POST);
$validation=$form->getValidation();



if (msSQL::sqlUniqueChamp("SELECT COUNT(*) FROM people") != "0") {
    msTools::redirRoute('userLogIn');
} else if ($validation === false) {
    unset($_SESSION['form'][$formIN]);
    $_SESSION['form'][$formIN]['validationErrorsMsg'][]='Veillez à bien remplir les deux champs de mot de passe.';
    msTools::redirRoute('userLogInFirst');
} else if ($_POST['p_2'] != $_POST['p_6']) {
    unset($_SESSION['form'][$formIN]);
    $_SESSION['form'][$formIN]['validationErrorsMsg'][]='Veillez à bien remplir les deux champs de mot de passe de façon identique.';
    msTools::redirRoute('userLogInFirst');
} else {
    $data=array(
        'id' => '1',
        'type' => 'pro',
        'rank' => 'admin',
        'moduleID' => '1',
        'registerDate' => date("Y/m/d H:i:s"),
        'fromID' => 0
    );
    msSQL::sqlInsert('people', $data);
    msSQL::sqlQuery("update people set pass=AES_ENCRYPT('".$_POST['p_2']."',@password) where id='1' limit 1");

    $user = new msUser();
    if (!$user->checkLogin('1', $_POST['p_2'])) {
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
        msTools::redirection('/pro/edit/1/');
    } else {
        msTools::redirRoute('userLogIn');
    }
}
