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
 * People : ajax > définir une relation entre 2 peopleID
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$relation = new msPeopleRelations;
$relation->setToID($_POST['peopleID']);
$relation->setToStatus($_POST['toStatus']);
$relation->setFromID($p['user']['id']);
$relation->setWithID($_POST['withID']);
$relation->setRelationType($_POST['relationType']);

header('Content-Type: application/json');
if($relation->checkRelationExist()) {
  exit(json_encode(array('status'=>'exist')));
}
if($relation->checkMaxGroupeRestriction()) {
  exit(json_encode(array('status'=>'reachmaxgroups')));
}
if($relation->setRelation()) {
  exit(json_encode(array('status'=>'ok')));
} else {
  exit(json_encode(array('status'=>'ko')));
}
