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

$passwords=msSQL::sql2tabKey("SELECT dt.id as k, od.id as Id, od.value as Value FROM data_types AS dt
        LEFT JOIN objets_data AS od on od.typeID=dt.id and od.toID='".$_POST['userID']."' and od.outdated='' and od.deleted=''
        where dt.groupe='user' and dt.formType='password'", "k");
if (is_array($_POST)) {
    foreach ($_POST as $k => $v) {
        $typeID=explode('_', $k);
        $typeID=$typeID[1];

        if (is_numeric($typeID) and is_numeric($_POST['userID'])) {
            $objet = new msObjet();
            $objet->setFromID($p['user']['id']);
            $objet->setToID($_POST['userID']);
            $id=$objet->createNewObjet($typeID, $v);
            if (array_key_exists($typeID, $passwords) and $v!=$passwords[$typeID]['Value']) {
                msSQL::sqlQuery("UPDATE objets_data set value=HEX(AES_ENCRYPT('".$v."',@password)) WHERE id='".$passwords[$typeID]['Id']."' limit 1");
            }
        }
    }
}

msTools::redirection('/configuration/user-param/'.$_POST['userID'].'/');
