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
 * LAP : ajax > supprimer une prescription type
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */

$debug = '';
if (!is_numeric($_POST['id'])) die;
if (msSQL::sqlQuery("DELETE from prescriptions where toID = :toID and id = :id limit 1 ", ['toID' => $p['user']['id'], 'id' => $_POST['id']])) {
	echo json_encode(['statut' => 'ok']);
}
