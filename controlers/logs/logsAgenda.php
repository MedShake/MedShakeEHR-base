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
* Logs : log Agenda
*
* @author Bertrand Boutillier <b.boutillier@gmail.com>
*/

//admin uniquement
if (!msUser::checkUserIsAdmin()) {

  $template="forbidden";

} else {

  $debug='';
  $template="logsAgenda";

  $whereLA[]=1;
  $whereLU[]=1;
  if (isset($match['params']['agenda']) and is_numeric($match['params']['agenda'])) {
     $p['page']['agendaID']=$match['params']['agenda'];
     $whereLA[]="a.userid='".$p['page']['agendaID']."'";
     $whereLU[]="l.userid='".$p['page']['agendaID']."'";
  }
  if (isset($match['params']['event']) and is_numeric($match['params']['event'])) {
     $p['page']['eventID']=$match['params']['event'];
     $whereLA[]="a.id='".$p['page']['eventID']."'";
     $whereLU[]="l.eventID='".$p['page']['eventID']."'";
  }

  //utilisateurs pouvant avoir un agenda
  $agendaUsers= new msPeople();
  $p['page']['agendaUsers']=$agendaUsers->getUsersListForService('administratifPeutAvoirAgenda');

  // dernier rdv créés
  $lastAdd=[];
  $lasrUpdate=[];

  $name2typeID = new msData();
  $name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'birthname']);

  if ($lastAdd=msSQL::sql2tab("select a.id as eventID, a.userid as agendaID, a.start, a.end, a.type, a.dateAdd as date, a.patientid as patientID, a.fromID, a.statut, a.absente, a.motif, TIMESTAMPDIFF(MINUTE,a.start,a.end) as duree,
  CASE WHEN n.value != '' THEN n.value ELSE bn.value END as patientNom, p.value as patientPrenom,
  CASE WHEN n1.value != '' THEN n1.value ELSE bn1.value END as auteurNom, p1.value as auteurPrenom
    from agenda as a
    left join objets_data as bn on bn.toID=a.patientid and bn.typeID='".$name2typeID['birthname']."' and bn.deleted = '' and bn.outdated = ''
    left join objets_data as n on n.toID=a.patientid and n.typeID='".$name2typeID['lastname']."' and n.deleted = '' and n.outdated = ''
    left join objets_data as p on p.toID=a.patientid and p.typeID='".$name2typeID['firstname']."' and p.deleted = '' and p.outdated = ''
    left join objets_data as bn1 on bn1.toID=a.fromID and bn1.typeID='".$name2typeID['birthname']."' and bn1.deleted = '' and bn1.outdated = ''
    left join objets_data as n1 on n1.toID=a.fromID and n1.typeID='".$name2typeID['lastname']."' and n1.deleted = '' and n1.outdated = ''
    left join objets_data as p1 on p1.toID=a.fromID and p1.typeID='".$name2typeID['firstname']."' and p1.deleted = '' and p1.outdated = ''
    where ".implode(' and ', $whereLA)."
    group by a.id, n.value, p.value, n1.value, p1.value, bn.value, bn1.value
    order by a.id desc
    limit 2000")) {
      foreach ($lastAdd as $v) {
          $p['page']['last'][$v['date']][]=$v;
      }
  }
  //derniers rdv modifiés
  if ($lastUpdate=msSQL::sql2tab("select l.eventID, l.userID as agendaID, l.date, l.operation, l.olddata, l.fromID, a.patientid as patientID, a.`type` as `type`, a.start, a.end, TIMESTAMPDIFF(MINUTE,a.start,a.end) as duree,
  CASE WHEN n.value != '' THEN n.value ELSE bn.value END as patientNom, p.value as patientPrenom,
  CASE WHEN n1.value != '' THEN n1.value ELSE bn1.value END as auteurNom, p1.value as auteurPrenom
    from agenda_changelog as l
    left join agenda as a on a.id=l.eventID
    left join objets_data as bn on bn.toID=a.patientid and bn.typeID='".$name2typeID['birthname']."' and bn.deleted = '' and bn.outdated = ''
    left join objets_data as n on n.toID=a.patientid and n.typeID='".$name2typeID['lastname']."' and n.deleted = '' and n.outdated = ''
    left join objets_data as p on p.toID=a.patientid and p.typeID='".$name2typeID['firstname']."' and p.deleted = '' and p.outdated = ''
    left join objets_data as bn1 on bn1.toID=l.fromID and bn1.typeID='".$name2typeID['birthname']."' and bn1.deleted = '' and bn1.outdated = ''
    left join objets_data as n1 on n1.toID=l.fromID and n1.typeID='".$name2typeID['lastname']."' and n1.deleted = '' and n1.outdated = ''
    left join objets_data as p1 on p1.toID=l.fromID and p1.typeID='".$name2typeID['firstname']."' and p1.deleted = '' and p1.outdated = ''
    where ".implode(' and ', $whereLU)."
    group by l.id, n.value, p.value, n1.value, p1.value, bn.value, bn1.value
    order by l.id desc
    limit 2000")) {
      foreach ($lastUpdate as $v) {
          $v['olddata']=unserialize($v['olddata']);
          $p['page']['last'][$v['date']][]=$v;
      }
  }
  if (!empty($p['page']['last'])) {

    if(isset($p['page']['eventID'])) {
      ksort($p['page']['last']);
      foreach($p['page']['last'] as $v) {
        foreach ($v as $w) {
          $p['page']['rdv'][]=$w;
        }
      }
      unset($p['page']['last']);

      foreach($p['page']['rdv'] as $k=>$v) {
        if($k-1>=0) {
          $p['page']['rdv'][$k-1]['newdata']=$p['page']['rdv'][$k]['olddata'];
        }
        if(!isset($p['page']['rdv'][$k+1]))  {
          $p['page']['rdv'][$k]['newdata']=$p['page']['rdv'][0];
        }
      }

    } else {
      krsort($p['page']['last']);
    }
  }
}
