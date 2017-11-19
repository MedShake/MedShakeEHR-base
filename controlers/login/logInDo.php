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
 * Login : loguer ou renvoyer
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

unset($_SESSION['formErreursReadable'], $_SESSION['formErreurs'], $_SESSION['formValues']);

// form number
//if (is_numeric($_POST['formID'])) {
    $formID=$_POST['formID'];
//} else {
//    die();
//}

//construc validation rules
$form = new msForm();
$form->setFormIDbyName($formID);
$form->setPostdatas($_POST);
$validation=$form->getValidation();



if ($validation === false) {
    msTools::redirRoute('userLogIn');
} else {

    //check login
    $user = new msUser();
    if (!$user->checkLogin($_POST['p_1'], $_POST['p_2'])) {
        unset($_SESSION['form'][$formID]);
        $message='Nous n\'avons pas trouvé d\'utilisateur correspondant';
        if (!in_array($message, $_SESSION['form'][$formID]['validationErrorsMsg'])) {
            $_SESSION['form'][$formID]['validationErrorsMsg'][]=$message;
        }
        $validation = false;
    }

    //do login
    if ($validation != false) {
        $user-> doLogin();
        unset($_SESSION['form'][$formID]);
        msTools::redirection('/patients/');
    } else {
        $form->savePostValues2Session();
        msTools::redirRoute('userLogIn');
    }
}
