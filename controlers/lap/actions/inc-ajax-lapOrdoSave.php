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
 * LAP : ajax > sauver une ordonnance
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$ordo = new msObjet();
$ordo->setFromID($p['user']['id']);
$ordo->setToID($_POST['patientID']);

// Créer porteur ordonnance
$ordoID=$ordo->createNewObjetByTypeName('lapOrdonnance', '');
// enregistrer nom de l'ordo.
if(!empty($_POST['ordoName'])) $ordo->setTitleObjet($ordoID, $_POST['ordoName']);

$lap = new msLapOrdo();
$lap->setToID($_POST['patientID']);
$lap->setOrdonnanceID($ordoID);

// Création des lignes ALD
if(!empty($_POST['ordo']['ordoMedicsALD'])) {
  foreach($_POST['ordo']['ordoMedicsALD'] as $ligneALD) {
    $lap->saveLignePrescription($ligneALD);
  }
}

// Création des lignes générales
if(!empty($_POST['ordo']['ordoMedicsG'])) {
  foreach($_POST['ordo']['ordoMedicsG'] as $ligneG) {
    $lap->saveLignePrescription($ligneG);
  }
}
//enregistrement de versionTheriaque + liste SAMs dans value porteur ordo
$ordoValue=array('versionTheriaque'=>$_POST['versionTheriaque']);
if($samsList=$lap->getSamsListInOrdo()) {
  if(!empty($samsList)) $ordoValue['sams']=$samsList;
}
$ordoValue=json_encode($ordoValue);
$ordo->createNewObjetByTypeName('lapOrdonnance', $ordoValue, '0','0',$ordoID);

echo json_encode(array('ordoID'=>$ordoID));
