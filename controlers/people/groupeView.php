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

$groupe = new msPeopleRelations;
$groupe->setToID($p['page']['groupeDataID']);
$p['page']['groupeData']['dossierType']=$groupe->getType();

if($p['page']['groupeData']['dossierType'] != 'groupe' or $p['config']['optionGeGroupesActiver'] != 'true') {
  $template = "404";
  return;
}

$p['page']['groupeData']=$groupe->getLabelForSimpleAdminDatas($groupe->getSimpleAdminDatasByName());

$labels = new msData();
$p['page']['groupeDataLabel'] = $labels->getLabelFromTypeName(array_keys($p['page']['groupeData']));

//les praticiens connus
$groupe->setRelationType('relationPraticienGroupe');
$p['page']['praticiensConnus'] = $groupe->getRelations(['identite', 'titre']);

// gestion groupe
if($p['config']['optionGeRegistresActiver'] == 'true') {

  //sortir les choix de relations groupe <-> registre
  $data = new msData();
  $typeID = $data->getTypeIDFromName('relationGroupeRegistre');
  $options = $data->getSelectOptionValue(array($typeID));
  foreach($options[$typeID] as $k=>$v) {
    $p['page']['preRelationGroupeRegistre']['formValues'][$k]=$v;
  }

}
