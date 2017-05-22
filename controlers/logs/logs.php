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

$debug='';
$template="logs";


if (isset($match['params']['patient'])) {
    $patientSel=" and pd.toID='".$match['params']['patient']."'";
} else {
    $patientSel=null;
}
if (isset($match['params']['typeID'])) {
    $typeSel=" and pd.typeID='".$match['params']['typeID']."'";
} else {
    $typeSel=null;
}
if (isset($match['params']['instance'])) {
    $instance=" and pd.instance='".$match['params']['instance']."'";
} else {
    $instance=null;
}

$p['page']['logs']=msSQL::sql2tab("select pd.* , f.value as prescripteur, t.label, t.groupe
from objets_data as pd
left join objets_data as f on f.toID=pd.fromID and f.typeID=3
left join data_types as t on t.id=pd.typeID
where 1 $patientSel $typeSel $instance order by id desc limit 2000");

if (isset($match['params']['patient'])) {
    $p['page']['patientID']=$match['params']['patient'];
}
