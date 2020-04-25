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
 * people : voir les infos sur un pro
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template='proView';

if(!is_numeric($match['params']['proID'])) die;
$p['page']['proDataID']=$match['params']['proID'];

//contrôle sur droit à voir le prat (même groupe ou enfant du user) si restriction active
if (!msUser::checkUserIsAdmin() and $p['config']['droitDossierPeutVoirUniquementPraticiensGroupes'] == 'true') {
  // même groupe
  $frat = new msPeopleRelations;
  $frat->setToID($p['page']['proDataID']);
  $frat->setRelationType('relationPraticienGroupe');
  $ids = $frat->getSiblingIDs($p['user']['id']);
  // parent
  $parentID = $frat->getFromID();

  if(!in_array($p['page']['proDataID'], $ids) and $parentID != $p['user']['id']) {
    $template = "forbidden";
    return;
  }
}

$patient = new msPeopleDroits($p['page']['proDataID']);

if($patient->getType() != 'pro') {
  $template = "404";
  return;
}

$p['page']['proData']=$patient->getLabelForSimpleAdminDatas($patient->getSimpleAdminDatasByName());
$p['page']['proData']['isUser']=$patient->checkIsUser();
$p['page']['proData']['dossierType']=$patient->getType();
$p['page']['proData']['parentID']=$patient->getFromID();

$labels = new msData();
$p['page']['proDataLabel'] = $labels->getLabelFromTypeName(array_keys($p['page']['proData']));

//les patients connus
if($p['config']['optionGePraticienMontrerPatientsLies'] == 'true') {
  $patients = new msPeopleRelations;
  $patients->setToID($p['page']['proDataID']);
  $patients->setRelationType('relationPatientPraticien');
  $patients->setReturnedPeopleTypes(['patient']);
  $p['page']['patientsConnus'] = $patients->getRelations(['identite', 'ageCalcule']);
  msTools::array_unatsort_by('identiteChainePourTri', $p['page']['patientsConnus']);
}

// gestion groupe
if($p['config']['optionGeActiverGroupes'] == 'true') {

  //sortir les choix de relations praticien <-> groupe
  $data = new msData();
  $typeID = $data->getTypeIDFromName('relationPraticienGroupe');
  $options = $data->getSelectOptionValue(array($typeID));
  foreach($options[$typeID] as $k=>$v) {
    $p['page']['preRelationPraticienGroupe']['formValues'][$k]=$v;
  }

  // vérifier le droit de gérer les groupes du prat
  if($p['user']['rank'] == 'admin' or ($patient->getFromID() == $p['user']['id'] and $p['config']['droitDossierPeutAssignerPropresGroupesPraticienFils'] == 'true')) {
    $p['page']['proData']['canModifyGroups'] = true;
  } else {
    $p['page']['proData']['canModifyGroups'] = false;
  }

}

//Poste admin registre connus
if($p['config']['optionGeActiverRegistres'] == 'true') {
  $registres = new msPeopleRelations;
  $registres->setToID($p['page']['proDataID']);
  $registres->setRelationType('relationRegistrePraticien');
  $p['page']['posteAdminRegistre'] = $registres->getRelations(['registryname']);
}

if($p['config']['droitDossierPeutTransformerPraticienEnUtilisateur'] == 'true') {
  $p['page']['loginUsername'] = msUser::makeRandomUniqLoginUsername(@$p['page']['proData']['firstname'], @$p['page']['proData']['lastname'], @$p['page']['proData']['birthname']);
}
