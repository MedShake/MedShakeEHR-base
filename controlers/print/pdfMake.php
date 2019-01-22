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
 * Print : fabriquer le PDF, le sauver et l'afficher
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$pdf= new msPDF();

$pdf->setFromID($p['user']['id']);
$pdf->setToID($match['params']['patient']);
$pdf->setType($match['params']['printType']);

if (isset($_POST['courrierBody'])) {
    $pdf->setBodyFromPost($_POST['courrierBody']);
}
if (isset($match['params']['modele'])) {
    $pdf->setModeleID($match['params']['modele']);
}
if (isset($match['params']['instance'])) {
    $pdf->setInstanceID($match['params']['instance']);
}
if (isset($match['params']['examen'])) {
    $pdf->setObjetID($match['params']['examen']);
}

if (isset($match['params']['anonyme'])) {
    if($match['params']['anonyme']=='anonyme') $pdf->setAnonymeMode();
}

$pdf->makePDF();
$pdf->savePDF();

$doc = new msStockage;
$doc->setObjetID($pdf->getObjetID());
msTools::redirection('/'.$doc->getWebPathToDoc());
