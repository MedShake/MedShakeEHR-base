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
 * Config : gérer les actes NGAP / CCAM qui permettent de construire les factures
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 //admin uniquement
 if (!msUser::checkUserIsAdmin()) {
     $template="forbidden";
 } else {
     $template="configActesBase";
     $debug='';

     //actes NGAP CCAM
     $p['page']['actesBase']=msSQL::sql2tabKey("select * from actes_base order by type='NGAP' desc, code", "code");

     // nombre d'utilisation de Chaque
     $tab=[];
     if ($details=msSQL::sql2tabSimple("select details from actes")) {


         foreach ($details as $det) {
             $det=Spyc::YAMLLoad($det);
             $det=array_keys($det);

             foreach ($det as $code) {
                 if (isset($tab[$code])) {
                     $tab[$code]=$tab[$code]+1;
                 } else {
                     $tab[$code]=1;
                 }
             }
         }


         foreach($tab as $code=>$nb) {
           $p['page']['actesBase'][$code]['nbUtilisation']=$nb;
         }
     }
 }
