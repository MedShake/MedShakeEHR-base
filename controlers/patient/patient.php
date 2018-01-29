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
 * Patient : la page du dossier patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */


$debug='';
$template="patient";

//le patient
$patient = new msPeople();
$patient->setToID($match['params']['patient']);
$p['page']['patient']['id']=$match['params']['patient'];

//si patient externe, on cherche une relation avec un patient, et si on trouve, on permute
if ($externe=$patient->isExterne() and 
    ($internePatient=msSQL::sqlUniqueChamp("SELECT od.value FROM data_types AS dt LEFT JOIN objets_data AS od
        ON dt.name='relationExternePatient' AND od.typeID=dt.id AND od.outdated='' AND od.deleted=''
        WHERE od.toID='".$p['page']['patient']['id']."'"))) {
     $patient->setToID($internePatient);
     $p['page']['patient']['id']=$internePatient;
    }

$p['page']['patient']['administrativeDatas']=$patient->getAdministrativesDatas();
$p['page']['patient']['administrativeDatas'][8]['age']=$patient->getAge();

//cas où le patient est externe et sans relation connue
if ($externe and !$internePatient) {
    // on cherche à identifier le patient interne par le téléphone et l'email
    $data=$p['page']['patient']['administrativeDatas'];
    $keys=['mobilePhone', 'homePhone', 'telPro', 'personalEmail', 'profesionnalEmail'];
    foreach($keys as $v) {
        if(!array_key_exists($v, $data)) {
          $data[$v]='**********';
        }
    }
    $name2typeID = new msData();
    $name2typeID = $name2typeID->getTypeIDsFromName($keys);

    $candidats=array();
    $candidats['phone']=msSQL::sql2tabSimple("SELECT od.toID FROM objets_data AS od left join people AS p 
               ON od.toID=p.id AND p.type!='externe' AND od.outdated='' AND od.deleted=''
               WHERE (od.typeID IN ('".$name2typeID['mobilePhone']."', '".$name2typeID['homePhone']."', '".$name2typeID['telPro']."') AND od.value LIKE '".$data['mobilePhone']."')
               OR (od.typeID IN ('".$name2typeID['mobilePhone']."', '".$name2typeID['homePhone']."', '".$name2typeID['telPro']."') AND od.value LIKE '".$data['homePhone']."')");

    $candidats['email']=msSQL::sql2tabSimple("SELECT od.toID FROM objets_data AS od left join people AS p 
               ON od.toID=p.id AND p.type!='externe' AND od.outdated='' AND od.deleted=''
               WHERE typeID IN('".$name2typeID['personalEmail']."', '".$name2typeID['profesionnalEmail']."') and value = '".$data['personalEmail']."'");

    // si on a pu identifier le patient de façon unique, on associe directement et on charge les données du patient interne
    if ((($candidats['phone'] and ($c1=count($candidats['phone']))==1) or ($candidats['email'] and ($c2=count($candidats['email']))==1)) and
        (!isset($c1) or !isset($c2) or $candidats['phone'][0]==$candidats['email'][0])) {
        $internePatient=isset($c1)?$candidats['phone'][0]:$candidats['email'][0];
        $obj=new msObjet();
        $obj->setToID($p['page']['patient']['id']);
        $obj->setFromID($p['user']['id']);
        $obj->createNewObjetByTypeName('relationExternePatient', $internePatient);
        $patient->setToID($internePatient);
        $p['page']['patient']['administrativeDatas']=$patient->getAdministrativesDatas();
        $p['page']['patient']['administrativeDatas'][8]['age']=$patient->getAge();
    } else {
        //sinon, on affiche la page de recherche patient
        $p['page']['patient']['administrativeDatas']=$patient->getSimpleAdminDatasByName();
        $p['page']['porp']="externe";
        include '../controlers/rechercher/patients.php';
        return;
    }
}

// le formulaire d'édition de ses données admin
$formpatient = new msForm();
$formpatient->setFormIDbyName('baseNewPatient');
$formpatient->setPrevalues($patient->getSimpleAdminDatas());
$p['page']['formEditAdmin']=$formpatient->getForm();

//type du dossier
$p['page']['patient']['dossierType']=msSQL::sqlUniqueChamp("select type from people where id='".$match['params']['patient']."' limit 1");

//historique du jour des consultation du patient
$p['page']['patient']['today']=$patient->getToday();

//historique complet des consultation du patient
$p['page']['patient']['historique']=$patient->getHistorique();

//les ALD du patient
$p['page']['patient']['ALD']=$patient->getALD();

//les certificats
$certificats=new msData();
$certificats->setModules(['base', $p['user']['module']]);

if($p['page']['modelesCertif']=$certificats->getDataTypesFromCatName('catModelesCertificats', ['id','label', 'validationRules as onlyfor', 'validationErrorMsg as notfor' ])) {
  foreach($p['page']['modelesCertif'] as $k=>$v) {
    if(isset($v['onlyfor'])) {
      $p['page']['modelesCertif'][$k]['onlyfor']=explode(',', $v['onlyfor']);
      if(is_array($p['page']['modelesCertif'][$k]['notfor'])) {
        if(count(array_filter($p['page']['modelesCertif'][$k]['onlyfor']))>0) {
          if(!in_array($p['user']['id'], $p['page']['modelesCertif'][$k]['onlyfor'])) {
            unset($p['page']['modelesCertif'][$k]);
          }
        }
      }
    }
    if(isset($v['notfor'])) {
      $p['page']['modelesCertif'][$k]['notfor']=explode(',', $v['notfor']);
      if(is_array($p['page']['modelesCertif'][$k]['notfor'])) {
        if(in_array($p['user']['id'], $p['page']['modelesCertif'][$k]['notfor'])) {
          unset($p['page']['modelesCertif'][$k]);
        }
      }
    }
  }
}
//les courriers
if($p['page']['modelesCourrier']=$certificats->getDataTypesFromCatName('catModelesCourriers', ['id','label', 'validationRules as onlyfor', 'validationErrorMsg as notfor'])) {
  foreach($p['page']['modelesCourrier'] as $k=>$v) {
    if(isset($v['onlyfor'])) {
      $p['page']['modelesCourrier'][$k]['onlyfor']=explode(',', $v['onlyfor']);
      if(is_array($p['page']['modelesCourrier'][$k]['notfor'])) {
        if(count(array_filter($p['page']['modelesCourrier'][$k]['onlyfor']))>0) {
          if(!in_array($p['user']['id'], $p['page']['modelesCourrier'][$k]['onlyfor'])) {
            unset($p['page']['modelesCourrier'][$k]);
          }
        }
      }
    }
    if(isset($v['notfor'])) {
      $p['page']['modelesCourrier'][$k]['notfor']=explode(',', $v['notfor']);
      if(is_array($p['page']['modelesCourrier'][$k]['notfor'])) {
        if(in_array($p['user']['id'], $p['page']['modelesCourrier'][$k]['notfor'])) {
          unset($p['page']['modelesCourrier'][$k]);
        }
      }
    }
  }
}

//les correspondants et liens familiaux
$p['page']['correspondants']=$patient->getRelationsWithPros();
$p['page']['liensFamiliaux']=$patient->getRelationsWithOtherPatients();
