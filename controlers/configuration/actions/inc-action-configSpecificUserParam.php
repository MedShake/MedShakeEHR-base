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

$userID=$_POST['userID'];
unset($_POST['userID']);

$prevData=msSQL::sql2tabKey("SELECT od.typeID AS k, dt.formType AS type, od.id AS Id, od.value AS Value 
		    FROM objets_data AS od 
        LEFT JOIN data_types AS dt ON od.typeID=dt.id and od.toID='".$userID."' and od.outdated='' and od.deleted=''
        where dt.groupe='user'", "k");
if (is_array($_POST)) {
    foreach ($_POST as $k => $v) {
        $typeID=explode('_', $k);
        $typeID=$typeID[1];
        if (is_array($v)) {
            $v=implode(',', $v);
        }
        if (is_numeric($typeID) and is_numeric($userID) and 
          ((!array_key_exists($typeID, $prevData) and $v) or (array_key_exists($typeID, $prevData) and $v!=$prevData[$typeID]['Value']))) {
            $objet = new msObjet();
            $objet->setFromID($p['user']['id']);
            $objet->setToID($userID);
            $id=$objet->createNewObjet($typeID, $v);
            if (array_key_exists($typeID, $prevData) and $prevData[$typeID]['type']=="password" and $v) {
                msSQL::sqlQuery("UPDATE objets_data set value=HEX(AES_ENCRYPT('".$v."',@password)) WHERE id='".$prevData[$typeID]['Id']."' limit 1");
            }
        }
    }
}
msTools::redirection('/configuration/user-param/'.$userID.'/');
