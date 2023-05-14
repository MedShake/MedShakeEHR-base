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
 * LAP : ajax > rafraichier la colonne laterale du LAP (patient data)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$template = "inc-lapPatientLateralData";

$patient = new msPeople();
$patient->setToID($_POST['patientID']);

$lap = new msLapPatient;
$lap->setToID($_POST['patientID']);
$p['page']['patientAdminData'] = $lap->getPatientAdminData();
$p['page']['patientBasicPhysio'] = $lap->getPatientBasicPhysioDataControle();
$p['page']['patientAllergies'] = $patient->getAllergies($p['config']['lapAllergiesStrucPersoPourAnalyse']);
$p['page']['patientALD'] = $patient->getALD();
if (!empty(trim($p['config']['lapAtcdStrucPersoPourAnalyse']))) {
	foreach (explode(',', $p['config']['lapAtcdStrucPersoPourAnalyse']) as $v) {
		$p['page']['patientATCD'][$v] = $patient->getAtcdStruc($v);
	}
}
