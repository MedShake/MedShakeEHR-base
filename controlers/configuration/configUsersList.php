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
 */

 //admin uniquement
 if (!msUser::checkUserIsAdmin()) {
     $template="forbidden";
 } else {
     $template="configUsersList";
     $debug='';
     $p['page']['users']=msSQL::sql2tab("select p.id, p.rank, o2.value as prenom, o.value as nom
      from people as p
      left join objets_data as o on o.toID=p.id and o.typeID=2 and o.outdated=''
      left join objets_data as o2 on o2.toID=p.id and o2.typeID=3 and o2.outdated=''
      where p.pass!='' order by p.id");
 }
