<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * Générer le SQL pour export du module de base
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


class msModBaseSqlGenerate extends msSqlGenerate
{

  private $_defautConfigValues = [
    'PraticienPeutEtrePatient'=>'true',
    'VoirRouletteObstetricale'=>'true',
    'administratifSecteurHonorairesCcam'=>'9',
    'administratifSecteurIK'=>'plaine',
    'administratifPeutAvoirFacturesTypes'=>'false',
    'administratifPeutAvoirPrescriptionsTypes'=>'false',
    'administratifPeutAvoirAgenda'=>'true',
    'administratifPeutAvoirRecettes'=>'true',
    'transmissionsPeutVoir'=>'true',
    'transmissionsPeutCreer'=>'true',
    'transmissionsPeutRecevoir'=>'true',
    'transmissionsNbParPage'=>'30',
    'transmissionsPurgerNbJours'=>'365',
    'templateDefautPage'=>'base-page-headAndFoot.html.twig',
    'templateOrdoHeadAndFoot'=>'base-page-headAndFoot.html.twig',
    'templateOrdoBody'=>'ordonnanceBody.html.twig',
    'templateOrdoALD'=>'ordonnanceALD.html.twig',
    'templateCrHeadAndFoot'=>'base-page-headAndNoFoot.html.twig',
    'templateCourrierHeadAndFoot'=>'base-page-headAndNoFoot.html.twig',
    'smtpPort'=>'587',
    'smtpSecureType'=>'tls',
    'smtpOptions'=>'off',
    'smtpDefautSujet'=>'Document vous concernant',
    'apicryptSmtpHost'=>'smtp.intermedic.org',
    'apicryptSmtpPort'=>'587',
    'apicryptPopHost'=>'pop.intermedic.org',
    'apicryptPopPort'=>'110',
    'apicryptDefautSujet'=>'Document concernant votre patient',
    'dicomPrefixIdPatient'=>'1.100.100',
    'dicomAutoSendPatient'=>'false',
    'dicomDiscoverNewTags'=>'true',
    'phonecaptureFingerprint'=>'phonecapture',
    'phonecaptureCookieDuration'=>'31104000',
    'phonecaptureResolutionWidth'=>'1920',
    'phonecaptureResolutionHeight'=>'1080',
    'agendaPremierJour'=>'1',
    'agendaLocalPatientsOfTheDay'=>'patientsOfTheDay.json',
    'agendaNumberForPatientsOfTheDay'=>'0',
    'agendaModePanneauLateral'=>'true',
    'optionGeActiverRappelsRdvMail'=>'false',
    'mailRappelDaysBeforeRDV'=>'3',
    'mailRappelMessage'=>'Bonjour,\n\nNous vous rappelons votre RDV du #jourRdv à #heureRdv avec le Dr #praticien.\nNotez bien qu’aucun autre rendez-vous ne sera donné à un patient n’ayant pas honoré le premier.\n\nMerci de votre confiance,\nÀ bientôt !\n\nP.S. : Ceci est un mail automatique, merci de ne pas répondre.',
    'optionGeActiverRappelsRdvSMS'=>'false',
    'smsRappelMessage'=>'Rappel: Vous avez rdv à #heureRdv le #jourRdv avec le Dr #praticien',
    'smsDaysBeforeRDV'=>'3',
    'smsCreditsFile'=>'creditsSMS.txt',
    'smsSeuilCreditsAlerte'=>'150',
    'smsTpoa'=>'Dr #praticien',
    'optionGeActiverLapInterne'=>'false',
    'theriaqueShowMedicHospi'=>'true',
    'theriaqueShowMedicNonComer'=>'false',
    'lapAlertPatientTermeGrossesseSup46'=>'true',
    'lapAlertPatientAllaitementSup3Ans'=>'true',
    'lapSearchResultsSortBy'=>'nom',
    'lapSearchDefaultType'=>'dci',
    'lapPrintAllergyRisk'=>'true',
    'optionGeActiverVitaleLecture'=>'false',
    'designTopMenuStyle'=>'icones',
    'designTopMenuInboxCountDisplay'=>'true',
    'designTopMenuTransmissionsCountDisplay'=>'true',
    'designTopMenuTransmissionsColorIconeImportant'=>'true',
    'designTopMenuTransmissionsColorIconeUrgent'=>'true',
    'administratifSecteurGeoTarifaire'=>'metro',
    'administratifReglementFormulaires'=>'reglePorteurS1,reglePorteurS2,reglePorteurLibre',
    'signPeriphName'=>'default',
    'administratifSecteurHonorairesNgap'=>'mspe',
    'droitExportPeutExporterPropresData'=>'true',
    'droitExportPeutExporterToutesDataGroupes'=>'false',
    'droitStatsPeutVoirStatsGenerales'=>'true',
    'statsExclusionCats'=>'catTypeCsATCD,csAutres,declencheur',
    'droitDossierPeutCreerPraticien'=>'true',
    'droitDossierPeutVoirUniquementPatientsPropres'=>'false',
    'optionGeAdminActiverLiensRendreUtilisateur'=>'false',
    'droitDossierPeutSupPraticien'=>'true',
    'droitDossierPeutSupPatient'=>'true',
    'droitDossierPeutRetirerPraticien'=>'true',
    'vitaleMode'=>'simple',
    'formFormulaireListingPatients'=>'baseListingPatients',
    'formFormulaireListingPraticiens'=>'baseListingPro',
    'formFormulaireListingRegistres'=>'baseListingRegistres',
    'formFormulaireNouveauPatient'=>'baseNewPatient',
    'formFormulaireNouveauPraticien'=>'baseNewPro',
    'formFormulaireNouveauRegistre'=>'baseNewRegistre',
    'designAppName'=>'MedShakeEHR',
    'optionGePatientOuvrirApresCreation'=>'liens',
    'transmissionsPeutCreer'=>'true',
    'administratifSecteurHonorairesCcam'=>'0',
    'apicryptVersion'=>'1',
    'agendaRefreshDelayMenuPOTD'=>'5',
    'agendaJoursFeriesFichier'=>'jours-feries-seuls.csv',
    'agendaJoursFeriesAfficher'=>'true',
    'agendaRefreshDelayEvents'=>'10',
    'designInboxMailsSortOrder'=>'desc',
    'optionGeLogin2FA'=>'false',
    'optionGeLoginPassMinLongueur'=>'10',
    'optionGeDestructionDataDossierPatient'=>'false',
    'dicomPort'=>'8042',
    'dicomProtocol'=>'http://',
    'optionGeActiverLapExterne'=>'false',
    'utiliserLapExterneName'=>'',
    'optionGeLoginPassAttribution'=>'admin',
    'optionGeLoginPassOnlineRecovery'=>'false',
    'optionGeActiverDropbox'=>'false',
    'designTopMenuDropboxCountDisplay'=>'true',
    'formFormulaireListingGroupes'=>'baseListingGroupes',
    'droitGroupePeutCreerGroupe'=>'false',
    'formFormulaireNouveauGroupe'=>'baseNewGroupe',
    'droitDossierPeutVoirUniquementPatientsGroupes'=>'false',
    'droitDossierPeutVoirUniquementPraticiensGroupes'=>'false',
    'designTopMenuSections'=>"- agenda\n- patients\n- praticiens\n- groupes\n- registres\n- compta\n- inbox\n- dropbox\n- transmissions\n- outils",
    'optionGeActiverAgenda'=>'true',
    'optionGeActiverCompta'=>'true',
    'optionGeActiverInboxApicrypt'=>'true',
    'optionGeActiverApiRest'=>'true',
    'optionGeActiverTransmissions'=>'true',
    'optionGeActiverPhonecapture'=>'true',
    'optionGeActiverDicom'=>'true',
    'droitRegistrePeutCreerRegistre'=>'false',
    'droitGroupePeutVoirTousGroupes'=>'false',
    'optionGeLoginCreationDefaultModule'=>'base',
    'optionGeLoginCreationDefaultTemplate'=>'',
    'optionGePraticienMontrerPatientsLies'=>'true',
    'droitDossierPeutTransformerPraticienEnUtilisateur'=>'false',
    'droitDossierPeutAssignerPropresGroupesPraticienFils'=>'false',
    'optionGeCreationAutoPeopleExportID'=>'true',
    'optionGeExportPratListSelection'=>'true',
    'optionGeActiverRegistres'=>'false',
    'droitRegistrePeutGererGroupes'=>'false',
    'droitRegistrePeutGererAdministrateurs'=>'false',
    'optionGeActiverGroupes'=>'false',
    'groupesNbMaxGroupesParPro'=>'1',
    'optionGeActiverSignatureNumerique'=>'true',
    'optionDossierPatientActiverGestionALD'=>'true',
    'optionDossierPatientActiverCourriersCertificats'=>'true',
    'optionDossierPatientInhiberHistoriquesParDefaut'=>'false',
    'droitDossierPeutRechercherParPeopleExportID'=>'false',
    'optionGeExportDataConsentementOff'=>'true',
  ];

