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
 * LAP : monographie
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$debug='';
$template="lapMonographie";

if($p['config']['optionGeActiverLapInterne'] != 'true') die("Le LAP n'est pas activé");

$mono=new msLapMonographie();
$mono->setSpe($match['params']['spe']);
$p['page']['spe']=$match['params']['spe'];
if(isset($match['params']['section'])) $p['page']['section']=$match['params']['section']; else $p['page']['section']='administratif';
$p['page']['speData']=$mono->getSpeData();
