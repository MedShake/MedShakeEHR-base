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
 * Config : installer un plugin
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

/**
 * Inclure les scripts php éventuels liés au dump
 * @param  string $file   chemin + fichier
 * @param  string $prefix préfixe pour le pre ou post update
 * @return void
 */
function includePhp($file, $suffixe) {
  global $p;
  if($suffixe == '_pre' or $suffixe == '_post' ) {
    $file=str_replace('.sql', $suffixe.'.php', $file);
    if(is_file($file)) include($file);
  }
}

if(!empty($p['config']['sqlServeur'])) {
  $sqlParams = array(
    'sqlServeur'=> $p['config']['sqlServeur'],
    'sqlUser' => $p['config']['sqlUser'],
    'sqlPass' => $p['config']['sqlPass'],
    'sqlBase' => $p['config']['sqlBase']
  );

} elseif(!empty($_SERVER['RDS_HOSTNAME'])) {
  $sqlParams = array(
    'sqlServeur'=> $_SERVER['RDS_HOSTNAME'],
    'sqlUser' => $_SERVER['RDS_USERNAME'],
    'sqlPass' => $_SERVER['RDS_PASSWORD'],
    'sqlBase' => $_SERVER['RDS_DB_NAME']
  );
} else {
  die();
}

$fichier=$_FILES['file'];
$mimetype=msTools::getmimetype($fichier['tmp_name']);
if ($mimetype!='application/zip') {
    die("Erreur: Le fichier n'est pas un fichier zip");
}
if (!is_writable($p['config']['webDirectory'])) {
    die("Erreur: www-data n'a pas les droits d'écriture sur le dossier ".$p['config']['webDirectory']);
}
if (!is_writable($p['homepath'])) {
    die("Erreur: www-data n'a pas les droits d'écriture sur le dossier ".$p['homepath']);
}

$installedPlugins=(array)msPlugins::getInstalledPluginsVersions();

$pluginName = false;
$zip = new ZipArchive;
if ($zip->open($fichier['tmp_name'])) {
    // nom
    $pluginName = trim($zip->getFromName(".pluginMedShakeEHR"));
    if(!is_string($pluginName) or strlen($pluginName) > 30 or strlen($pluginName) < 3) die("Erreur: le nom du plugin ne peut être déterminé ou est invalide");

    // infos plugin
    $pluginInfos = Spyc::YAMLLoad($zip->getFromName('config/plugins/'.$pluginName.'/aboutPlugin'.ucfirst( $pluginName).'.yml'));
    if(!isset($pluginInfos['version'])) die("Erreur: numéro de version du plugin manquant");

    // liste fichiers
    $pluginZipContent=[];
    for( $i = 0; $i < $zip->numFiles; $i++ ){
        $infos = $zip->statIndex($i);
        $pluginZipContent[]=$infos['name'];
    }

    // décompaction
    if ($pluginName != false and count($pluginZipContent)>0) {
        if ($zip->extractTo($p['homepath'])) {
            // log des fichiers extraits
            $pluginFilesInstalled = $p['homepath'].'config/plugins/'.$pluginName.'/unzipFiles_'.$pluginInfos['version'].'.txt';
            file_put_contents($pluginFilesInstalled, implode("\n", $pluginZipContent));

            $zip->close();

            if ($p['config']['webDirectory']!=$p['homepath'].'public_html/') {
                exec('cp -r '.$p['homepath'].'public_html/* '.$p['config']['webDirectory']);
                exec('rm -rf '.$p['homepath'].'public_html/');
            }

            @unlink($p['homepath'].'public_html/install.php');
            @unlink($p['homepath'].'.pluginMedShakeEHR');
        } else {
          $zip->close();
          die("Erreur: une erreur est survenue durant la décompression du fichier");
        }
    } else {
      $zip->close();
      die("Erreur: Le fichier n'est pas un fichier MedShakeEHR");
    }
} else {
  die("Erreur: le fichier n'a pas pu être ouvert pour une raison inconnue");
}


// mise à jour
if(key_exists($pluginName, $installedPlugins)) {

  $updateFiles=glob($p['homepath'].'config/plugins/'.$pluginName.'/sqlUpgrade_*.sql');
  $sqlUpdateFiles=[];
  foreach ($updateFiles as $k=>$file) {
      if (preg_match('/sqlUpgrade_(.+)_(.+)/', $file, $matches) and version_compare($matches[1],  $installedPlugins[$pluginName], '>=')) {
          $sqlUpdateFiles[$matches[1]]=$updateFiles[$k];
      }
  }


  if(!empty($sqlUpdateFiles)) {
    uksort($sqlUpdateFiles, 'version_compare');
    foreach ($sqlUpdateFiles as $file) {
        includePhp($file, '_pre');
        exec('mysql -h'.escapeshellarg($sqlParams['sqlServeur']).' -u '.escapeshellarg($sqlParams['sqlUser']).' -p'.escapeshellarg($sqlParams['sqlPass']).' --default-character-set=utf8 '.escapeshellarg($sqlParams['sqlBase']).' 2>&1 < '.$file, $output);
        includePhp($file, '_post');
    }
  }

}
// installation
else {
  $fileSqlInstall = $p['homepath'].'config/plugins/'.$pluginName.'/sqlInstall.sql';
  exec('mysql -h'.escapeshellarg($sqlParams['sqlServeur']).' -u '.escapeshellarg($sqlParams['sqlUser']).' -p'.escapeshellarg($sqlParams['sqlPass']).' --default-character-set=utf8 '.escapeshellarg($sqlParams['sqlBase']).' 2>&1 < '.$fileSqlInstall, $output);
}

exit("ok");
