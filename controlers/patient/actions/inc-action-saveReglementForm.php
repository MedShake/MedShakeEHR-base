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
 * Patient > action : sauver un règlement
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

if (count($_POST['acteID'])>0) {
    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);
    $patient->setToID($_POST['patientID']);

    //support
    if (isset($_POST['objetID'])) {
        $supportID=$patient->createNewObjetByTypeName($_POST['porteur'], '', '0', $_POST['acteID'], $_POST['objetID']);
    } else {
        $supportID=$patient->createNewObjetByTypeName($_POST['porteur'], '', '0', $_POST['acteID']);
    }

    //situation patient
    if (!isset($_POST['regleSituationPatient'])) {
      $_POST['regleSituationPatient']='A';
    }
    $patient->createNewObjetByTypeName('regleSituationPatient', $_POST['regleSituationPatient'], $supportID);
    //tarif ss
    if (!isset($_POST['regleTarifCejour'])) {
      $_POST['regleTarifCejour']='';
    }
    $patient->createNewObjetByTypeName('regleTarifCejour', $_POST['regleTarifCejour'], $supportID);
    //dépassement
    if (!isset($_POST['regleDepaCejour'])) {
      $_POST['regleDepaCejour']='';
    }
    $patient->createNewObjetByTypeName('regleDepaCejour', $_POST['regleDepaCejour'], $supportID);
    //cheque
    if (!isset($_POST['regleCheque'])) {
      $_POST['regleCheque']='';
    }
    $patient->createNewObjetByTypeName('regleCheque', $_POST['regleCheque'], $supportID);
    //cb
    if (!isset($_POST['regleCB'])) {
      $_POST['regleCB']='';
    }
    $patient->createNewObjetByTypeName('regleCB', $_POST['regleCB'], $supportID);
    //espèces
    if (!isset($_POST['regleEspeces'])) {
      $_POST['regleEspeces']='';
    }
    $patient->createNewObjetByTypeName('regleEspeces', $_POST['regleEspeces'], $supportID);
    //tiers
    if (!isset($_POST['regleTiersPayeur'])) {
      $_POST['regleTiersPayeur']='';
    }
    $patient->createNewObjetByTypeName('regleTiersPayeur', $_POST['regleTiersPayeur'], $supportID);
    //à régler
    if (!isset($_POST['regleFacture'])) {
      $_POST['regleFacture']='';
    }
    $patient->createNewObjetByTypeName('regleFacture', $_POST['regleFacture'], $supportID);
    //nom chèque
    if (!isset($_POST['regleIdentiteCheque'])) {
      $_POST['regleIdentiteCheque']='';
    }
    $patient->createNewObjetByTypeName('regleIdentiteCheque', $_POST['regleIdentiteCheque'], $supportID);

    //titre
    $codes = msSQL::sqlUniqueChamp("select details from actes where id='".$_POST['acteID']."' limit 1");
    $codes = Spyc::YAMLLoad($codes);
    $codes = implode(' + ', array_keys($codes));
    $patient->setTitleObjet($supportID, $codes.' / '.$_POST['regleFacture'].'€');

    msTools::redirection('/patient/'.$_POST['patientID'].'/');
} else {
    echo 'Formulaire vide !';
}
