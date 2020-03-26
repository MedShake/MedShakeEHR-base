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
 * People : assigner automatiquement ses groupes à un praticien fils
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!msUser::checkUserIsAdmin() and $p['config']['droitDossierPeutAssignerPropresGroupesPraticienFils'] != 'true') {
  die("Erreur: vous n'êtes pas administrateur ou autorisé à effectuer cette action");
}

// vérifier que le pro est bien un fil du user
if (!msUser::checkUserIsAdmin() and $p['config']['droitDossierPeutAssignerPropresGroupesPraticienFils'] == 'true') {
  $pro = new msPeople;
  $pro->setToID($_POST['proID']);
  if($pro->getFromID() != $p['user']['id']) {
    die("Erreur: vous n'êtes pas autorisé à effectuer cette action");
  }
}

// liens user actif
$liensUser = new msPeopleRelations();
$liensUser->setToID($p['user']['id']);
$liensUser->setRelationType('relationPraticienGroupe');
$groupesUser = $liensUser->getRelations();

if(!empty($groupesUser)) {
  $relation = new msPeopleRelations;
  $relation->setToID($_POST['proID']);
  $relation->setToStatus('membre');
  $relation->setFromID($p['user']['id']);
  $relation->setRelationType('relationPraticienGroupe');

  foreach($groupesUser as $groupe) {
    $relation->setWithID($groupe['peopleID']);
    $relation->setRelation();
  }
}
exit(json_encode(array('status'=>'ok')));
