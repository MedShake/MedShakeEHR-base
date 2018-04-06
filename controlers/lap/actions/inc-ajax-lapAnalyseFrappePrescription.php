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
 * LAP : ajax > analyser la prescription frappée
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//print_r($_POST);
header('Content-Type: application/json');
if (count($_POST['ligneData']['medics']) == 1) {
    $lappres=new msLapPrescription;
    $lappres->setTxtPrescription($_POST['ligneData']['medics'][0]['prescriptionMachinePoso']);
    $lappres->setSpeThe($_POST['ligneData']['medics'][0]['speThe']);
    $lappres->setPresThe($_POST['ligneData']['medics'][0]['presThe']);
    $lappres->setNomSpe($_POST['ligneData']['medics'][0]['nomSpe']);
    $lappres->setNomDC($_POST['ligneData']['medics'][0]['nomDC']);
    $lappres->setUniteUtilisee($_POST['ligneData']['medics'][0]['uniteUtilisee']);
    $lappres->setUniteUtiliseeOrigine($_POST['ligneData']['medics'][0]['uniteUtiliseeOrigine']);
    $lappres->setUnitesConversion($_POST['ligneData']['medics'][0]['unitesConversion']);
    $lappres->setVoieUtilisee($_POST['ligneData']['ligneData']['voieUtilisee']);
    $lappres->setDivisibleEn($_POST['ligneData']['medics'][0]['divisibleEn']);
    $lappres->setMedicVirtuel($_POST['ligneData']['medics'][0]['medicVirtuel']);
    $lappres->setPrescriptibleEnDC($_POST['ligneData']['medics'][0]['prescriptibleEnDC']);
    $lappres->setNbRenouvellement($_POST['ligneData']['ligneData']['nbRenouvellements']);
    $lappres->setDatePremierePrise($_POST['ligneData']['ligneData']['dateDebutPrise']);
    $lappres->setStupefiant($_POST['ligneData']['medics'][0]['stup']);

    $lappres->interpreterPrescription();
    echo $lappres->getPrescriptionInterpreteeJSON();
}
