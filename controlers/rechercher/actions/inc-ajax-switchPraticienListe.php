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
 * Patients > ajax : ajout / retrait d'un dossier à la liste des Praticiens.
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

header('Content-Type: application/json');

if(is_numeric($_POST['patientID'])) {
  if($statutActu=msSQL::sqlUniqueChamp("select type from people where id='".$_POST['patientID']."'")) {
    if($statutActu=='patient') {
      $statutFutur='pro';
      if($p['config']['droitDossierPeutCreerPraticien'] != 'true') die();
    } else {
      $statutFutur='patient';
      if($p['config']['droitDossierPeutRetirerPraticien'] != 'true') die();
    }

    $data=array(
      'id'=>$_POST['patientID'],
      'type'=>$statutFutur
    );

    msSQL::sqlInsert('people', $data);

    echo json_encode($data);

  }

}
