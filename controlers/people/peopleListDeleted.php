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
 * Mister les dossiers supprimés
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$template="peopleListDeleted";
$debug='';

$administratifMarqueurSuppressionID=msData::getTypeIDFromName('administratifMarqueurSuppression');

if($p['page']['users']=msSQL::sql2tab("select p.id, concat (o.value, ' ',  o2.value) as identiteDossier , concat (o4.value, ' ',  o3.value) as identiteUser, m.value as mvalue, m.creationDate as dateDeleted, m.value as typeDossier
from people as p
left join objets_data as o on o.toID=p.id and o.typeID=2 and o.outdated=''
left join objets_data as o2 on o2.toID=p.id and o2.typeID=3 and o2.outdated=''
left join objets_data as m on m.toID=p.id and m.typeID='".$administratifMarqueurSuppressionID."' and m.outdated='' and m.deleted=''
left join objets_data as o3 on o3.toID=m.fromID and o3.typeID=2 and o3.outdated=''
left join objets_data as o4 on o4.toID=m.fromID and o4.typeID=3 and o4.outdated=''
where p.type='deleted'
group by p.id
order by p.id")) {

  foreach($p['page']['users'] as $k=>$v) {
    if(isset($v['mvalue'])) $value = Spyc::YAMLLoad($v['mvalue']);
    if(isset($value['typeDossier'])) $p['page']['users'][$k]['typeDossier']=$value['typeDossier'];
    if(isset($value['motif'])) $p['page']['users'][$k]['motif']=$value['motif'];
  }

}
