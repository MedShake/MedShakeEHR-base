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
 * Config : les actions avec reload de page
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


header('Content-Type: application/json');

$m=$match['params']['m'];

$acceptedModes=array(
    'configFormEdit', // Edition du formulaire
    'configUpdatePassword', //Attribuer un password à un user
    'configSpecificUserParam', //Attribuer une config spécifique à un utilisateur
    'configGiveAdmin' // Toggle droit d'admin
);

if (!in_array($m, $acceptedModes)) {
    die;
}


// Edition du formulaire
if ($m=='configFormEdit') {
    include('inc-action-configFormEdit.php');
}
// Attribuer un password à un user
elseif ($m=='configUpdatePassword') {
    include('inc-action-configUpdatePassword.php');
}
// Attribuer une config spécifique à un utilisateur
elseif ($m=='configSpecificUserParam') {
    include('inc-action-configSpecificUserParam.php');
}
// Donner / retirer droit d'admin à un utilisateur
elseif ($m=='configGiveAdmin') {
    include('inc-action-configGiveAdmin.php');
}
