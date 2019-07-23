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
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

 // Apicrypt 1 & 2
 if ($_POST['mailType']=='apicrypt') {
   if(isset($p['config']['apicryptVersion']) and $p['config']['apicryptVersion']==2) {
     $fileToInclude=$p['homepath'].'controlers/patient/actions/inc-action-sendMail-'.$_POST['mailType'].'2.php';
   } else {
     $fileToInclude=$p['homepath'].'controlers/patient/actions/inc-action-sendMail-'.$_POST['mailType'].'.php';
   }
 }

 // Mail non sécurisé
 elseif ($_POST['mailType']=='ns') {
     // Fichier correspondant au type d'envoi ET au type de service mail
   $fileToInclude=$p['homepath'].'controlers/patient/actions/inc-action-sendMail-'.$_POST['mailType'].'-'.$p['config']['smtpTracking'].'.php';

   // Régression au type d'envoi uniquement
   if (!is_file($fileToInclude)) {
       $fileToInclude=$p['homepath'].'controlers/patient/actions/inc-action-sendMail-'.$_POST['mailType'].'.php';
   }
 }

 // Fax en ligne
 elseif ($_POST['mailType']=='ecofax') {
     // Fichier correspondant au type d'envoi ET au type de service fax
   $fileToInclude=$p['homepath'].'controlers/patient/actions/inc-action-sendMail-'.$_POST['mailType'].'-'.$p['config']['faxService'].'.php';

   // Régression au type d'envoi uniquement
   if (!is_file($fileToInclude)) {
       $fileToInclude=$p['homepath'].'controlers/patient/actions/inc-action-sendMail-'.$_POST['mailType'].'.php';
   }
 }

// Inclusion après vérification
if (is_file($fileToInclude)) {
    include($fileToInclude);
} else {
    die('Erreur: Pas d\'action correspondante');
}
