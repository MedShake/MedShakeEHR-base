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
 * LAP : ajax > produire une alerte SAM
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';

$sam = new msLapSAM;
$sam->setFromID($p['user']['id']);
$sam->setToID($_POST['patientID']);
$sam->setSamID($_POST['samID']);

$data = $sam->getSamData();
// procédure pour vérifier si pas bloqué ou pas pour le patient
$samStatut = $sam->getSamStatusForPatient();

if( $samStatut == 'enabled' or $_POST['analyseWithNoRestriction'] == true ) {

$commentaire = $sam->getSamCommentForPatient();

  $html = '<img class="float-right" src="data:'.$data['logoMediaType'].';base64, '.$data['logo'].'" alt="logo" style="margin-left : 10px;"/>
  <h4>'.$data['titre'].'</h4>
  <p>'.nl2br($data['liste_medicaments']).'</p>
  <p>'.str_replace(array('html:','href='),array('','target="_blank" href='),$data['messageLAPV']).'</p>
  <p>Référence : '.str_replace(array('html:','href='),array('','target="_blank" href='),$data['reference']).'</p>
  <label>Commentaire pour ce patient concernant ce SAM</label>
  <textarea data-samID="'.$_POST['samID'].'" class="form-control samCommentObserv" rows="3" placeholder="Commentaire pour ce patient"';
  if(isset($commentaire['id'])) $html .= ' data-objetID="'.$commentaire['id'].'" ';
  $html .= '>';
  if(isset($commentaire['id'])) $html .= $commentaire['value'];
  $html .= '</textarea>';


  echo json_encode(array(
    'alert'=>'ok',
    'analyseWithNoRestriction'=>$_POST['analyseWithNoRestriction'],
    'samID'=>$_POST['samID'],
    'samData'=>$data,
    'html'=>$html
  ));

} else {
  echo json_encode(array(
    'alert'=>'ko',
    'analyseWithNoRestriction'=>$_POST['analyseWithNoRestriction'],
    'samID'=>$_POST['samID'],
    'samData'=>$data,
    'html'=>''
  ));
}
