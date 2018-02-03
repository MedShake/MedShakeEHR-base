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
 * Login : loguer ou renvoyer
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

unset($_SESSION['formErreursReadable'], $_SESSION['formErreurs'], $_SESSION['formValues']);

$formIN=$_POST['formIN'];

//construc validation rules
$form = new msForm();
$form->setformIDbyName($formIN);
$form->setPostdatas($_POST);
$validation=$form->getValidation();



if ($validation === false) {
    msTools::redirRoute('userLogIn');
} else {

    //check login
    $user = new msUser();
    if (!$user->checkLogin($_POST['p_username'], $_POST['p_password'])) {
        unset($_SESSION['form'][$formIN]);
        $message='Nous n\'avons pas trouvé d\'utilisateur correspondant';
        if (!in_array($message, $_SESSION['form'][$formIN]['validationErrorsMsg'])) {
            $_SESSION['form'][$formIN]['validationErrorsMsg'][]=$message;
        }
        $validation = false;
    }

    //do login
    if ($validation != false) {
        $user-> doLogin();
        unset($_SESSION['form'][$formIN]);

        if (msSQL::sqlUniqueChamp("SELECT rank FROM people WHERE name='".$_POST['p_username']."' limit 1")) {
            //compare les versions installées et les versions indiquées dans la bdd 
            $modules=msSQL::sql2tab("SELECT name, value as version FROM system WHERE groupe='module'");
            //on fait la liste des patches à appliquer        
            $moduleUpdateFiles=[];
            foreach ($modules as $module) {
                $installed=file_get_contents('../versionMedShakeEHR-'.$module['name'].'.txt');
                if (trim($installed," \t\n\r\0\x0B") == trim($module['version'])) {
                  continue;
                }
                $updateFiles=glob('../upgrade/'.$module['name'].'/sqlUpgrade_*.sql');
                foreach ($updateFiles as $k=>$file) {
                    if (preg_match('/sqlUpgrade_(.+)_(.+)/', $file, $matches) and $matches[1] >= $module['version']) {
                        $moduleUpdateFiles[$module['name']][]=$updateFiles[$k];
                    }
                }
            }
            //s'il y a des patches à appliquer
            if (count($moduleUpdateFiles)) {
                msSQL::sqlQuery("UPDATE system SET value='maintenance' WHERE name='state' and groupe='system'");
                //on fait une sauvegarde de la base
                exec('mysqldump -u '.$p['config']['sqlUser'].' -p'.$p['config']['sqlPass'].' '.$p['config']['sqlBase'].' > '.$p['config']['backupLocation'].$p['config']['sqlBase'].'_'.date('Y-m-d H:i:s').'-avant update.sql');
                //puis on applique les patches en commençant par ceux de base s'il y en a
                if (array_key_exists($moduleUpdateFiles, 'base')) {
                    foreach ($moduleUpdateFiles['base'] as $file) {
                        exec('mysql -u '.$p['config']['sqlUser'].' -p'.$p['config']['sqlPass'].' --default-character-set=utf8 '.$p['config']['sqlBase'].' < '.$file);
                    }
                    unset($moduleUpdateFiles['base']);
                }
                foreach ($moduleUpdateFiles as $k=>$module) {
                    foreach ($module as $file) {
                        exec('mysql -u '.$p['config']['sqlUser'].' -p'.$p['config']['sqlPass'].' --default-character-set=utf8 '.$p['config']['sqlBase'].' < '.$file);
                    }
                }
                msSQL::sqlQuery("UPDATE system SET value='normal' WHERE name='state' and groupe='system'");
            }
        }
        msTools::redirection('/patients/');
    } else {
        $form->savePostValues2Session();
        msTools::redirRoute('userLogIn');
    }
}
