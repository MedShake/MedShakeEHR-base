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
 * LAP : ajax > sauvegarder automatiquement l'ordo brute en json pour récupération rapide
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */
header('Content-Type: application/json');
$file=$p['config']['workingDirectory'].'/'.$p['user']['id'].'/ordoLive.json';
file_put_contents($file, json_encode($_POST['ordoLive']));
echo json_encode($_POST['ordoLive']);
