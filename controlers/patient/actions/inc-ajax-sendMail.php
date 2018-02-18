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
 * Patient > action : envoyer un mail
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 // Apicrypt
 if ($_POST['mailType']=='apicrypt') {
     $fileToInclude=$p['config']['homeDirectory'].'controlers/patient/actions/inc-action-sendMail-'.$_POST['mailType'].'.php';
 }

 // Mail non sécurisé
 elseif ($_POST['mailType']=='ns') {
     // Fichier correspondant au type d'envoi ET au type de service mail
   $fileToInclude=$p['config']['homeDirectory'].'controlers/patient/actions/inc-action-sendMail-'.$_POST['mailType'].'-'.$p['config']['smtpTracking'].'.php';

   // Régression au type d'envoi uniquement
   if (!is_file($fileToInclude)) {
       $fileToInclude=$p['config']['homeDirectory'].'controlers/patient/actions/inc-action-sendMail-'.$_POST['mailType'].'.php';
   }
 }

 // Fax en ligne
 elseif ($_POST['mailType']=='ecofax') {
     // Fichier correspondant au type d'envoi ET au type de service fax
   $fileToInclude=$p['config']['homeDirectory'].'controlers/patient/actions/inc-action-sendMail-'.$_POST['mailType'].'-'.$p['config']['faxService'].'.php';

   // Régression au type d'envoi uniquement
   if (!is_file($fileToInclude)) {
       $fileToInclude=$p['config']['homeDirectory'].'controlers/patient/actions/inc-action-sendMail-'.$_POST['mailType'].'.php';
   }
 }

// Inclusion après vérification
if (is_file($fileToInclude)) {
    include($fileToInclude);
} else {
    echo 'Pas d\'action correspondante';
}
