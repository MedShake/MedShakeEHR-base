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
 * Config : les actions avec reload de page
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */


header('Content-Type: application/json');

$m=$match['params']['m'];

$acceptedModes=array(
    'configUserCreate', //Créer un user
    'configApplyUpdates', // Appliquer les updates
    'configTemplatePDFSave', // sauvegarder un template PDF
    'configUserTemplatesSave', // sauver un template user
    'configRemoveInstallFiles', // supprimer les fichies d'installation
    'configDicomRmWl', // supprimer tous les fichiers worklist actifs
    'configToggleSystemState', // activer / désactiver le mode maintenance
    'configRestartApicrypt2', // relancer le service Apicrypt2
    'configUserApplyTemplate', // appliquer un template de droits à utilisateur existant
	'configAdminerInstall', // installer Adminer dernière version
	'configAdminerRemove', // retirer Adminer
);

if (!in_array($m, $acceptedModes)) {
  die;
} else {
  include('inc-action-'.$m.'.php');
}

die();
