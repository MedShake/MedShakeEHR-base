<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Config : outils pour la paramétrage CDA lié à un form
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


function treatDataType(&$t) {
  global $formc;
  if($t['formType'] == 'select') {
    $t['formValues']=Spyc::YAMLLoad($t['formValues']);
    $t['formValues']=array_filter($t['formValues']);
    $t['keyValues']=array_keys($t['formValues']);
  } elseif($t['formType'] == 'number') {
    $padat=$formc->getDataTypeFormParams($t['name']);
    if(isset($padat['min'],$padat['max'],$padat['step'])) {
      for($i=(float)$padat['min'];$i<=(float)$padat['max'];$i=$i+(float)$padat['step']) {
        $tab[$i]=$i;
      }
      $t['formValues']=(array)$tab;
    }
    $t['formValues']=array_filter($t['formValues']);
    $t['keyValues']=array_keys($t['formValues']);
  }
}

function concat(array $array, $clef) {
   $current = array_shift($array);
   if(count($array) > 0) {
       $results = array();
       $temp = concat($array, $clef);
       foreach($current[$clef] as $word) {
         foreach($temp as $value) {
           $results[] =  $word . '|' . $value;
         }
       }
       return $results;
   }
   else {
      return $current[$clef];
   }
}


//admin uniquement
if (!msUser::checkUserIsAdmin()) {
  $template="forbidden";
} else {
  $debug='';
  $template="configFormCdaTools";
  $p['page']['formID']=$match['params']['form'];
  if (!is_numeric($p['page']['formID'])) {
     die();
  }

  $formc = new msForm;
  $formc->setFormID($p['page']['formID']);
  $formCda=$formc->getFormRawData(['cda'])['cda'];
  $formCda=Spyc::YAMLLoad($formCda);

  if(isset($formCda['actesPossibles'],$formCda['clinicalDocument']['documentationOf']['serviceEvent']['paramConditionServiceEvent'])) {

    // associations déjà en place
    $p['page']['deja']=$formCda['clinicalDocument']['documentationOf']['serviceEvent']['code'];

    // actes possibles définis
    $p['page']['actesPossibles']=$formCda['actesPossibles'];

    // paramètres concernés
    $p['page']['paramsPossibles']=(array)$formCda['clinicalDocument']['documentationOf']['serviceEvent']['paramConditionServiceEvent'];

    // sortie data de chaque param
    $data = new msData;
    foreach($p['page']['paramsPossibles'] as $k=>$pa) {
      if(is_string($pa)) {
        $p['page']['paramsPossiblesData'][$k]=$data->getDataTypeByName($pa);
        treatDataType($p['page']['paramsPossiblesData'][$k]);
      } elseif (is_array($pa)) {
        foreach($pa as $sk=>$spa) {
          $p['page']['paramsPossiblesData'][$k][$sk]=$data->getDataTypeByName($spa);
          treatDataType($p['page']['paramsPossiblesData'][$k][$sk]);
        }
      }
    }

    // on regroupe
    foreach($p['page']['paramsPossiblesData'] as $k=>$v) {
        if(isset($v['label'])) {
          foreach($v['keyValues'] as $kv) {
            $tab[$k]['asso'][$v['name'].'@'.$kv]=$v['formValues'][$kv];
          }
        } else {
          foreach($v as $k2=>$v2)
            foreach($v2['keyValues'] as $kv2) {
              $tab[$k]['asso'][$v2['name'].'@'.$kv2]=$v2['formValues'][$kv2];
          }
        }
        $tab[$k]['clefs']=array_keys($tab[$k]['asso']);
        $tab[$k]['values']=array_values($tab[$k]['asso']);
    }

    $tabclefs=concat($tab, 'clefs');
    $tablabel=concat($tab, 'values');
    foreach($tablabel as $k=>$v) {
      $tabr[$k]['clef']=$tabclefs[$k];
      $tabr[$k]['values']=explode('|', $v);
    }
    sort($tabr);
    $p['page']['dataTab']=$tabr;
  }

  // jeux de valeurs
  $p['page']['jdvClinicalDocumentCode']=msExternalData::getJdvDataFromXml('JDV_J07-XdsTypeCode_CI-SIS.xml');

}
