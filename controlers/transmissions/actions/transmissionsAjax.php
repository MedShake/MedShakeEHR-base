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
 * Transmissions : les requête ajax
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$debug='';
$m=$match['params']['m'];

$acceptedModes=array(
    'transGetTransmissions', //obtenir la liste des sujets des transmissions 
    'transReponsePoster', // poster une réponse à une transmissions
    'searchPatient', // chercher un patient concerné
    'transTransmissionPoster', //poster une transmission
    'transGetTransmissionData', //obtenir les data de la transmission en json
    'transTransmissionMarquer', //marquer comme traitée
    'transTransmissionSupp', //marquer comme effacée
);

//inclusion
$file = $p['homepath'].'controlers/transmissions/actions/inc-ajax-'.$m.'.php';
if(in_array($m, $acceptedModes) and is_file($file)) {
    include($file);
} else {
    die();
}
