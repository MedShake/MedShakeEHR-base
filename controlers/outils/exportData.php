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
 * Outils : export data
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 //admin uniquement
 if ($p['config']['droitExportPeutExporterPropresData'] != 'true') {
     $template="forbidden";
 } else {
  $template="exportData";
  $debug='';

  if(!isset($match['params']['dataTypeID'])) {
    $listForms=new msExportData;
    $p['page']['listeForms']=$listForms->getExportabledList();
    $p['page']['listeCats']=msData::getCatListFromGroupe(['typecs'],['id','label']);
  }

  elseif(isset($match['params']['dataTypeID']) and is_numeric($match['params']['dataTypeID'])) {

    $data=new msData;
    $p['page']['dataTypeinfos']=$data->getDataType($match['params']['dataTypeID']);
    $p['page']['dataTypeinfos']['catLabel']=$data->getCatLabelFromCatID($p['page']['dataTypeinfos']['cat']);

    if($p['page']['dataTypeinfos']['groupe']=='typecs' and $p['page']['dataTypeinfos']['formType']=='select') {

      // informations et champs dans le form
      $form=new msForm;
      $form->setFormIDbyName($p['page']['dataTypeinfos']['formValues']);
      $p['page']['formInfos']=$form->getFormRawData(['id','name', 'description']);

      $p['page']['dataFields']=$form->formExtractDistinctTypes();
      $p['page']['dataFields']=$data->getLabelFromTypeName(array_keys($p['page']['dataFields']));

      //champs dans les data administratives patient
      $form=new msForm;
      $form->setFormIDbyName('baseNewPatient');
      $p['page']['dataFieldsAdmin']=$form->formExtractDistinctTypes();
      $p['page']['dataFieldsAdmin']=$data->getLabelFromTypeName(array_keys($p['page']['dataFieldsAdmin']));

      //champs dans les data administratives praticien
      $form=new msForm;
      $form->setFormIDbyName('baseNewPro');
      $p['page']['dataFieldsAdminPro']=$form->formExtractDistinctTypes();
      $p['page']['dataFieldsAdminPro']=$data->getLabelFromTypeName(array_keys($p['page']['dataFieldsAdminPro']));

      //liste praticiens
      $p['page']['prat']=msPeopleSearch::getUsersList();

    } else {
      msTools::redirection('/outils/export-data/', '401');
    }
  }
}
