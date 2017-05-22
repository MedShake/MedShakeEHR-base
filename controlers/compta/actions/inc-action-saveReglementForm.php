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
    if (!isset($_POST['p_193'])) {
        $_POST['p_193']='';
    }
    $patient->createNewObjet(193, $_POST['p_193'], $supportID);

    //cb
    if (!isset($_POST['p_194'])) {
        $_POST['p_194']='';
    }
    $patient->createNewObjet(194, $_POST['p_194'], $supportID);

    //espèces
    if (!isset($_POST['p_195'])) {
        $_POST['p_195']='';
    }
    $patient->createNewObjet(195, $_POST['p_195'], $supportID);

    //identité chèque
    if (!isset($_POST['p_205'])) {
        $_POST['p_205']='';
    }
    $patient->createNewObjet(205, $_POST['p_205'], $supportID);

    msTools::redirection('/compta/aujourdhui/');
} else {
    echo 'Formulaire vide !';
}
