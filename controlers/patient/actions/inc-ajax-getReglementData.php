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
 * Patient > ajax : obtenir les informations de règlement d'un acte
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

header('Content-Type: application/json');

if(!is_numeric($_POST['acteID'])) die();

$reglement = new msReglement();
if (isset($_POST['reglementForm'])) {
    $reglement->set_secteurTarifaire($_POST['reglementForm']=='baseReglementS1'?'1':($_POST['reglementForm']=='baseReglementS2'?'2':''));
} else {
    $reglement->set_secteurTarifaire($p['config']['administratifSecteurHonoraires']);
}
$reglement->set_factureTypeID($_POST['acteID']);
$data = $reglement->getCalculateFactureTypeData();


echo json_encode($data);

die();
