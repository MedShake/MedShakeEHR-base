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
 */

//admin uniquement
if (!msUser::checkUserIsAdmin()) {
    $template="forbidden";
} else {
    $debug='';
    $template='configDefaultUsersParams';
    $p['page']['params']=array(
      'MedShakeEHR'=>array(
        'Serveur'=>array(
          'protocol'=>$p['configDefaut']['protocol'],
          'host'=>$p['configDefaut']['host'],
          'urlHostSuffixe'=>$p['configDefaut']['urlHostSuffixe'],
          'webDirectory'=>$p['configDefaut']['webDirectory'],
          'stockageLocation'=>$p['configDefaut']['stockageLocation'],
          'backupLocation'=>$p['configDefaut']['backupLocation'],
          'workingDirectory'=>$p['configDefaut']['workingDirectory'],
          'cookieDomain'=>$p['configDefaut']['cookieDomain'],
          'cookieDuration'=>$p['configDefaut']['cookieDuration'],
          'fingerprint'=>$p['configDefaut']['fingerprint'],
        ),
        'Serveur MySQL'=>array( 
          'sqlServeur'=>$p['configDefaut']['sqlServeur'],
          'sqlBase'=>$p['configDefaut']['sqlBase'],
          'sqlUser'=>$p['configDefaut']['sqlUser'],
          'sqlPass'=>$p['configDefaut']['sqlPass'],
          'sqlVarPassword'=>$p['configDefaut']['sqlVarPassword'],
        ),
        'Options'=>array(
          'PraticienPeutEtrePatient'=>$p['configDefaut']['PraticienPeutEtrePatient']?'true':'false',
          'administratifSecteurHonoraires'=>$p['configDefaut']['administratifSecteurHonoraires'],
          'administratifPeutAvoirFacturesTypes'=>$p['configDefaut']['administratifPeutAvoirFacturesTypes'],
          'administratifPeutAvoirPrescriptionsTypes'=>$p['configDefaut']['administratifPeutAvoirPrescriptionsTypes'],
          'administratifPeutAvoirAgenda'=>$p['configDefaut']['administratifPeutAvoirAgenda'],
          'administratifPeutAvoirRecettes'=>$p['configDefaut']['administratifPeutAvoirRecettes'],
          'administratifComptaPeutVoirRecettesDe'=>$p['configDefaut']['administratifComptaPeutVoirRecettesDe'],
        ),
        'Modèles de documents'=>array(
          'templatesPdfFolder'=>$p['configDefaut']['templatesPdfFolder'],
          'templateDefautPage'=>$p['configDefaut']['templateDefautPage'],
          'templateOrdoHeadAndFoot'=>$p['configDefaut']['templateOrdoHeadAndFoot'],
          'templateOrdoBody'=>$p['configDefaut']['templateOrdoBody'],
          'templateOrdoALD'=>$p['configDefaut']['templateOrdoALD'],
          'templateCrHeadAndFoot'=>$p['configDefaut']['templateCrHeadAndFoot'],
          'templateCourrierHeadAndFoot'=>$p['configDefaut']['templateCourrierHeadAndFoot'],
         ),
         'Phonecapture'=>array(
          'phonecaptureFingerprint'=>$p['configDefaut']['phonecaptureFingerprint'],
          'phonecaptureCookieDuration'=>$p['configDefaut']['phonecaptureCookieDuration'],
          'phonecaptureResolutionWidth'=>$p['configDefaut']['phonecaptureResolutionWidth'],
          'phonecaptureResolutionHeight'=>$p['configDefaut']['phonecaptureResolutionHeight'],
        ),
        'Service d\'affichage'=>array(
          'templatesFolder'=>$p['configDefaut']['templatesFolder'],
          'twigEnvironnementCache'=>$p['configDefaut']['twigEnvironnementCache']?:'false',
          'twigEnvironnementAutoescape'=>$p['configDefaut']['twigEnvironnementAutoescape']?:'false',
        ),
      ),
      'Services tiers'=>array(
        'Mail'=>array(
          'smtpTracking'=>$p['configDefaut']['smtpTracking'],
          'smtpFrom'=>$p['configDefaut']['smtpFrom'],
          'smtpFromName'=>$p['configDefaut']['smtpFromName'],
          'smtpHost'=>$p['configDefaut']['smtpHost'],
          'smtpPort'=>$p['configDefaut']['smtpPort'],
          'smtpSecureType'=>$p['configDefaut']['smtpSecureType'],
          'smtpOptions'=>$p['configDefaut']['smtpOptions'],
          'smtpUsername'=>$p['configDefaut']['smtpUsername'],
          'smtpPassword'=>$p['configDefaut']['smtpPassword'],
          'smtpDefautSujet'=>$p['configDefaut']['smtpDefautSujet'],
          ),
        'ApiCrypt'=>array(
          'apicryptCheminInbox'=>$p['configDefaut']['apicryptCheminInbox'],
          'apicryptCheminArchivesInbox'=>$p['configDefaut']['apicryptCheminArchivesInbox'],
          'apicryptInboxMailForUserID'=>$p['configDefaut']['apicryptInboxMailForUserID'],
          'apicryptCheminFichierNC'=>$p['configDefaut']['apicryptCheminFichierNC'],
          'apicryptCheminFichierC'=>$p['configDefaut']['apicryptCheminFichierC'],
          'apicryptCheminVersClefs'=>$p['configDefaut']['apicryptCheminVersClefs'],
          'apicryptCheminVersBinaires'=>$p['configDefaut']['apicryptCheminVersBinaires'],
          'apicryptUtilisateur'=>$p['configDefaut']['apicryptUtilisateur'],
          'apicryptAdresse'=>$p['configDefaut']['apicryptAdresse'],
          'apicryptSmtpHost'=>$p['configDefaut']['apicryptSmtpHost'],
          'apicryptSmtpPort'=>$p['configDefaut']['apicryptSmtpPort'],
          'apicryptPopHost'=>$p['configDefaut']['apicryptPopHost'],
          'apicryptPopPort'=>$p['configDefaut']['apicryptPopPort'],
          'apicryptPopUser'=>$p['configDefaut']['apicryptPopUser'],
          'apicryptPopPass'=>$p['configDefaut']['apicryptPopPass'],
          'apicryptDefautSujet'=>$p['configDefaut']['apicryptDefautSujet'],
        ),
        'Fax'=>array(
          'faxService'=>$p['configDefaut']['faxService'],
          'ecofaxMyNumber'=>$p['configDefaut']['ecofaxMyNumber'],
          'ecofaxPass'=>$p['configDefaut']['ecofaxPass'],
        ),
        'DICOM'=>array(
          'dicomHost'=>$p['configDefaut']['dicomHost'],
          'dicomPrefixIdPatient'=>$p['configDefaut']['dicomPrefixIdPatient'],
          'dicomWorkListDirectory'=>$p['configDefaut']['dicomWorkListDirectory'],
          'dicomWorkingDirectory'=>$p['configDefaut']['dicomWorkingDirectory'],
          'dicomAutoSendPatient2Echo'=>$p['configDefaut']['dicomAutoSendPatient2Echo'],
          'dicomDiscoverNewTags'=>$p['configDefaut']['dicomDiscoverNewTags'],
        ),
        'Agenda'=>array(
          'agendaService'=>$p['configDefaut']['agendaService'],
          'agendaDistantLink'=>$p['configDefaut']['agendaDistantLink'],
          'agendaDistantPatientsOfTheDay'=>$p['configDefaut']['agendaDistantPatientsOfTheDay'],
          'agendaLocalPatientsOfTheDay'=>$p['configDefaut']['agendaLocalPatientsOfTheDay'],
          'agendaNumberForPatientsOfTheDay'=>$p['configDefaut']['agendaNumberForPatientsOfTheDay'],
        ),
        'Rappels mail'=>array(
          'mailRappelLogCampaignDirectory'=>$p['configDefaut']['mailRappelLogCampaignDirectory'],
          'mailRappelDaysBeforeRDV'=>$p['configDefaut']['mailRappelDaysBeforeRDV'],
        ),
        'SMS'=>array(
          'smsProvider'=>$p['configDefaut']['smsProvider'],
          'smsLogCampaignDirectory'=>$p['configDefaut']['smsLogCampaignDirectory'],
          'smsDaysBeforeRDV'=>$p['configDefaut']['smsDaysBeforeRDV'],
          'smsCreditsFile'=>$p['configDefaut']['smsCreditsFile'],
          'smsSeuilCreditsAlerte'=>$p['configDefaut']['smsSeuilCreditsAlerte'],
          'smsTpoa'=>$p['configDefaut']['smsTpoa'],
        )
      )
    );

    $p['page']['commentaires']=array(
      'PraticienPeutEtrePatient'=>'si false, il faudra créer une fiche patient pour le praticien',
      'templatesPdfFolder'=>'dossier par défaut',
      'templateDefautPage'=>'modèle par defaut',
      'templateOrdoHeadAndFoot'=>'ordonnance',
      'templateOrdoBody'=>'ordonnance',
      'templateOrdoALD'=>'ordonnance',
      'templateCrHeadAndFoot'=>'compte rendu',
      'templateCourrierHeadAndFoot'=>'courrier et certificat',
      'agendaDistantLink'=>'si agendaService est actif, alors agendaDistantLink doit être vide',
      'twigEnvironnementCache'=>"false ou chemin (ex: /tmp/templates_cache/)"
    );
}
