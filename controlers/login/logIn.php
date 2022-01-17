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
 * Login : page de login
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';
$template="login";

// vérifications et ajustement saut de version majeure  
$versionBase = msSQL::sqlUniqueChamp("SELECT value AS version FROM `system` WHERE name='base'");
if(version_compare($versionBase, 'v6.0.0', '<')) {
  include($homepath.'/scripts/jumpv5tov6.php');
}

$formLogin = new msForm();
$formLogin->setFormIDbyName($p['page']['formIN']='baseLogin');
$p['page']['form']=$formLogin->getForm();

if($p['config']['optionGeLogin2FA'] == 'false') {
  $formLogin->removeFieldFromForm($p['page']['form'], 'otpCode');
}
