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
 * Lister les dossiers supprimés
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$template="peopleListDeleted";
$debug='';

//ajustement en fonction des droits
if($p['config']['droitDossierPeutVoirUniquementPatientsPropres'] == 'true' ) {
  $where=' and p.fromID='.$p['user']['id'];

} elseif($p['config']['droitDossierPeutVoirUniquementPatientsGroupes'] == 'true') {
  // fratrie praticiens
  $frat = new msPeopleRelations;
  $frat->setToID($p['user']['id']);
  $frat->setRelationType('relationPraticienGroupe');
  $ids = $frat->getSiblingIDs();
  $ids[] = $p['user']['id'];
  $where = " and p.fromID in ('".implode("', '", $ids)."')";

} else {
  $where='';
}

$name2typeID = new msData();
$name2typeID = $name2typeID->getTypeIDsFromName(['administratifMarqueurSuppression', 'firstname', 'lastname', 'birthname']);

if($p['page']['users']=msSQL::sql2tab("select p.id, m.value as mvalue, m.creationDate as dateDeleted, m.value as typeDossier,
CASE
  WHEN o.value != '' and bn1.value != '' THEN concat(o.value, ' (', bn1.value, ') ', o2.value)
  WHEN o.value != '' THEN concat(o.value, ' ', o2.value)
  ELSE concat(bn1.value, ' ', o2.value)
  END as identiteDossier,
CASE
  WHEN o3.value != '' THEN concat(o4.value, ' ', o3.value)
  ELSE concat(o4.value, ' ', bn2.value)
  END as identiteUser
from people as p
left join objets_data as o on o.toID=p.id and o.typeID='".$name2typeID['lastname']."' and o.outdated=''
left join objets_data as o2 on o2.toID=p.id and o2.typeID='".$name2typeID['firstname']."' and o2.outdated=''
left join objets_data as bn1 on bn1.toID=p.id and bn1.typeID='".$name2typeID['birthname']."' and bn1.outdated=''
left join objets_data as m on m.toID=p.id and m.typeID='".$name2typeID['administratifMarqueurSuppression']."' and m.outdated='' and m.deleted=''
left join objets_data as o3 on o3.toID=m.fromID and o3.typeID='".$name2typeID['lastname']."' and o3.outdated=''
left join objets_data as o4 on o4.toID=m.fromID and o4.typeID='".$name2typeID['firstname']."' and o4.outdated=''
left join objets_data as bn2 on bn2.toID=m.fromID and bn2.typeID='".$name2typeID['birthname']."' and bn2.outdated=''
where p.type='deleted' ".$where."
group by p.id, bn1.id, o.id, o2.id, m.id, bn2.id, o3.id, o4.id
order by p.id")) {

  foreach($p['page']['users'] as $k=>$v) {
    if(isset($v['mvalue'])) $value = Spyc::YAMLLoad($v['mvalue']);
    if(isset($value['typeDossier'])) $p['page']['users'][$k]['typeDossier']=$value['typeDossier'];
    if(isset($value['motif'])) $p['page']['users'][$k]['motif']=$value['motif'];
  }


  $formDel = new msForm();
  $formDel->setFormIDbyName($p['page']['formIN']='baseAskUserPassword');
  $p['page']['formDel']=$formDel->getForm();
  $formDel->setFieldAttrAfterwards($p['page']['formDel'], 'password', ['label'=>'Saisissez votre mot de passe pour valider l\'action']);
  $formDel->addHiddenInput($p['page']['formDel'], ['peopleID'=>'']);

}
