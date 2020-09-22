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
 * People : ajax > obtenir le tableau de relation praticien <-> groupes
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if($p['config']['optionGeActiverGroupes'] != 'true') {
 die();
}

if(isset($_POST['pratID'])) {
  $pratID=$_POST['pratID'];
} elseif(isset($_GET['pratID'])) {
  $pratID=$_GET['pratID'];
}
$liensPrat = new msPeopleRelations();
$liensPrat->setToID($pratID);

header('Content-Type: application/json');
$liensPrat->setRelationType('relationPraticienGroupe');
$liensPrat->setReturnedPeopleTypes(['groupe']);
$groupes = $liensPrat->getRelations(['groupname', 'city','country']);
msTools::array_unatsort_by('groupname', $groupes);

if($p['config']['droitGroupePeutVoirTousGroupes'] != 'true') {
  $userGroups = new msPeopleRelations();
  $userGroups->setToID($p['user']['id']);
  $userGroups->setRelationType('relationPraticienGroupe');
  if($userGroups = $userGroups->getRelations()) {
    $intersection=array_intersect(array_column($groupes, 'peopleID'), array_column($userGroups, 'peopleID'));
    foreach($groupes as $k=>$v) {
      if(!in_array($v['peopleID'], $intersection)) {
        unset($groupes[$k]);
      }
    }
  }
}

exit(json_encode(array_merge($groupes)));
