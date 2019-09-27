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
 * enregistrement des paramètres utilisateur
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */


$formIN=$_POST['formIN'];

//construc validation rules
$form = new msForm();
$form->setformIDbyName($formIN);
$form->setPostdatas($_POST);

$changeMdp=false;

if (!empty($_POST['p_password']) or !empty($_POST['p_verifPassword'])) {
    unset($_SESSION['form'][$formIN]);
    if (empty($_POST['p_currentPassword'])) {
        unset($_SESSION['form'][$formIN]);
        $_SESSION['form'][$formIN]['validationErrorsMsg'][]='Pour changer le mot de passe de votre compte MedShakeEHR, vous devez entrer votre mot de passe actuel.';
        msTools::redirRoute('userParameters');
    } elseif ($_POST['p_password'] != $_POST['p_verifPassword']) {
        unset($_SESSION['form'][$formIN]);
        $_SESSION['form'][$formIN]['validationErrorsMsg'][]='Veillez à bien remplir les deux champs de nouveau mot de passe de façon identique.';
        msTools::redirRoute('userParameters');
    }
    else {
        $checkLogin = new msUser;
        if ($checkLogin->checkLoginByUserID($p['user']['id'], $_POST['p_currentPassword'])) {
            $changeMdp=true;
        } else {
            unset($_SESSION['form'][$formIN]);
            $_SESSION['form'][$formIN]['validationErrorsMsg'][]='Le champ de mot de passe actuel du compte MedShakeEHR n\'est pas correct.';
            msTools::redirRoute('userParameters');
        }
    }
}

if ($changeMdp) {
    msUser::setUserNewPassword($p['user']['id'], $_POST['p_password']);
}

msTools::redirRoute('userParameters');
