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
 * Patient : les requête ajax
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */


$debug='';
$m=$match['params']['m'];

$acceptedModes=array(
    'extractCsForm', // Extraire un form de cs et l'envoyer dans la nouvelle cs
    'extractMailForm', // Extraire le form d'envoi de mail
    'extractOrdoForm', // Extraire le form de rédaction d'ordonnance
    'extractReglementForm', // Extraire le form de règlement
    'completerTitreCs', // Ajouter un complément au titre d'une ligne d'historique
    'importanceCsToogle', // Toggle importance d'une ligne d'historique
    'suppCs', // Marquer une cs comme effacée
    'uploadNewDoc', // Uploader un document dans dossier patient
    'getLigneOrdo', // Obtenir les infos sur une ligne type d'ordonnnace
    'getReglementData', // Obtenir les infos sur un acte pour le réglement
    'getGraphData', //Obtenir l'historique de poids et taille du patient
    'ObjetDet', // obtenir le détail sur un objet (sa version imprimée)
    'prepareEcho', //préparer l'échographe
    'prepareReceptionDoc', //préparer la réception de doc via phonecapture
    'catchLastDicomSrData', //attraper les dernière mesures DICOM pour un patient
    'listPatientDicomStudies', // lister les studies dicom du patient
    'extractMailModele', // Extraire le modele de mail
    'extractCourrierForm', // Extraire l'éditeur de courrier
    'refreshHeaderPatientAdminData', // Mettre à jour les données administratives patient en tête de dossier
    'refreshLatColPatientAtcdData', //rafraichir la colonne atcd
    'saveCsForm', // sauver le formulaire de consultation
    'saveOrdoForm', // sauver une ordonnance
    'saveReglementForm', // sauver une ordonnance
    'changeObjetCreationDate', // changer le creationDate d'un objet
    'getHistorique', // Obtenir l'historique complet
    'getHistoriqueToday', // Obtenir l'historique du jour
    'getGraphData', // Obtenir les data pour les graphs biométrie
    'getGraphDataCardio', // Obtenir les data pour les graphs biométrie cardio
    'getFseData', // obtenir les data nécessaires à l'établissement d'une FSE
    'getFseReturnData', // obtenir les data nécessaires à l'établissement d'une FSE
    'getFilePreviewDocument', // obtenir le html de prévisualisation d'un fichier
    'lapExternePrepare', // préparer le LAP externe
    'lapExterneCheckOrdo', // vérifier la dispo d'une ordo produite par le LAP externe
    'rotateDoc', // rotation image dans aperçu ligne historique
);


//inclusion
if(in_array($m, $acceptedModes) and is_file($p['homepath'].'controlers/patient/actions/inc-ajax-'.$m.'.php')) {
    include('inc-ajax-'.$m.'.php');
} else {
    die();
}
