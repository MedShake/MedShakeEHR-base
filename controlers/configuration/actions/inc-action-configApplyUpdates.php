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
 * Config : Appliquer les mises à jour
 *
 * @author fr33z00 <https://github.com/fr33z00
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
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

$formIN=$_POST['formIN'];
unset($_SESSION['form'][$formIN]);

$modules=msSQL::sql2tabKey("SELECT name, value as version FROM `system` WHERE groupe='module'", "name");

$availableInstalls=scandir($p['homepath'].'upgrade/');
$installFiles=[];
//on fait la liste des installations à réaliser
foreach ($availableInstalls as $module) {
    if ($module!='.' and $module!='..' and !array_key_exists($module, $modules)) {
        $installFiles[]=glob($p['homepath'].'upgrade/'.$module.'/sqlInstall.sql');
    }
}
//on fait la liste des patches à appliquer
$moduleUpdateFiles=[];
foreach ($modules as $module) {
    $installed=file_get_contents($p['homepath'].'versionMedShakeEHR-'.$module['name'].'.txt');
    if (trim($installed," \t\n\r\0\x0B") == trim($module['version'])) {
      continue;
    }
    $updateFiles=glob($p['homepath'].'upgrade/'.$module['name'].'/sqlUpgrade_*.sql');
    foreach ($updateFiles as $k=>$file) {
        if (preg_match('/sqlUpgrade_(.+)_(.+)/', $file, $matches) and version_compare($matches[1],  $module['version'], '>=')) {
            $moduleUpdateFiles[$module['name']][]=$updateFiles[$k];
        }
    }
}
//s'il y a des patches à appliquer
if (count($installFiles) or count($moduleUpdateFiles)) {
    msSQL::sqlQuery("UPDATE `system` SET value='maintenance' WHERE name='state' and groupe='system'");
    //on fait une sauvegarde de la base
    exec('mysqldump -h'.escapeshellarg($sqlParams['sqlServeur']).'  -u '.escapeshellarg($sqlParams['sqlUser']).' -p'.escapeshellarg($sqlParams['sqlPass']).' '.escapeshellarg($sqlParams['sqlBase']).' > '.escapeshellarg($p['config']['backupLocation'].$sqlParams['sqlBase'].'_'.date('Y-m-d_H:i:s').'-avant_update.sql'));
    //puis on applique les patches en commençant par ceux de base s'il y en a
    if (array_key_exists('base', $moduleUpdateFiles)) {
        foreach ($moduleUpdateFiles['base'] as $file) {
            includePhp($file, '_pre');
            exec('mysql -h'.escapeshellarg($sqlParams['sqlServeur']).'  -u '.escapeshellarg($sqlParams['sqlUser']).' -p'.escapeshellarg($sqlParams['sqlPass']).' --default-character-set=utf8 '.escapeshellarg($sqlParams['sqlBase']).' 2>&1 < '.$file, $output);
            includePhp($file, '_post');
        }
        unset($moduleUpdateFiles['base']);
    }
    foreach ($moduleUpdateFiles as $k=>$module) {
        foreach ($module as $file) {
            includePhp($file, '_pre');
            exec('mysql -h'.escapeshellarg($sqlParams['sqlServeur']).'  -u '.escapeshellarg($sqlParams['sqlUser']).' -p'.escapeshellarg($sqlParams['sqlPass']).' --default-character-set=utf8 '.escapeshellarg($sqlParams['sqlBase']).' 2>&1 < '.$file, $output);
            includePhp($file, '_post');
        }
    }
    //enfin, on installe les nouveaux modules
    foreach ($installFiles as $k=>$module) {
        foreach ($module as $file) {
            includePhp($file, '_pre');
            exec('mysql -h'.escapeshellarg($sqlParams['sqlServeur']).'  -u '.escapeshellarg($sqlParams['sqlUser']).' -p'.escapeshellarg($sqlParams['sqlPass']).' --default-character-set=utf8 '.escapeshellarg($sqlParams['sqlBase']).' 2>&1 < '.$file, $output);
            includePhp($file, '_post');
        }
    }
}
msSQL::sqlQuery("UPDATE `system` SET value='normal' WHERE name='state' and groupe='system'");

if (isset($output) and is_array($output)) {
    foreach($output as $k=>$message) {
        if (strpos(strtolower($message), "error")===false) {
            unset($output[$k]);
        }
    }
    if (count($output)) {
        $_SESSION['form'][$formIN]['validationErrorsMsg'][]=implode("<br>", $output);
        msTools::redirRoute('configUpdates');
    }
}
unset($_SESSION['form'][$formIN]);
msTools::redirRoute('configModules');
