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
 * LAP : ajax > historique des ordonnances
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$template="inc-lapOrdosHistoriqueGet";
$lap=new msLapOrdo();
$lap->setToID($_POST['patientID']);
$p['page']['histoOrdoAnnee']=$_POST['year'];
$p['page']['histoOrdoAnnees']=$lap->getHistoriqueAnneesDistinctesOrdos();
if($ordos=$lap->getHistoriqueOrdos($p['page']['histoOrdoAnnee'])) {
  foreach($ordos as $ordo) {
    $p['page']['ordos'][strftime('%B', mktime(0, 0, 0, $ordo['mois'], 1, 2018))][]=$ordo;
  }

}
