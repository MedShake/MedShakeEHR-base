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
 */

 $formIN=$_POST['formIN'];

 //definition formulaire de travail
 $form = new msFormValidation();
 $form->setFormIDbyName($formIN);
 $form->setPostdatas($_POST);
 $validation=$form->getValidation();

 if ($validation === false) {
     // pas d'exploitation car pas de champ required
     // utilisés ici.
 } else {
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
           $objet->setID($supportID);
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

     // si édition et qu'on devra agis sur valeurs antérieures, on les sort
     if (!$dontIgnoreEmpty or !empty($tabDoNotSaveEmpty)) {
         if (isset($_POST['objetID']) and is_numeric($_POST['objetID'])) {
             $prevData=msSQL::sql2tabKey("SELECT dt.name AS name, od.id FROM objets_data as od
               LEFT JOIN data_types AS dt ON od.typeID=dt.id and od.outdated='' and od.deleted=''
               WHERE od.instance='".msSQL::cleanVar($_POST['objetID'])."'", "name", "id");
         }
     }

     //on traite chaque POST
     foreach ($_POST as $k=>$v) {
         if (($pos = strpos($k, "_")) !== false) {
             $in = substr($k, $pos+1);
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
                     if(isset($prevData[$in])) $patient->setDeletedObjetAndSons($prevData[$in]);
                   }
                 } else {
                     if(isset($prevData[$in])) $patient->setDeletedObjetAndSons($prevData[$in]);
                 }

               }

             }
         }
     }


     unset($_SESSION['form'][$formIN]);

    msTools::redirection('/patient/'.$_POST['patientID'].'/');
}
