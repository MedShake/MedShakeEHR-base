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
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

// le formulaire latéral ATCD
$form_baseATCD = new msForm();
$form_baseATCD->setFormIDbyName($p['page']['formName_baseATCD']='baseATCD');
$form_baseATCD->getPrevaluesForPatient($p['page']['patient']['id']);
$p['page']['formData_baseATCD']=$form_baseATCD->getForm();
$p['page']['formJavascript']['baseATCD']=$form_baseATCD->getFormJavascript();

// si LAP activé : allergie et atcd structurés
if($p['config']['optionGeActiverLapInterne'] == 'true') {

    // gestion atcd structurés
    $listeChampsAtcd=array('atcdMedicChir');
    $gethtml=new msGetHtml;
    $gethtml->set_template('inc-patientAtcdStruc');
    foreach($listeChampsAtcd as $v) {
      $p['page']['beforeVar'][$v]=$patient->getAtcdStruc($v);
      if(empty($p['page']['beforeVar'][$v])) $p['page']['beforeVar'][$v]=array('fake');
      $p['page']['formData_baseATCD']['before'][$v]=$gethtml->genererHtmlVar($p['page']['beforeVar'][$v]);
    }
    unset($p['page']['beforeVar'], $listeChampsAtcd, $gethtml);

    // gestion allergies structurées
    $listeChampsAllergie=array('allergies');
    $gethtml=new msGetHtml;
    $gethtml->set_template('inc-patientAllergies');
    foreach($listeChampsAllergie as $v) {
      $p['page']['beforeVar'][$v]=$patient->getAllergies($v);
      if(empty($p['page']['beforeVar'][$v])) $p['page']['beforeVar'][$v]=array('fake');
      $p['page']['formData_baseATCD']['before'][$v]=$gethtml->genererHtmlVar($p['page']['beforeVar'][$v]);
    }
    unset($p['page']['beforeVar'], $listeChampsAllergie, $gethtml);
}

// le formulaire de synthèse patient
$form_baseSynthese = new msForm();
$form_baseSynthese->setFormIDbyName($p['page']['formName_baseSynthese']='baseSynthese');
$form_baseSynthese->getPrevaluesForPatient($p['page']['patient']['id']);
$p['page']['formData_baseSynthese']=$form_baseSynthese->getForm();
$p['page']['formJavascript']['baseSynthese']=$form_baseSynthese->getFormJavascript();


$typeCs_csBase = new msData;
$p['page']['typeCs_csBase']=$typeCs_csBase->getDataTypesFromCatName('csBase', array('id','label', 'formValues'));

$data = new msData;
$ordos=$data->getDataTypesFromCatName('porteursOrdo', array('id', 'module', 'label', 'description', 'formValues'));
foreach ($ordos as $v) {
    if ($v['module']=='base') {
      $p['page']['formOrdo'][]=$v;
    }
}
