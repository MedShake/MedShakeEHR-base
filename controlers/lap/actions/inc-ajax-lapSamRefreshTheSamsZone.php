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
 * LAP : ajax > générer les SAMS
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';

$sams = new msLapSAM;
$sams->setFromID($p['user']['id']);
$sams->setToID($_POST['patientID']);

$samsArray = array_unique($_POST['sams']);

if(count($samsArray) > 0) {

  foreach($samsArray as $sam) {
    $sams->setSamID($sam);
    if($data = $sams->getSamData()) {
      $p['page']['sams'][$sam] = $data;
      $p['page']['sams'][$sam]['co'] = $sams->getSamCommentForPatient();
    }

  }
  $p['page']['zone']=$_POST['zone'];
  $html = new msGetHtml;
  $html->set_template('inc-lapSamsZone.html.twig');
  $html = $html->genererHtmlVar($p);

  $samsInSamsZone = array_keys($p['page']['sams']);

} else {
  $html='';
  $samsInSamsZone=[];
}

echo json_encode(array(
  'zone'=>$_POST['zone'],
  'html'=>$html,
  'samsInSamsZone'=>$samsInSamsZone
));
