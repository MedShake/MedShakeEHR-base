<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Transmissions : enregistrer / éditer une réponce à une transmission 
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if(!is_numeric($_POST['sujetID'])) die;

$trans=new msTransmissions;
$trans->setUserID($p['user']['id']);
if(is_numeric($_POST['reponseID'])) {
  $trans->setId($_POST['reponseID']);
}
if(is_numeric($_POST['sujetID'])) {
  $trans->setSujetID($_POST['sujetID']);
}
$trans->setFromID($p['user']['id']);
$trans->setTexte($_POST['texte']);
$trans->setTranmissionReponsePoster();

$p['page']['transmissionReponses']=$trans->getTransmissionReponses();
$html = new msGetHtml;
$html->set_template('inc-transmissionsBlocReponses');
$html=$html->genererHtmlVar($p);
header('Content-Type: application/json');
echo json_encode([
  'html'=>$html
]);
