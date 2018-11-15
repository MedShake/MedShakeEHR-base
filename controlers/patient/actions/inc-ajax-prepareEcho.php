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
 * Patient > ajax : générer le fichier DICOM worklist pour Orthanc
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

if(!is_numeric($_POST['patientID'])) die;

$template="dicomWL";

$prat = new msPeople();
$prat->setToID($p['user']['id']);
$p['page']['prat']=$prat->getSimpleAdminDatasByName();
$p['page']['prat']['pratID']=$p['user']['id'];

$patient = new msPeople();
$patient->setToID($_POST['patientID']);
$p['page']['patient']=$patient->getSimpleAdminDatasByName();
$p['page']['patient']['id']=$_POST['patientID'];
$p['page']['patient']['dicomPatientID']=$p['config']['dicomPrefixIdPatient'].$_POST['patientID'];
if(isset($p['page']['patient']['birthdate'])) $p['page']['patient']['dicomBirthdate']=msTools::readableDate2Reverse($p['page']['patient']['birthdate']);

//poids taille
$objetPatient = new msObjet;
$objetPatient->setToID($_POST['patientID']);
$p['page']['patient']['poids'] = $objetPatient->getLastObjetValueByTypeName('poids');
$p['page']['patient']['taillePatient'] = number_format ($objetPatient->getLastObjetValueByTypeName('taillePatient')/100, 2, '.','');

//inclusion si présence dans module installé du fichier sépcifique
if (is_file($p['homepath'].'controlers/module/'.$p['user']['module'].'/patient/actions/inc-ajax-prepareEcho.php')) {
    include($p['homepath'].'controlers/module/'.$p['user']['module'].'/patient/actions/inc-ajax-prepareEcho.php');
}

//générer et sortir html
$getHtml = new msGetHtml();
$getHtml->set_template($template);
$fichierDicomTXT = $getHtml->genererHtml();
$fichierDicomTXT = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $fichierDicomTXT);

// Vérification répertoire de travail
msTools::checkAndBuildTargetDir($p['config']['workingDirectory'].$p['user']['id'].'/');

//data patient pour phonecapture
$jsondata=json_encode(array('prat'=>$p['page']['prat'], 'patient'=>$p['page']['patient'], 'saveAs'=>'dicom'));
file_put_contents($p['config']['workingDirectory'].$p['user']['id'].'/workList.json', $jsondata);

//wl dicom
file_put_contents($p['config']['workingDirectory'].$p['user']['id'].'/workList'.$p['user']['id'].'.txt', $fichierDicomTXT);
exec("dump2dcm ".$p['config']['workingDirectory'].$p['user']['id']."/workList".$p['user']['id'].".txt ".$p['config']['dicomWorkListDirectory']."workList".$p['user']['id'].".wl");
unlink($p['config']['workingDirectory'].$p['user']['id'].'/workList'.$p['user']['id'].'.txt');
die();
