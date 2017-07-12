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
 * Config : générer le contenu d'un fichier de modèle de FSE Pyxvital linux
 *  *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$id=$match['params']['id'];

//secteur
if($p['config']['administratifSecteurHonoraires']==1) $tarifs='tarifs1'; elseif($p['config']['administratifSecteurHonoraires']==2)  $tarifs='tarifs2';

$data=msSQL::sqlUnique("select * from actes where id='".$id."' ");
$data['details']=Spyc::YAMLLoad($data['details']);

$actes=array_keys($data['details']);

$detActes=msSQL::sql2tabKey("select * from actes_base where code in ('".implode("', '", $actes)."')", 'code');

//détection de la présence d'un acte NGAP dans la liste
if(in_array('NGAP', array_column($detActes, 'type'))) $ngapDetect=true; else $ngapDetect=false;

$i=1;
foreach ($data['details'] as $k=>$v) {
  $quantite[]='1';
  if($detActes[$k]['type'] == 'NGAP') $code[]=$k; elseif($detActes[$k]['type'] == 'CCAM') $code[]='CCAM';
  $coefficient[]='1';
  if($detActes[$k]['type'] == 'CCAM') $code_ccam[]=$k;elseif($detActes[$k]['type'] == 'NGAP') $code_ccam[]='Néant';
  $code_compl_CCAM[]='10';
  if($detActes[$k]['type'] == 'CCAM') $modificateurs_ccam[]='____'; elseif($detActes[$k]['type'] == 'NGAP') $modificateurs_ccam[]='Néant';
  if($ngapDetect==true and $detActes[$k]['type'] == 'CCAM') {
    $code_suppl_ccam[]='___';
  } elseif($ngapDetect==true and $detActes[$k]['type'] == 'NGAP') {
    $code_suppl_ccam[]='Néant';
  } elseif($detActes[$k]['type'] == 'CCAM') {
    $code_suppl_ccam[]=$i.'__';
  }
  $code_affine[]='0';
  $montant_honoraires[]=number_format(($v['pourcents']*$detActes[$k][$tarifs]/100)+$v['depassement'] , 2, '.', '');
  $qualificatif_depense[]='Néant';
  $domicile[]='N';
  $d_jf[]='N';
  $nuit[]='N';
  $urgence[]='N';
  $i++;
}




$chaine='[Prestation]
Quantite='.implode('+', $quantite).'
Code='.implode('+', $code).'
Coefficient='.implode('+', $coefficient).'
Code_CCAM='.implode('+', $code_ccam).'
Code_compl_CCAM='.implode('+', $code_compl_CCAM).'
Modificateurs_CCAM='.implode('+', $modificateurs_ccam).'
Code_suppl_CCAM='.implode('+', $code_suppl_ccam).'
Code_affine='.implode('+', $code_affine).'
Montant_honoraires='.implode('+', $montant_honoraires).'
Qualificatif_depense='.implode('+', $qualificatif_depense).'
Domicile='.implode('+', $domicile).'
D_JF='.implode('+', $d_jf).'
Nuit='.implode('+', $nuit).'
Urgence='.implode('+', $urgence).'';

header("Content-Type:text/plain; charset=Windows-1252");
echo iconv('UTF-8', 'Windows-1252', $chaine);
