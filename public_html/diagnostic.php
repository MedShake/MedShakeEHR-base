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
 * Script indépendant de test de l'installation pour la détection de problèmes
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 */

 // mod_rewrite
 if(in_array('mod_rewrite', apache_get_modules()) or strpos(shell_exec('/usr/local/apache/bin/apachectl -l'), 'mod_rewrite') !== false ) {
   $infos[] = 'apache mod_rewrite : présent';
 } else {
   $infos[] = 'apache mod_rewrite : absent';
 }

// variable MEDSHAKEEHRPATH et droits
if (($homepath=getenv("MEDSHAKEEHRPATH"))!=false) {
  $infos[] = 'MEDSHAKEEHRPATH : '.$homepath;
  $infos[] = 'homepath owner : '. posix_getpwuid(fileowner($homepath))['name'];
  $infos[] = 'homepath permissions : '.substr(sprintf('%o', fileperms($homepath)), -4);
} else {
  $infos[] = 'MEDSHAKEEHRPATH : absent';
}

// droits sur webdirectory
$infos[] = 'web directory owner : '. posix_getpwuid(fileowner(dirname(__FILE__)))['name'];
$infos[] = 'web directory permissions : '.substr(sprintf('%o', fileperms(dirname(__FILE__))), -4);

// htaccess
if(is_file('.htaccess')) {
    $infos[] = '.htaccess : présent';
    $infos[] = '.htaccess contenu :<br>'.nl2br(file_get_contents('.htaccess'));
} else {
    $infos[] = '.htaccess : absent';
}

// composer public_html -> dossier thirdparty
if(is_dir('thirdparty')) {
  $infos[] = 'dossier thirparty : présent';
  $dirs = array_filter(glob('thirdparty/*'), 'is_dir');
  if(!empty($dirs)) {
    $liste='contenu thirdparty :<ul>';
    foreach($dirs as $dir) {
      $liste.='<li>'.$dir.'</li>';
    }
    $liste.='</ul>';
    $infos[] = $liste;
  }
} else {
  $infos[] = 'dossier thirparty : absent';
}

// composer public_html -> dossier vendor
if(is_dir($homepath.'/vendor')) {
  $infos[] = 'dossier vendor : présent';
  $dirs = array_filter(glob($homepath.'/vendor/*'), 'is_dir');
  if(!empty($dirs)) {
    $liste='contenu vendor :<ul>';
    foreach($dirs as $dir) {
      $liste.='<li>'.$dir.'</li>';
    }
    $liste.='</ul>';
    $infos[] = $liste;
  }
} else {
  $infos[] = 'dossier vendor : absent';
}

echo '<ul><li>';
echo implode('</li><li>', $infos);
echo '</li></ul>';
