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
 * Patients > ajax : marquer un dossier patient comme effacé
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if(is_numeric($_POST['patientID'])) {

  // création d'un marqueur pour sauvegarde de l'info
  // on place en valeur le type du dossier + motif converti en yaml

  $value['typeDossier']=msSQL::sqlUniqueChamp("select type from people where id='".$_POST['patientID']."' limit 1");

  if (($p['config']['droitDossierPeutSupPatient'] == 'true' and $value['typeDossier'] == 'patient') or ($p['config']['droitDossierPeutSupPraticien'] == 'true' and $value['typeDossier'] == 'pro')) {

    $value['motif']=$_POST['motif'];
    $value = Spyc::YAMLDump($value);

    $marqueur=new msObjet();
    $marqueur->setFromID($p['user']['id']);
    $marqueur->setToID($_POST['patientID']);
    $marqueur->createNewObjetByTypeName('administratifMarqueurSuppression', $value);

    // on marque le dossier dans people
    $data=array(
      'id'=>$_POST['patientID'],
      'type'=>'deleted'
    );
    msSQL::sqlInsert('people', $data);
  }
}
