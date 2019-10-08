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

$data = new msData();
$name2typeId=$data->getTypeIDsFromName([$match['params']['type']]);
$type=$name2typeId[$match['params']['type']];

if (isset($match['params']['setTypes'])) {
   $searchTypes=$data->getTypeIDsFromName(explode(':', $match['params']['setTypes']));
   foreach ($searchTypes as $v) {
      if(is_numeric($v)) $concatValue[]= " COALESCE(d".$v.".value, '')";
   }
} else {
   if(is_numeric($type)) $searchTypes[]=$type;
}

$joinleft=[];
$concat=[];
$groupby=array('label');
if (isset($match['params']['linkedTypes'])) {
   $originalOrderLabel=explode(':', $match['params']['linkedTypes']);
   $linkedTypes=$data->getTypeIDsFromName($originalOrderLabel);

   foreach ($linkedTypes as $k=>$v) {
     if(is_numeric($v)) {
       $sel[]= " d".$v.".value as ".$k;
       $concatLabel[$k]= " COALESCE(d".$v.".value, '')";
       $joinleft[]=" left join objets_data as d".$v." on do.toID = d".$v.".toID and d".$v.".typeID='".$v."' and d".$v.".outdated='' and d".$v.".deleted='' ";
       $groupby[]='d'.$v.'.value';
     }
   }
}
// remettre dans l'ordre original de l'url
if(!empty($concatLabel)) {
  $concatLabel=array_replace(array_flip($originalOrderLabel), $concatLabel);
}

$data=msSQL::sql2tab("select trim(concat(".implode(', " ",', $concatValue).")) as value, trim(concat(".implode(', " ",', $concatLabel).")) as label, ".implode(",", $sel)."
from objets_data as do
".implode(" ", $joinleft)."
where do.typeID in ('".implode("','", msSQL::cleanArray($searchTypes))."') and trim(concat(".implode(', " ",', $concatLabel).")) like '%".msSQL::cleanVar($_GET['term'])."%'
and d".msSQL::cleanVar($type).".value is not null
group by ".implode(",", $groupby)." limit 25");


echo json_encode($data);
