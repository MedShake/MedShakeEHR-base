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
 * Patient > ajax : obtenir le formulaire d'envoi de mail
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';
//template
$template="mailForm";

//recupére les administrative data
$to=new msPeopleRelations();
  $to->setToID($_POST['patientID']);
  $toAdminData=$to->getSimpleAdminDatasByName();

//définir les valeurs par défaut
if ($_POST['mailType']=='ns') {
    $preValues['mailFrom']=$p['config']['smtpFrom'];
    $preValues['mailTo']= array_key_exists('personalEmail', $toAdminData) ? $toAdminData['personalEmail'] : '';
    $preValues['mailBody']="";
    $preValues['mailSujet']=$p['config']['smtpDefautSujet'];
    $catModelesMails='catModelesMailsToPatient';
    //les correspondants
    $to->setRelationType('relationPatientPraticien');
    $p['page']['correspondants']=$to->getRelations(['identite']);
} elseif ($_POST['mailType']=='apicrypt') {
    $preValues['mailFrom']=$p['config']['apicryptAdresse'];
    $preValues['mailBody']="";
    $preValues['mailSujet']=$p['config']['apicryptDefautSujet'];
    $catModelesMails='catModelesMailsToApicrypt';
    //les correspondants et liens familiaux
    $to->setRelationType('relationPatientPraticien');
    $p['page']['correspondants']=$to->getRelations(['identite', 'emailApicrypt'], ['emailApicrypt']);
} elseif ($_POST['mailType']=='ecofax') {
    $catModelesMails=false;
    $to->setRelationType('relationPatientPraticien');
    $p['page']['correspondants']=$to->getRelations(['identite', 'faxPro'], ['faxPro']);
} else {
    $catModelesMails=false;
}

$submitLabel='Envoyer';
if ($_POST['mailType']=='ecofax') {
  $submitLabel='Faxer';
}

//modèles
$lmc=new msData();
if($catModelesMails and $lm=$lmc->getDataTypesFromCatName($catModelesMails, ['id','label', 'validationRules as onlyfor', 'validationErrorMsg as notfor' ])) {
  $lmc->applyRulesOnlyforNotforOnArray($lm, $p['user']['id']);
  $typeID = msData::getTypeIDFromName('mailModeles');
  $preValues[$typeID][0]='';
  if(!empty($lm)) {
    foreach($lm as $v) {
      $preValues[$typeID][$v['id']]=$v['label'];
    }
  }
}

//sur le doc à joindre
if (isset($_POST['objetID'])) {
    $doc = new msObjet();
    $doc->setObjetID($_POST['objetID']);
    $p['page']['doc']=$doc->getCompleteObjetDataByID();

    //make URL
    $doc = new msStockage;
    $doc->setObjetID($_POST['objetID']);

    if ($doc->testDocExist()) {
        $p['page']['doc']['url']=$doc->getWebPathToDoc();
        $p['page']['doc']['filesize']=$doc->getFileSize(0);
        $p['page']['doc']['mimetype']=msTools::getmimetype($doc->getPathToDoc());
    } else {
        $pdf= new msPDF();
        $pdf->setObjetID($_POST['objetID']);
        $pdf->makePDFfromObjetID();
        $pdf->savePDF();

        if ($doc->testDocExist()) {
            $p['page']['doc']['url']=$doc->getWebPathToDoc();
            $p['page']['doc']['filesize']=$doc->getFileSize(0);
            $p['page']['doc']['mimetype']=msTools::getmimetype($doc->getPathToDoc());
        }
    }

    if(isset($p['page']['doc']['mimetype'])) {
      $p['page']['doc']['mimetypeParts'] = explode('/', $p['page']['doc']['mimetype']);
    }
}

//formulaire
$form = new msForm();
$form->setFormIDbyName($p['page']['formIN']=$_POST['formIN']);
if(isset($preValues)) $form->setPrevalues($preValues);
$form->setTypeForNameInForm('byName');
$p['page']['form']=$form->getForm();
$p['page']['formJavascript']=$form->getFormJavascript();
$form->addSubmitToForm($p['page']['form'], 'btn-warning btn-lg btn-block', $submitLabel);

$p['page']['form']['addHidden']=array(
  'patientID'=>$_POST['patientID'],
  'mailType'=>$_POST['mailType']
);
if (isset($_POST['objetID'])) {
    $p['page']['form']['addHidden']['objetID']=$_POST['objetID'];
}
