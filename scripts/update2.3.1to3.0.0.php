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

 /////////// SQL connexion
 $mysqli=msSQL::sqlConnect();



 // Changements
 // lastname => nom de naissance
 // birthname => usageName nom d'usage

 if($people=msSQL::sql2tabSimple("select id from people")) {
   foreach($people as $id) {
     $patient=new msPeople();
     $patient->setToID($id);
     $patient=$patient->getSimpleAdminDatas();
     if(isset($patient['1']) and isset($patient['2'])) {
       msSQL::sqlQuery("update objets_data set typeID='10000' where toID='".$id."' and typeID='1'");
       msSQL::sqlQuery("update objets_data set typeID='1' where toID='".$id."' and typeID='2'");
       msSQL::sqlQuery("update objets_data set typeID='2' where toID='".$id."' and typeID='10000'");

     }
   }


 }
