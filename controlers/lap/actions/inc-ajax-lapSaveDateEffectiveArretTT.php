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
 * LAP : ajax > enregistrer une date effective d'arret pour une ligne de prescription
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

header('Content-Type: application/json');
if(is_numeric($_POST['ligneID']) and isset($_POST['date'])) {

  $ob = new msObjet;
  $ob->setToID($_POST['patientID']);
  $ob->setFromID($p['user']['id']);
  $ob->createNewObjetByTypeName('lapLignePrescriptionDatePriseFinEffective',$_POST['date'],$_POST['ligneID']);
  echo json_encode(array('statut'=>'ok'));
} else {
  echo json_encode(array('statut'=>'ko'));
}
