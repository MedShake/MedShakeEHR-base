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
 * Patient : onglet LAP
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 $debug='';
 $template="inc-tabLAP";

 if($p['config']['optionGeActiverLapInterne'] != 'true') die("Le LAP n'est pas activé");

 $p['page']['patient']['id']=$match['params']['patientID'];
 $patient=new msPeople();
 $patient->setToID($match['params']['patientID']);

 $lapPatient=new msLapPatient;
 $lapPatient->setToID($match['params']['patientID']);
 $p['page']['patientAdminData']=$lapPatient->getPatientAdminData();
 $p['page']['patientBasicPhysio']=$lapPatient->getPatientBasicPhysioDataControle();
 $p['page']['patientAllergies']=$patient->getAllergies($p['config']['lapAllergiesStrucPersoPourAnalyse']);
 $p['page']['patientALD']=$patient->getALD();
 if(!empty(trim($p['config']['lapAtcdStrucPersoPourAnalyse']))) {
  foreach(explode(',', $p['config']['lapAtcdStrucPersoPourAnalyse']) as $v) {
    $p['page']['patientATCD'][$v]=$patient->getAtcdStruc($v);
  }
 }
