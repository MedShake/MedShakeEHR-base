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
 * Patients : listing patients ou pros
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';
$template="patients";

if (isset($match['params']['porp'])) {
    $p['page']['porp']=$match['params']['porp'];
}

// liste des types par catégorie
if ($tabTypes=msSQL::sql2tab("select t.label, t.id, c.label as catName, c.label as catLabel
  from data_types as t
  left join data_cat as c on c.id=t.cat
  where t.id > 0 and t.groupe = 'admin' and t.formType != 'group'
  order by c.label asc, t.label asc")) {
    foreach ($tabTypes as $v) {
        $p['page']['tabTypes'][$v['catName']][]=$v;
    }
}

// Transmissions
if($p['config']['transmissionsPeutCreer'] == 'true') {
  $trans = new msTransmissions();
  $trans->setUserID($p['user']['id']);
  $p['page']['transmissionsListeDestinatairesPossibles']=$trans->getTransmissionDestinatairesPossibles();
  $p['page']['transmissionsListeDestinatairesDefaut']=explode(',', $p['config']['transmissionsDefautDestinataires']);
}
