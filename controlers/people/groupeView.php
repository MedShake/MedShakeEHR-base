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
 * people : voir les infos sur un groupe
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template='groupeView';

if(!is_numeric($match['params']['groupeID'])) die;
$p['page']['groupeDataID']=$match['params']['groupeID'];

$groupe = new msPeopleRelationsDroits;
$groupe->setToID($p['page']['groupeDataID']);
$p['page']['groupeData']['dossierType']=$groupe->getType();

// position de l'utilisateur courant // groupe et ajust si admin général
$p['page']['userPositionInGroup'] = $groupe->getCurrentUserStatusInGroup();
if($p['user']['rank'] == 'admin') $p['page']['userPositionInGroup'] = 'admin';

if($p['page']['groupeData']['dossierType'] != 'groupe' or $p['config']['optionGeActiverGroupes'] != 'true') {
  $template = "404";
  return;
}

if($p['config']['droitGroupePeutVoirTousGroupes'] != 'true') {
  $userGroups = new msPeopleRelations();
  $userGroups->setToID($p['user']['id']);
  $userGroups->setRelationType('relationPraticienGroupe');
  if($userGroups = $userGroups->getRelations()) {
    if(!in_array($p['page']['groupeDataID'], array_column($userGroups,'peopleID'))) {
      $template = "forbidden";
      return;
    }
  }
}

$p['page']['groupeData']=$groupe->getLabelForSimpleAdminDatas($groupe->getSimpleAdminDatasByName());

// création exportID si manquant
if(!isset($p['page']['groupeData']['peopleExportID']) and $p['config']['optionGeCreationAutoPeopleExportID'] == 'true') {
  $groupe->setFromID($p['user']['id']);
  $groupe->setPeopleExportID();
}
unset($p['page']['groupeData']['peopleExportID']);

$labels = new msData();
$p['page']['groupeDataLabel'] = $labels->getLabelFromTypeName(array_keys($p['page']['groupeData']));

//sortir les choix de relations praticien <-> groupe
$data = new msData();
$typeID = $data->getTypeIDFromName('relationPraticienGroupe');
$options = $data->getSelectOptionValue(array($typeID));
foreach($options[$typeID] as $k=>$v) {
  $p['page']['preRelationPraticienGroupe']['formValues'][$k]=$v;
}

// gestion registre
if($p['config']['optionGeActiverRegistres'] == 'true') {

  //sortir les choix de relations groupe <-> registre
  $data = new msData();
  $typeID = $data->getTypeIDFromName('relationGroupeRegistre');
  $options = $data->getSelectOptionValue(array($typeID));
  foreach($options[$typeID] as $k=>$v) {
    $p['page']['preRelationGroupeRegistre']['formValues'][$k]=$v;
  }

}
