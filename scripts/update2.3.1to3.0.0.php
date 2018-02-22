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
 * Upgrade de la base en version 2.3.1 vers 3.0.0
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");
session_start();

$homepath=getenv("MEDSHAKEEHRPATH");
$homepath.=$homepath[strlen($homepath)-1]=='/'?'':'/';

/////////// Composer class auto-upload
require $homepath.'vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
    include $homepath.'class/' . $class . '.php';
});


/////////// Config loader
require $homepath.'config/config.php';

/////////// SQL connexion
$mysqli=msSQL::sqlConnect();

// conversion des Formulaires

if($forms=msSQL::sql2tabKey("select id, yamlStructure, dataset from forms", 'id')) {
    foreach($forms as $id=>$d) {
        $obform=new msForm();
        $newyaml=$obform->cleanForm($d['yamlStructure'],$d['dataset']);

        if(msSQL::sqlQuery("update forms set yamlStructure='".addslashes($newyaml)."' where id='".$id."'")) {
            echo $id." : ok\n";
        } else {
            echo $id." : PROBLEME !\n";
        }

    }

}
