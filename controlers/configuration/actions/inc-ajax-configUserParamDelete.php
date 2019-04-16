<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00
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
 * Config : supprimer un paramètre pour un utilisateur
 *
 * @author fr33z00 <https://github.com/fr33z00
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

if(is_numeric($_POST['userID']) and is_string($_POST['paramName'])) {
  msSQL::sqlQuery("DELETE FROM configuration WHERE name='".msSQL::cleanVar($_POST['paramName'])."' AND level='user' AND toID='".$_POST['userID']."' limit 1");
}

echo json_encode("ok");
