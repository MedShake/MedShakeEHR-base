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

// liste des formulaires fixes au 1er affichage dossier patient pour JS
$p['page']['listeForms']=array('baseATCD','baseSynthese');

// le formulaire latéral ATCD
$form_baseATCD = new msForm();
$form_baseATCD->setFormIDbyName($p['page']['formName_baseATCD']='baseATCD');
$form_baseATCD->getPrevaluesForPatient($p['page']['patient']['id']);
$p['page']['formData_baseATCD']=$form_baseATCD->getForm();

// si LAP activé : allergie et atcd structurés
if($p['config']['utiliserLap'] == 'true') {

    // gestion atcd structurés
    $listeChampsAtcd=array('atcdMedicChir');
    $gethtml=new msGetHtml;
    $gethtml->set_template('inc-patientAtcdStruc');
    foreach($listeChampsAtcd as $v) {
      $p['page']['beforeVar'][$v]=$patient->getAtcdStruc($v);
      if(empty($p['page']['beforeVar'][$v])) $p['page']['beforeVar'][$v]=array('fake');
      $p['page']['formData_baseATCD']['before'][$v]=$gethtml->genererHtmlString($p['page']['beforeVar'][$v]);
    }
    unset($p['page']['beforeVar'], $listeChampsAtcd, $gethtml);

    // gestion allergies structurées
    $listeChampsAllergie=array('allergies');
    $gethtml=new msGetHtml;
    $gethtml->set_template('inc-patientAllergies');
    foreach($listeChampsAllergie as $v) {
      $p['page']['beforeVar'][$v]=$patient->getAllergies($v);
      if(empty($p['page']['beforeVar'][$v])) $p['page']['beforeVar'][$v]=array('fake');
      $p['page']['formData_baseATCD']['before'][$v]=$gethtml->genererHtmlString($p['page']['beforeVar'][$v]);
    }
    unset($p['page']['beforeVar'], $listeChampsAllergie, $gethtml);
}

// le formulaire de synthèse patient
$form_baseSynthese = new msForm();
$form_baseSynthese->setFormIDbyName($p['page']['formName_baseSynthese']='baseSynthese');
$form_baseSynthese->getPrevaluesForPatient($p['page']['patient']['id']);
$p['page']['formData_baseSynthese']=$form_baseSynthese->getForm();

$typeCs_csBase = new msData;
$p['page']['typeCs_csBase']=$typeCs_csBase->getDataTypesFromCatName('csBase', array('id','label', 'formValues'));

$data=new msData;
$reglements=$data->getDataTypesFromCatName('porteursReglement', array('id', 'module', 'label', 'description', 'formValues'));
foreach ($reglements as $v) {
    if ($v['module']=='base' and (
       ($v['formValues']=='baseReglementLibre' and $p['config']['administratifSecteurHonoraires']=='') or
       ($v['formValues']=='baseReglementS1' and $p['config']['administratifSecteurHonoraires']=='1') or
       ($v['formValues']=='baseReglementS2' and $p['config']['administratifSecteurHonoraires']=='2'))) {
        $p['page']['formReglement'][]=$v;
    }
}
$ordos=$data->getDataTypesFromCatName('porteursOrdo', array('id', 'module', 'label', 'description', 'formValues'));
foreach ($ordos as $v) {
    if ($v['module']=='base') {
      $p['page']['formOrdo'][]=$v;
    }
}
