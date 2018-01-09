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
    $where = "m.fromID='".$p['page']['expediteurID']."' and";
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

$typeID = new msData();
$typeID = $typeID-> getTypeIDsFromName(['mailPorteur', 'mailTo', 'mailFrom', 'mailSujet', 'mailTrackingID', 'mailToEcofaxNumber', 'firstname', 'lastname']);


if ($mails=msSQL::sql2tab("select m.id from objets_data as m
  join objets_data as mto on mto.instance=m.id and mto.typeID='".$typeID['mailTo']."'
  left join objets_data as mtof on mtof.instance=m.id and mtof.typeID='".$typeID['mailToEcofaxNumber']."'
  where ".$where." m.typeID='".$typeID['mailPorteur']."' and mtof.id is null and m.deleted = ''
  group by m.id
  order by m.creationDate desc limit $startSQL,$nbParPage")) {
    foreach ($mails as $mail) {
        $ob = new msObjet();
        $objs[$mail['id']] = $ob->getObjetAndSons($mail['id']);
    }

    foreach ($objs as $k=>$v) {
        if(isset($v[$typeID['mailTo']]['toID'])){
          $patient = new msPeople();
          $patient->setToID($v[$typeID['mailTo']]['toID']);
          $patientData = $patient->getSimpleAdminDatas();
        } else {
          $patientData = null;
        }
        $p['page']['mailListe'][]=@array(
        'mailid'=>$k,
        'patient'=>$patientData,
        'date'=>$v[$typeID['mailTo']]['creationDate'],
        'to'=>$v[$typeID['mailTo']]['value'],
        'toID'=>$v[$typeID['mailTo']]['toID'],
        'fromID'=>$v[$typeID['mailTo']]['fromID'],
        'from'=>$v[$typeID['mailFrom']]['value'],
        'sujet'=>$v[$typeID['mailSujet']]['value'],
        'mailTrackingID'=>$v[$typeID['mailTrackingID']]['value'],
      );
    }
}

$p['page']['expediteurs']=msSQL::sql2tabKey("select m.fromID as id, concat(p.value, ' ', n.value) as identite
  from objets_data as m
  left join objets_data as n on n.toID=m.fromID and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
  left join objets_data as p on p.toID=m.fromID and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
  where m.typeID='".$typeID['mailPorteur']."'
  group by m.fromID, p.value, n.value
  order by n.value", "id", "identite");
