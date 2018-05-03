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
 * LAP : ajax > enregistrer la prescription préétablie
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */
$debug='';

$data=array(
  'cat'=>$_POST['cat'],
  'label'=>$_POST['label'],
  'description'=>json_encode($_POST['ordo']),
  'fromID'=>$p['user']['id'],
  'toID'=>$p['user']['id'],
);

if(msSQL::sqlInsert('prescriptions', $data)) {
  echo json_encode(['statut'=>'ok']);
}
