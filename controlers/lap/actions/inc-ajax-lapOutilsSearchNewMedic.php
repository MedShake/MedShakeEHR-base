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
 * LAP : ajax > chercher des médicaments à prescrire
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */
$debug='';
$template='inc-lapOutilsSearchMedicTableResults';


$lap=new msLap;

if($_POST['typeRecherche'] == 'dci' ) {
  $p['page']['medicListeSpe']=$lap->getMedicByName(str_replace(' ', '%',$_POST['term']).'%', '1');
} elseif ($_POST['typeRecherche'] == 'dcispe' ) {
  $p['page']['medicListeSpe']=$lap->getMedicByName(str_replace(' ', '%',$_POST['term']).'%', '3');
} elseif ($_POST['typeRecherche'] == 'spe' ) {
  $p['page']['medicListeSpe']=$lap->getMedicByName(str_replace(' ', '%',$_POST['term']).'%', '0');
} elseif ($_POST['typeRecherche'] == 'suba' ) {
  $p['page']['medicListeSpe']=$lap->getMedicBySub($_POST['term'].'%', 1 ,$_POST['retourRecherche']);
} elseif ($_POST['typeRecherche'] == 'atc' ) {
  $p['page']['medicListeSpe']=$lap->getMedicByATC($_POST['term'], $_POST['retourRecherche']);
}

$p['page']['listeCodeSpeTrouve']=$lap->getListeCodeSpeTrouve();
if(!empty($p['page']['listeCodeSpeTrouve'])) {
  foreach($p['page']['listeCodeSpeTrouve'] as $codeSpe) {
    $p['page']['suba'][$codeSpe]=$lap->getSubtancesActivesTab($codeSpe);
  }
}
