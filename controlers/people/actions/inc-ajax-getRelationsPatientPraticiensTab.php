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
 * People : ajax > obtenir le tableau de relation patient <-> praticiens
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//sortir le type de relation patient <-> praticien
$data = new msData();
$typeID = $data->getTypeIDFromName('relationPatientPraticien');
$options = $data->getSelectOptionValue(array($typeID));
$typeRelations=$options[$typeID];

$name2typeID = new msData();
$name2typeID = $name2typeID->getTypeIDsFromName(['relationID', 'relationPatientPraticien', 'firstname', 'lastname', 'birthname']);

if(isset($_POST['patientID'])) $patientID=$_POST['patientID']; elseif(isset($_GET['patientID'])) $patientID=$_GET['patientID'];

$data=[];
if($data = msSQL::sql2tab("select o.value as pratID, c.value as typeRelation, p.value as prenom, CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom
from objets_data as o
inner join objets_data as c on c.instance=o.id and c.typeID='".$name2typeID['relationPatientPraticien']."'
left join objets_data as n on n.toID=o.value and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
left join objets_data as bn on bn.toID=o.value and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
left join objets_data as p on p.toID=o.value and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
where o.toID='".$patientID."' and c.value!='patient' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated=''
group by o.value, c.id, bn.id, n.id, p.id
order by typeRelation = 'MT' desc, nom asc")) {

  foreach($data as $k=>$v) {
    if(isset($typeRelations[$v['typeRelation']])) $data[$k]['typeRelationDisplay']=$typeRelations[$v['typeRelation']];
  }


}

echo json_encode($data);
