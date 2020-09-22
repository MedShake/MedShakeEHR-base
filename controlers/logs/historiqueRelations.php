<?php
/*
* This file is part of MedShakeEHR.
*
* Copyright (c) 2020
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
* Logs : log Relations
*
* @author Bertrand Boutillier <b.boutillier@gmail.com>
*/

//admin uniquement
if (!msUser::checkUserIsAdmin()) {
  $template="forbidden";
  return;
}
$debug='';
$template="historiqueRelations";

$dataComp=[];
$relationType = $match['params']['type'];

$people = new msPeople();
$people->setToID($match['params']['peopleID']);
$peopleType = $people->getType();

if($relationType == 'relationPraticienGroupe') {
  if($peopleType == 'pro') {
    $dataComp=['groupname'];
    $dataCompReverse=['birthname', 'lastname', 'firstname'];
    $p['page']['titreRelation'] = "Historique d'appartenance aux groupes de ".$people->getSimpleAdminDatasByName(['identite'])['identiteUsuelle'].' <small class="text-muted">#'.$match['params']['peopleID'].'</small>';
  } else {
    $dataComp=['birthname', 'lastname', 'firstname'];
    $dataCompReverse=['groupname'];
    $p['page']['titreRelation'] = "Historique d'ajout / suppression des membres au groupe ".$people->getSimpleAdminDatasByName(['groupname'])['groupname'].' <small class="text-muted">#'.$match['params']['peopleID'].'</small>';
  }
} elseif($relationType == 'relationGroupeRegistre') {
  if($peopleType == 'groupe') {
    $dataComp=['registryname'];
    $dataCompReverse=['groupname'];
    $p['page']['titreRelation'] = "Historique des registres autorisés au groupe ".$people->getSimpleAdminDatasByName(['groupname'])['groupname'].' <small class="text-muted">#'.$match['params']['peopleID'].'</small>';
  } else {
    $dataComp=['groupname'];
    $dataCompReverse=['registryname'];
    $p['page']['titreRelation'] = "Historique d'ajout / suppression des groupes au registre ".$people->getSimpleAdminDatasByName(['registryname'])['registryname'].' <small class="text-muted">#'.$match['params']['peopleID'].'</small>';
  }
} elseif($relationType == 'relationPatientPatient') {
} elseif($relationType == 'relationRegistrePraticien') {
  if($peopleType == 'registre') {
    $dataComp=['birthname', 'lastname', 'firstname'];
    $dataCompReverse=['registryname'];
    $p['page']['titreRelation'] = "Historique des postes d'administrateur du registre ".$people->getSimpleAdminDatasByName(['registryname'])['registryname'].' <small class="text-muted">#'.$match['params']['peopleID'].'</small>';
  } else {
    $dataComp=['registryname'];
    $dataCompReverse=['birthname', 'lastname', 'firstname'];
    $p['page']['titreRelation'] = "Historique des postes d'administrateur registre de ".$people->getSimpleAdminDatasByName(['identite'])['identiteUsuelle'].' <small class="text-muted">#'.$match['params']['peopleID'].'</small>';
  }
} elseif($relationType == 'relationRegistrePatient') {
  if($peopleType == 'patient') {
    $dataComp=['registryname'];
    $dataCompReverse=['birthname', 'lastname', 'firstname'];
    $p['page']['titreRelation'] = "Historique des consentement ou refus de participation aux registres de ".$people->getSimpleAdminDatasByName(['identite'])['identiteUsuelle'].' <small class="text-muted">#'.$match['params']['peopleID'].'</small>';
  }
}

$data = new msData();
$name2typeID = $data->getTypeIDsFromName(array_merge(['relationID', $relationType], $dataComp));

$champsSql=[];
$tablesSql=[];
$groupBy=[];
$notEmpty=[];
if(!empty($dataComp)) {
  foreach($dataComp as $k=>$v) {
    if(key_exists($v,$name2typeID)) {
      $champsSql[] = ', co'.$name2typeID[$v].'.value as '.$v;
      $tablesSql[] = " left join objets_data as co".$name2typeID[$v]." on co".$name2typeID[$v].".toID=o.value and co".$name2typeID[$v].".typeID='".$name2typeID[$v]."' and co".$name2typeID[$v].".outdated='' and co".$name2typeID[$v].".deleted='' ";
      $groupBy[]= ', co'.$name2typeID[$v].'.id';

    }
  }
}



if($relations =  msSQL::sql2tab("select o.*, c.value as typeRelation ".implode(" ", $champsSql)."
from objets_data as o
inner join objets_data as c on c.instance=o.id and c.typeID='".$name2typeID[$relationType]."'
".implode(" ", $tablesSql)."
where o.toID='".$match['params']['peopleID']."' and o.typeID='".$name2typeID['relationID']."'  ".implode("", $notEmpty)."
group by o.value, c.id ".implode("", $groupBy) )) {

  $tab=[];
  foreach($relations as $k=>$v) {
    $tab[$v['registerDate'].' '.$k]=array(
      'objetID'=>$v['id'],
      'date'=>$v['registerDate'],
      'action'=>'add',
      'typeRelation'=>$v['typeRelation'],
      'byID'=>$v['fromID'],
      'withID'=>$v['value'],
    );

    if($v['deleted'] == 'y') {
      $tab[$v['updateDate'].' '.$k]=array(
        'objetID'=>$v['id'],
        'date'=>$v['updateDate'],
        'action'=>'delete',
        'typeRelation'=>$v['typeRelation'],
        'byID'=>$v['deletedByID'],
        'withID'=>$v['value'],
      );
    }

    if(!empty($dataComp)) {
      foreach($dataComp as $typeName) {
        $tab[$v['registerDate'].' '.$k][$typeName]=$v[$typeName];
        if(isset($tab[$v['updateDate'].' '.$k])) $tab[$v['updateDate'].' '.$k][$typeName]=$v[$typeName];
      }
    }

  }
  krsort($tab);
  $p['page']['historique']=$tab;
  unset($tab, $relations);

  if(!empty($p['page']['historique']) ) {
    $auteursID=array_unique(array_column($p['page']['historique'],'byID'));
    foreach($auteursID as $auteur) {
      $aut = new msPeople();
      $aut->setToID($auteur);
      $p['page']['auteurs'][$auteur]=$aut->getSimpleAdminDatasByName(['identite']);
    }
  }
}
