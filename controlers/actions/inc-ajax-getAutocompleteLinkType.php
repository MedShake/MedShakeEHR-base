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
 * Requêtes AJAX > autocomplete des forms, version complexe
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 $type=$match['params']['type'];
 $dataset=$match['params']['dataset'];

 $dataset2database=array(
     'data_types'=>'objets_data'
 );

 $database=$dataset2database[$dataset];

 if (isset($match['params']['setTypes'])) {
     $searchTypes=explode(':', $match['params']['setTypes']);
     foreach ($searchTypes as $v) {
         $concatValue[]= " COALESCE(d".$v.".value, '')";
     }
 } else {
     $searchTypes[]=$type;
 }

 $joinleft=[];
 $sel[]="do.value as d".$type;
 $concat=[];
 if (isset($match['params']['linkedTypes'])) {
     $linkedTypes=explode(':', $match['params']['linkedTypes']);

     foreach ($linkedTypes as $v) {
         $sel[]= " d".$v.".value as d".$v;
         $concatLabel[]= " COALESCE(d".$v.".value, '')";
         $joinleft[]=" left join ".$database." as d".$v." on do.toID = d".$v.".toID and d".$v.".typeID='".$v."' and d".$v.".outdated='' and d".$v.".deleted='' ";
     }
 }

 $data=msSQL::sql2tab("select trim(concat(".implode(', " ",', $concatValue).")) as value, trim(concat(".implode(', " ",', $concatLabel).")) as label, ".implode(",", $sel)."
 from ".$database." as do
 ".implode(" ", $joinleft)."
 where do.typeID in ('".implode("','", $searchTypes)."') and trim(concat(".implode(', " ",', $concatLabel).")) like '%".msSQL::cleanVar($_GET['term'])."%'
 and d".$type.".value is not null
 group by label limit 25");

 //print_r($data);

 echo json_encode($data);
