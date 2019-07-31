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

if (isset($_POST['acteID']) or strlen($_POST['regleDetailsActes']) > 0 ) {
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
          $_POST[$param]=0;
        }
    }
    //support
    if (isset($_POST['objetID']) and is_numeric($_POST['objetID'])) {
        $supportID=$patient->createNewObjet($_POST['porteur'], '', '0', $_POST['acteID'], $_POST['objetID']);

        //par précaution on supprime le pdf antérieur
        $doc= new msStockage();
        $doc->setObjetID($supportID);
        $doc->deleteDoc();
    } elseif($_POST['acteID']>0) {
        $supportID=$patient->createNewObjet($_POST['porteur'], '', '0', $_POST['acteID']);
    } else {
        $supportID=$patient->createNewObjet($_POST['porteur'], '');
    }

    $paye = (float)$_POST['regleCheque'] + (float)$_POST['regleCB'] + (float)$_POST['regleEspeces'] + (float)$_POST['regleTiersPayeur'] + '0';
    $apayer= (float)$_POST['regleTarifSSCejour'] + (float)$_POST['regleDepaCejour'] + (float)$_POST['regleTarifLibreCejour'] + (float)$_POST['regleModulCejour'] + '0';
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
        $ft = new msReglement;
        $ft->setFactureTypeID($_POST['acteID']);
        $codes = $ft->getFactureTypeData()['syntheseActes'];
    } else {
        $codes = json_decode($_POST['regleDetailsActes'], TRUE);
        if(!empty($codes)) {
          foreach($codes as $code) {
            if(isset($code['quantite']) and $code['quantite'] > 1 ) {
              $titre[] = $code['quantite'].$code['acte'];
            } else {
              $titre[] = $code['acte'];
            }
          }
          $codes = implode(' + ', $titre);
        } else {
          $codes = '';
        }
    }

    $patient->setTitleObjet($supportID, $codes.' / '.$_POST['regleFacture'].'€');

    // faire le ménage dans la salle d'attente (si si ... !)
    $agenda=new msAgenda;
    $agenda->set_userID($p['user']['id']);
    $agenda->set_patientID($_POST['patientID']);
    $agenda->cleanEnAttente();

    // générer le retour, dont html
    $patient=new msPeople();
    $patient->setToID($_POST['patientID']);
    $p['cs']=$patient->getHistoriqueObjet($supportID);
    $datCrea = new DateTime($p['cs']['creationDate']);

    $html = new msGetHtml;
    $html->set_template('pht-ligne-reglement');
    $html=$html->genererHtml();

    header('Content-Type: application/json');
    exit(json_encode([
      'statut'=>'ok',
      'today'=>($datCrea->format('Y-m-d') == date('Y-m-d'))?'oui':'non',
      'html'=>$html,
    ]));

} else {
    header('Content-Type: application/json');
    exit(json_encode([
      'statut'=>'avertissement',
      'msg'=>'Attention : formulaire vide !',
      'html'=>'',
    ]));
}
