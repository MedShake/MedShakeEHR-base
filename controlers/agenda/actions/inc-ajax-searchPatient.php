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
 * Agenda : chercher patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 $term = msSQL::cleanVar($_GET['term']);
 $a_json = array();

 if ($data=msSQL::sql2tab("select p.id, concat(d2.value, ' ', d3.value) as  identite, d8.value as ddn
 from people as p
 left join objets_data as d2 on d2.toID=p.id and d2.typeID=2 and d2.outdated='' and d2.deleted=''
 left join objets_data as d8 on d8.toID=p.id and d8.typeID=8 and d8.outdated='' and d8.deleted=''
 left join objets_data as d3 on d3.toID=p.id and d3.typeID=3 and d3.outdated='' and d3.deleted=''
 where concat(d2.value, ' ', d3.value) like '%".$term."%'
 group by p.id, d2.value, d3.value, d8.value
 order by d2.value, d3.value limit 20")) {

 	foreach ($data as $k=>$v) {
 		$a_json[]=array(
 			'label'=>trim($v['identite']).' '.$v['ddn'],
 			'value'=>trim($v['identite']),
 			'patientID'=>$v['id'],
 		);
 	}
 }


header('Content-Type: application/json');
echo json_encode($a_json);
