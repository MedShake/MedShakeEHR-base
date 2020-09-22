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

// vérifier si correspond à un patient existant
if(!in_array($patient->getType(), ['patient', 'pro', 'externe'])) {
  $template = "404";
  return;
}

// vérifier type exacte people
if($p['config']['PraticienPeutEtrePatient'] != 'true' and !in_array($patient->getType(), ['patient', 'externe'])) {
  $template = "404";
  return;
}

//vérifier les droits
$droits = new msPeopleDroits($p['user']['id']);
if(!$droits->checkUserCanSeePatientData($match['params']['patient'])) {
  $template="forbidden";
  return;
}

//si patient externe, on cherche une relation avec un patient, et si on trouve, on permute
if ($externe=$patient->isExterne() and ($internePatient=msSQL::sqlUniqueChamp("SELECT od.value FROM data_types AS dt LEFT JOIN objets_data AS od
  ON dt.name='relationExternePatient' AND od.typeID=dt.id AND od.outdated='' AND od.deleted=''
  WHERE od.toID='".$p['page']['patient']['id']."'"))) {
    msTools::redirection('/patient/'.$internePatient.'/');
}

$p['page']['patient']['administrativeDatas']=$patient->getAdministrativesDatas();
$p['page']['patient']['administrativeDatas']['birthdate']['ageFormats']=$patient->getAgeFormats();
$p['page']['patient']['administrativeDatas']['birthdate']['age']=$patient->getAge();
if(isset($p['page']['patient']['administrativeDatas']['deathdate'])) {
  if(msTools::validateDate($p['page']['patient']['administrativeDatas']['deathdate']['value'], "d/m/Y")) {
    $p['page']['patient']['administrativeDatas']['deathAge']=$patient->getDeathAge();
  }
}

//cas où le patient est externe et sans relation connue
if ($externe and !$internePatient) {
    // on cherche à identifier le patient interne par le téléphone et l'email
    $data=$p['page']['patient']['administrativeDatas'];
    $keys=['mobilePhone', 'homePhone', 'telPro', 'personalEmail', 'profesionnalEmail'];
    foreach($keys as $v) {
        if(!array_key_exists($v, $data)) {
          $data[$v]['value']='**********';
        }
    }
    $name2typeID = new msData();
    $name2typeID = $name2typeID->getTypeIDsFromName($keys);

    $candidats=array();
    $candidats['phone']=msSQL::sql2tabSimple("SELECT od.toID FROM objets_data AS od left join people AS p
               ON od.toID=p.id AND p.type!='externe' AND od.outdated='' AND od.deleted=''
               WHERE (od.typeID IN ('".$name2typeID['mobilePhone']."', '".$name2typeID['homePhone']."', '".$name2typeID['telPro']."') AND od.value LIKE '".$data['mobilePhone']['value']."')
               OR (od.typeID IN ('".$name2typeID['mobilePhone']."', '".$name2typeID['homePhone']."', '".$name2typeID['telPro']."') AND od.value LIKE '".$data['homePhone']['value']."')");

    $candidats['email']=msSQL::sql2tabSimple("SELECT od.toID FROM objets_data AS od left join people AS p
               ON od.toID=p.id AND p.type!='externe' AND od.outdated='' AND od.deleted=''
               WHERE typeID IN('".$name2typeID['personalEmail']."', '".$name2typeID['profesionnalEmail']."') and value = '".$data['personalEmail']['value']."'");

    // si on a pu identifier le patient de façon unique, on associe directement et on charge les données du patient interne
    if ((($candidats['phone'] and ($c1=count($candidats['phone']))==1) or ($candidats['email'] and ($c2=count($candidats['email']))==1)) and
        (!isset($c1) or !isset($c2) or $candidats['phone'][0]==$candidats['email'][0])) {
        $internePatient=isset($c1)?$candidats['phone'][0]:$candidats['email'][0];
        $obj=new msObjet();
        $obj->setToID($p['page']['patient']['id']);
        $obj->setFromID($p['user']['id']);
        $obj->createNewObjetByTypeName('relationExternePatient', $internePatient);
        msTools::redirection('/patient/'.$internePatient.'/');
    } else {
        //sinon, on affiche la page de recherche patient
        $p['page']['patient']['administrativeDatas']=$patient->getSimpleAdminDatasByName();
        $p['page']['porp']="externe";
        include $p['homepath'].'controlers/rechercher/patients.php';
        $match['target']='';
        return;
    }
}

// le formulaire d'édition de ses données admin
$formpatient = new msForm();
$formpatient->setFormIDbyName($p['config']['formFormulaireNouveauPatient']);
$formpatient->setPrevalues($patient->getSimpleAdminDatas());
$p['page']['formEditAdmin']=$formpatient->getForm();
$p['page']['formJavascript'][$p['config']['formFormulaireNouveauPatient']]=$formpatient->getFormJavascript();
$p['page']['formEditAdmin']['addHidden']=array(
  'patientID'=>$p['page']['patient']['id'],
  'actAsAjax'=>'true',
  'porp'=>'patient'
);

//type du dossier
$p['page']['patient']['dossierType']=$patient->getType();

if($p['config']['optionDossierPatientInhiberHistoriquesParDefaut'] != 'true') {
  //historique du jour des consultation du patient
  $p['page']['patient']['today']=$patient->getToday();

  //historique complet des consultation du patient
  $p['page']['patient']['historique']=$patient->getHistorique();
}

//les ALD du patient
if($p['config']['optionGeActiverSignatureNumerique'] == 'true') {
  $p['page']['patient']['ALD']=$patient->getALD();
}

if($p['config']['optionDossierPatientActiverCourriersCertificats'] == 'true') {
  //les certificats
  $certificats=new msData();
  $certificats->setModules(['base', $p['user']['module']]);

  if($p['page']['modelesCertif']=$certificats->getDataTypesFromCatName('catModelesCertificats', ['id','name','label', 'validationRules as onlyfor', 'validationErrorMsg as notfor' ])) {
    $certificats->applyRulesOnlyforNotforOnArray($p['page']['modelesCertif'], $p['user']['id']);
  }
  //les courriers
  if($p['page']['modelesCourrier']=$certificats->getDataTypesFromCatName('catModelesCourriers', ['id','name','label', 'validationRules as onlyfor', 'validationErrorMsg as notfor'])) {
    $certificats->applyRulesOnlyforNotforOnArray($p['page']['modelesCourrier'], $p['user']['id']);
  }
}

// liste des documents pouvant être envoyés à la signature par l'utilisateur courant
if($p['config']['optionGeActiverSignatureNumerique'] == 'true') {
  $docAsSigner = new msSignatureNumerique;
  $docAsSigner->setFromID($p['user']['id']);
  $p['page']['modelesDocASigner']=$docAsSigner->getPossibleDocToSign();
}

//les correspondants
$correspondants = new msPeopleRelations;
$correspondants->setToID($match['params']['patient']);
$correspondants->setReturnedPeopleTypes(['pro']);
$correspondants->setRelationType('relationPatientPraticien');
$p['page']['correspondants']=$correspondants->getRelations(['identite','titre','emailApicrypt', 'faxPro', 'profesionnalEmail', 'telPro', 'telPro2', 'mobilePhonePro']);


// Transmissions
if($p['config']['optionGeActiverTransmissions'] == 'true') {
  if($p['config']['transmissionsPeutCreer'] == 'true') {
    $trans = new msTransmissions();
    $trans->setUserID($p['user']['id']);
    $p['page']['transmissionsListeDestinatairesPossibles']=$trans->getTransmissionDestinatairesPossibles();
    $p['page']['transmissionsListeDestinatairesDefaut']=explode(',', $p['config']['transmissionsDefautDestinataires']);
  }
}

// Formulaires de règlement
if($p['config']['optionGeActiverCompta'] == 'true') {
  $data=new msData;
  $p['page']['formReglement']=$data->getDataTypesFromNameList(explode(',',$p['config']['administratifReglementFormulaires']), array('id', 'module', 'label', 'description', 'formValues'));
}
