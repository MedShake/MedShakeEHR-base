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

 $dontIgnoreEmpty=true;
 if (isset($match['params']['ignoreEmpty'])) {
     $dontIgnoreEmpty = false;
     if (isset($_POST['objetID'])) {
         $prevData=msSQL::sql2tabKey("SELECT dt.name AS name FROM objets_data as od LEFT JOIN data_types AS dt
             ON od.typeID=dt.id and od.outdated='' and od.deleted=''
             WHERE od.instance='".$_POST['objetID']."'", "name", "name");
     }
 }

 //definition formulaire de travail
 $form = new msForm();
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

     //on traite chaque POST
     foreach ($_POST as $k=>$v) {
         if (($pos = strpos($k, "_")) !== false) {
             $in = substr($k, $pos+1);
         }
         if (isset($in)) {
             if (!empty($in) and ($dontIgnoreEmpty or !empty(trim($v)) or (isset($prevData) and array_key_exists($in, $prevData)))) {
                 $patient->createNewObjetByTypeName($in, $v, $supportID);
             }
         }
     }


     unset($_SESSION['form'][$formIN]);

    msTools::redirection('/patient/'.$_POST['patientID'].'/');
}
