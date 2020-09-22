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
 * people : voir les infos sur un registre
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template='registreView';

if(!is_numeric($match['params']['registreID'])) die;
$p['page']['registreDataID']=$match['params']['registreID'];

$registre = new msPeopleRelations;
$registre->setToID($p['page']['registreDataID']);
$p['page']['registreData']['dossierType']=$registre->getType();

if($p['page']['registreData']['dossierType'] != 'registre' or $p['config']['optionGeActiverRegistres'] != 'true') {
  $template = "404";
  return;
}

$p['page']['registreData']=$registre->getLabelForSimpleAdminDatas($registre->getSimpleAdminDatasByName());
unset($p['page']['registreData']['registryPrefixTech']);
unset($p['page']['registreData']['peopleExportID']);

$labels = new msData();
$p['page']['registreDataLabel'] = $labels->getLabelFromTypeName(array_keys($p['page']['registreData']));



//sortir les choix de relations registre <-> praticiens
$data = new msData();
$typeID = $data->getTypeIDFromName('relationRegistrePraticien');
$options = $data->getSelectOptionValue(array($typeID));
foreach($options[$typeID] as $k=>$v) {
  $p['page']['preRelationRegistrePraticien']['formValues'][$k]=$v;
}

//sortir les choix de relations groupe <-> registre
$data = new msData();
$typeID = $data->getTypeIDFromName('relationGroupeRegistre');
$options = $data->getSelectOptionValue(array($typeID));
foreach($options[$typeID] as $k=>$v) {
  $p['page']['preRelationRegistreGroupe']['formValues'][$k]=$v;
}
