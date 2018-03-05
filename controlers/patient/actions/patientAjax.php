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
    'saveCsForm', // sauver le formulaire de consultation
    'saveOrdoForm', // sauver une ordonnance
    'saveReglementForm', // sauver une ordonnance
    'changeObjetCreationDate', // changer le creationDate d'un objet
    'getHistorique', // Obtenir l'historique complet
    'getHistoriqueToday'// Obtenir l'historique du jour
);

if (!in_array($m, $acceptedModes)) {
    die;
}

//inclusion
if(is_file($p['config']['homeDirectory'].'controlers/patient/actions/inc-ajax-'.$m.'.php')) {
   include('inc-ajax-'.$m.'.php');
}

//ajouter un complément au titre d'une ligne de l'historique
elseif ($m=='completerTitreCs' and is_numeric($_POST['objetID'])) {
    msObjet::setTitleObjet($_POST['objetID'], $_POST['titre']);
}

//changer l'importance d'une ligne de l'historique
elseif ($m=='importanceCsToogle' and is_numeric($_POST['objetID'])) {
    include('inc-ajax-importanceCsToogle.php');
}

//upload new Doc
elseif ($m=='uploadNewDoc' and is_numeric($_POST['patientID'])) {
    include('inc-ajax-uploadNewDoc.php');
}

//obtenir les ligne d'ordo
elseif ($m=='getLigneOrdo' and is_numeric($_POST['ligneID'])) {
    include('inc-ajax-getLigneOrdo.php');


//obtenir les data sur un règlement
} elseif ($m=='getReglementData' and is_numeric($_POST['acteID'])) {
    include('inc-ajax-getReglementData.php');
}

//Obtenir l'historique de poids et taille du patient
elseif ($m=='getGraphData' and is_numeric($_POST['patientID'])) {
    include('inc-ajax-getGraphData.php');
}

// marquer une ligne de l'historique comme effacée
elseif ($m=='suppCs' and is_numeric($_POST['objetID'])) {
    include('inc-ajax-suppCs.php');
}

// obtenir détails sur objet
elseif ($m=='ObjetDet' and is_numeric($_POST['objetID'])) {
    include('inc-ajax-ObjetDet.php');
}

// préparer l'échographe
elseif ($m=='prepareEcho' and is_numeric($_POST['patientID'])) {
    include('inc-ajax-prepareEcho.php');
}

// préparer la réception de doc par phonecapture
elseif ($m=='prepareReceptionDoc' and is_numeric($_POST['patientID'])) {
    include('inc-ajax-prepareReceptionDoc.php');
}

// attraper les dernières data SR de l'échographe pour un patient
elseif ($m=='catchLastDicomSrData' and is_numeric($_POST['patientID'])) {
    include('inc-ajax-catchLastDicomSrData.php');
}

// lister les studies dicom pour un patient
elseif ($m=='listPatientDicomStudies' and is_numeric($_POST['patientID'])) {
    include('inc-ajax-listPatientDicomStudies.php');
}

// attraper les dernières data SR de l'échographe pour un patient
elseif ($m=='extractMailModele') {
    include('inc-ajax-extractMailModele.php');
}

// extraire l'éditeur de courrier
elseif ($m=='extractCourrierForm') {
    include('inc-ajax-extractCourrierForm.php');
}

// Mettre à jour les données administratives patient en tête de dossier
elseif ($m=='refreshHeaderPatientAdminData') {
    include('inc-ajax-refreshHeaderPatientAdminData.php');
}

// sauver le formulaire de consultation
elseif ($m=='saveCsForm') {
    include('inc-ajax-saveCsForm.php');
}

// envoyer un mail
elseif ($m=='sendMail') {
    include('inc-ajax-sendMail.php');
}

// sauver une ordonnance
elseif ($m=='saveOrdoForm') {
    include('inc-ajax-saveOrdoForm.php');
}

// sauver un règlement
elseif ($m=='saveReglementForm') {
    include('inc-ajax-saveReglementForm.php');
}

 // changer le creationDate d'un objet
elseif ($m=='changeObjetCreationDate') {
    include('inc-ajax-changeObjetCreationDate.php');
}

// Obtenir l'historique
elseif ($m=='getHistorique') {
    include('inc-ajax-getHistorique.php');
}

// Obtenir l'historique du jour
elseif ($m=='getHistoriqueToday') {
    include('inc-ajax-getHistoriqueToday.php');
}
