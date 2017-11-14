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
 */

$template="dicomWL";

$prat = new msPeople();
$prat->setToID($p['user']['id']);
$p['page']['prat']=$prat->getSimpleAdminDatas();

$patient = new msPeople();
$patient->setToID($_POST['patientID']);
$p['page']['patient']=$patient->getSimpleAdminDatas();
$p['page']['patient']['id']=$_POST['patientID'];
$p['page']['patient']['dicomPatientID']=$p['config']['dicomPrefixIdPatient'].$_POST['patientID'];
$p['page']['patient']['dicomBirthdate']=msTools::readableDate2Reverse($p['page']['patient'][8]);

//inclusion si présence dans module installé du fichier sépcifique
if (is_file($p['config']['homeDirectory'].'controlers/module/patient/actions/inc-ajax-prepareEcho.php')) {
    include($p['config']['homeDirectory'].'controlers/module/patient/actions/inc-ajax-prepareEcho.php');
}

// les variables d'environnement twig
if(isset($p['config']['twigEnvironnementCache'])) $twigEnvironment['cache']=$p['config']['twigEnvironnementCache']; else $twigEnvironment['cache']=false;
if(isset($p['config']['twigEnvironnementAutoescape'])) $twigEnvironment['autoescape']=$p['config']['twigEnvironnementAutoescape']; else $twigEnvironment['autoescape']=false;

$loaderPDF = new Twig_Loader_Filesystem($p['config']['homeDirectory'].'templates/'.$p['config']['templatesModuleFolder'].'/');
$twigPDF = new Twig_Environment($loaderPDF, $twigEnvironment);
$twigPDF->getExtension('Twig_Extension_Core')->setDateFormat('d/m/Y', '%d days');
$twigPDF->getExtension('Twig_Extension_Core')->setTimezone('Europe/Paris');

$fichierDicomTXT = $twigPDF->render($template.'.html.twig', $p);
$fichierDicomTXT = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $fichierDicomTXT);

file_put_contents($p['config']['workingDirectory'].'workList.txt', $fichierDicomTXT);
exec("dump2dcm ".$p['config']['workingDirectory']."workList.txt ".$p['config']['dicomWorkListDirectory']."workList.wl");
unlink($p['config']['workingDirectory'].'workList.txt');
die();
