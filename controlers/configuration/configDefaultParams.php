<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00>
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
 * Config : gérer les paramètres de configuration par défaut des utilisateurs
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com> 
 */

//admin uniquement
if (!msUser::checkUserIsAdmin()) {
    $template="forbidden";
} else {
    $debug='';
    $template='configDefaultParams';
    $p['page']['params']=array(
      'MedShakeEHR'=>array(
        'Serveur'=>array(
          'protocol'=>array('value'=>$p['configDefaut']['protocol'],'type'=>'text','typeText'=>'string'),
          'host'=>array('value'=>$p['configDefaut']['host'],'type'=>'text','typeText'=>'string'),
          'urlHostSuffixe'=>array('value'=>$p['configDefaut']['urlHostSuffixe'],'type'=>'text','typeText'=>'string'),
          'webDirectory'=>array('value'=>$p['configDefaut']['webDirectory'],'type'=>'text','typeText'=>'string'),
          'stockageLocation'=>array('value'=>$p['configDefaut']['stockageLocation'],'type'=>'text','typeText'=>'string'),
          'backupLocation'=>array('value'=>$p['configDefaut']['backupLocation'],'type'=>'text','typeText'=>'string'),
          'workingDirectory'=>array('value'=>$p['configDefaut']['workingDirectory'],'type'=>'text','typeText'=>'string'),
          'cookieDomain'=>array('value'=>$p['configDefaut']['cookieDomain'],'type'=>'text','typeText'=>'string'),
          'cookieDuration'=>array('value'=>$p['configDefaut']['cookieDuration'],'type'=>'text','typeText'=>'number'),
          'fingerprint'=>array('value'=>$p['configDefaut']['fingerprint'],'type'=>'text','readonly'=>true,'typeText'=>'string'),
        ),
        'Serveur MySQL'=>array(
          'sqlServeur'=>array('value'=>$p['configDefaut']['sqlServeur'],'type'=>'text','typeText'=>'string'),
          'sqlBase'=>array('value'=>$p['configDefaut']['sqlBase'],'type'=>'text','typeText'=>'string'),
          'sqlUser'=>array('value'=>$p['configDefaut']['sqlUser'],'type'=>'text','typeText'=>'string'),
          'sqlPass'=>array('value'=>$p['configDefaut']['sqlPass'],'type'=>'password','typeText'=>'string'),
          'sqlVarPassword'=>array('value'=>$p['configDefaut']['sqlVarPassword'],'type'=>'text','readonly'=>true,'typeText'=>'string'),
        ),
        'Options'=>array(
          'PraticienPeutEtrePatient'=>array('value'=>$p['configDefaut']['PraticienPeutEtrePatient']?'true':'false','type'=>'text','typeText'=>'boolean','com'=>'si false, il faudra créer une fiche patient pour le praticien'),
          'administratifSecteurHonoraires'=>array('value'=>$p['configDefaut']['administratifSecteurHonoraires'],'type'=>'text','typeText'=>'1 ou 2'),
          'administratifPeutAvoirFacturesTypes'=>array('value'=>$p['configDefaut']['administratifPeutAvoirFacturesTypes'],'type'=>'text','typeText'=>'boolean'),
          'administratifPeutAvoirPrescriptionsTypes'=>array('value'=>$p['configDefaut']['administratifPeutAvoirPrescriptionsTypes'],'type'=>'text','typeText'=>'boolean'),
          'administratifPeutAvoirAgenda'=>array('value'=>$p['configDefaut']['administratifPeutAvoirAgenda'],'type'=>'text','typeText'=>'boolean'),
          'administratifPeutAvoirRecettes'=>array('value'=>$p['configDefaut']['administratifPeutAvoirRecettes'],'type'=>'text','typeText'=>'boolean'),
          'administratifComptaPeutVoirRecettesDe'=>array('value'=>$p['configDefaut']['administratifComptaPeutVoirRecettesDe'],'type'=>'text','typeText'=>'string','com'=>'Liste des id séparés par une virgule'),
        ),
        'Modèles de documents'=>array(
          'templatesPdfFolder'=>array('value'=>$p['configDefaut']['templatesPdfFolder'],'type'=>'text','typeText'=>'string','com'=>'dossier par défaut'),
          'templateDefautPage'=>array('value'=>$p['configDefaut']['templateDefautPage'],'type'=>'text','typeText'=>'string','com'=>'modèle par defaut'),
          'templateOrdoHeadAndFoot'=>array('value'=>$p['configDefaut']['templateOrdoHeadAndFoot'],'type'=>'text','typeText'=>'string','com'=>'ordonnance'),
          'templateOrdoBody'=>array('value'=>$p['configDefaut']['templateOrdoBody'],'type'=>'text','typeText'=>'string','com'=>'ordonnance'),
          'templateOrdoALD'=>array('value'=>$p['configDefaut']['templateOrdoALD'],'type'=>'text','typeText'=>'string','com'=>'ordonnance'),
          'templateCrHeadAndFoot'=>array('value'=>$p['configDefaut']['templateCrHeadAndFoot'],'type'=>'text','typeText'=>'string','com'=>'compte rendu'),
          'templateCourrierHeadAndFoot'=>array('value'=>$p['configDefaut']['templateCourrierHeadAndFoot'],'type'=>'text','typeText'=>'string','com'=>'courrier et certificat'),
         ),
         'Phonecapture'=>array(
          'phonecaptureFingerprint'=>array('value'=>$p['configDefaut']['phonecaptureFingerprint'],'type'=>'text','typeText'=>'string'),
          'phonecaptureCookieDuration'=>array('value'=>$p['configDefaut']['phonecaptureCookieDuration'],'type'=>'text','typeText'=>'number'),
          'phonecaptureResolutionWidth'=>array('value'=>$p['configDefaut']['phonecaptureResolutionWidth'],'type'=>'text','typeText'=>'number'),
          'phonecaptureResolutionHeight'=>array('value'=>$p['configDefaut']['phonecaptureResolutionHeight'],'type'=>'text','typeText'=>'number'),
        ),
        'Service d\'affichage'=>array(
          'templatesFolder'=>array('value'=>$p['configDefaut']['templatesFolder'],'type'=>'text','typeText'=>'string'),
          'twigEnvironnementCache'=>array('value'=>$p['configDefaut']['twigEnvironnementCache']?:'false','type'=>'text','typeText'=>'false/string','com'=>'false ou chemin (ex: /tmp/templates_cache/)'),
          'twigEnvironnementAutoescape'=>array('value'=>$p['configDefaut']['twigEnvironnementAutoescape']?:'false','type'=>'text','typeText'=>'false/string'),
        ),
      ),
      'Services tiers'=>array(
        'Mail'=>array(
          'smtpTracking'=>array('value'=>$p['configDefaut']['smtpTracking'],'type'=>'text','typeText'=>'string'),
          'smtpFrom'=>array('value'=>$p['configDefaut']['smtpFrom'],'type'=>'text','typeText'=>'string'),
          'smtpFromName'=>array('value'=>$p['configDefaut']['smtpFromName'],'type'=>'text','typeText'=>'string'),
          'smtpHost'=>array('value'=>$p['configDefaut']['smtpHost'],'type'=>'text','typeText'=>'string'),
          'smtpPort'=>array('value'=>$p['configDefaut']['smtpPort'],'type'=>'text','typeText'=>'number'),
          'smtpSecureType'=>array('value'=>$p['configDefaut']['smtpSecureType'],'type'=>'text','typeText'=>'string'),
          'smtpOptions'=>array('value'=>$p['configDefaut']['smtpOptions'],'type'=>'text','typeText'=>'string'),
          'smtpUsername'=>array('value'=>$p['configDefaut']['smtpUsername'],'type'=>'text','typeText'=>'string'),
          'smtpPassword'=>array('value'=>$p['configDefaut']['smtpPassword'],'type'=>'password','typeText'=>'string'),
          'smtpDefautSujet'=>array('value'=>$p['configDefaut']['smtpDefautSujet'],'type'=>'text','typeText'=>'string'),
          ),
        'Apicrypt'=>array(
          'apicryptCheminInbox'=>array('value'=>$p['configDefaut']['apicryptCheminInbox'],'type'=>'text','typeText'=>'string'),
          'apicryptCheminArchivesInbox'=>array('value'=>$p['configDefaut']['apicryptCheminArchivesInbox'],'type'=>'text','typeText'=>'string'),
          'apicryptInboxMailForUserID'=>array('value'=>$p['configDefaut']['apicryptInboxMailForUserID'],'type'=>'text','typeText'=>'number'),
          'apicryptCheminFichierNC'=>array('value'=>$p['configDefaut']['apicryptCheminFichierNC'],'type'=>'text','typeText'=>'string'),
          'apicryptCheminFichierC'=>array('value'=>$p['configDefaut']['apicryptCheminFichierC'],'type'=>'text','typeText'=>'string'),
          'apicryptCheminVersClefs'=>array('value'=>$p['configDefaut']['apicryptCheminVersClefs'],'type'=>'text','typeText'=>'string'),
          'apicryptCheminVersBinaires'=>array('value'=>$p['configDefaut']['apicryptCheminVersBinaires'],'type'=>'text','typeText'=>'string'),
          'apicryptUtilisateur'=>array('value'=>$p['configDefaut']['apicryptUtilisateur'],'type'=>'text','typeText'=>'string'),
          'apicryptAdresse'=>array('value'=>$p['configDefaut']['apicryptAdresse'],'type'=>'text','typeText'=>'string'),
          'apicryptSmtpHost'=>array('value'=>$p['configDefaut']['apicryptSmtpHost'],'type'=>'text','typeText'=>'string'),
          'apicryptSmtpPort'=>array('value'=>$p['configDefaut']['apicryptSmtpPort'],'type'=>'text','typeText'=>'number'),
          'apicryptPopHost'=>array('value'=>$p['configDefaut']['apicryptPopHost'],'type'=>'text','typeText'=>'string'),
          'apicryptPopPort'=>array('value'=>$p['configDefaut']['apicryptPopPort'],'type'=>'text','typeText'=>'number'),
          'apicryptPopUser'=>array('value'=>$p['configDefaut']['apicryptPopUser'],'type'=>'text','typeText'=>'string'),
          'apicryptPopPass'=>array('value'=>$p['configDefaut']['apicryptPopPass'],'type'=>'password','typeText'=>'string'),
          'apicryptDefautSujet'=>array('value'=>$p['configDefaut']['apicryptDefautSujet'],'type'=>'text','typeText'=>'string'),
        ),
        'Fax'=>array(
          'faxService'=>array('value'=>$p['configDefaut']['faxService'],'type'=>'text','typeText'=>'string'),
          'ecofaxMyNumber'=>array('value'=>$p['configDefaut']['ecofaxMyNumber'],'type'=>'text','typeText'=>'string'),
          'ecofaxPass'=>array('value'=>$p['configDefaut']['ecofaxPass'],'type'=>'password','typeText'=>'string'),
        ),
        'DICOM'=>array(
          'dicomHost'=>array('value'=>$p['configDefaut']['dicomHost'],'type'=>'text','typeText'=>'string'),
          'dicomPrefixIdPatient'=>array('value'=>$p['configDefaut']['dicomPrefixIdPatient'],'type'=>'text','typeText'=>'string'),
          'dicomWorkListDirectory'=>array('value'=>$p['configDefaut']['dicomWorkListDirectory'],'type'=>'text','typeText'=>'string'),
          'dicomWorkingDirectory'=>array('value'=>$p['configDefaut']['dicomWorkingDirectory'],'type'=>'text','typeText'=>'string'),
          'dicomAutoSendPatient2Echo'=>array('value'=>$p['configDefaut']['dicomAutoSendPatient2Echo'],'type'=>'text','typeText'=>'boolean'),
          'dicomDiscoverNewTags'=>array('value'=>$p['configDefaut']['dicomDiscoverNewTags'],'type'=>'text','typeText'=>'boolean'),
        ),
        'Agenda'=>array(
          'agendaService'=>array('value'=>$p['configDefaut']['agendaService'],'type'=>'text','typeText'=>'string','com'=>'clicRDV ou vide'),
          'agendaDistantLink'=>array('value'=>$p['configDefaut']['agendaDistantLink'],'type'=>'text','typeText'=>'string','com'=>'si agendaService est configuré, alors agendaDistantLink doit être vide'),
          'agendaDistantPatientsOfTheDay'=>array('value'=>$p['configDefaut']['agendaDistantPatientsOfTheDay'],'type'=>'text','typeText'=>'string'),
          'agendaLocalPatientsOfTheDay'=>array('value'=>$p['configDefaut']['agendaLocalPatientsOfTheDay'],'type'=>'text','typeText'=>'string'),
          'agendaNumberForPatientsOfTheDay'=>array('value'=>$p['configDefaut']['agendaNumberForPatientsOfTheDay'],'type'=>'text','typeText'=>'number'),
        ),
        'Rappels mail'=>array(
          'mailRappelLogCampaignDirectory'=>array('value'=>$p['configDefaut']['mailRappelLogCampaignDirectory'],'type'=>'text','typeText'=>'string'),
          'mailRappelDaysBeforeRDV'=>array('value'=>$p['configDefaut']['mailRappelDaysBeforeRDV'],'type'=>'text','typeText'=>'number'),
        ),
        'SMS'=>array(
          'smsProvider'=>array('value'=>$p['configDefaut']['smsProvider'],'type'=>'text','typeText'=>'string'),
          'smsLogCampaignDirectory'=>array('value'=>$p['configDefaut']['smsLogCampaignDirectory'],'type'=>'text','typeText'=>'string'),
          'smsDaysBeforeRDV'=>array('value'=>$p['configDefaut']['smsDaysBeforeRDV'],'type'=>'text','typeText'=>'number'),
          'smsCreditsFile'=>array('value'=>$p['configDefaut']['smsCreditsFile'],'type'=>'text','typeText'=>'string'),
          'smsSeuilCreditsAlerte'=>array('value'=>$p['configDefaut']['smsSeuilCreditsAlerte'],'type'=>'text','typeText'=>'number'),
          'smsTpoa'=>array('value'=>$p['configDefaut']['smsTpoa'],'type'=>'text','typeText'=>'string'),
        )
      )
    );

    ksort($p['page']['params']['Services tiers']);
}
