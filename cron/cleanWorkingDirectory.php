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
 * Cron : nettoyage du répertoire de travail
 * idéalement, avant sauvegarde quotidienne
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

// pour le configurateur de cron
if (isset($p)) {
    $p['page']['availableCrons']['cleanWorkingDir']=array(
        'task' => 'Nettoyage',
        'defaults' => array('m'=>'0','h'=>'20','M'=>'*','dom'=>'*','dow'=>'*'),
        'description' => 'Nettoyage du répertoire de travail');
    return;
}

ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");
session_start();

$homepath=getcwd().'/';

/////////// Composer class auto-upload
require $homepath.'vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
    global $homepath;
    include $homepath.'class/' . $class . '.php';
});


/////////// Config loader
$p['config']=yaml_parse_file($homepath.'config/config.yml');
$p['homepath']=$homepath;

/////////// SQL connexion
$mysqli=msSQL::sqlConnect();

$p['config']=array_merge($p['config'], msConfiguration::getAllParametersForUser());

/////////// utilisateurs potentiels et leur répertoire
if ($usersTab= msSQL::sql2tabSimple("select p.id from people as p where p.pass!='' order by p.id")) {
    foreach ($usersTab as $userID) {
        if (is_numeric($userID)) {
            // repertoire de travail
            msTools::rmdir_recursive($p['config']['workingDirectory'].$userID);
            /////////// repertoire de travail apicrypt
            msTools::rmdir_recursive($p['config']['apicryptCheminFichierNC'].$userID);
            msTools::rmdir_recursive($p['config']['apicryptCheminFichierC'].$userID);
            // fichier worklist dicom
            @unlink($p['config']['dicomWorkListDirectory']."workList".$userID.".wl");
        }
    }
}

///////// fichier patientsOfTheDay
unlink($p['config']['workingDirectory'].$p['config']['agendaLocalPatientsOfTheDay']);
