<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * Config > ajax : prévisualiser un formulaire
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}
if(!is_numeric($_POST['formID'])) die();

//formulaire
$forceAllTemplates="oui";
$form = new msForm();
$form->setFormID($_POST['formID']);
$p['page']['form']=$form->getForm();

if(!empty($p['page']['form'])) {
  $sqlGen = new msSqlGenerate;
  $sqlGen=$sqlGen->getSqlForForm($form->getFormIN());

  $sqlGenUpdate = new msSqlGenerate;
  $sqlGenUpdate->setAddUpdateOnDuplicate(true);
  $sqlGenUpdate=$sqlGenUpdate->getSqlForForm($form->getFormIN());

  $basicTemplateCode=$form->getFlatBasicTemplateCode();

  $html = new msGetHtml;
  $html->set_template('configFormPreviewAjax.html.twig');
  $html = $html->genererHtmlVar($p);
} else {
  $sqlGen="Données non disponibles";
  $basicTemplateCode="Données non disponibles";
  $html='<div class="alert alert-info" role="alert">
      Aperçu non disponible : stucture non présente ou correspondant à un formulaire d\'affichage
      </div>';
}

exit(json_encode(array(
  'htmlFormPreview'=>$html,
  'basicTemplateCode'=>$basicTemplateCode,
  'sqlGen'=>$sqlGen,
  'sqlGenUpdate'=>$sqlGenUpdate
)));
