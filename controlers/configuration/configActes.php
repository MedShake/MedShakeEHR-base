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
 * Config : gérer les actes
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 //admin uniquement
 if (!msUser::checkUserIsAdmin()) {
     $template="forbidden";
 } else {
     $template="configActes";
     $debug='';

     if (isset($match['params']['cat'])) {
         $cat=$match['params']['cat'];
         if (is_numeric($cat)) {
             $where="where a.cat='".$cat."'";
         }
     } else {
         $where=null;
     }

	    // liste des types par catégorie
	    if ($tabTypes=msSQL::sql2tab("select a.* , c.name as catName, c.label as catLabel
					from actes as a
					left join actes_cat as c on c.id=a.cat
          $where
					group by a.id
					order by c.label asc, a.label asc")) {
	        foreach ($tabTypes as $v) {
	            $p['page']['tabTypes'][$v['catName']][]=$v;
	        }
	    }

	    // liste des catégories
	    $p['page']['catList']=msSQL::sql2tabKey("select id, label from actes_cat order by label", 'id', 'label');
	}
