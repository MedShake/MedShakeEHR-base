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
 * Config : lister les utilisateurs
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

 //admin uniquement
 if (!msUser::checkUserIsAdmin()) {
     $template="forbidden";
 } else {
     $template="configUsersList";
     $debug='';

     $p['page']['modules']=msModules::getInstalledModulesNames();
     $p['page']['userTemplates']=msConfiguration::getUserTemplatesList();
     $p['page']['userid']=$p['user']['id'];
     $p['page']['users']=msPeopleSearch::getUsersList();

     $formModal = new msForm;
     $formModal->setFormIDbyName($p['page']['formIN']='baseNewUser');
     $formModal->setOptionsForSelect(array(
       'template'=>[''=>'aucun'] + $p['page']['userTemplates'],
       'module'=>$p['page']['modules'],
     ));
     $formModal->setPrevalues(['template'=> $p['config']['optionGeLoginCreationDefaultTemplate'], 'module'=> $p['config']['optionGeLoginCreationDefaultModule']]);
     $p['page']['formModal']=$formModal->getForm();

     if($p['config']['optionGeLoginPassAttribution'] == 'random') {
       $formModal->setFieldAttrAfterwards($p['page']['formModal'], 'password', ['placeholder'=>'aléatoire envoyé par mail', 'readonly'=>'readonly']);
       $formModal->removeFieldAttrAfterwards($p['page']['formModal'], 'password', ['tabindex']);
     } else {
       $formModal->setFieldAttrAfterwards($p['page']['formModal'], 'password', ['required'=>'required']);
     }
 }
