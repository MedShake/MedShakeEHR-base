<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00
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
 * @author fr33z00 <https://github.com/fr33z00
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

if (isset($_POST['p_username']) and isset($_POST['p_password'])) {
    $module=isset($_POST['p_module'])?$_POST['p_module']:'base';
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
      if(isset($_POST['p_template']) and !empty($_POST['p_template'])) {
        $directory=$homepath.'config/userTemplates/';
        $fichier=basename($_POST['p_template']).'.yml';
        if(is_file($directory.$fichier)) {
          $dataTp = yaml_parse_file($directory.$fichier);
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
}
msTools::redirection('/configuration/users/');
