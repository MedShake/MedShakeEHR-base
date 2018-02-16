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
    'catchLastDicomSrData', //attraper les dernière mesures DICOM pour un patient
    'listPatientDicomStudies', // lister les studies dicom du patient
    'extractMailModele', // Extraire le modele de mail
    'extractCourrierForm', // Extraire l'éditeur de courrier
    'refreshHeaderPatientAdminData' // Mettre à jour les données administratives patient en tête de dossier
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
