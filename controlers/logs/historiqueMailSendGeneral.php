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
 * Logs : présente l'historique général des mails envoyés
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template="historiqueMailSendGeneral";


if (!isset($match['params']['user'])) {
    $p['page']['expediteurID']=0;
    $where = null;
} elseif ($match['params']['user'] == 0) {
    $p['page']['expediteurID']=0;
    $where = null;
} else {
    $p['page']['expediteurID']=$match['params']['user'];
    if(is_numeric($p['page']['expediteurID'])) $where = "m.fromID='".$p['page']['expediteurID']."' and";
}

$nbParPage=12;
if (!isset($match['params']['start'])) {
    $startSQL=0;
    $p['page']['nextStart']=-1;
    $p['page']['previousStart']=$startSQL+$nbParPage;
} else {
    $startSQL=$match['params']['start'];
    $p['page']['nextStart']=$startSQL-$nbParPage;
    $p['page']['previousStart']=$startSQL+$nbParPage;
}

$name2typeID = new msData();
$name2typeID = $name2typeID-> getTypeIDsFromName(['mailPorteur', 'mailTo', 'mailFrom', 'mailSujet', 'mailTrackingID', 'mailToEcofaxNumber', 'firstname', 'lastname', 'birthname']);


if ($mails=msSQL::sql2tab("select m.id from objets_data as m
  join objets_data as mto on mto.instance=m.id and mto.typeID='".$name2typeID['mailTo']."'
  left join objets_data as mtof on mtof.instance=m.id and mtof.typeID='".$name2typeID['mailToEcofaxNumber']."'
  where ".$where." m.typeID='".$name2typeID['mailPorteur']."' and mtof.id is null and m.deleted = ''
  group by m.id
  order by m.creationDate desc limit $startSQL,$nbParPage")) {
    foreach ($mails as $mail) {
        $ob = new msObjet();
        $ob->setObjetID($mail['id']);
        $objs[$mail['id']] = $ob->getObjetAndSons();
    }

    foreach ($objs as $k=>$v) {
        if(isset($v[$name2typeID['mailTo']]['toID'])){
          $patient = new msPeople();
          $patient->setToID($v[$name2typeID['mailTo']]['toID']);
          $patientData = $patient->getSimpleAdminDatasByName();
        } else {
          $patientData = null;
        }
        $p['page']['mailListe'][]=@array(
        'mailid'=>$k,
        'patient'=>$patientData,
        'date'=>$v[$name2typeID['mailTo']]['creationDate'],
        'to'=>$v[$name2typeID['mailTo']]['value'],
        'toID'=>$v[$name2typeID['mailTo']]['toID'],
        'fromID'=>$v[$name2typeID['mailTo']]['fromID'],
        'from'=>$v[$name2typeID['mailFrom']]['value'],
        'sujet'=>$v[$name2typeID['mailSujet']]['value'],
        'mailTrackingID'=>$v[$name2typeID['mailTrackingID']]['value'],
      );
    }
}

$p['page']['expediteurs']=msSQL::sql2tabKey("select m.fromID as id,
  CASE WHEN n.value != '' THEN concat(p.value, ' ', n.value) ELSE concat(p.value, ' ', bn.value) END as identite
  from objets_data as m
  left join objets_data as n on n.toID=m.fromID and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
  left join objets_data as p on p.toID=m.fromID and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
  left join objets_data as bn on bn.toID=m.fromID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
  where m.typeID='".$name2typeID['mailPorteur']."'
  group by m.fromID, bn.value, p.value, n.value
  order by n.value", "id", "identite");
