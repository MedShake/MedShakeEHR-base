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
 * LAP : ajax > données brutes envoyées à Thériaque
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//print_r($_POST);

// sortie de l'objet patient
$lapPatient=new msLapPatient;
$lapPatient->setToID($_POST['patientID']);

//sortie de l'analyse Thériaque pour l'ordo courante
$lapOrdo= new msLapAnalysePres;
$lapOrdo->setToID($_POST['patientID']);
$lapOrdo->setObjetPatient($lapPatient->getPatientObjetTheriaque());
$lapOrdo->setOrdonnanceContenu($_POST['ordo']);
$lapOrdo->getObjetsFromOrdo();
$lapOrdo->getObjetsFromTTenCours();


$prescription=$lapOrdo->getObjetPrescription();
$posologie=$lapOrdo->getObjetPosoPres();
$lapOrdo->getAnalyseTheriaque();
$retourAnalyseBrute=$lapOrdo->getBrutAnalyseResults();
$retourAnalyseFormate=$lapOrdo->getFormateAnalyseResults();

echo '<ul class="nav nav-tabs" style="margin-bottom: 10px">
  <li class="nav-item active"><a class="nav-link active" href="#patient" role="tab" data-toggle="tab">Patient</a></li>
  <li class="nav-item"><a class="nav-link" href="#prescriptions" role="tab" data-toggle="tab">Prescriptions / posologies</a></li>
  <li class="nav-item"><a class="nav-link" href="#retour" role="tab" data-toggle="tab">Retour brut</a></li>
  <li class="nav-item"><a class="nav-link" href="#retourformate" role="tab" data-toggle="tab">Retour formaté</a></li>
  </ul>';

echo '<div class="tab-content" >';

echo '<div role="tabpanel" class="tab-pane active" id="patient">';
echo '<pre>';
print_r($lapOrdo->getObjetPatient());
echo '</pre>';
echo '</div>';

echo '<div role="tabpanel" class="tab-pane" id="prescriptions">';
echo '<pre>';
echo "OBJET PRESCRIPTIONS\n";
print_r($prescription);
echo "OBJET POSOLOGIES\n";
print_r($posologie);
echo "LIGNES CORRESPONDANCE\n";
print_r($lapOrdo->getCorrespondanceLignes());
echo '</pre>';
echo '</div>';

echo '<div role="tabpanel" class="tab-pane" id="retour">';
echo '<pre>';
echo "RETOUR BRUT ANALYSE\n";
print_r($retourAnalyseBrute);
echo '</pre>';
echo '</div>';

echo '<div role="tabpanel" class="tab-pane" id="retourformate">';
echo '<pre>';
echo "RETOUR FORMATE\n";
print_r($retourAnalyseFormate);
echo '</pre>';
echo '</div>';

echo '</div>';
