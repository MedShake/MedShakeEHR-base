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

 ini_set('display_errors', 1);
 setlocale(LC_ALL, "fr_FR.UTF-8");
 session_start();


 /////////// Composer class auto-upload
 require '../vendor/autoload.php';

 /////////// Class medshakeEHR auto-upload
 spl_autoload_register(function ($class) {
     if (is_file('../class/' . $class . '.php')) {
         include '../class/' . $class . '.php';
     }
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
 require $p['config']['homeDirectory'].'fonctions/validators.php';

 /////////// Router
 $router = new AltoRouter();
 $routes=Spyc::YAMLLoad('../config/routes.yml');
 $router->addRoutes($routes);
 $router->setBasePath($p['config']['urlHostSuffixe']);
 $match = $router->match();

 ///////// user
 if (isset($_COOKIE['userIdPc'])) {
     $p['user']=msUser::userIdentificationPhonecapture();
     if (isset($p['user']['id'])) {
         msUser::applySpecificConfig($p['config'], $p['user']['id']);
     }
 } else {
     $p['user']=null;
     $p['user']['id']=null;
 }

 ///////// Controler else -> 404
 if ($match and is_file($p['config']['homeDirectory'].'controlers/'.$match['target'].'.php')) {
     include $p['config']['homeDirectory'].'controlers/'.$match['target'].'.php';

     // complément lié au module installé
     if (is_file($p['config']['homeDirectory'].'controlers/module/'.$match['target'].'.php')) {
         include $p['config']['homeDirectory'].'controlers/module/'.$match['target'].'.php';
     }
 } else {
     //$template='problem';
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

 //////// Debug
 if (!isset($debug)) {
     $debug=null;
 }

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
