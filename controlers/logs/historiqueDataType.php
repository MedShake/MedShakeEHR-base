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
 * Historique d'un data type particulier
 * Approprié en particulier sur les types consultation
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//vérification droits
if ($p['config']['droitStatsPeutVoirStatsGenerales'] != 'true' or $p['config']['droitDossierPeutVoirUniquementPatientsPropres'] == 'true') {
 $template="forbidden";
 return;
}

$debug='';
$template='historiqueDataType';
$typeName = $match['params']['dataType'];

if(isset($match['params']['page'])) {
  $page = $match['params']['page'];
} else {
  $page = 0;
}
$nbParPage=20;
$start = $page*$nbParPage;

$dataType = new msData;
if($dataType->checkDataTypeExistByName($typeName)) {
  $typeID = $dataType->getTypeIDFromName($typeName);
  $p['page']['typeLabel'] = $dataType->getLabelFromTypeID([$typeID])[$typeID];
  $p['page']['typeName'] = $typeName;
  $obj = new msObjet;

  $p['page']['page']=$page;
  $p['page']['pageaff']=$page+1;
  $p['page']['totalObj']=$obj->getNumberOfObjetOfType($typeName, '', '', false );
  $p['page']['nbpages']=ceil($p['page']['totalObj']/$nbParPage);
  $p['page']['listeObj']=$obj->getHistoriqueDataType($typeName, $start,$nbParPage);

  $p['page']['pageLoopFirst']=$page-5;
  $p['page']['pageLoopLast']=$page+5;
  if($p['page']['pageLoopFirst'] <= 1) $p['page']['pageLoopFirst']=2;
  if($p['page']['pageLoopLast'] >= $p['page']['nbpages']) $p['page']['pageLoopLast']=$p['page']['nbpages']-1;

} else {

}
