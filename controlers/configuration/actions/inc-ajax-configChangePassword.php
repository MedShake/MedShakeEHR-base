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
 * Config : change le mot de passe d'un utilisateur
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

if($p['config']['optionGeLoginPassAttribution'] == 'random') {
  //check & validate datas
  $gump=new GUMP('fr');
  $_POST = $gump->sanitize($_POST);
  $gump->validation_rules(array(
    'id'=> 'required|numeric',
  ));
  $gump->filter_rules(array(
    'id'=> 'trim',
  ));

  $validated_data = $gump->run($_POST);

  if ($validated_data === false) {
    exit(json_encode([
      'status'=>'erreur',
      'msg'=>implode('; ',$gump->get_errors_array())
    ]));
  } else {
    $randomPassword = msTools::getRandomStr($p['config']['optionGeLoginPassMinLongueur']);
    msUser::setUserNewPassword($_POST['id'], $randomPassword);
    msUser::mailUserNewPassword($_POST['id'], $randomPassword);

    exit(json_encode([
      'status'=>'ok',
      'msg'=>''
    ]));
  }


} else {
  //check & validate datas
  $gump=new GUMP('fr');
  $_POST = $gump->sanitize($_POST);
  $gump->validation_rules(array(
    'id'=> 'required|numeric',
    'password'=> 'required|checkPasswordLength',
  ));

  $gump->filter_rules(array(
    'id'=> 'trim',
    'password'=> 'trim',
  ));

  $validated_data = $gump->run($_POST);

  if ($validated_data === false) {
    exit(json_encode([
      'status'=>'erreur',
      'msg'=>implode('; ',$gump->get_errors_array())
    ]));
  } else {
    msUser::setUserNewPassword($_POST['id'], $_POST['password']);
    exit(json_encode([
      'status'=>'ok',
      'msg'=>''
    ]));
  }
}
