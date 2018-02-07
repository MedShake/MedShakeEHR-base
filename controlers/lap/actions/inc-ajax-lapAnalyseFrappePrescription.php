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
header('Content-Type: application/json');
$lappres=new msLapPrescription;
$lappres->setTxtPrescription($_POST['txtPrescription']);
$lappres->setSpeThe($_POST['medicData']['speThe']);
$lappres->setPresThe($_POST['medicData']['presThe']);
$lappres->setNomSpe($_POST['medicData']['nomSpe']);
$lappres->setNomDC($_POST['medicData']['nomDC']);
$lappres->setUniteUtilisee($_POST['uniteUtilisee']);
$lappres->setUniteUtiliseeOrigine($_POST['uniteUtiliseeOrigine']);
$lappres->setUnitesConversion($_POST['medicData']['unitesConversion']);
$lappres->setVoieUtilisee($_POST['voieUtilisee']);
$lappres->setDivisibleEn($_POST['medicData']['divisibleEn']);
$lappres->setMedicVirtuel($_POST['medicData']['medicVirtuel']);
$lappres->setPrescriptibleEnDC($_POST['medicData']['prescriptibleEnDC']);
$lappres->interpreterPrescription();
$lappres->getPrescriptionInterpreteeJSON();
