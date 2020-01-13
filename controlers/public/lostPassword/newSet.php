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
 * Public : procédure lost password, entrer un nouveau password
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if($p['config']['optionGeLoginPassOnlineRecovery'] != "true") {
$template="forbidden";
return;
}

$randStringControl=$match['params']['str'];

$userID = msSQL::sqlUniqueChamp("select id from people where lastLostPassRandStr = '".msSQL::cleanVar($randStringControl)."' and lastLostPassDate >= DATE_SUB(NOW(),INTERVAL 10 MINUTE) limit 1");

if(!is_numeric($userID) or $userID < 1) {
  $template="404";
  return;
}

$template="lostPasswordNewSet";

$form = new msForm;
$form->setFormIDbyName('baseUserPasswordRecovery');
$p['page']['baseUserPasswordRecovery']=$form->getForm();
$p['page']['baseUserPasswordRecoveryJS']=$form->getFormJavascript();
$form->setFieldAttrAfterwards($p['page']['baseUserPasswordRecovery'],'password',['placeholder'=>'nouveau mot de passe']);
$form->setFieldAttrAfterwards($p['page']['baseUserPasswordRecovery'],'verifPassword',['placeholder'=>'confirmation du nouveau mot de passe']);
$form->addHiddenInput($p['page']['baseUserPasswordRecovery'], ['randStringControl'=>$randStringControl]);
