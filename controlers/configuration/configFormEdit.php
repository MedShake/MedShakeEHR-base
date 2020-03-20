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
 * Config : édition d'un formulaire
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

 //admin uniquement
 if (!msUser::checkUserIsAdmin()) {
     $template="forbidden";
 } else {
     $debug='';
     $template="configFormEdit";
     $p['page']['formID']=$match['params']['form'];
     if (!is_numeric($p['page']['formID'])) {
         die();
     }


    //sortie du formulaire et préparation à son exploitation par le templates
    $form = new msForm;
    $form->setFormID($p['page']['formID']);
    if ($p['page']['form']=$form->getFormRawData()) {
        $p['page']['tabCat']=msForms::getCatListByID();
        $p['page']['form']['catName']=$form->getCatNameFromCatID($p['page']['form']['cat']);

        // liste des types par catégorie
        if ($tabTypes=msSQL::sql2tab("select t.*, c.name as catName, c.label as catLabel
    		from data_types as t
    		left join data_cat as c on c.id=t.cat
    		where t.id > 0 and t.groupe = '".$p['page']['form']['groupe']."'
    		order by c.label asc, t.label asc")) {
            foreach ($tabTypes as $v) {
                $p['page']['tabTypes'][$v['catName']][]=$v;
            }
        }
        //liste des modules
        $p['page']['modules']=msModules::getInstalledModulesNames();
    }
 }
