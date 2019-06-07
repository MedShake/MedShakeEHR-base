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
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

if(is_numeric($_POST['porteur'])) $data=msSQL::sqlUnique("SELECT module, formValues as reglementForm FROM data_types WHERE id=".$_POST['porteur']);
if (!in_array($data['reglementForm'], ['baseReglementLibre', 'baseReglementS1', 'baseReglementS2'])) {
      $hook=$p['homepath'].'/controlers/module/'.$data['module'].'/compta/actions/inc-hook-saveReglementForm.php';
      if ($data['module']!='' and $data['module']!='base' and is_file($hook)) {
          include $hook;
      }
      if (!isset($delegate)) {
          return;
      }
}

if (count($_POST)>0 and is_numeric($_POST['objetID'])) {
    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);
    $patient->setToID($_POST['patientID']);

    $supportID = $_POST['objetID'];

    foreach (['regleCheque', 'regleCB', 'regleEspeces', 'regleTiersPayeur', 'regleIdentiteCheque'] as $param) {
        if (!isset($_POST[$param])) {
          $_POST[$param]='';
        }
    }

    $important=array('id'=>$supportID, 'important'=>($_POST['regleCheque']+$_POST['regleCB']+$_POST['regleEspeces']) < $_POST['apayer']?'y':'n');
    msSQL::sqlInsert('objets_data', $important);

    if (($_POST['regleCheque']+$_POST['regleCB']+$_POST['regleEspeces']) <= $_POST['apayer']) {
        $patient->createNewObjetByTypeName('regleCheque', $_POST['regleCheque']+$_POST['dejaCheque'], $supportID);
        $patient->createNewObjetByTypeName('regleCB', $_POST['regleCB']+$_POST['dejaCB'], $supportID);
        $patient->createNewObjetByTypeName('regleEspeces', $_POST['regleEspeces']+$_POST['dejaEspeces'], $supportID);
        $patient->createNewObjetByTypeName('regleIdentiteCheque', $_POST['regleIdentiteCheque'], $supportID);
    }

    if ($_POST['page']=='comptaToday') {
        msTools::redirection('/compta/aujourdhui/');
    } else {
        msTools::redirection('/compta/');
    }
}
