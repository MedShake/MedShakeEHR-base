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
 * Config : aperçu des formulaires DEPRECATED
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

 //admin uniquement
 if (!msUser::checkUserIsAdmin()) {
     $template="forbidden";
 } else {
     $debug='';
     $template="configFormPreview";
     if(!is_numeric($match['params']['form'])) die();

     // liste des forms par catégorie
     $listForms=new msForms;
     $p['page']['tabTypes']=$listForms->getFormsListByCatName();

     // liste des catégories
     $p['page']['catList']=$listForms->getCatListByID();

     //liste des modules
     $p['page']['modules']=msModules::getInstalledModulesNames();


     //sortie du formulaire et préparation à son exploitation par le templates
     if ($p['page']['formData']=msSQL::sqlUnique("select * from forms where id='".$match['params']['form']."' limit 1")) {

         //formulaire
         $forceAllTemplates="oui";
         $form = new msForm();
         $form->setFormID($match['params']['form']);
         $p['page']['form']=$form->getForm();

         $p['page']['basicTemplateCode']=$form->getFlatBasicTemplateCode();
         $sqlGen = new msSqlGenerate;
         $p['page']['sqlCode']=$sqlGen->getSqlForForm($form->getFormIN());
     }
 }
