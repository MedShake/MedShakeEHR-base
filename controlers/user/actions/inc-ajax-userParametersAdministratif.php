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
 * enregistrement des paramètres utilisateur Administratif
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


if($listesVar = msConfiguration::getCatParametersForUser('Règlements')) {
  foreach($listesVar as $k=>$v) {
    if($k == 'administratifReglementFormulaires') {
      if(isset($_POST['administratifReglementFormulaires']) and is_array($_POST['administratifReglementFormulaires'])) {
        $_POST['administratifReglementFormulaires']=implode(',', $_POST['administratifReglementFormulaires']);
      } else {
         $_POST['administratifReglementFormulaires']='';
      }
    }
    if(isset($_POST[$k])) {
      msConfiguration::setUserParameterValue($k, $_POST[$k], $p['user']['id']);
    }
  }
}

header('Content-Type: application/json');
echo json_encode(array('status'=>'success'));
