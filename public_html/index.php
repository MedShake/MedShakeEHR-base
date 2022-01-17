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
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

ini_set('display_errors', 0);
setlocale(LC_ALL, "fr_FR.UTF-8");

if (($homepath=getenv("MEDSHAKEEHRPATH"))===false) {
    if (!is_file("MEDSHAKEEHRPATH") or ($homepath=file_get_contents("MEDSHAKEEHRPATH"))===false) {
        die("La variable d'environnement MEDSHAKEEHRPATH n'a pas été fixée.<br>Veuillez insérer <code>SetEnv MEDSHAKEEHRPATH /chemin/vers/MedShakeEHR</code> dans votre .htaccess ou la configuration du serveur.<br>Alternativement, vous pouvez créer un fichier 'MEDSHAKEEHRPATH' contenant <code>/chemin/vers/MedShakeEHR</code> et le placer dans le dossier web de MedShakeEHR");
    }
    $homepath=trim(str_replace("\n", '', $homepath));
}
$homepath.=$homepath[strlen($homepath)-1]=='/'?'':'/';

session_start();

/////////// Composer class auto-upload
require $homepath.'vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
    global $homepath;
    if (is_file($homepath.'class/' . $class . '.php')) {
        include $homepath.'class/' . $class . '.php';
    }
});

/////////// Compatibilité versions antérieures PHP
require $homepath.'fonctions/compatibilite.php';

/////////// Vérification de l'état d'installation
if (!is_file($homepath.'config/config.yml')) {
    msTools::redirection('/install.php');
}
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

/////////// État système
$p['config']['systemState']=msSystem::getSystemState();

/////////// Sortie des versions des modules
if (empty($p['modules']=msModules::getInstalledModulesVersions())) {
    msTools::redirection('/install.php');
}
/////////// Validators loader
define("PASSWORDLENGTH", msConfiguration::getDefaultParameterValue('optionGeLoginPassMinLongueur'));
require $homepath.'fonctions/validators.php';


///////// user
$p['user']=null;
$p['user']['id']=null;
$p['user']['module']='base';

if (msSystem::getProUserCount() == 0) {

    $match = msSystem::getRoutes(['login']);

    if ($match['target']!='login/logInFirst' and $match['target']!='login/logInFirstDo') {
        msTools::redirRoute('userLogInFirst');
    }
} elseif (isset($_COOKIE['userName'])) {
    $iUser = new msUser;
    $p['user']=$iUser->userIdentification();

    if (isset($p['user']['id'])) {
        $p['config']=array_merge($p['config'], msConfiguration::getAllParametersForUser($p['user']));
    }

    $match = msSystem::getRoutes();

    if ($p['user']['rank']!='admin' and $p['config']['systemState']=='maintenance') {
        msTools::redirection('/maintenance.html');
    }

    if ($p['config']['optionGeLogin2FA'] == 'true' and !$iUser->check2faValidKey() and $match['target']!='login/logIn' and $match['target']!='login/logInDo' and $match['target']!='rest/rest' and $match['target']!='login/logInSet2fa') {
      $iUser->doLogout();
      msTools::redirRoute('userLogIn');
    }
} else {
    if(msConfiguration::getDefaultParameterValue('optionGeActiverApiRest') == 'true') {
      $match = msSystem::getRoutes(['login', 'apiRest']);
    } else {
      $match = msSystem::getRoutes(['login']);
    }

    if ($match['target']!='login/logIn' and $match['target']!='login/logInDo' and $match['target']!='rest/rest') {
        msTools::redirection('/login/');
    }
    // compléter la config par défaut
    $p['config'] = array_merge($p['config'], msConfiguration::getAllParametersForUser());

}

// simplification pour étiquetage des dossiers patients test
if(!empty($p['config']['statsExclusionPatients'])) {
  $p['config']['statsExclusionPatientsArray']=explode(',', $p['config']['statsExclusionPatients']);
} else {
  $p['config']['statsExclusionPatientsArray']=[];
}

