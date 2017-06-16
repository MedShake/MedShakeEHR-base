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
 * Compta : la page générale de compta
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template="compta";

//gestion des plages
if(!isset($_POST['beginPeriode'])) $beginPeriode = new DateTime(); else $beginPeriode= DateTime::createFromFormat('d/m/Y', $_POST['beginPeriode']);
if(!isset($_POST['endPeriode'])) $endPeriode = new DateTime(); else $endPeriode= DateTime::createFromFormat('d/m/Y', $_POST['endPeriode']);


$p['page']['periode']['begin']=$beginPeriode->format("d/m/Y") ;
$p['page']['periode']['end']=$endPeriode->format("d/m/Y") ;
if(isset($_POST['periodeQuickSelect'])) $p['page']['periode']['quick']=$_POST['periodeQuickSelect'];

//quick options
$p['page']['periode']['quickOptions']=array(
"today"=>"Aujourd'hui",
"yesterday"=>"Hier",
"thisweek"=>"Cette semaine",
"lastweek"=>"Semaine dernière",
"thismonth"=>"Ce mois",
"lastmonth"=>"Mois dernier");

//select typeID du groupe reglement
$listeTypeID = new msData();
if ($listeTypeID = $listeTypeID->getDataTypesFromGroupe('reglement', ['id'])) {
    foreach ($listeTypeID as $v) {
        $tabliste[]=$v['id'];
    }
}



//sortir les reglements du jour
if ($lr=msSQL::sql2tab("select pd.toID, pd.id, pd.typeID, pd.value, pd.creationDate, pd.instance, p.value as prenom , n.value as nom, a.label, dc.name
  from objets_data as pd
  left join data_types as dc on dc.id=pd.typeID
  left join actes as a on pd.parentTypeID=a.id
  left join objets_data as p on p.toID=pd.toID and p.typeID=3
  left join objets_data as n on n.toID=pd.toID and n.typeID=2
  where pd.typeID in (".implode(',', $tabliste).")  and DATE(pd.creationDate) >= '".$beginPeriode->format("Y-m-d")."' and DATE(pd.creationDate) <= '".$endPeriode->format("Y-m-d")."' and pd.deleted=''
  order by pd.creationDate asc
  ")) {

    //constituer le tableau
    foreach ($lr as $v) {
        if ($v['instance']==0) {
            $tabReg[$v['id']]=$v;
        } else {
            $tabReg[$v['instance']][$v['name']]=$v['value'];
        }
    }
    //tableau des totaux
    $tabTot=array(
      'regleCheque' => '',
      'regleCB' => '',
      'regleEspeces' => '',
      'regleFacture' => '',
      'regleTiersPayeur' => '');

    //faire quelques calculs
    foreach ($tabReg as $k=>$v) {
        $tabReg[$k]['dejaPaye']=$v['regleCheque']+$v['regleCB']+$v['regleEspeces']+$v['regleTiersPayeur'];

        $tabTot['regleCheque']=$tabTot['regleCheque']+$v['regleCheque'];
        $tabTot['regleCB']=$tabTot['regleCB']+$v['regleCB'];
        $tabTot['regleEspeces']=$tabTot['regleEspeces']+$v['regleEspeces'];
        $tabTot['regleFacture']=$tabTot['regleFacture']+$v['regleFacture'];
        $tabTot['regleTiersPayeur']=$tabTot['regleTiersPayeur']+$v['regleTiersPayeur'];
    }

    //transmission à la page
    $p['page']['tabReg']=$tabReg;
    $p['page']['tabTot']=$tabTot;
}
