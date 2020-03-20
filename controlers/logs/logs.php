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
 * Logs : log de toutes les datas créées
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//admin uniquement
if (!msUser::checkUserIsAdmin()) {

  $template="forbidden";

} else {

  $debug='';
  $template="logs";


  if (isset($match['params']['patient']) and is_numeric($match['params']['patient'])) {
      $patientSel=" and pd.toID='".$match['params']['patient']."'";
  } else {
      $listeId = msSQL::sql2tabSimple("select id from objets_data order by id desc limit 2000");
      $patientSel=" and pd.id in (".implode(", ", $listeId ).")";
  }
  if (isset($match['params']['typeID']) and is_numeric($match['params']['typeID'])) {
      $typeSel=" and pd.typeID='".$match['params']['typeID']."'";
  } else {
      $typeSel=null;
  }
  if (isset($match['params']['instance']) and is_numeric($match['params']['instance'])) {
      $instance=" and pd.instance='".$match['params']['instance']."'";
  } else {
      $instance=null;
  }

  $ids = msData::getTypeIDsFromName(['firstname', 'lastname', 'birthname']);
  $p['page']['logs']=msSQL::sql2tab("select pd.* , TRIM(CONCAT(COALESCE(f.value,''), ' ', TRIM(CONCAT(COALESCE(l.value, ''), ' ', COALESCE(b.value,''))))) as prescripteur, CASE WHEN pd.byID!='' THEN pd.byID ELSE pd.fromID END as prescripteurID, t.label, t.groupe, t.name
  from objets_data as pd
  left join objets_data as f on f.toID=(CASE WHEN pd.byID!='' THEN pd.byID ELSE pd.fromID END) and f.typeID in (NULL, '', '".$ids['firstname']."') and f.outdated='' and f.deleted=''
  left join objets_data as l on l.toID=(CASE WHEN pd.byID!='' THEN pd.byID ELSE pd.fromID END) and l.typeID in (NULL, '', '".$ids['lastname']."') and l.outdated='' and l.deleted=''
  left join objets_data as b on b.toID=(CASE WHEN pd.byID!='' THEN pd.byID ELSE pd.fromID END) and b.typeID in (NULL, '', '".$ids['birthname']."') and b.outdated='' and b.deleted=''
  left join data_types as t on t.id=pd.typeID
  where 1 $patientSel $typeSel $instance
  group by pd.id,t.id, f.id, b.id, l.id
  order by id desc limit 2000");

  if (isset($match['params']['patient'])) {
      $p['page']['patientID']=$match['params']['patient'];
  }
}