///////// Controler
if ($match and is_file($homepath.'controlers/'.$match['target'].'.php')) {

    include $homepath.'controlers/'.$match['target'].'.php';

    // complément lié au module installé
    if (is_file($homepath.'controlers/module/'.$p['user']['module'].'/'.$match['target'].'.php')) {
        include $homepath.'controlers/module/'.$p['user']['module'].'/'.$match['target'].'.php';
    }
    // si c'est l'interface RESTful qui était visée et qu'on est ici, c'est que l'instruction n'est pas supportée
    if ($match['target']=='rest/rest') {
        http_response_code(404);
        die;
    }
} elseif ($match and (is_file($homepath.'controlers/module/'.$p['user']['module'].'/'.$match['target'].'.php') ||
        is_file($homepath.'controlers/module/'.$match['target'].'.php'))) {

    // Gestion des controlers additionnels des modules (controlers non existant dans MedShakeEHR-base)
    if (isset($p['user']['id'])) {
        include $homepath.'controlers/module/'.$p['user']['module'].'/'.$match['target'].'.php';
    } else {
        include $homepath.'controlers/module/'.$match['target'].'.php';
    }

} elseif(isset($p['user']['id'])) {
    $template ='404';
}

//////// View if defined
if (isset($template)) {

    if (isset($_SESSION)) {
        $p['session']=$_SESSION;
    }

    if (isset($p['user']['id'])) {
      //inbox number of messages
      if($p['config']['optionGeActiverInboxApicrypt'] == 'true' and $p['config']['designTopMenuInboxCountDisplay'] == 'true') {
        $p['page']['inbox']['numberOfMsg']=msInbox::getInboxUnreadMessages();
      }

      // dropbox
      if($p['config']['optionGeActiverDropbox'] == 'true') {
        if(!isset($dropbox) or !is_a($dropbox, 'msDropbox')) $dropbox = new msDropbox;
        $p['page']['dropboxNbFiles'] = $dropbox->getTotalFilesInBoxes();
      }

      //transmissions non lues
      if($p['config']['optionGeActiverTransmissions'] == 'true' and $p['config']['transmissionsPeutVoir'] == 'true') {
        $transCompter=new msTransmissions;
        $transCompter->setUserID($p['user']['id']);
        $p['page']['transmissionsNbNonLues']=$transCompter->getNbTransmissionsNonLuesParPrio();
      }

      // patients of the day
      if($p['config']['optionGeActiverAgenda'] == 'true') {
        $events = new msAgenda();
        if(!isset($p['page'])) $p['page']=[];
        $p['page']=array_merge($p['page'], $events->getDataForPotdMenu());
      }

      // crédits SMS
      if (is_file($p['config']['workingDirectory'].$p['config']['smsCreditsFile'])) {
          $p['page']['creditsSMS']=(int)file_get_contents($p['config']['workingDirectory'].$p['config']['smsCreditsFile']);
      }

      //utilisateurs pouvant avoir un agenda
      if($p['config']['optionGeActiverAgenda'] == 'true') {
        $agendaUsers= new msPeople();
        $p['page']['agendaUsers']=$agendaUsers->getUsersListForService('administratifPeutAvoirAgenda');
      }

      // barre de navigation sup.
      $p['page']['topNavBarSections'] = yaml_parse($p['config']['designTopMenuSections']);
    }


    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    if($template=='404') {
      http_response_code(404);
    }
    if($template=='forbidden') {
      http_response_code(403);
    }

    //générer et sortir le html
    $getHtml = new msGetHtml();
    $getHtml->set_template($template);
    if (isset($forceAllTemplates)) {
        $getHtml->set_templatesDirectories(msTools::getAllSubDirectories($p['config']['templatesFolder'], '/'));
    }
    echo $getHtml->genererHtml();
}

//////// Debug
if (!isset($debug)) {
    $debug=null;
}

if ($debug=='y' and $p['user']['id']=='3') {
    echo '<pre style="margin-top : 50px;">';
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
