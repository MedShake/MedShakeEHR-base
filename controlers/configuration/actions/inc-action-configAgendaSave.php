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
 * Config > action : sauver la configuration d'un agenda
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//utilisateurs pouvant avoir un agenda
$agendaUsers= new msPeople();
$autorisedUsers=$agendaUsers->getUsersListForService('administratifPeutAvoirAgenda');

//construction du répertoire
msTools::checkAndBuildTargetDir($p['config']['webDirectory'].'agendasConfigurations/');

if($_POST['userID']>0 and in_array($_POST['userID'], array_keys($autorisedUsers))) {
  file_put_contents($p['config']['webDirectory'].'agendasConfigurations/configAgenda'.$_POST['userID'].'.js', $_POST['configAgenda']);
  file_put_contents($p['config']['webDirectory'].'agendasConfigurations/configTypesRdv'.$_POST['userID'].'.yml', $_POST['configTypesRdv']);

  if(empty($_POST['configAgenda'])) unlink($p['config']['webDirectory'].'agendasConfigurations/configAgenda'.$_POST['userID'].'.js');
  if(empty($_POST['configTypesRdv'])) unlink($p['config']['webDirectory'].'agendasConfigurations/configTypesRdv'.$_POST['userID'].'.yml');
}

msTools::redirection('/configuration/agenda/'.$_POST['userID'].'/');
