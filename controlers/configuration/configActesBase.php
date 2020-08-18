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

  //types actes
  $p['page']['typesActes']=msSQL::sql2tabSimple("select distinct `type` from `actes_base` order by `type`='NGAP' desc, `type`='CCAM' desc, `type`='Libre' asc");

  //actes NGAP CCAM
  if ($actesBase=msSQL::sql2tab("select `id`, `code`, `phase`, `activite`, `codeProf`, `label`, `type` from `actes_base` order by `type`='NGAP' desc, `code`")) {
    foreach ($actesBase as $k=>$v) {
      if($v['type'] == 'NGAP') {
        $p['page']['actesBase'][$v['type']][$v['codeProf']][$v['code']]=$v;
      } else {
        $p['page']['actesBase'][$v['type']][$v['activite'].'-'.$v['phase']][$v['code']]=$v;
      }
    }
  }

  //Correspondances code NGAP
  $p['page']['codeProf']=msReglementActe::getCodeProfLabel();

  //nombre d'utilisation de chaque
  $tab=[];
  if ($details=msSQL::sql2tabSimple("select `details` from `actes`")) {
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

    foreach ($tab as $code=>$nb) {
      foreach($p['page']['typesActes'] as $typeActe) {
        if(isset($p['page']['actesBase'][$typeActe][$code])) $p['page']['actesBase'][$typeActe][$code]['nbUtilisation']=$nb;
      }
    }
  }
}
