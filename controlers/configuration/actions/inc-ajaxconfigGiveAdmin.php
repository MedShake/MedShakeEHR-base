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
 * Config : les actions avec reload de page
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if(!is_numeric($_GET['id'])) die;
$actualRank=msSQL::sqlUniqueChamp("select rank from people where id = '".$_GET['id']."' limit 1");

if( $actualRank == 'admin') {
  msSQL::sqlInsert('people', array('id'=>$_GET['id'], 'rank'=>''));
} else {
  msSQL::sqlInsert('people', array('id'=>$_GET['id'], 'rank'=>'admin'));
}

msTools::redirection('/configuration/users/');
