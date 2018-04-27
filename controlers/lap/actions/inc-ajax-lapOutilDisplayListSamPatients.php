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
 * LAP : ajax > obtenir la liste des patients dont la condition du SAM est réalisée
 * lors de la dernière prescription à l’aide du LAP
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$name2typeID = new msData();
$name2typeID = $name2typeID->getTypeIDsFromName(['lapOrdonnance', 'firstname', 'lastname', 'birthname']);

$patientsList = msSQL::sql2tab("SELECT w.fromID, w.toID, w.titre, w.registerDate, s.id,
CASE
  WHEN o.value != '' and bn1.value != '' THEN concat(o.value, ' (', bn1.value, ') ', o2.value)
  WHEN o.value != '' THEN concat(o.value, ' ', o2.value)
  ELSE concat(bn1.value, ' ', o2.value)
  END as identiteDossier
FROM objets_data as w
left join objets_data as o on o.toID=w.toID and o.typeID='".$name2typeID['lastname']."' and o.outdated='' and o.deleted=''
left join objets_data as o2 on o2.toID=w.toID and o2.typeID='".$name2typeID['firstname']."' and o2.outdated='' and o2.deleted=''
left join objets_data as bn1 on bn1.toID=w.toID and bn1.typeID='".$name2typeID['birthname']."' and bn1.outdated='' and bn1.deleted=''
left join objets_data as s on s.registerDate > w.registerDate and s.toID = w.toID and s.outdated ='' and s.deleted=''
where w.outdated ='' and w.deleted=''and w.typeID = '".$name2typeID['lapOrdonnance']."' and w.value like '%".msSQL::cleanVar($_POST['samID'])."%' and s.id is null
order by w.registerDate desc
");

header('Content-Type: application/json');
echo json_encode(array('patientsList'=>$patientsList));
