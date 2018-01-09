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
 * Compta : les réglements du jour
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$debug='';
$template="comptaToday";

// sortie des typeID dont on va avoir besoin
$name2typeID = new msData();
$name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'reglePorteur']);

//liste praticiens autorisé
$pratIdAutorises[]=$p['user']['id'];
if (isset($p['config']['administratifComptaPeutVoirRecettesDe'])) {
    $pratIdAutorises=array_merge($pratIdAutorises, explode(',', $p['config']['administratifComptaPeutVoirRecettesDe']));
    $pratIdAutorises=array_unique($pratIdAutorises);
}
$p['page']['pratsAuto']=msSQL::sql2tabKey("select p.id, p.rank, o2.value as prenom, o.value as nom
 from people as p
 left join objets_data as o on o.toID=p.id and o.typeID='".$name2typeID['lastname']."' and o.outdated='' and o.deleted=''
 left join objets_data as o2 on o2.toID=p.id and o2.typeID='".$name2typeID['firstname']."' and o2.outdated='' and o2.deleted=''
 where p.id in ('".implode("','", $pratIdAutorises)."') order by p.id", "id");

//sortir les reglements du jour
if ($lr=msSQL::sql2tab("select pd.toID, pd.fromID, pd.id, pd.typeID, pd.value, pd.creationDate, pd.registerDate, pd.instance, p.value as prenom , n.value as nom, a.label, dc.name
  from objets_data as pd
  left join data_types as dc on dc.id=pd.typeID
  left join actes as a on pd.parentTypeID=a.id
  left join objets_data as p on p.toID=pd.toID and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
  left join objets_data as n on n.toID=pd.toID and n.typeID='".$name2typeID['lastname']."' and n.outdated=''  and n.deleted=''
  where pd.id in (
    select pd1.id from objets_data as pd1
    where pd1.typeID = '".$name2typeID['reglePorteur']."'  and DATE(pd1.creationDate) = CURDATE() and pd1.deleted='' and pd1.fromID in ('".implode("','", array_keys($p['page']['pratsAuto']))."'))

  union

  select pd.toID, pd.fromID, pd.id, pd.typeID, pd.value, pd.creationDate, pd.registerDate, pd.instance, p.value as prenom , n.value as nom, a.label, dc.name
  from objets_data as pd
  left join data_types as dc on dc.id=pd.typeID
  left join actes as a on pd.parentTypeID=a.id
  left join objets_data as p on p.toID=pd.toID and p.typeID='".$name2typeID['firstname']."' and p.outdated=''  and p.deleted=''
  left join objets_data as n on n.toID=pd.toID and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
  where pd.instance in (
    select pd2.id from objets_data as pd2
    where pd2.typeID = '".$name2typeID['reglePorteur']."'  and DATE(pd2.creationDate) = CURDATE() and pd2.deleted='' and pd2.fromID in ('".implode("','", array_keys($p['page']['pratsAuto']))."'))

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

    //faire quelques calculs
    foreach ($tabReg as $k=>$v) {
      $tabReg[$k]['dejaPaye']=number_format($v['regleCheque']+$v['regleCB']+$v['regleEspeces']+$v['regleTiersPayeur'], 2,'.','');
      $tabReg[$k]['resteAPaye']=number_format($v['regleFacture']-$tabReg[$k]['dejaPaye'], 2,'.','');
    }

    //séparer en paiement complété et paiement à faire
    foreach ($tabReg as $k=>$v) {
      if($tabReg[$k]['dejaPaye'] != $tabReg[$k]['regleFacture']) $p['page']['tabRegNC'][$k]=$tabReg[$k]; else $p['page']['tabRegC'][$k]=$tabReg[$k];
    }
}