  protected function _getSpecifSql() {

    // création des tables
    $tablesList = array(
      'actes',
      'actes_base',
      'actes_cat',
      'agenda',
      'agenda_changelog',
      'configuration',
      'data_cat',
      'data_types',
      'dicomTags',
      'forms',
      'forms_cat',
      'hprim',
      'inbox',
      'objets_data',
      'people',
      'prescriptions',
      'prescriptions_cat',
      'printed',
      'system',
      'transmissions',
      'transmissions_to'
    );
    foreach($tablesList as $t) {
      $this->_getTableStructure($t);
    }

    // complément form cat
    if($v=msSQL::sqlUnique("select * from $this->_bdd.forms_cat where name='formsProdOrdoEtDoc' limit 1")) {
      unset($v['id']);
      $v['fromID']='1';
      $v['creationDate']="2019-01-01 00:00:00";
      if(!isset($this->_forms_cat_fields)) $this->_forms_cat_fields=$this->_getSqlFieldsPart($v);
      if(!isset($this->_forms_cat_values[$v['name']])) $this->_forms_cat_values[$v['name']]=$this->_getSqlValuesPart($v);
    }

    // param de config
    if($configurations=msSQL::sql2tab("select * from $this->_bdd.configuration where module in ('','base') and level='default'")) {
      foreach($configurations as $configuration) {
        unset($configuration['id']);
        if(isset($this->_defautConfigValues[$configuration['name']])) {
          $configuration['value']=$this->_defautConfigValues[$configuration['name']];
        } else {
          $configuration['value']='';
        }
        if(!isset($this->_configuration_fields)) $this->_configuration_fields=$this->_getSqlFieldsPart($configuration);
        $this->_configuration_values[]=$this->_getSqlValuesPart($configuration);
      }
    }

    // complément data_cat
    if($catData=msSQL::sql2tab("select * from $this->_bdd.data_cat where `type`='base'")) {
    foreach($catData as $v) {
        unset($v['id']);
        $v['fromID']='1';
        $v['creationDate']="2019-01-01 00:00:00";
        if(!isset($this->_data_cat_fields)) $this->_data_cat_fields=$this->_getSqlFieldsPart($v);
        if(!isset($this->_data_cat_values[$v['name']])) $this->_data_cat_values[$v['name']]=$this->_getSqlValuesPart($v);
      }
    }

    // complément system
    $system=msSQL::sqlUnique("select * from $this->_bdd.`system` where name='state' and groupe='system' limit 1");
    unset($system['id']);
    if(isset($this->_system_fields)) $this->_system_fields=$this->_getSqlFieldsPart($system);
    $this->_system_values[]=$this->_getSqlValuesPart($system);

    // people services
    if($services=msSQL::sql2tab("select * from $this->_bdd.people where `type`='service' and `module`='base'")) {
      foreach($services as $service) {
        unset($service['id']);
        $service['pass']='';
        $service['lastLogFingerprint']='';
        $service['fromID']=1;
        $service['registerDate']="2019-01-01 00:00:00";
        $service['lastLogDate']="2019-01-01 00:00:00";
        if(!isset($this->_people_fields)) $this->_people_fields=$this->_getSqlFieldsPart($service);
        $this->_people_values[]=$this->_getSqlValuesPart($service);
      }
    }

    //prescriptions
    if($prescriptions=msSQL::sql2tab("select * from $this->_bdd.prescriptions where label='Ligne vierge'")) {
      foreach($prescriptions as $prescription) {
        unset($prescription['id']);
        $cat=$prescription['cat'];
        $prescription['creationDate']="2019-01-01 00:00:00";
        $prescription['fromID']=1;
        $prescription['toID']=0;
        $prescription['cat']='@catID';
        if(!isset($this->_prescriptions_fields)) $this->_prescriptions_fields=$this->_getSqlFieldsPart($prescription);
        $this->_prescriptions_values[$cat][]=$this->_getSqlValuesPart($prescription);
      }

      $cats=array_unique(array_column($prescriptions, 'cat'));
      $catsData=msSQL::sql2tab("select * from $this->_bdd.prescriptions_cat where id in ('".implode("', '", $cats)."')");

      // prescriptions_cat
      foreach($catsData as $v) {
        unset($v['id']);
        $v['fromID']=1;
        $v['toID']=0;
        $v['creationDate']="2019-01-01 00:00:00";
        if(!isset($this->_prescriptions_cat_fields)) $this->_prescriptions_cat_fields=$this->_getSqlFieldsPart($v);
        if(!isset($this->_prescriptions_cat_values[$v['name']])) $this->_prescriptions_cat_values[$v['name']]=$this->_getSqlValuesPart($v);
      }
    }

  }

}
