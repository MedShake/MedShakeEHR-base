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
 * Config : attribuer un mot de passe et un module à un utilisateur
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */
$module=isset($_POST['p_module'])?$_POST['p_module']:'public';
$type=$module=='public'?'patient':'pro';

if (empty($_POST['p_password'])) {
    msSQL::sqlQuery("UPDATE people SET module='".$module."' WHERE name='".$_POST['p_username']."'");
} else {
    $data=array(
        'name' => $_POST['p_username'],
        'type' => $type,
        'rank' => '',
        'module' => $module,
        'registerDate' => date("Y/m/d H:i:s"),
        'fromID' => $p['user']['id']
    );
    msSQL::sqlInsert('people', $data);
    msSQL::sqlQuery("UPDATE people SET pass=AES_ENCRYPT('".$_POST['p_password']."',@password) WHERE name='".$_POST['p_username']."' limit 1");
    $id=msSQL::sqlUniqueChamp("SELECT id FROM people WHERE name='".$_POST['p_username']."'");
    msTools::redirection('/'.$type.'/edit/'.$id.'/');
}
msTools::redirection('/configuration/');
