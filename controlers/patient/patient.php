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
 * Patient : la page du dossier patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$debug='';
$template="patient";

//le patient
$patient = new msPeople();
$patient->setToID($match['params']['patient']);
$p['page']['patient']['id']=$match['params']['patient'];
$p['page']['patient']['administrativeDatas']=$patient->getAdministrativesDatas();
$p['page']['patient']['administrativeDatas'][8]['age']=$patient->getAge();

// le formulaire d'édition de ses données admin
$formpatient = new msForm();
$formpatient->setFormIDbyName('baseNewPatient');
$formpatient->setPrevalues($patient->getSimpleAdminDatas());
$p['page']['formEditAdmin']=$formpatient->getForm();

//type du dossier
$p['page']['patient']['dossierType']=msSQL::sqlUniqueChamp("select type from people where id='".$match['params']['patient']."' limit 1");

//historique du jour des consultation du patient
$p['page']['patient']['today']=$patient->getToday();

//historique complet des consultation du patient
$p['page']['patient']['historique']=$patient->getHistorique();

//les certificats
$certificats=new msData();
$p['page']['modelesCertif']=$certificats->getDataTypesFromCatName('catModelesCertificats', ['id','label']);
//les courriers
$p['page']['modelesCourrier']=$certificats->getDataTypesFromCatName('catModelesCourriers', ['id','label']);

//les correspondants et liens familiaux
$p['page']['correspondants']=$patient->getRelationsWithPros();
$p['page']['liensFamiliaux']=$patient->getRelationsWithOtherPatients();
