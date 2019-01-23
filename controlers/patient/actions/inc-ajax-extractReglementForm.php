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
 * Patient > ajax : obtenir le formulaire de règlement
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';
$template="patientReglementForm";
$forceAllTemplates=TRUE;

$hono = new msReglement;
$hono->setPatientID($_POST['patientID']);

if (!isset($delegate)) {
  if (!isset($_POST['objetID']) or $_POST['objetID']==='') {
    $hono->setReglementForm($_POST['reglementForm']);
    $hono->setPorteur($_POST['porteur']);
    $hono->setUserID($userID=is_numeric($_POST['asUserID']) ? $_POST['asUserID'] : $p['user']['id']);
    $hono->setModule($_POST['module']);
  } else {
    $hono->setObjetID($_POST['objetID']);
  }
  if(isset($_POST['asUserID']) and is_numeric($_POST['asUserID'])) $hono->setAsUserID($_POST['asUserID']);

  //si le formulaire de règlement n'est pas celui de base, c'est au module de gérer (à moins qu'il délègue)
  if (!in_array($hono->getReglementForm(), ['baseReglementLibre', 'baseReglementS1', 'baseReglementS2'])) {
      $hook=$p['homepath'].'/controlers/module/'.$hono->getModule().'/patient/actions/inc-hook-extractReglementForm.php';
      if ($hono->getModule()!='' and $hono->getModule()!='base' and is_file($hook)) {
          include $hook;
      }
      if (!isset($delegate)) {
          return;
      }
  }
}

//pour menu de choix de l'acte, par catégories
$p['page']['menusActes']=$hono->getFacturesTypesMenus();

//edition : acte choisi :
$p['page']['selectedFactureTypeID']=$hono->getFactureTypeIDFromObjetID();

$form = new msForm();
$form->setFormIDbyName($hono->getReglementForm());
$form->setTypeForNameInForm('byName');
if ($_POST['objetID'] > 0) {
  $prevalues=$hono->getPreValuesForReglementForm();
  $form->setPrevalues($prevalues);
}
$p['page']['form']=$form->getForm();
$form->addSubmitToForm($p['page']['form'], 'btn-warning btn-lg btn-block');

// déterminer les secteurs tarifaires
$hono->setSecteursTarifaires();

// champ cachés
$hono->setHiddenInputToReglementForm($p['page']['form']);

// Data complémentaires templates
$p['page']['formIN']=$hono->getReglementForm();
$p['page']['modifcateursCcam']=$hono->getModificateursCcam();
$p['page']['patient']['id']=$_POST['patientID'];
