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
 */


if (is_array($_POST)) {
    foreach ($_POST as $k => $v) {
        $typeID=explode('_', $k);
        $typeID=$typeID[1];

        if (is_numeric($typeID) and is_numeric($_POST['userID'])) {
            $objet = new msObjet();
            $objet->setFromID($p['user']['id']);
            $objet->setToID($_POST['userID']);
            $objet->createNewObjet($typeID, $v, 1);
        }
    }
}

msTools::redirection('/configuration/user-param/'.$_POST['userID'].'/');
