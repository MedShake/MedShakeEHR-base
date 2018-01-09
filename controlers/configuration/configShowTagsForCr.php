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
 * Config : montrer les tags utilisables dans un templates pour un objetID donné
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='y';
$template='configShowTagsForCr';

$tags = new msCourrier();
$tags->setObjetID($match['params']['objetID']);
$tagsValues=$tags->getDataByObjetID();

foreach($tagsValues as $k=>$v) {
  if(!is_numeric($k)) $tagListe[$k]=$v;
}
$tagsKeys=array_keys($tagListe);

$tagsInfos=msSQL::sql2tabKey("select dt.name, dt.label, dt.id, dt.cat, dc.label as catName
from data_types as dt
left join data_cat as dc on dt.cat=dc.id
where dt.name in ('".implode('\',\'', $tagsKeys)."')", 'name');



foreach ($tagsValues as $k=>$v) {
    if (isset($tagsInfos[$k]) and !is_numeric($k)) {
      $val=substr($k,0,3);
      if ($val != 'val' and $val != 'pct') {
        $tabFinal[$tagsInfos[$k]['cat']]['tags'][$k]=array(
          'value'=>$v,
          'infos'=>$tagsInfos[$k]
        );
        $tabFinal[$tagsInfos[$k]['cat']]['catName']=$tagsInfos[$k]['catName'];
      }
    } elseif(!is_numeric($k)) {
      $val=substr($k,0,3);
      if ($val != 'val' and $val != 'pct') {
        $tabFinal['calc']['tags'][$k]=array(
          'value'=>$v,
          'infos'=>$v
        );
        $tabFinal['calc']['catName']='calc';
      }
    }

}

if(isset($tabFinal)) $p['page']['tabTags']=$tabFinal;
if(isset($tagsValues)) $p['page']['tagsValues']=$tagsValues;
unset($tabFinal,$tagsValues);
