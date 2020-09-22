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
 * People : ajax > obtenir la liste des registres pour l'autocomplete Relations
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 if($p['config']['optionGeActiverRegistres'] != 'true') {
   die();
 }

$term = msSQL::cleanVar($_GET['term']);
$a_json = array();

$mss=new msPeopleSearch;
$mss->setNameSearchMode('BnFnOrLnFn');
$mss->setPeopleType(['registre']);
$criteres = array(
  'registryname'=>$term,
);
$mss->setCriteresRecherche($criteres);
$mss->setColonnesRetour(['registryname']);
$mss->setLimitNumber(20);
if ($data=msSQL::sql2tab($mss->getSql())) {

	foreach ($data as $k=>$v) {
    $label = $v['registryname'];
		$a_json[]=array(
			'label'=>trim($label),
			'value'=>trim($label),
			'id'=>$v['peopleID'],
		);
	}
}

header('Content-Type: application/json');
exit(json_encode($a_json));
