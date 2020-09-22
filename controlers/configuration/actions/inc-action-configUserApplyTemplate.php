<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2020
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
 * Config : appliquer un templates de droits à un utilisateur existant
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

$people = new msPeopleDroits($_POST['userID']);

if(isset($_POST['p_template']) and !empty($_POST['p_template']) and $people->checkIsUser()) {
  $directory=$homepath.'config/userTemplates/';
  $fichier=basename($_POST['p_template']).'.yml';
  if(is_file($directory.$fichier)) {
    $dataTp = yaml_parse_file($directory.$fichier);
    if(is_array($dataTp) and !empty($dataTp)) {
      foreach($dataTp as $k=>$v) {
        if(array_key_exists($k, $p['config'])) {
          msConfiguration::setUserParameterValue($k, $v, $_POST['userID']);
        }
      }
    }
  }
}

msTools::redirection('/configuration/user-param/'.$_POST['userID'].'/');
