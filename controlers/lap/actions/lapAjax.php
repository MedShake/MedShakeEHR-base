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
 * LAP : les requêtes ajax
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$debug='';
$m=$match['params']['m'];

$acceptedModes=array(
    'cim10search', // chercher des codes CIM10 à partir de mot clé.
    'allergieSearch', // chercher des codes / label allergie à partir de mot clé
    'allergieAdd', // ajouter allergies à un dossier patient
    'allergieDel', // retirer allergies à un dossier patient
    'searchNewMedic', // checher un médicament
    'searchNewMedicDetails', // approfondir la recherche
    'lapPatientLateralDataRefresh', // rafraichier la colonne lat du LAP (patient data)
    'lapInstallPrescription', //installer la prescription dans la modal
    'lapAnalyseFrappePrescription', //analyse de la prescription frappée
    'lapMakeLigneOrdonnance', //généré une ligne d'ordonnance
    'lapOrdoLiveSave', //sauver l'ordonnance courante à chaque modif
    'lapOrdoLiveRestore', //restaurer l'ordonnance courante
    'lapGetPosologies', //obtenir les posologies pour la fenêtre de prescription
    'lapTTenCoursSaveOrUpdateLigne', // sauver / updater une ligne de TT en cours
    'lapTTenCoursGet', //obtenir le tt en cours
    'lapOrdoSave', // sauver ordonnnace
    'lapOrdoHistoriqueGet', // historique des ordonnnaces
    'lapOrdoHistoriqueTTGet', // historique des tt
    'lapOrdoGet', // obtenir l'ordonnnace
    'lapSaveDateEffectiveArretTT',
    'lapOrdoAnalyse', // analyser l'ordo
    'lapOrdoAnalyseResBrut', // voir données brutes pré analyse
    'lapVoirEffetsIndesirables', // voir effets indésirable d'un medic
    'lapMonographieSection', // sortir une section de monographie
    'lapSamRefreshTheSamsZone', // afficher les SAMS ordonnance
    'lapSamAlerteForNew', // produire une alerte SAM
    'lapSamSaveSamComment', // sauver le commentaire patient sur le SAM
    'lapSamToggleForPatient', // basculer l'état dispo / non dispo pour le patient
    'lapOutilsSearchNewMedic', // recherche de médicaments pour lapOutils
    'lapOutilDisplayListSamPatients', //btenir la liste des patients dont la condition du SAM est réalisée dernière ordo
    'lapOutilsSearchPres', //rechercher des prescritions sur critères (outils LAP)
    'lapPresPreGet', // obtenir les prescriptions préétablies
    'lapPresPreGetCat', // obtenir les catégories de prescriptions types
    'modalLapPresPreSaveDo', //enregistrer la prescription type
    'lapPresTypeGetDetails', //obtenir le json de la prescription type
    'lapPresTypeGetDelete' // effacer une prescription type
);

if (!in_array($m, $acceptedModes)) {
    die;
}

//inclusion
if(is_file($p['homepath'].'controlers/lap/actions/inc-ajax-'.$m.'.php')) {
   include('inc-ajax-'.$m.'.php');
}
