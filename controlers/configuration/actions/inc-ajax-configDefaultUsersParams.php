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
 * Config > action : enregistrer les paramètres par défaut des utilisateurs
 *
 * @author fr33z00 <https://github.com/fr33z00>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");} 

$booleans=array(
          'PraticienPeutEtrePatient',
          'twigEnvironnementAutoescape',
          'twigEnvironnementCache'
          );

unset($p['configDefaut']['homeDirectory']);

foreach ($p['configDefaut'] as $param=>$v) {
    if (array_key_exists($param, $_POST)) {
      if (in_array($param, $booleans)) {
          if ($_POST[$param]==='true') {
              $_POST[$param]=true;
          } elseif ($_POST[$param]==='false') {
              $_POST[$param]=false;
          }
      }
      $p['configDefaut'][$param]=$_POST[$param];
    }
}
file_put_contents($p['config']['homeDirectory'].'config/config.yml', Spyc::YAMLDump($p['configDefaut'], false, 0, true));

echo json_encode(array('ok'));
