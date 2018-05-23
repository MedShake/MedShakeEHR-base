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
$lappres->setToID($_POST['toID']);
$lappres->setFromID($p['user']['id']);
$lappres->setSpeThe($_POST['speThe']);
$lappres->setPresThe($_POST['presThe']);
$lappres->setTauxRbt($_POST['tauxrbt']);
$lappres->setPrixUcd($_POST['prixucd']);
echo json_encode($lappres->lapInstallPrescription());
