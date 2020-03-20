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
 * enregistrement des paramètres utilisateur : nouveau mot de passe
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

$gump = new GUMP('fr');
$_POST = $gump->sanitize($_POST);

$gump->validation_rules(array(
	'p_currentPassword' => 'required|checkPasswordValidity,'.$p['user']['id'],
	'p_password' => 'required|max_len,40|checkPasswordLength',
	'p_verifPassword' => 'equalsfield,p_password',
));

$gump->filter_rules(array(
	'p_currentPassword' => 'trim',
	'p_password' => 'trim',
	'p_verifPassword' => 'trim',
));

$gump->set_field_names(array(
  'p_currentPassword' => 'mot de passe actuel',
  'p_password' => 'nouveau mot de passe',
  'p_verifPassword' => 'copie du nouveau mot de passe',
));

$validated_data = $gump->run($_POST);

unset($_SESSION['form'][$_POST['formIN']]);
if ($validated_data === false) {
  $_SESSION['form'][$_POST['formIN']]['validationErrorsMsg']=$gump->get_readable_errors();
} else {
  msUser::setUserNewPassword($p['user']['id'], $_POST['p_password']);
}

msTools::redirection('/user/userParameters/#pmdp');
