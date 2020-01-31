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
 * Config : les requêtes ajax
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

header('Content-Type: application/json');

$m=$match['params']['m'];

$acceptedModes=array(
    'configDataCatCreate', // Création d'une cat pour données
    'configDataTypeCreate', // Création d'un type de données
    'configDelByPrimaryKey', // Effacer dans une table par l'intermédiaire de la primary key
    'configExtractByPrimaryKey', // Effacer dans une table par l'intermédiaire de la primary key
    'configFormsCatCreate', // Création d'une cat pour les forms
    'configFormCreate', // Création d'un form
    'configFormPreview', // Prévisu d'un form
    'configChangeModule', // Change le module d'un utilisateur
    'configChangePassword', // Change le mot de passe d'un utilisateur
    'configGiveAdmin', // Toggle droit d'admin
    'configRevokeUser', // Supprimer un utilisateur
    'configPrescriptionCreate', //Création d'une prescription type
    'configPrescriptionsCatCreate', //Création d'une cat de prescription type
    'configActesCreate', //Création d'un acte
    'configActesBaseCreate', //Création d'un acte de base, ngap ou ccam
    'configActesCatCreate', //Création d'une cat d'actes
    'configTagDicomCreate', //Associer tag dicom et typeID
    'configUploadFichierZoneConfig', //Downloader une clef apicrypt
    'configDeleteApicryptClef', //Delete d'une clef apicrypt
    'configTemplatePDFDelete', //Delete d'un template
    'configAgendaSave', // sauvegarder config agenda
    'configDefaultUsersParams', //Enregistrer la config par défaut des utilisateurs
    'configFormEdit', // Edition du formulaire
    'configCronJobs', //Configurer les crons
    'configInstallModule', // Installer un module
    'configSpecificUserParam', // Attribuer une config spécifique à un utilisateur
    'configUserCreate', // créer un utilisateur
    'configUserParamCreate', // Créer un paramètre dans la configuration spécifique à un utilisateur
    'configUserParamDelete', // Supprimer un paramètre dans la configuration spécifique à un utilisateur
    'configUserTemplateDelete', // Supp d'un template user
    'extractCcamActeData', // extraire les data sur un acte CCAM
    'fixDisplayOrder', // fixer le displayOrder pour les data types en fonction de l'ordre dans un form
    'configUncryptApicryptKey', //déchiffrer une clef Apicrypt
    'configRevoke2faKey',  // révoquer la clef 2FA d'un utilisateur
    'configInstallPlugin' // installation de plugin
);
if (!in_array($m, $acceptedModes)) {
    die;
} else {
  include('inc-ajax-'.$m.'.php');
}

die();
