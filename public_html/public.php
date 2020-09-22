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
 * Pivot central des pages non loguées
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

ini_set('display_errors', 0);
setlocale(LC_ALL, "fr_FR.UTF-8");
session_start();

if(($homepath=getenv("MEDSHAKEEHRPATH"))===false) {
    $homepath=file_get_contents("MEDSHAKEEHRPATH");
    $homepath=trim(str_replace("\n", '', $homepath));
}
$homepath.=$homepath[strlen($homepath)-1]=='/'?'':'/';

/////////// Composer class auto-upload
require $homepath.'vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
    global $homepath;
    if (is_file($homepath.'/class/' . $class . '.php')) {
        include $homepath.'/class/' . $class . '.php';
    }
});

/////////// Compatibilité versions antérieures PHP
require $homepath.'fonctions/compatibilite.php';

/////////// Config loader
$p['config']=yaml_parse_file($homepath.'config/config.yml');

/////////// correction pour host non présent (IP qui change)
if ($p['config']['host']=='') {
    $p['config']['host']=$_SERVER['SERVER_ADDR'];
    $p['config']['cookieDomain']=$_SERVER['SERVER_ADDR'];
}
$p['homepath']=$homepath;

/////////// SQL connexion
$mysqli=msSQL::sqlConnect();


/////////// Sortie des versions des modules
if (empty($p['modules']=msModules::getInstalledModulesVersions())) {
    msTools::redirection('/install.php');
}

/////////// Compléter le tableau des paramètres de configuration par défaut
$p['config']=array_merge($p['config'], msConfiguration::getAllParametersForUser());

/////////// Validators loader
define("PASSWORDLENGTH", msConfiguration::getDefaultParameterValue('optionGeLoginPassMinLongueur'));
require $homepath.'fonctions/validators.php';

/////////// Router
$match = msSystem::getRoutes(['public']);

///////// Maintenance
if (msSystem::getSystemState()=='maintenance') {
    msTools::redirection('/maintenancePublic.html');
}

///////// Controler else -> 404
if ($match and is_file($homepath.'controlers/'.$match['target'].'.php')) {
    include $homepath.'controlers/'.$match['target'].'.php';

    // complément lié au module installé
    if (is_file($homepath.'controlers/module/'.$match['target'].'.php')) {
        include $homepath.'controlers/module/'.$match['target'].'.php';
    }
} else if ($match and is_file($homepath.'controlers/module/'.$match['target'].'.php')) {
    // Permet d'ajouter un controler via un module même si celui-ci n'existe pas dans MedShakeEHR-base
    include $homepath.'controlers/module/'.$match['target'].'.php';
} else {
    $template='404';
}

//////// View if defined
if (isset($template)) {
    if (isset($_SESSION)) {
        $p['session']=$_SESSION;
    }

    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: private, no-store, max-age=0, no-cache, must-revalidate, post-check=0, pre-check=0");
    header("Pragma: no-cache");

    //générer et sortir le html
    $getHtml = new msGetHtml();
    $getHtml->set_template($template);
    echo $getHtml->genererHtml();
}
