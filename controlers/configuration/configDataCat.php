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
 * Config : gérer les catégories du modèle de données
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//admin uniquement
if (!msUser::checkUserIsAdmin()) {
   $template="forbidden";
} else {
   $template="configDataCat";
   $debug='';

   $p['page']['groupe']=$match['params']['groupe'];
   if(!in_array($p['page']['groupe'], msSQL::sqlEnumList('data_types', 'groupe'))) die();

   $p['page']['tabCat']=msSQL::sql2tabKey("select c.*, count(t.id) as enfants
		from data_cat as c
		left join data_types as t on c.id=t.cat
		where c.groupe='".msSQL::cleanVar($p['page']['groupe'])."'
		group by c.id
		order by c.label asc", 'id');
}
