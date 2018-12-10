<?php

/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Config : montrer les fichiers de jeu de valeurs installés et leur contenu
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template='configJdvShow';

if(is_dir($p['homepath'].'ressources/JDV/')) {
  foreach(glob($p['homepath'].'ressources/JDV/*.xml') as $file) {
    $path_parts = pathinfo($file);
    $p['page']['files'][$path_parts['filename']]= msExternalData::getJdvDataFromXml($path_parts['basename']);
  }

  if(array_key_exists('JDV_J01-XdsAuthorSpecialty-CI-SIS', $p['page']['files'])) {
    $p['page']['filesPresence']['JDV_J01-XdsAuthorSpecialty-CI-SIS']=true;
  } else {
    $p['page']['filesPresence']['JDV_J01-XdsAuthorSpecialty-CI-SIS']=false;
  }

  if(array_key_exists('JDV_J02-HealthcareFacilityTypeCode_CI-SIS', $p['page']['files'])) {
    $p['page']['filesPresence']['JDV_J02-HealthcareFacilityTypeCode_CI-SIS']=true;
  } else {
    $p['page']['filesPresence']['JDV_J02-HealthcareFacilityTypeCode_CI-SIS']=false;
  }

  if(array_key_exists('JDV_J07-XdsTypeCode_CI-SIS', $p['page']['files'])) {
    $p['page']['filesPresence']['JDV_J07-XdsTypeCode_CI-SIS']=true;
  } else {
    $p['page']['filesPresence']['JDV_J07-XdsTypeCode_CI-SIS']=false;
  }

}
