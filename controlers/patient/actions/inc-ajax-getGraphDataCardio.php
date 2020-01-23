<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Patient > ajax : obtenir les datas pour la biométrie cardio TA / FC / Sat
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

header('Content-Type: application/json');

$data = new msPeople();
$data->setToID($_POST['patientID']);

$datas = array('taSystolique','taDiastolique','spO2','freqCardiaque');
$p['page']['distinctYears']=[];
foreach($datas as $dat) {
    $la = $data->getDataHistoricalValuesDistinctYears($dat);
    if(is_array($la)) $p['page']['distinctYears']=array_merge($p['page']['distinctYears'] , $la);
    $d[$dat] = $data->getDataHistoricalValues($dat, $_POST['year'].'-01-01 00:00:00', $_POST['year'].'-12-31 23:59:59');
    if(!empty($d[$dat])) {
        foreach($d[$dat] as $date=>$v) {
          $mois=strftime('%B', mktime(0, 0, 0, explode('-',$v['dateonly'])[1], 1, 2018));
          $p['page']['histoData'][$mois][$v['dateonly']][$dat][$v['timeonly']]=$v;
        }
    }
}

$p['page']['selectedYear']=$_POST['year'];
$p['page']['distinctYears']=array_unique($p['page']['distinctYears']);
rsort($p['page']['distinctYears']);

$html = new msGetHtml;
$html->set_template('inc-patientBiometrieCardio.html.twig');
$html = $html->genererHtmlVar($p);

exit(json_encode(array(
  'html'=>$html,
  'statut'=>'ok'
)));
