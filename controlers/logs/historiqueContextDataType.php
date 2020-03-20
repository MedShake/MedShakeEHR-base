<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * Historique contextuel (patient, instance) de la valeur d'un data type particulier
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//admin uniquement
if (!msUser::checkUserIsAdmin()) {
   $template="forbidden";
   return;
}

$debug='';
$template='historiqueContextDataType';
$typeName = $match['params']['dataType'];

if(isset($match['params']['instance'])) {
  $instance = $match['params']['instance'];
} else {
  $instance = 0;
}


$dataType = new msData;
if($dataType->checkDataTypeExistByName($typeName)) {
  $typeID = $dataType->getTypeIDFromName($typeName);
  $p['page']['dataType'] = $dataType->getDataTypeByName($typeName);
  $p['page']['typeLabel'] = $dataType->getLabelFromTypeID([$typeID])[$typeID];
  $p['page']['typeName'] = $typeName;
  $obj = new msObjet;
  $obj->setToID($match['params']['patientID']);
  $p['page']['histo']=$obj->getDataTypeContextualHistoric($typeName, $instance);



} else {

}
