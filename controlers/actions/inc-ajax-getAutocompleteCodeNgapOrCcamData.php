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
 * Requêtes AJAX > retourner les infos sur les actes NGAP / CCAM correspondant à la recherche texte
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$reg = new msReglement;
if($data = $reg->getActeDataFromTerm($_GET['term'])) {
  foreach($data as $k=>$v) {
    $data[$k]['labelo'] = $v['label'];
    $data[$k]['label'] = $v['code'].' '.$v['label'];
    $data[$k]['base'] = $data[$k]['tarif'] = $data[$k]['total'] = $v['tarifs1'];
    $data[$k]['pourcents'] = '100';
    $data[$k]['depassement'] = '0';
    $data[$k]['codeAsso'] = '';
  }
}
exit(json_encode($data));
