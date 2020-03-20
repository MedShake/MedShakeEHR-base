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
 * Patient : onglet Bio
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template="inc-patientTabBio";

if(!is_numeric($match['params']['patientID'])) die;
$p['page']['patient']['id']=$match['params']['patientID'];

// sortir dates distincts
$p['page']['datesBio']=msSQL::sql2tabSimple("select distinct(date) from hprim where toID='".$p['page']['patient']['id']."' order by date desc ");

// déterminer la date courante
if (isset($_POST['param']['dateBio'])) {
    $p['page']['dateCouranteBio']=$_POST['param']['dateBio'];
} elseif (!empty($p['page']['datesBio'])) {
    $p['page']['dateCouranteBio']=$p['page']['datesBio'][0];
} else {
    $p['page']['dateCouranteBio']=null;
}

if ($p['page']['dateCouranteBio'] != null) {

    //documents liés
    $p['page']['docsID']=msSQL::sql2tabKey("select h.objetID, o.titre from hprim as h
    left join objets_data as o on o.id=h.objetID
    where h.toID='".$p['page']['patient']['id']."' and h.date='".$p['page']['dateCouranteBio']."'
    group by objetID", 'objetID', 'titre');

    // next et previous
    $keyDateCourante=array_search($p['page']['dateCouranteBio'],$p['page']['datesBio']);
    if(isset($p['page']['datesBio'][$keyDateCourante-1])) $p['page']['dateSuivanteBio']=$p['page']['datesBio'][$keyDateCourante-1];
    if(isset($p['page']['datesBio'][$keyDateCourante+1])) $p['page']['datePrecedBio']=$p['page']['datesBio'][$keyDateCourante+1];

    // sortir bio date courante
    if ($p['page']['bio']=msSQL::sql2tab("select * from hprim where toID='".$p['page']['patient']['id']."' and date='".$p['page']['dateCouranteBio']."' order by id ")) {

      // antériorités
      $analysesExtraites=array_column($p['page']['bio'], 'labelStandard');
      if ($anteriorites=msSQL::sql2tab("select * from hprim where toID='".$match['params']['patientID']."' and date < '".$p['page']['dateCouranteBio']."' and labelStandard in ('".implode("', '", $analysesExtraites)."') order by date desc, id asc ")) {
          foreach ($anteriorites as $v) {
              $p['page']['bioAnt'][$v['date']][$v['labelStandard']]=$v;
          }
          $p['page']['bioAntCount']=count($p['page']['bioAnt']);
          $p['page']['bioAnt']=array_slice($p['page']['bioAnt'], 0, 3, true);
      }
    }
}
