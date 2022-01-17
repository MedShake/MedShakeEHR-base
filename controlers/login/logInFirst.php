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
 * Login : page de login
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template="firstLogin";

if (msSQL::sqlUniqueChamp("SELECT COUNT(*) FROM `people` WHERE `type`='pro'") != "0") {
  msTools::redirRoute('userLogIn');
}

// compléter la config par défaut
$p['config'] = array_merge($p['config'], msConfiguration::getAllParametersForUser());

$form = new msForm();
$form->setFormIDbyName($p['page']['formIN']='baseFirstLogin');
$p['page']['form']=$form->getForm();
