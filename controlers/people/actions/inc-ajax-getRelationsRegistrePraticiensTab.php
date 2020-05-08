<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2020
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
 * People : ajax > obtenir le tableau de relation registre <-> praticiens
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if($p['config']['optionGeActiverRegistres'] != 'true') {
 die();
}

if(isset($_POST['registreID'])) {
  $registreID=$_POST['registreID'];
} elseif(isset($_GET['registreID'])) {
  $registreID=$_GET['registreID'];
}
$liensPrat = new msPeopleRelations();
$liensPrat->setToID($registreID);

header('Content-Type: application/json');
$liensPrat->setRelationType('relationRegistrePraticien');
$liensPrat->setReturnedPeopleTypes(['pro']);
$praticiens = $liensPrat->getRelations(['identite','titre']);

exit(json_encode(array_merge($praticiens)));
