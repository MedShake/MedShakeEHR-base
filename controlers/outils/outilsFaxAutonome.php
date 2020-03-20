<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * Outils : envoyer un fax autonome
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$template="outilsFaxAutonome";
$debug='';

//fichier déjà présent ?
$destination_file=$p['config']['workingDirectory'].$p['user']['id'].'/pdf2fax.pdf';
if(is_file($destination_file)) {
  $p['page']['destination_file']=str_replace($p['config']['webDirectory'], '', $destination_file);
  $p['page']['destination_file_present'] = TRUE;
} else {
  $p['page']['destination_file_present'] = FALSE;
}
