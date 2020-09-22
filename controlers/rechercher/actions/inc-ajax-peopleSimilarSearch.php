<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2020
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
 * Patients > ajax : obtenir un listing de people similaires
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$mss=new msPeopleSearch;
$mss->setNameSearchMode('BnFnOrLnFn');
$mss->setPeopleType([$_POST['peopleType']]);

if($_POST['peopleType'] == 'patient' or $_POST['peopleType'] == 'pro') {
  $criteres = array(
    'birthname'=>$_POST['p_birthname'],
    'lastname'=>$_POST['p_lastname'],
    'firstname'=>$_POST['p_firstname'],
  );
  $colsRetour = ['identite', 'birthdate'];
} elseif($_POST['peopleType'] == 'groupe') {
  $criteres = array(
    'groupname'=>$_POST['p_groupname'],
  );
  $colsRetour = ['groupname', 'country', 'city'];
} elseif($_POST['peopleType'] == 'registre') {
  $criteres = array(
    'registryname'=>$_POST['p_registryname'],
  );
  $colsRetour = ['registryname'];
}

$mss->setCriteresRecherche($criteres);
$mss->setColonnesRetour($colsRetour);
$mss->setLimitNumber(20);

//restrictions sur retours si droits limités
if($p['config']['droitDossierPeutVoirUniquementPatientsPropres'] == 'true') {
 $mss->setRestricDossiersPropres(true);
} elseif($p['config']['droitDossierPeutVoirUniquementPatientsGroupes'] == 'true') {
 $mss->setRestricDossiersGroupes(true);
}

$a_json = array();
if ($data=msSQL::sql2tab($mss->getSql())) {

	foreach ($data as $k=>$v) {


    if($_POST['peopleType'] == 'patient' or $_POST['peopleType'] == 'pro') {
      $a_json[]=array(
  			'label'=>trim($v['identite']).' - '.$v['birthdate'],
  			'type'=>$_POST['peopleType'],
  			'id'=>$v['peopleID'],
  		);
    } elseif($_POST['peopleType'] == 'groupe') {
      $a_json[]=array(
  			'label'=>trim($v['groupname'].' (<small>'.$v['city'].' - '.$v['country'].')</small>'),
  			'type'=>$_POST['peopleType'],
  			'id'=>$v['peopleID'],
  		);
    } elseif($_POST['peopleType'] == 'registre') {
      $a_json[]=array(
  			'label'=>trim($v['registryname']),
  			'type'=>$_POST['peopleType'],
  			'id'=>$v['peopleID'],
  		);
    }

	}
}

header('Content-Type: application/json');
exit(json_encode($a_json));
