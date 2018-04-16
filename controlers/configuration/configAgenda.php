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
 * Config : gérer les paramètres des agendas
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

 //admin uniquement
if (!msUser::checkUserIsAdmin()) {
    $template="forbidden";
    return;
}
$template="configAgenda";
$debug='';

//utilisateurs pouvant avoir un agenda
$agendaUsers= new msPeople();
$p['page']['agendaUsers']=$agendaUsers->getUsersListForService('administratifPeutAvoirAgenda');

//config défaut
if (!isset($match['params']['userID'])) {
    $p['page']['config']=array_merge(msConfiguration::getCatParametersForUser('Agenda'), msConfiguration::getCatParametersForUser('Options'));
    return;
}

// si user

$p['page']['selectUser']=$match['params']['userID'];

//paramètres de l'agenda
if(is_file($p['homepath'].'config/agendas/agenda'.$match['params']['userID'].'.yml')) {
    $p['page']['configAgenda']=file_get_contents($p['homepath'].'config/agendas/agenda'.$match['params']['userID'].'.yml');
}

if(is_file($p['homepath'].'config/agendas/agenda'.$match['params']['userID'].'.js')) {
    $p['page']['configAgendaJs']=file_get_contents($p['homepath'].'config/agendas/agenda'.$match['params']['userID'].'.js');
}

if(is_file($p['homepath'].'config/agendas/agenda'.$match['params']['userID'].'_ad.js')) {
    $p['page']['configAgendaAd']=file_get_contents($p['homepath'].'config/agendas/agenda'.$match['params']['userID'].'_ad.js');
}

// types de rendez-vous
if(is_file($p['homepath'].'config/agendas/typesRdv'.$match['params']['userID'].'.yml')) {
    $p['page']['typeRdv']=file_get_contents($p['homepath'].'config/agendas/typesRdv'.$match['params']['userID'].'.yml');
}
