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
 * User : les actions avec reload de page
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */


//$debug='';
$m=$match['params']['m'];

//modes acceptés et die() si non connu
$acceptedModes=array(
    'changeUserPhoneCaptureFingerprint', // changer phonecaptureFingerprint de l'utilisateur courant
    'userParametersPassword', // changer le mot de passe de l'utilisateur courant
    'userParametersErgonomie', // changer param ergonomie et design
    'userParametersRevoke2faKey', // révoquer sa clef 2FA
);
if (!in_array($m, $acceptedModes)) {
    die;
}

//inclusion
include('inc-action-'.$m.'.php');
