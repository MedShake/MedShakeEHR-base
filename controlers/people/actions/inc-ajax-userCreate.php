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
 * People : créer un utilisateur à partir d'un praticien
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!msUser::checkUserIsAdmin() and $p['config']['droitDossierPeutTransformerPraticienEnUtilisateur'] != 'true') {die("Erreur: vous n'êtes pas administrateur ou autorisé à effectuer cette action");}

// vérifier que le pro est bien un fils du user et s'assurer de l'utilisation du template par défaut
if (!msUser::checkUserIsAdmin() and $p['config']['droitDossierPeutTransformerPraticienEnUtilisateur'] == 'true') {
  $pro = new msPeople;
  $pro->setToID($_POST['preUserID']);
  if($pro->getFromID() != $p['user']['id']) {
    die("Erreur: vous n'êtes pas autorisé à effectuer cette action");
  }

  if($_POST['p_template'] != $p['config']['optionGeLoginCreationDefaultTemplate']) {
    die("Erreur: vous n'êtes pas autorisé à effectuer cette action");
  }

}

unset($_SESSION['form'][$_POST['formIN']]);

//construc validation rules
$form = new msFormValidation();
$form->setformIDbyName($_POST['formIN']);
$form->setPostdatas($_POST);
$form->setContextualValidationErrorsMsg(false);
$form->setContextualValidationRule('username',['checkUniqueUsername','alpha_numeric_dash','max_len,25']);
$validation=$form->getValidation();

if ($validation === false) {
  exit (json_encode(array(
    'status'=>'error',
    'msg'=>$_SESSION['form'][$_POST['formIN']]['validationErrorsMsg'],
    'code'=>$_SESSION['form'][$_POST['formIN']]['validationErrors']
  )));

} else {
  $module=isset($_POST['p_module'])?$_POST['p_module']:'base';
  $user=$p['user']['id']?:1;

  $data=array(
      'id'=>$_POST['preUserID'],
      'name' => $_POST['p_username'],
      'type' => 'pro',
      'rank' => '',
      'module' => $module,
      'registerDate' => date("Y/m/d H:i:s"),
      'fromID' => $user
  );

  if($id=msSQL::sqlInsert('people', $data)) {

    // mot de passe
    if($p['config']['optionGeLoginPassAttribution'] == 'admin') {
      msUser::setUserNewPassword($id, $_POST['p_password']);
    } elseif($p['config']['optionGeLoginPassAttribution'] == 'random') {
      $randomPassword = msTools::getRandomStr($p['config']['optionGeLoginPassMinLongueur']);
      msUser::setUserNewPassword($id, $randomPassword);
      if(msUser::mailUserNewAccount($id)) {
        msUser::mailUserNewPassword($id, $randomPassword);
      }
    }

    // application du template si précisé
    if(isset($_POST['p_template']) and !empty($_POST['p_template'])) {
      $directory=$homepath.'config/userTemplates/';
      $fichier=basename($_POST['p_template']).'.yml';
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
