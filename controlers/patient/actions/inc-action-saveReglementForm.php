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
 */

if (count($_POST['acteID'])>0) {
    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);
    $patient->setToID($_POST['patientID']);

    //support
    if (isset($_POST['objetID'])) {
        $supportID=$patient->createNewObjet(192, '', '0', $_POST['acteID'], $_POST['objetID']);
    } else {
        $supportID=$patient->createNewObjet(192, '', '0', $_POST['acteID']);
    }

    //situation patient
    if (!isset($_POST['p_197'])) {
      $_POST['p_197']='A';
    }
    $patient->createNewObjet(197, $_POST['p_197'], $supportID);
    //tarif ss
    if (!isset($_POST['p_198'])) {
      $_POST['p_198']='';
    }
    $patient->createNewObjet(198, $_POST['p_198'], $supportID);
    //dépassement
    if (!isset($_POST['p_199'])) {
      $_POST['p_199']='';
    }
    $patient->createNewObjet(199, $_POST['p_199'], $supportID);
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
    //tiers
    if (!isset($_POST['p_200'])) {
      $_POST['p_200']='';
    }
    $patient->createNewObjet(200, $_POST['p_200'], $supportID);
    //à régler
    if (!isset($_POST['p_196'])) {
      $_POST['p_196']='';
    }
    $patient->createNewObjet(196, $_POST['p_196'], $supportID);
    //nom chèque
    if (!isset($_POST['p_205'])) {
      $_POST['p_205']='';
    }
    $patient->createNewObjet(205, $_POST['p_205'], $supportID);

    //titre
    $code = msSQL::sqlUniqueChamp("select code from actes where id='".$_POST['acteID']."' limit 1");
    $patient->setTitleObjet($supportID, $code.' / '.$_POST['p_196'].'€');

    msTools::redirection('/patient/'.$_POST['patientID'].'/');
} else {
    echo 'Formulaire vide !';
}
