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
 * Logs : présente l'historique d'impression d'un document
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$debug='';
$template="historiquePrint";

if(!is_numeric($match['params']['objetID'])) die;

$objet = new msObjet();
$objet->setObjetID($match['params']['objetID']);
$p['page']['patient'] = $objet->getObjetDataByID(['toID']);

$name2typeID = new msData();
$name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'birthname']);

if ($p['page']['print']=msSQL::sql2tab("select p.id, p.creationDate, p.value, p.toID, p.anonyme,
    CASE
      WHEN ln1.value != '' THEN concat(ln1.value , ' ' , fn1.value)
      ELSE concat(fn1.value , ' ' , bn1.value)
    END as identiteAuteur
    from printed as p
    left join objets_data as ln1 on ln1.toID=p.fromID and ln1.typeID='".$name2typeID['lastname']."' and ln1.outdated='' and ln1.deleted=''
    left join objets_data as bn1 on bn1.toID=p.fromID and bn1.typeID='".$name2typeID['birthname']."' and bn1.outdated='' and bn1.deleted=''
    left join objets_data as fn1 on fn1.toID=p.fromID and fn1.typeID='".$name2typeID['firstname']."' and fn1.outdated='' and fn1.deleted=''
    where p.objetID='".$match['params']['objetID']."'
    order by p.creationDate desc")) {

    foreach ($p['page']['print'] as $k=>$v) {
        $p['page']['print'][$k]['value'] = msTools::cutHtmlHeaderAndFooter($v['value']);
    }
}
