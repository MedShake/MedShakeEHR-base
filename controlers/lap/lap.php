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
 * Login : page de login
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 $debug='';
 $template="lap";

 $p['page']['patient']['id']=$match['params']['patient'];
 $patient=new msPeople();
 $patient->setToID($match['params']['patient']);

 $lap=new msLAP;
 $lap->setToID($match['params']['patient']);
 $p['page']['patientAdminData']=$lap->getPatientAdminData();
 $p['page']['patientBasicPhysio']=$lap->getPatientBasicPhysioDataControle();
 $p['page']['patientAllergies']=$patient->getAllergies('allergies');
 $p['page']['patientALD']=$patient->getALD();
 $listeChampsAtcd=array('atcdObs','atcdPersoGyneco','atcdMedicChir');
 foreach($listeChampsAtcd as $v) {
   $p['page']['patientATCD'][$v]=$patient->getAtcdStruc($v);
 }
