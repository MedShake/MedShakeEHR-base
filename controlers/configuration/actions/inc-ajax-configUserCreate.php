<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * Config : créer un utilisateur
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

unset($_SESSION['form'][$_POST['formIN']]);

//construc validation rules
$form = new msFormValidation();
$form->setformIDbyName($_POST['formIN']);
$form->setPostdatas($_POST);
$form->setContextualValidationErrorsMsg(false);
$form->setContextualValidationRule('username',['checkUniqueUsername','alpha_dash','max_len,25']);
$form->setContextualValidationRule('password',['checkPasswordLength']);
$form->setContextualValidationRule('birthname',['checkNoName']);
$form->setContextualValidationRule('lastname',['checkNoName']);
$validation=$form->getValidation();

if ($validation === false) {
  exit (json_encode(array(
    'status'=>'error',
    'msg'=>$_SESSION['form'][$_POST['formIN']]['validationErrorsMsg'],
    'code'=>$_SESSION['form'][$_POST['formIN']]['validationErrors']
  )));

} else {
  $module=isset($_POST['p_modules'])?$_POST['p_modules']:'base';
  $user=$p['user']['id']?:1;

  $data=array(
      'name' => $_POST['p_username'],
      'type' => 'pro',
      'rank' => '',
      'module' => $module,
      'registerDate' => date("Y/m/d H:i:s"),
      'fromID' => $user
  );

  if(isset($_POST['preUserID']) and is_numeric($_POST['preUserID'])) $data['id']=$_POST['preUserID'];

  if($id=msSQL::sqlInsert('people', $data)) {
    msUser::setUserNewPassword($id, $_POST['p_password']);

    $obj = new msObjet();
    $obj->setFromID($p['user']['id']);
    $obj->setToID($id);
    if (isset($_POST['p_firstname'])) {
        $obj->createNewObjetByTypeName('firstname', $_POST['p_firstname']);
    }
    if (isset($_POST['p_birthname'])) {
        $obj->createNewObjetByTypeName('birthname', $_POST['p_birthname']);
    }
    if (isset($_POST['p_lastname'])) {
        $obj->createNewObjetByTypeName('lastname', $_POST['p_lastname']);
    }

    // application du template si précisé
    if(isset($_POST['p_userTemplate']) and !empty($_POST['p_userTemplate'])) {
      $directory=$homepath.'config/userTemplates/';
      $fichier=basename($_POST['p_userTemplate']).'.yml';
      if(is_file($directory.$fichier)) {
        $dataTp = Spyc::YAMLLoad($directory.$fichier);
        if(is_array($dataTp) and !empty($dataTp)) {
          foreach($dataTp as $k=>$v) {
            if(array_key_exists($k, $p['config'])) {
              msConfiguration::setUserParameterValue($k, $v, $id);
            }
          }
        }
      }
    }
  }
  unset($_SESSION['form'][$_POST['formIN']]);
  exit(json_encode(array('status'=>'ok')));
}
