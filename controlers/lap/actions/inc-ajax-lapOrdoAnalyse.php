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
 * LAP : ajax > analyser ordonnance et traitement en cours
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

// sortie de l'objet patient
$lapPatient=new msLapPatient;
$lapPatient->setToID($_POST['patientID']);

//sortie de l'analyse Thériaque pour l'ordo courante
$lapOrdo= new msLapAnalysePres;
$lapOrdo->setToID($_POST['patientID']);
$lapOrdo->setObjetPatient($lapPatient->getPatientObjetTheriaque());
$lapOrdo->setPatientPhysioControleData($lapPatient->getPatientBasicPhysioDataControle());
if(isset($_POST['ordo'])) {
  $lapOrdo->setOrdonnanceContenu($_POST['ordo']);
  $lapOrdo->getObjetsFromOrdo();
}
$lapOrdo->getObjetsFromTTenCours();
$lapOrdo->getAnalyseTheriaque();

//data légales Thériaque
$dataTheriaque=$lapOrdo->getTheriaqueInfos();

$retour=array(
  'html'=>$lapOrdo->getHtmlAnalysesResults(),
  'correspondanceLignes'=>$lapOrdo->getCorrespondanceLignes(),
  'lignesRisqueAllergique'=>$lapOrdo->getLignesRisqueAllergique(),
  'versionTheriaque'=>$dataTheriaque[0]['vers'].' '.$dataTheriaque[0]['date_ext'],
);
echo json_encode($retour);
