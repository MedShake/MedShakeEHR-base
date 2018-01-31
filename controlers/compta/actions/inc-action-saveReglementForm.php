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
 * Compta > action : sauver un réglement
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


if (count($_POST)>0) {
    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);
    $patient->setToID($_POST['patientID']);

    $supportID = $_POST['objetID'];

    //cheque
    if (!isset($_POST['regleCheque'])) {
        $_POST['regleCheque']='';
    }
    $patient->createNewObjetByTypeName('regleCheque', $_POST['regleCheque']+$_POST['dejaCheque'], $supportID);

    //cb
    if (!isset($_POST['regleCB'])) {
        $_POST['regleCB']='';
    }
    $patient->createNewObjetByTypeName('regleCB', $_POST['regleCB']+$_POST['dejaCB'], $supportID);

    //espèces
    if (!isset($_POST['regleEspeces'])) {
        $_POST['regleEspeces']='';
    }
    $patient->createNewObjetByTypeName('regleEspeces', $_POST['regleEspeces']+$_POST['dejaEspeces'], $supportID);

    //identité chèque
    if (!isset($_POST['regleIdentiteCheque'])) {
        $_POST['regleIdentiteCheque']='';
    }
    $patient->createNewObjetByTypeName('regleIdentiteCheque', $_POST['regleIdentiteCheque'], $supportID);

    if ($_POST['page']=='comptaToday') {
        msTools::redirection('/compta/aujourdhui/');
    } else {
        msTools::redirection('/compta/');
    }
}
