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
 * Patients > ajax : place les infos nécessaires dans un TXT pour le retrouver sur le
 * périphérique tactil de signature de consentement, coté non logué.
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$data = new msData;
$data = $data->getDataType($_POST['typeID'], $col=['id','label', 'formValues as template'] );

// petite prévention
$data['template']=str_replace('.html.twig','',$data['template']);

$tab=array(
  'patientID'=>(int)$_POST['patientID'],
  'fromID'=>(int)$p['user']['id'],
  'typeID'=>(int)$data['id'],
  'template'=>(string)$data['template'],
  'label'=>(string)$data['label'],
);

file_put_contents($p['config']['workingDirectory'].'consentementPatientID.txt', Spyc::YAMLDump($tab, false, 0, true));
