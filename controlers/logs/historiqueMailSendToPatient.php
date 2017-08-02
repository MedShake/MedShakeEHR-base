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
 * Logs : présente l'historique des mails envoyés à un patient et l'état de réception
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template="historiqueMailSendToPatient";

$patient = new msPeople();
$patient->setToID($match['params']['patientID']);
$p['page']['patientData']= $patient->getSimpleAdminDatas();
$p['page']['patientData']['id']=$match['params']['patientID'];

$mj = new msMailTracking();
$mj->set_contactEmail($p['page']['patientData'][4]);
$mj->getListMessagesSendedToContact();
$mj->addCampaignDataToMessagesList();
$p['page']['listeMessages']=$mj->get_contactMessagesList();
