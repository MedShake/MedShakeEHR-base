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
 * Transmissions, transmission
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 */

$debug='';
$template="transmissionsTransmission";

if($p['config']['transmissionsPeutVoir'] != 'true') die("Vous n'êtes pas autorisé à accéder aux transmissions.");

$p['page']['transmissionID']=$match['params']['transmission'];

$trans = new msTransmissions();
$trans->setUserID($p['user']['id']);
$trans->setId($p['page']['transmissionID']);
$trans->setSujetID($p['page']['transmissionID']);
$trans->setTranmissionDateLecture();
$p['page']['transmission']=$trans->getTransmission();
$p['page']['transmissionReponses']=$trans->getTransmissionReponses();
$p['page']['transmissionsListeDestinatairesPossibles']=$trans->getTransmissionDestinatairesPossibles();
$p['page']['transmissionsListeDestinatairesDefaut']=explode(',', $p['config']['transmissionsDefautDestinataires']);
if(!empty($p['page']['transmission']['destinataires'])) {
  $p['page']['transmission']['statutDestinataires']=array_column($p['page']['transmission']['destinataires'],'statut', 'toID');
  $p['page']['transmission']['destinatairesID']=array_column($p['page']['transmission']['destinataires'],'toID');
}

if($p['page']['transmission']['fromID'] == $p['user']['id']) {
  $p['page']['transmission']['fromBox']='envoyees';
} else {
  $p['page']['transmission']['fromBox']='recues';
}
