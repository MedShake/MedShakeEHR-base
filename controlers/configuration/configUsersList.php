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

     $name2typeID = new msData();
     $name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'birthname']);
     $p['page']['modules']=msSQL::sql2tabKey("SELECT id, name AS module FROM system WHERE groupe='module'", "id", "module");
     $p['page']['userid']=$p['user']['id'];
     $p['page']['users']=msSQL::sql2tab("select pp.id, pp.name, pp.rank, pp.module, p.value as prenom, CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom
      from people as pp
      left join objets_data as n on n.toID=pp.id and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
      left join objets_data as bn on bn.toID=pp.id and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      left join objets_data as p on p.toID=pp.id and p.typeID='".$name2typeID['firstname']."' and p.outdated=''  and p.deleted=''
      where pp.name!='' and pp.pass!='' order by pp.id");
 }
