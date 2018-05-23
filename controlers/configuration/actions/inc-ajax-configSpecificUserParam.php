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
 * Config > action : enregistrer les paramètres spécifiques aux utilisateurs
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");} 

$userID=$_POST['userID'];
unset($_POST['userID']);

if (is_array($_POST)) {
    foreach ($_POST as $k => $v) {
        if (is_array($v)) {
            msConfiguration::setUserParameterValue($k, implode(',',$v), $userID);
        } else if (strpos(strtolower($k), 'password')===false or $v!=str_repeat('*',strlen($v))) {
            msConfiguration::setUserParameterValue($k, $v, $userID);
        }
    }
}
echo json_encode("ok");
