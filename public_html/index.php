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
 * Pivot central des pages loguées
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");
session_start();


/////////// Composer class auto-upload
require '../vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
    include '../class/' . $class . '.php';
});


/////////// Config loader
$p['config']=Spyc::YAMLLoad('../config/config.yml');
$p['config']['relativePathForInbox']=str_replace($p['config']['webDirectory'], '', $p['config']['apicryptCheminInbox']);

/////////// correction pour host non présent (IP qui change)
if ($p['config']['host']=='') {
  $p['config']['host']=$_SERVER['SERVER_ADDR'];
  $p['config']['cookieDomain']=$_SERVER['SERVER_ADDR'];

}

/////////// SQL connexion
$mysqli=msSQL::sqlConnect();

/////////// Validators loader
require '../fonctions/validators.php';

/////////// Router
$router = new AltoRouter();
$routes=Spyc::YAMLLoad('../config/routes.yml');
$router->addRoutes($routes);
$match = $router->match();


///////// user
if (isset($_COOKIE['userId'])) {
    $p['user']=msUser::userIdentification();
    if (isset($p['user']['id'])) {
        msUser::applySpecificConfig($p['config'], $p['user']['id']);
    }
} else {
    $p['user']=null;
    $p['user']['id']=null;
    if ($match['target']!='login/logIn' and $match['target']!='login/logInDo') {
        msTools::redirRoute('userLogIn');
    }
}


///////// Controler else -> 404
if ($match and is_file('../controlers/'.$match['target'].'.php')) {
    include '../controlers/'.$match['target'].'.php';

    // complément lié au module installé
    if (is_file('../controlers/module/'.$match['target'].'.php')) {
        include '../controlers/module/'.$match['target'].'.php';
    }
} else {
    //$template='problem';
}


//////// View if defined
if (isset($template)) {
    if (isset($_SESSION)) {
        $p['session']=$_SESSION;
    }

    if (isset($p['user']['id'])) {
      //inbox number of messages
      $p['page']['inbox']['numberOfMsg']=msSQL::sqlUniqueChamp("select count(txtFileName) from inbox where archived='n' and mailForUserID = '".$p['config']['apicryptInboxMailForUserID']."' ");

      // patients of the day
      $p['page']['patientsOfTheDay']=msExternalData::jsonFileToPhpArray($p['config']['workingDirectory'].$p['config']['agendaLocalPatientsOfTheDay']);
    }

    // crédits SMS
    if(is_file($p['config']['workingDirectory'].$p['config']['smsCreditsFile'])) {
      $p['page']['creditsSMS']=file_get_contents($p['config']['workingDirectory'].$p['config']['smsCreditsFile']);
    }

    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");


    //les répertoires de templates twig
    $twigTemplateDirs=msTools::getAllSubDirectories($p['config']['homeDirectory'].'templates/'.$p['config']['templateBaseFolder'],'/');

    // les variables d'environnement twig
    if(isset($p['config']['twigEnvironnementCache'])) $twigEnvironment['cache']=$p['config']['twigEnvironnementCache']; else $twigEnvironment['cache']=false;
    if(isset($p['config']['twigEnvironnementAutoescape'])) $twigEnvironment['autoescape']=$p['config']['twigEnvironnementAutoescape']; else $twigEnvironment['autoescape']=false;

    $loader = new Twig_Loader_Filesystem($twigTemplateDirs);
    $twig = new Twig_Environment($loader, $twigEnvironment);
    $twig->getExtension('Twig_Extension_Core')->setDateFormat('d/m/Y', '%d days');
    $twig->getExtension('Twig_Extension_Core')->setTimezone('Europe/Paris');


    // display
    echo $twig->render($template.'.html.twig', $p);
}

//////// Debug
if (!isset($debug)) {
    $debug=null;
}

//and $p['user']['id']=='1'

if ($debug=='y' and $p['user']['id']=='1') {
    echo '<pre style="margin-top : 50px;">';
    //echo '$p[\'config\'] :';
    //print_r($p['config']);
    echo '$p[\'page\'] :';
    print_r($p['page']);
    echo '$p[\'user\'] :';
    print_r($p['user']);
    echo '$MATCH :';
    print_r($match);
    echo '$_COOKIE :';
    print_r($_COOKIE);
    echo '$_SESSION :';
    print_r($_SESSION);
}
