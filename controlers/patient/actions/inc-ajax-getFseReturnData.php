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
 * Patient > ajax : obtenir les data retournées après l'établissement d'une FSE
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

// objet paiement
$paiem = new msObjet;
$paiem->setObjetID($_GET['objetID']);
$dataPaiem = $paiem->getObjetAndSons('name');


if(isset($dataPaiem['regleFseData'])) {
  // sortir de la boucle JS
  $data['status']='end';

  $dataregFse=json_decode($dataPaiem['regleFseData']['value'], true)[0];

  //actes FSE
  if(isset($dataregFse)) {
    foreach($dataregFse['dataDetail'] as $acte) {
      if($acte['is_ligne_ok'] == 1) {
        $actes['actesOK'][]=$acte['code_prestation'];
      } else {
        $actes['actesKO'][]=$acte['code_prestation'];
      }
    }
    if(!empty($actes['actesOK'])) $data['actesFSE']=implode(' + ', $actes['actesOK']);
  }

  //actes règlement
  if(isset($dataPaiem['regleDetailsActes'])) {
    $dataPaiem['regleDetailsActes']['value'] = json_decode($dataPaiem['regleDetailsActes']['value'], TRUE);
    $data['actesEHR']=implode(' + ', array_column($dataPaiem['regleDetailsActes']['value'], 'acte'));
  }

  // montant total
  $data['totalFSE'] = (float)$dataregFse['montant_total_facture'];
  $data['totalEHR'] = (float)$dataPaiem['regleFacture']['value'];
  if(($data['totalFSE'] - $data['totalEHR']) != 0) {
    $data['totalError']=true;
  } else {
    $data['totalError']=false;
  }

  // à payer :
  $data['aPayerFSE'] = (float)$dataregFse['montant_part_assure'];
  $data['aPayerEHR'] = (float)$dataPaiem['regleFacture']['value']-(float)$dataPaiem['regleTiersPayeur']['value'];
  if(($data['aPayerFSE'] - $data['aPayerEHR']) != 0) {
    $data['aPayerError']=true;
  } else {
    $data['aPayerError']=false;
  }

} else {
  $data['status']='wait';
}


header('Content-Type: application/json');
exit(json_encode($data));
