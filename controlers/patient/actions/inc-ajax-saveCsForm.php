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
 * Patient > action : sauver un formulaire de consulation
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$formIN=$_POST['formIN'];
$finalStatut='ok';
unset($_SESSION['form'][$formIN]);

//definition formulaire de travail
$form = new msFormValidation();
$form->setFormIDbyName($formIN);
$form->setPostdatas($_POST);
$validation=$form->getValidation();

if ($validation === false) {
  exit(json_encode(array(
    'statut'=>'erreur',
    'form'=>$formIN,
    'msg'=>$_SESSION['form'][$formIN]['validationErrorsMsg'],
    'code'=>$_SESSION['form'][$formIN]['validationErrors']
  )));

} else {

    // class et méthodes du module qui viennent agir en complément.
    $class='msMod'.ucfirst($form->getFormUniqueRawField($formIN, 'module')).'Forms';
    $method_post_generic='doPostPostFormGeneric';

    // construction du PDF immédiatement après le retour du JS
    $optionsFormulaire=$form->getFormOptions();
    if(isset($optionsFormulaire['optionsPdf']['buildPdfOnFormSubmit']) and $optionsFormulaire['optionsPdf']['buildPdfOnFormSubmit'] == true) {
      $buildPdfNow = true;
    } else {
      $buildPdfNow = false;
    }

    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);
    $patient->setToID($_POST['patientID']);

    //nouvelle ou update ?
    if (isset($_POST['objetID'])) {
        $supportID=$_POST['objetID'];

        //par précaution on supprime le pdf antérieur
        $doc= new msStockage();
        $doc->setObjetID($supportID);
        $doc->deleteDoc();
    } else {
        $supportID=$patient->createNewObjet($_POST['csID'], '', $_POST['parentID']);
    }

    // si on a un champ qui est déclaré pour l'autoTitle
    if(isset($_POST['autoTitle'])) {
      if(isset($_POST['p_'.$_POST['autoTitle']])) {
        $patient->setTitleObjet($supportID,$_POST['p_'.$_POST['autoTitle']]);
      }
    }

    // si on a un champ qui est déclaré pour l'autoDate
    if(isset($_POST['autoDate'])) {
      if(isset($_POST['p_'.$_POST['autoDate']])) {
        if(!empty($_POST['p_'.$_POST['autoDate']]) and msTools::validateDate($_POST['p_'.$_POST['autoDate']],'d/m/Y')) {
          $objet=new msObjet();
          $objet->setObjetID($supportID);
          $newDate = DateTime::createFromFormat('d/m/Y', $_POST['p_'.$_POST['autoDate']]);
          $newDate = $newDate->format('Y-m-d 00:00:00');
          $objet->setCreationDate($newDate);
          $objet->changeCreationDate();

          $finalStatut='ok-fullrefresh';
        }
      }
    }

    // on cherche si certains champs doivent ne pas être sauvés si vide.
    $tabDoNotSaveEmpty=$form->getDoNotSaveEmptyDataInForm();

    // réglage mode ignoreEmpty
    $dontIgnoreEmpty=true;
    if (isset($match['params']['ignoreEmpty'])) $dontIgnoreEmpty = false;

    // si édition et qu'on devra agir sur valeurs antérieures, on les sort
    if (isset($_POST['objetID']) and is_numeric($_POST['objetID'])) {
        $prevData=msSQL::sql2tabKey("SELECT dt.name AS name, od.id FROM objets_data as od
          LEFT JOIN data_types AS dt ON od.typeID=dt.id and od.outdated='' and od.deleted=''
          WHERE od.instance='".msSQL::cleanVar($_POST['objetID'])."' and dt.groupe='medical'", "name", "id");
    }


    //on traite chaque POST
    foreach ($_POST as $k=>$v) {
        if (($pos = strpos($k, "_")) !== false) {
            $in = substr($k, $pos+1);
			      $inList[]=$in;
        }
        if (isset($in)) {
            if (!empty($in)) {
              if(!empty(trim($v)) or $v == '0') {
                $patient->createNewObjetByTypeName($in, $v, $supportID);
              } else {
                if(!in_array($in, $tabDoNotSaveEmpty)) {
                  if($dontIgnoreEmpty) {
                    $patient->createNewObjetByTypeName($in, '', $supportID);
                  } else {
                    if(isset($prevData[$in])) {
                      $objDel = new msObjet;
                      $objDel->setFromID($p['user']['id']);
                      $objDel->setObjetID($prevData[$in]);
                      $objDel->setDeletedObjetAndSons();
                    }
                  }
                } else {
                    if(isset($prevData[$in])) {
                      $objDel = new msObjet;
                      $objDel->setFromID($p['user']['id']);
                      $objDel->setObjetID($prevData[$in]);
                      $objDel->setDeletedObjetAndSons();
                    }
                }

              }

            }
        }
    }

  	// On balaye les previous data et on marque deleted si pas d'équivalent dans les POST
  	if(isset($prevData) and !empty($prevData)) {
  		foreach($prevData as $k=>$v) {
  			if(!in_array($k,$inList)) {
  				$objDel = new msObjet;
  				$objDel->setFromID($p['user']['id']);
  				$objDel->setObjetID($prevData[$k]);
  				$objDel->setDeletedObjetAndSons();
  			}
  		}
  	}

    // méthode spécifique au module
    if(method_exists($class,$method_post_generic)) {
      $formModule = new $class;
      $formModule->$method_post_generic();
    }

    unset($_SESSION['form'][$formIN]);

    // générer le retour, dont html
    $patient=new msPeople();
    $patient->setToID($_POST['patientID']);
    $p['cs']=$patient->getHistoriqueObjet($supportID);
    if(isset($p['cs']['creationDate'])) {
      $datCrea = new DateTime($p['cs']['creationDate']);
      $html = new msGetHtml;
      $html->set_template('pht-ligne-typecs');
      $html=$html->genererHtml();

      $tabReturn = [
        'statut'=>$finalStatut,
        'today'=>($datCrea->format('Y-m-d') == date('Y-m-d'))?'oui':'non',
        'html'=>$html,
        'objetID'=>$supportID,
        'buildPdfNow'=> $buildPdfNow
      ];

    } else {
      $tabReturn = [
        'statut'=>$finalStatut,
        'today'=>'non',
        'html'=>'',
        'objetID'=>$supportID,
        'buildPdfNow'=> $buildPdfNow
      ];
    }

    header('Content-Type: application/json');
    exit(json_encode($tabReturn));

}
