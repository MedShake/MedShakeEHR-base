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
 * People : ajax > obtenir la liste des patients pour l'autocomplete Relations
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */



 $data=msSQL::sql2tab("select trim(concat(COALESCE(d2.value, ''), ' ', COALESCE(d3.value, ''))) as value, trim(concat(COALESCE(d2.value, ''), ' ', COALESCE(d3.value, ''))) as label, p.id
 from objets_data as do
 left join objets_data as d2 on do.toID = d2.toID and d2.typeID='2' and d2.outdated='' and d2.deleted=''
 left join objets_data as d3 on do.toID = d3.toID and d3.typeID='3' and d3.outdated='' and d3.deleted=''
 left join people as p on p.id=do.toID
 where do.typeID in ('2', '3') and concat(COALESCE(d2.value, ''), ' ', COALESCE(d3.value, '')) like '%".msSQL::cleanVar($_GET['term'])."%'
 group by label limit 25");


 echo json_encode($data);
