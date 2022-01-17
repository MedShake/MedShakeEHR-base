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
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';
if (isset($match['params']['m']) and $match['params']['m']=='getTableData') {
    $template="comptaTable";
} else {
    $template="compta";
}

// sortie des typeID dont on va avoir besoin
$data = new msData();
$porteursReglementIds=array_column($data->getDataTypesFromCatName('porteursReglement', ['id']), 'id');
$name2typeID = $data->getTypeIDsFromName(['firstname', 'lastname', 'birthname']);

//gestion des plages
if (!isset($_POST['beginPeriode']) or !$_POST['beginPeriode']) {
    $beginPeriode = new DateTime();
} else {
    $beginPeriode= DateTime::createFromFormat('d/m/Y', $_POST['beginPeriode']);
}
if (!isset($_POST['endPeriode']) or !$_POST['endPeriode']) {
    $endPeriode = new DateTime();
} else {
    $endPeriode= DateTime::createFromFormat('d/m/Y', $_POST['endPeriode']);
}
if (isset($_POST['impayes']) and $_POST['impayes']) {
    $impayes=true;
} else {
    $impayes=false;
}
if (isset($_POST['bilan']) and $_POST['bilan']) {
    $bilan=true;
} else {
    $bilan=false;
}


$p['page']['periode']['begin']=$beginPeriode->format("d/m/Y") ;
$p['page']['periode']['end']=$endPeriode->format("d/m/Y") ;

//quick options
$p['page']['periode']['quickOptions']=array(
"today"=>"Aujourd'hui",
"yesterday"=>"Hier",
"thisweek"=>"Cette semaine",
"lastweek"=>"Semaine dernière",
"thismonth"=>"Ce mois",
"lastmonth"=>"Mois dernier",
"bilanmois"=>"Bilan mois dernier",
"bilanannee"=>"Bilan année dernière",
"impayesmois"=>"Impayés mois",
"impayesannee"=>"Impayés année");


