<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2020
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
 * Requêtes AJAX > autocomplete des forms, version simple
 *
 * @author Maxime DEMAREST <maxime@indelog.fr>
 */

// Ne retrouner déscativé si déscativé
if ($p['config']['activGenBarreCode'] == 'false') {
	echo json_encode(array('is_disabled' => 'true'));
	exit();
}

$barcodedir = $p['config']['stockageLocation'].'barecode/';

// TODO Check adeli and rpps format

$praticien = new msPeople();
$praticien->setToID($_POST['pratID']);
$pratData = $praticien->getLabelForSimpleAdminDatas($praticien->getSimpleAdminDatasByName());

$adeli = $pratData['adeli'];
$rpps = $pratData['rpps'];

$rpps_generated = 0;
$adeli_generated = 0;
if ($_POST['genCode'] == 'true') {
    if (!empty($rpps)) $rpps_generated = msTools::genBareCodeFile('rpps', $rpps);
    if (!empty($adeli)) $adeli_generated = msTools::genBareCodeFile('adeli', $adeli);
}

$html = '<div id="barecodeGenContainer" class="mt-5 border-top border-left border-bottom border-right col-xl-10 col-12 p-4  bg-light">';
$html .= '<div class="row" data-num-row="1">';

// block rpps
$has_rpps_barecode_file = false;
$html .= '<div class="col-md-6 data-num-row="1" data-num-col="1">';
if (empty($rpps)) {
    $html .= '<div class="text-center">N° RPPS non fournis.</div>';
} else if (file_exists($barcodedir.'barecode-rpps-'.$rpps.'.svg')) {
    $has_rpps_barecode_file = true;
    $html .= '<div class="text-center">';
    $html .= file_get_contents($barcodedir.'barecode-rpps-'.$rpps.'.svg');
    $html .= '<p>Code-barres RPPS généré.</br><i>(n° '.$rpps.')</i></p>';
    $html .= '</div>';
} else {
    $html .= '<div class="text-center">Code-barres RPPS non généré</div>';
}
$html .= '</div>';

// block adeli
$has_adeli_barecode_file = false;
$html .= '<div class="col-md-6 data-num-row="1" data-num-col="1">';
if (empty($adeli)) {
    $html .= '<div class="text-center">N° ADELI non fournis.</div>';
} else if (file_exists($barcodedir.'barecode-adeli-'.$adeli.'.svg')) {
    $has_adeli_barecode_file = true;
    $html .= '<div class="text-center">';
    $html .= file_get_contents($barcodedir.'barecode-adeli-'.$adeli.'.svg');
    $html .= '<p>Code-barres ADELI généré.</br><i>(n° '.$adeli.')</i></p>';
    $html .= '</div>';
} else {
    $html .= '<div class="text-center">Code-barres ADELI non généré</div>';

}
$html .= '</div>';

$html .= '</div>';

// block button to gen
if ((!empty($rpps) || !empty($adeli)) && (!$has_rpps_barecode_file || !$has_adeli_barecode_file)) {
    $html .= '<div class="row  mt-3 justify-content-center" data-num-row="2">';
    $html .= '<button id="getCodeBarreButton" type="button" class="btn btn-primary">Générer les codes-barres</button>';
    $html .= '</div>';
}

$html .= '</div>';

$data = array();
$data['rpps_generated'] = $rpps_generated;
$data['adeli_generated'] = $adeli_generated;
$data['has_rpps'] = !empty($rpps);
$data['has_adeli'] = !empty($adeli);
$data['has_rpps_barecode_file'] = $has_rpps_barecode_file;
$data['has_adeli_barecode_file'] = $has_adeli_barecode_file;
// Ne pas retourner le html si il n'y a ni rpps ni adeli
if (empty($rpps) && empty($adeli)) $data['html'] = '';
else $data['html'] = $html;

echo json_encode($data);
