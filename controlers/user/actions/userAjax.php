<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://www.github.com/fr33z00>
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
 * user : les requête ajax
 *
 * @author fr33z00 <https://www.github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */


$debug='';
$m=$match['params']['m'];

$acceptedModes=array(
    'updateGroups', // Récupérer la liste des groupes de clicRDV
    'updateCals', // Récupérer la liste des agendas d'un groupe de clicRDV
    'updateConsults', // Récupérer la liste des types de consultation d'un agenda de clicRDV
    'userParametersAgenda', // changer les paramètres d'agenda
    'userParametersConsultations', // changer les paramètres de consultations
    'userParametersClicRdv', // changer les paramètres clicRDV
    'userParametersLap', // changer les paramètres LAP
    'userParametersDisplayListSamPatientsDisabled', // afficher la liste des patients concernés par la blocage d'un SAM
    'userParametersPrescriptionsCatList', // lister les catégories de prescription
    'userParametersPrescriptionsList', // lister les prescriptions types
    'userParametersExtractByPrimaryKey', //extraire d'une table par la primary key
    'userParametersDelByPrimaryKey', // effacer d'une table par la primary key
    'userParametersPrescriptionsCatCreate', // céer une nouvelle catégorie
    'userParametersPrescriptionsCreate', // créer une nouvelle prescription type
    'userParametersAdministratif', // changer les paramètres Administratif
    'cleanSessionFormWarning', // supprimer les alertes formulaire en session
);

//inclusion
if( in_array($m,$acceptedModes) and is_file($p['homepath'].'controlers/user/actions/inc-ajax-'.$m.'.php')) {
    include('inc-ajax-'.$m.'.php');
} else {
    die();
}