//liste praticiens autorisés
$pratIdAutorises=array();
if($p['config']['administratifPeutAvoirRecettes'] == 'true') $pratIdAutorises[]=$p['user']['id'];
if (isset($p['config']['administratifComptaPeutVoirRecettesDe'])) {
  if (!empty($p['config']['administratifComptaPeutVoirRecettesDe'])) {
    $pratIdAutorises=array_merge($pratIdAutorises, explode(',', $p['config']['administratifComptaPeutVoirRecettesDe']));
    $pratIdAutorises=array_unique($pratIdAutorises);
  }
}
$p['page']['pratsAuto']=msSQL::sql2tabKey("select p.id, p.`rank`, o2.value as prenom, CASE WHEN o.value != '' THEN o.value  ELSE bn.value END as nom
 from people as p
 left join objets_data as o on o.toID=p.id and o.typeID='".$name2typeID['lastname']."' and o.outdated='' and o.deleted=''
 left join objets_data as o2 on o2.toID=p.id and o2.typeID='".$name2typeID['firstname']."' and o2.outdated='' and o2.deleted=''
 left join objets_data as bn on bn.toID=p.id and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
 where p.id in ('".implode("','", $pratIdAutorises)."') order by p.id", "id");

//praticien concerné par la recherche actuelle
if (isset($_POST['prat'])) {
    $p['page']['pratsSelect'][]=$_POST['prat'];
} else {
  if($p['config']['administratifPeutAvoirRecettes'] == 'true') {
    $p['page']['pratsSelect'][]=$p['user']['id'];
  } else {
    $p['page']['pratsSelect'][]=key($p['page']['pratsAuto']);
  }
}

$prat=new msPeople();
$prat->setToID($p['page']['pratsSelect'][0]);
$user=array('id'=>$p['page']['pratsSelect'][0], 'module'=>$prat->getModule());
$p['page']['secteur']=msConfiguration::getParameterValue('administratifSecteurHonorairesCcam', $user);

//sortir les reglements du jour
if ($lr=msSQL::sql2tab("select pd.toID, pd.id, pd.typeID, pd.value, pd.creationDate, pd.registerDate, pd.instance, p.value as prenom , a.label, dc.name,
      CASE WHEN n.value != '' and bn.value !='' THEN concat(n.value, ' (', bn.value,')')
      WHEN n.value != '' THEN n.value
      ELSE bn.value
      END as nom
      from objets_data as pd
      left join data_types as dc on dc.id=pd.typeID
      left join actes as a on pd.parentTypeID=a.id
      left join objets_data as p on p.toID=pd.toID and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
      left join objets_data as n on n.toID=pd.toID and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
      left join objets_data as bn on bn.toID=pd.toID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      where
      pd.id in (
        select pd1.id from objets_data as pd1
        where pd1.typeID in ('".implode("','", $porteursReglementIds)."') and DATE(pd1.creationDate) >= '".$beginPeriode->format("Y-m-d")."' and DATE(pd1.creationDate) <= '".$endPeriode->format("Y-m-d")."' and pd1.deleted='' and pd1.fromID in ('".implode("','", $p['page']['pratsSelect'])."')"
      .($impayes?"and important='y'":"")."
      )
  union
      select pd.toID, pd.id, pd.typeID, pd.value, pd.creationDate, pd.registerDate, pd.instance, p.value as prenom , a.label, dc.name,
      CASE WHEN n.value != '' and bn.value !='' THEN concat(n.value, ' (', bn.value,')')
      WHEN n.value != '' THEN n.value
      ELSE bn.value
      END as nom
      from objets_data as pd
      left join data_types as dc on dc.id=pd.typeID
      left join actes as a on pd.parentTypeID=a.id
      left join objets_data as p on p.toID=pd.toID and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
      left join objets_data as n on n.toID=pd.toID and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
      left join objets_data as bn on bn.toID=pd.toID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      where
      pd.instance in (
        select pd2.id from objets_data as pd2
        where pd2.typeID in ('".implode("','", $porteursReglementIds)."') and DATE(pd2.creationDate) >= '".$beginPeriode->format("Y-m-d")."' and DATE(pd2.creationDate) <= '".$endPeriode->format("Y-m-d")."' and pd2.deleted='' and pd2.fromID in ('".implode("','", $p['page']['pratsSelect'])."')"
      .($impayes?"and important='y'":"")."
      )
  order by creationDate asc
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
      'regleCheque' => 0,
      'regleCB' => 0,
      'regleEspeces' => 0,
      'regleFacture' => 0,
      'regleTiersPayeur' => 0);

    //faire quelques calculs
    foreach ($tabReg as $k=>$v) {

        $v['regleCheque']=(float)$v['regleCheque'];
        $v['regleCB']=(float)$v['regleCB'];
        $v['regleEspeces']=(float)$v['regleEspeces'];
        $v['regleTiersPayeur']=(float)$v['regleTiersPayeur'];
        $v['regleFacture']=(float)$v['regleFacture'];

        $tabReg[$k]['dejaPaye']=$v['regleCheque']+$v['regleCB']+$v['regleEspeces']+$v['regleTiersPayeur'];
        $tabReg[$k]['dejaPayeTab']=array('dejaCheque'=>$v['regleCheque'], 'dejaCB'=>$v['regleCB'], 'dejaEspeces'=>$v['regleEspeces']);

        $tabTot['regleCheque']=$tabTot['regleCheque']+$v['regleCheque'];
        $tabTot['regleCB']=$tabTot['regleCB']+$v['regleCB'];
        $tabTot['regleEspeces']=$tabTot['regleEspeces']+$v['regleEspeces'];
        $tabTot['regleFacture']=$tabTot['regleFacture']+$v['regleFacture'];
        $tabTot['regleTiersPayeur']=$tabTot['regleTiersPayeur']+$v['regleTiersPayeur'];
    }

    //transmission à la page
    if (!$bilan) {
        $p['page']['tabReg']=$tabReg;
    }
    $p['page']['tabTot']=$tabTot;
}
