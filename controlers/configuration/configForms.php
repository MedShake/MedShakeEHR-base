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
 * Config : liste des formulaires
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

 //admin uniquement
 if (!msUser::checkUserIsAdmin()) {
     $template="forbidden";
 } else {
     $template="configForms";
     $p['page']['groupe']=isset($match['params']['groupe'])?$match['params']['groupe']:'';
     $debug='';

        // liste des types par catégorie
        if ($tabTypes=msSQL::sql2tab("select f.id, f.internalName, f.name, f.description, f.module, c.name as catName, c.label as catLabel
						from forms as f
						left join forms_cat as c on c.id=f.cat
						where f.id > 0 and f.type='public'
						group by f.id
						order by c.label asc, f.module, f.id asc")) {
            foreach ($tabTypes as $v) {
                $p['page']['tabTypes'][$v['catName']][]=$v;
            }
        }

        // liste des catégories
        if ($p['page']['catList']=msSQL::sql2tabKey("select id, label from forms_cat order by label", 'id', 'label'));

        //liste des modules
        $p['page']['modules']=msSQL::sql2tabKey("SELECT name AS module FROM system WHERE groupe='module' order by name", "module", "module");
 }
