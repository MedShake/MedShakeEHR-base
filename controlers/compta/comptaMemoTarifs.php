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
 * Compta : mémo tarifs consultation
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$template="comptaMemoTarifs";
$debug='';

//utilisateurs différents qui peuvent enregistrer des recettes
$autoUsers= new msPeople();
if($p['page']['users']=$autoUsers->getUsersListForService('administratifPeutAvoirRecettes')) {

  $where[]="a.toID='0'";

  // sélection du user
  if (isset($match['params']['user'])) {
    $p['page']['selectUser']=$match['params']['user'];
    if (is_numeric($p['page']['selectUser'])) {
       $where[]="a.toID='".$p['page']['selectUser']."'";
    }
  } else {
    reset($p['page']['users']);
    $p['page']['selectUser']=key($p['page']['users']);
    $where[]="a.toID='".$p['page']['selectUser']."'";
  }
  // params du user
  $userOb = new msPeople;
  $userOb->setToID($p['page']['selectUser']);
  $module = $userOb->getModule();
  $secteur=msConfiguration::getParameterValue('administratifSecteurHonorairesCcam', array('id'=>$p['page']['selectUser'], 'module'=>$module));
  $secteurNgap=msConfiguration::getParameterValue('administratifSecteurHonorairesNgap', array('id'=>$p['page']['selectUser'], 'module'=>$module));
  $secteurGeo=msConfiguration::getParameterValue('administratifSecteurGeoTarifaire', array('id'=>$p['page']['selectUser'], 'module'=>$module));
  $reglement = new msReglement();
  $reglement->setSecteurTarifaire($secteur);
  $reglement->setSecteurTarifaireNgap($secteurNgap);
  $reglement->setSecteurTarifaireGeo($secteurGeo);


  if ($tabTypes=msSQL::sql2tab("select a.* , c.name as catName, c.label as catLabel, c.module as catModule
  		from actes as a
  		left join actes_cat as c on c.id=a.cat
      where (".implode(' or ', $where).") and c.module='".$module."' and a.active='oui'
  		group by a.id
  		order by c.displayOrder, c.label asc, a.label asc")) {
     foreach ($tabTypes as $v) {
         $reglement->setFactureTypeID($v['id']);
         $reglement->setFactureTypeData($v);
         $p['page']['secteurs'][$v['catName']]=$secteur;
         $p['page']['tabTypes'][$v['catName']][]=$reglement->getCalculateFactureTypeData();
     }
  }
  // liste des catégories
  $p['page']['catList']=msSQL::sql2tabKey("select id, label from actes_cat where module='".$module."' order by label", 'id', 'label');
}
