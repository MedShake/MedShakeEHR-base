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
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");} 

unset($_SESSION['formErreursReadable'], $_SESSION['formErreurs'], $_SESSION['formValues']);
$formIN=$_POST['formIN'];
unset($_SESSION['form'][$formIN]);

$modules=msSQL::sql2tabKey("SELECT name, value as version FROM system WHERE groupe='module'", "name");

$availableInstalls=scandir($p['config']['homeDirectory'].'upgrade/');
$installFiles=[];
//on fait la liste des installations à réaliser
foreach ($availableInstalls as $module) {
    if ($module!='.' and $module!='..' and !array_key_exists($module, $modules)) {
        $installFiles[]=glob($p['config']['homeDirectory'].'upgrade/'.$module.'/sqlInstall.sql');
    }
}
//on fait la liste des patches à appliquer
$moduleUpdateFiles=[];
foreach ($modules as $module) {
    $installed=file_get_contents($p['config']['homeDirectory'].'versionMedShakeEHR-'.$module['name'].'.txt');
    if (trim($installed," \t\n\r\0\x0B") == trim($module['version'])) {
      continue;
    }
    $updateFiles=glob($p['config']['homeDirectory'].'upgrade/'.$module['name'].'/sqlUpgrade_*.sql');
    foreach ($updateFiles as $k=>$file) {
        if (preg_match('/sqlUpgrade_(.+)_(.+)/', $file, $matches) and $matches[1] >= $module['version']) {
            $moduleUpdateFiles[$module['name']][]=$updateFiles[$k];
        }
    }
}
//s'il y a des patches à appliquer
if (count($installFiles) or count($moduleUpdateFiles)) {
    msSQL::sqlQuery("UPDATE system SET value='maintenance' WHERE name='state' and groupe='system'");
    //on fait une sauvegarde de la base
    exec('mysqldump -u '.$p['config']['sqlUser'].' -p'.$p['config']['sqlPass'].' '.$p['config']['sqlBase'].' > '.$p['config']['backupLocation'].$p['config']['sqlBase'].'_'.date('Y-m-d H:i:s').'-avant update.sql');
    //puis on applique les patches en commençant par ceux de base s'il y en a
    if (array_key_exists($moduleUpdateFiles, 'base')) {
        foreach ($moduleUpdateFiles['base'] as $file) {
            exec('mysql -u '.$p['config']['sqlUser'].' -p'.$p['config']['sqlPass'].' --default-character-set=utf8 '.$p['config']['sqlBase'].' 2>&1 < '.$file, $output);
        }
        unset($moduleUpdateFiles['base']);
    }
    foreach ($moduleUpdateFiles as $k=>$module) {
        foreach ($module as $file) {
            exec('mysql -u '.$p['config']['sqlUser'].' -p'.$p['config']['sqlPass'].' --default-character-set=utf8 '.$p['config']['sqlBase'].' 2>&1 < '.$file, $output);
        }
    }
    //enfin, on installe les nouveaux modules
    foreach ($installFiles as $k=>$module) {
        foreach ($module as $file) {
            exec('mysql -u '.$p['config']['sqlUser'].' -p'.$p['config']['sqlPass'].' --default-character-set=utf8 '.$p['config']['sqlBase'].' 2>&1 < '.$file, $output);
        }
    }
    msSQL::sqlQuery("UPDATE system SET value='normal' WHERE name='state' and groupe='system'");
}

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
msTools::redirection('/patients/');
