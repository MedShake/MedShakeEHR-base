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
 * LAP : monographie, fiches annexes
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template="lapMonoAnnexes";

if($p['config']['optionGeActiverLapInterne'] != 'true') die("Le LAP n'est pas activé");

$mono=new msLapMonographie();

$p['page']['spe']=$match['params']['spe'];
$p['page']['mode']=$match['params']['mode'];
$p['page']['fiche']=$match['params']['fiche'];
if(isset($match['params']['type'])) $p['page']['type']=$match['params']['type'];

if($p['page']['mode']=='ei') {
  $mono->setSpe($match['params']['spe']);
  $p['page']['speData']=$mono->getSpeData();
  $p['page']['ficheEi']=$mono->getMonoAnnexesEI($p['page']['fiche']);
  $p['page']['title'] = 'Fiche effets indésirables '.$p['page']['speData']['sp_nom'];
}
elseif($p['page']['mode']=='eis') {
  $mono->setSpe($match['params']['spe']);
  $p['page']['speData']=$mono->getSpeData();
  $p['page']['ficheEi']=$mono->getMonoAnnexesEIS($p['page']['fiche']);
  $p['page']['title'] = 'Fiche surdosage '.$p['page']['speData']['sp_nom'];
}
elseif($p['page']['mode']=='doc') {
  $mono->setSpe($match['params']['spe']);
  $p['page']['speData']=$mono->getSpeData();
  $p['page']['doc']=$mono->getMonoAnnexesDoc($p['page']['fiche'],$p['page']['type']);
  $p['page']['title'] = 'Document tiers '.$p['page']['speData']['sp_nom'];
}
elseif($p['page']['mode']=='sub' and $p['page']['fiche'] == '1') {
  $p['page']['sub']=$mono->getMonoAnnexesSubA($p['page']['spe']);
  $p['page']['title'] = 'Fiche substance '.$p['page']['sub']['subId']['sac_nom'];
}
elseif($p['page']['mode']=='sub' and $p['page']['fiche'] == '2') {
  $p['page']['sub']=$mono->getMonoAnnexesSubE($p['page']['spe']);
  $p['page']['title'] = 'Fiche excipient '.$p['page']['sub']['subId']['sau_nom'];
}
