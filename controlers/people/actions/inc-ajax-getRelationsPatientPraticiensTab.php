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
 * People : ajax > obtenir le tableau de relation patient <-> praticiens
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


if(isset($_POST['patientID'])) {
  $patientID=$_POST['patientID'];
} elseif(isset($_GET['patientID'])) {
  $patientID=$_GET['patientID'];
}
$liensPrat = new msPeopleRelations();
$liensPrat->setToID($patientID);
$liensPrat->setReturnedPeopleTypes(['pro']);

header('Content-Type: application/json');
$liensPrat->setRelationType('relationPatientPraticien');
exit(json_encode($liensPrat->getRelations(['identite','titre'])));
