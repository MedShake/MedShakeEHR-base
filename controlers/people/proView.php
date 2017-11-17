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
 * people : voir les infos sur un pro
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template='proView';

$p['page']['proDataID']=$match['params']['proID'];

$patient = new msPeople();
$patient->setToID($p['page']['proDataID']);
$p['page']['proData']=$patient->getSimpleAdminDatas();

//type du dossier (pour deleted en particulier)
$p['page']['proData']['dossierType']=msSQL::sqlUniqueChamp("select type from people where id='".$match['params']['proID']."' limit 1");

$labels = new msData();
$p['page']['proDataLabel'] = $labels->getLabelFromTypeID(array_keys($p['page']['proData']));

//les patients connus
$name2typeID = new msData();
$name2typeID = $name2typeID->getTypeIDsFromName(['relationID']);
$p['page']['patientsConnus']=msSQL::sql2tab("select o.value as patientID, c.value as typeRelation, n.value as nom, p.value as prenom
from objets_data as o
left join objets_data as c on c.instance=o.id
left join objets_data as n on n.toID=o.value and n.typeID=2 and n.outdated='' and n.deleted=''
left join objets_data as p on p.toID=o.value and p.typeID=3 and p.outdated='' and p.deleted=''
where o.toID='".$match['params']['proID']."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated=''
group by o.value, c.id, n.id, p.id 
order by nom asc");
