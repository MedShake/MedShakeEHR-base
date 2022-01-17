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
 * Dropbox
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';

if($p['config']['optionGeActiverDropbox'] != 'true') {
  $template="forbidden";
  return;
}
$template="dropbox";

$dropbox = new msDropbox;

$p['page']['dropBoxesParams'] = $dropbox->getAllBoxesParametersCurrentUser();
foreach($p['page']['dropBoxesParams'] as $box=>$params) {
  $dropbox->setCurrentBoxId($box);
  $p['page']['dropBoxesContent'] = $dropbox->getAllAllowedFilesInBoxes();

}
