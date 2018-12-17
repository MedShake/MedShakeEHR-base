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
 * Public : signer un doc sur périphérique tactile
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$template="signer";

if(isset($match['params']['signPeriphName'])) {
  $signPeriphName=$match['params']['signPeriphName'];
} else {
  $signPeriphName=$p['config']['signPeriphName'];
}

$p['page']['signPeriphName']=$signPeriphName;

if (is_file($p['config']['workingDirectory'].'signData-'.$signPeriphName.'.txt')) {
  $p['page']['docasigner']=Spyc::YAMLLoad($p['config']['workingDirectory'].'signData-'.$signPeriphName.'.txt');
} else {
  die('Les données sur la signature à réaliser ne sont pas disponibles.');
}

if (!isset($p['page']['docasigner']['patientID'])) {
  die('Le patient n\'est pas défini.');
} else {
  $courrier = new msCourrier();
  $courrier->setPatientID($p['page']['docasigner']['patientID']);
  if(isset($p['page']['docasigner']['objetID'])) {
    $courrier->setObjetID($p['page']['docasigner']['objetID']);
    $p['page']['courrier']=$courrier->getDataByObjetID();
  } elseif (is_numeric($p['page']['docasigner']['patientID'])) {
    $courrier->setFromID($p['page']['docasigner']['fromID']);
    $p['page']['courrier']=$courrier->getCourrierData();
  } else {
    die('Le patient n\'est pas défini');
  }
}
