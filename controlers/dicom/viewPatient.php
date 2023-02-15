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
 * Dicom : voir les études correspondant au patient
 * (action via Orthanc, cf class msDicom)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */


$debug = '';
$template = 'viewPatient';

//le patient
$patient = new msPeople();
$patient->setToID($match['params']['patientID']);
$p['page']['patient']['id'] = $match['params']['patientID'];
$p['page']['patient']['administrativeDatas'] = $patient->getAdministrativesDatas();
$p['page']['patient']['administrativeDatas'][8]['age'] = $patient->getAge();

//l'examen
$dc = new msDicom();
$dc->setToID($match['params']['patientID']);
$p['page']['studiesDcData'] = $dc->getAllStudiesFromPatientDcData();
if (!isset($p['page']['studiesDcData']['HttpError'])) {

	//on complète les data dicom avec un datetime facilement exploitable
	foreach ($p['page']['studiesDcData'] as $k => $v) {
		$p['page']['studiesDcData'][$k]['Datetime'] =  $v['MainDicomTags']['StudyDate'] . 'T' . round($v['MainDicomTags']['StudyTime']);
	}

	//on cherche les examens EHR qui peuvent être attachés.
	if ($d = msSQL::sql2tabKey("SELECT value, instance from objets_data where typeID = :typeID and toID = :toID ", 'instance', 'value', ['typeID' => msData::getTypeIDFromName('dicomStudyID'), 'toID' => $p['page']['patient']['id']])) {
		foreach ($d as $k => $v) {
			$ob = new msObjet();
			$ob->setObjetID($k);
			$p['page']['studiesDcDataRapro'][$v] = $ob->getCompleteObjetDataByID();
		}
	}
} else {
	unset($p['page']['studiesDcData']);
}
