<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Transmissions : poster / editer une transmission
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$trans=new msTransmissions;
$trans->setUserID($p['user']['id']);
if(isset($_POST['transID']) and is_numeric($_POST['transID'])) $trans->setId($_POST['transID']);
$trans->setFromID($p['user']['id']);
if(isset($_POST['patientConcerne']) and is_numeric($_POST['patientConcerne'])) {
  $trans->setAboutID($_POST['patientConcerne']);
}
if(isset($_POST['destinataires']) and !empty($_POST['destinataires'])) {
  foreach($_POST['destinataires'] as $toID) {
    $trans->setToID($toID);
  }
}
$trans->setTexte($_POST['texte']);
$trans->setSujet($_POST['sujet']);
$trans->setPriorite($_POST['priorite']);
$sujetID=$trans->setTranmissionPoster();

//purger les transmissions anciennes
$trans->purgerTransmissions();

header('Content-Type: application/json');
echo json_encode(['sujetID'=>$sujetID]);
