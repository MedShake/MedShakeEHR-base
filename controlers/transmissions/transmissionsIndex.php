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
 * Transmissions, index
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 */

 $debug='';
 $template="transmissionsIndex";

if(isset($match['params']['transBox'])) {
  $p['page']['transBox']=$match['params']['transBox'];
} else {
  $p['page']['transBox']='recues';
}

$trans = new msTransmissions();
$trans->setUserID($p['user']['id']);
if($p['config']['transmissionsPeutVoir'] != 'true') die("Vous n'êtes pas autorisé à accéder aux transmissions.");
$p['page']['transmissionsListeDestinatairesPossibles']=$trans->getTransmissionDestinatairesPossibles();
$p['page']['transmissionsListeDestinatairesDefaut']=explode(',', $p['config']['transmissionsDefautDestinataires']);
$p['page']['nbTransmissionsRecuesNonLues']=$trans->getNbTransmissionsRecuesNonLues();
$p['page']['nbTransmissionsEnvoyeesNonLues']=$trans->getNbTransmissionsEnvoyeesNonLues();
