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
 * @contrib fr33z00 <https://github.com/fr33z00>
 */


$formIN=$_POST['formIN'];

//construc validation rules
$form = new msFormValidation();
$form->setformIDbyName($formIN);
$form->setPostdatas($_POST);
$validation=$form->getValidation();

if ($validation === false) {

    msTools::redirRoute('userLogIn');

} else {

    //check login
    $user = new msUser();
    if (!$user->checkLogin($_POST['p_username'], $_POST['p_password'])) {
        $message='Nous n\'avons pas trouvé d\'utilisateur correspondant ou le mot de passe est incorrect';
        if (!is_array($_SESSION['form'][$formIN]['validationErrorsMsg']) or !in_array($message, $_SESSION['form'][$formIN]['validationErrorsMsg'])) {
            $_SESSION['form'][$formIN]['validationErrorsMsg'][]=$message;
        }
        $validation = false;
    }

    //vérifier 2fa
    if($validation != false and $p['config']['optionGeLogin2FA'] == 'true') {
      if(!$user->check2faValidKey()) {
        // redirection vers création de la clef et affichage
        $user->doLogin();
        unset($_SESSION['form'][$formIN]);
        msTools::redirection('/login/set2fa/');
      } else {
        if(!$user->check2fa((string)$_POST['p_otpCode'])) {
          $message="L'authentification OTP a échoué";
          if(!in_array($message, $_SESSION['form'][$formIN]['validationErrorsMsg'])) $_SESSION['form'][$formIN]['validationErrorsMsg'][]=$message;
          $validation = false;
        }
      }
    }

    //do login
    if ($validation != false) {
        $user->doLogin();

        unset($_SESSION['form'][$formIN]);

        if ('admin'==msSQL::sqlUniqueChamp("SELECT `rank` FROM people WHERE name='".msSQL::cleanVar($_POST['p_username'])."' limit 1") and
            'maintenance'==msSQL::sqlUniqueChamp("SELECT value FROM `system` WHERE name='state' and groupe='system'")) {
            msTools::redirection('/configuration/applyUpdates/');
        }
        msTools::redirection('/patients/');
    } else {
        // Echec de connexion: on écrit dans le log à destination éventuelle de fail2ban, si configuré
        openlog("MedShakeEHR", LOG_PID | LOG_PERROR, LOG_LOCAL0);
        syslog(LOG_WARNING, "MedShakeEHR - echec de connexion depuis {$_SERVER['REMOTE_ADDR']}");
        closelog();

        $form->savePostValues2Session();
        msTools::redirRoute('userLogIn');
    }
}
