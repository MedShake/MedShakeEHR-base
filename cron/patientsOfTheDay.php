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
 * Cron : relève le JSON patients du jour fourni par soft tiers
 * Doit fournir par rdv :
 * - id : numero du dossier patient
 * - identite : identité patient
 * - type : type de consultation
 * - heure : heure au format %h:%i
 * Le tableau json doit être pré trier asc.
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

// pour le configurateur de cron
if (isset($p)) {
    $p['page']['availableCrons']['patientsOfTheDay']=array(
        'task' => 'Patients du jour',
        'defaults' => array('m'=>'0','h'=>'8','M'=>'*','dom'=>'*','dow'=>'1,2,3,4,5,6'),
        'description' => 'Enregistre la liste des patients du jour depuis une source externe.');
    return;
}

ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");
session_start();

$homepath=getcwd().'/';

////////// Composer class auto-upload
require $homepath.'vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
    global $homepath;
    include $homepath.'class/' . $class . '.php';
});


/////////// Config loader
$p['configDefault']=$p['config']=yaml_parse_file($homepath.'config/config.yml');
$p['homepath']=$homepath;

/////////// SQL connexion
$mysqli=msSQL::sqlConnect();

$users=msPeople::getUsersWithSpecificParam('agendaDistantPatientsOfTheDay');

foreach ($users as $userID=>$value) {
    /////////// config pour l'utilisateur concerné
    $p['config']=array_merge($p['configDefault'], msConfiguration::getAllParametersForUser(['id'=>$userID]));

    /// enregistre le fichier sous le nom déterminé en config
    if(isset($p['config']['agendaDistantPatientsOfTheDay']) and isset($p['config']['agendaLocalPatientsOfTheDay'])) {
      msExternalData::fileSaveLocal($p['config']['agendaDistantPatientsOfTheDay'], $p['config']['workingDirectory'].$p['config']['agendaLocalPatientsOfTheDay']);
    }
}
