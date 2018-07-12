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
 * Patient > ajax : sauver un règlement
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

if (!in_array($_POST['reglementForm'], ['baseReglementLibre', 'baseReglementS1', 'baseReglementS2'])) {
      $hook=$p['homepath'].'/controlers/module/'.$_POST['module'].'/patient/actions/inc-hook-saveReglementForm.php';
      if ($_POST['module']!='' and $_POST['module']!='base' and is_file($hook)) {
          include $hook;
      }
      if (!isset($delegate)) {
          return;
      }
}

if (count($_POST['acteID'])>0 or strlen($_POST['regleDetailsActes']) > 0 ) {
    if(!is_numeric($_POST['acteID'])) $_POST['acteID']=0;
    $patient = new msObjet();
    $patient->setFromID($_POST['asUserID']?:$p['user']['id']);
    $patient->setToID($_POST['patientID']);
    if ($_POST['asUserID']) {
        $patient->setByID($p['user']['id']);
    }

    if (!isset($_POST['regleSituationPatient'])) {
      $_POST['regleSituationPatient']='A';
    }
    foreach (['regleTarifSSCejour', 'regleTarifLibreCejour', 'regleDepaCejour', 'regleModulCejour', 'regleCheque', 'regleCB', 'regleEspeces', 'regleTiersPayeur', 'regleFacture'] as $param) {
        if (!isset($_POST[$param])) {
          $_POST[$param]='';
        }
    }
    //support
    if (isset($_POST['objetID']) and is_numeric($_POST['objetID'])) {
        $supportID=$patient->createNewObjet($_POST['porteur'], '', '0', $_POST['acteID'], $_POST['objetID']);
    } elseif($_POST['acteID']>0) {
        $supportID=$patient->createNewObjet($_POST['porteur'], '', '0', $_POST['acteID']);
    } else {
        $supportID=$patient->createNewObjet($_POST['porteur'], '');
    }
    echo 'support : '.$supportID;

    $paye= $_POST['regleCheque'] + $_POST['regleCB'] + $_POST['regleEspeces'] + $_POST['regleTiersPayeur'] + '0';
    $apayer= $_POST['regleTarifSSCejour'] + $_POST['regleDepaCejour'] + $_POST['regleTarifLibreCejour'] + $_POST['regleModulCejour'] + '0';
    $important=array('id'=>$supportID, 'important'=>$paye < $apayer?'y':'n');
    msSQL::sqlInsert('objets_data', $important);

    if ($_POST['regleTarifSSCejour']!='' or $_POST['regleDepaCejour']!='') {
         unset($_POST['regleTarifLibreCejour']);
         unset($_POST['regleModulCejour']);
    } elseif ($_POST['regleTarifLibreCejour']!='' or $_POST['regleModulCejour']!='') {
         unset($_POST['regleTarifSSCejour']);
         unset($_POST['regleDepaCejour']);
    }

    foreach ($_POST as $param=>$value) {
        if (!in_array($param, ['module', 'asUserID', 'reglementForm', 'formIN', 'acteID', 'objetID', 'patientID', 'porteur'])) {
            $patient->createNewObjetByTypeName($param, $value, $supportID);
        }
    }

    //titre
    if($_POST['acteID'] > 0) {
        $codes = msSQL::sqlUniqueChamp("select details from actes where id='".$_POST['acteID']."' limit 1");
        $codes = Spyc::YAMLLoad($codes);
        $codes = implode(' + ', array_keys($codes));
    } else {
        $codes = json_decode($_POST['regleDetailsActes'], TRUE);
        $codes = implode(' + ', array_column($codes, 'acte') );
    }

    $patient->setTitleObjet($supportID, $codes.' / '.$_POST['regleFacture'].'€');

    // générer le retour
    $debug='';
    //template
    $template="pht-ligne-reglement";
    $patient=new msPeople();
    $patient->setToID($_POST['patientID']);
    if (isset($_POST['objetID']) and $_POST['objetID']!=='') {
        $ligneHisto=$patient->getHistorique($_POST['objetID']);
        $p['cs']=array_pop($ligneHisto)[0];
    } else {
        $p['cs']=$patient->getToday("limit 1")[0];
    }

} else {
    die('Avertissement: Formulaire vide !');
}
