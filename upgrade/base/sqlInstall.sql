
-- création de la table actes
CREATE TABLE IF NOT EXISTS `actes` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `cat` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `label` varchar(250) NOT NULL,
  `shortLabel` varchar(255) DEFAULT NULL,
  `details` text NOT NULL,
  `flagImportant` tinyint(1) NOT NULL DEFAULT '0',
  `flagCmu` tinyint(1) NOT NULL DEFAULT '0',
  `fromID` smallint(5) unsigned NOT NULL,
  `toID` mediumint(6) NOT NULL DEFAULT '0',
  `creationDate` datetime NOT NULL DEFAULT '2018-01-01 00:00:00',
  `active` enum('oui','non') NOT NULL DEFAULT 'oui',
  PRIMARY KEY (`id`),
  KEY `toID` (`toID`),
  KEY `cat` (`cat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table actes_base
CREATE TABLE IF NOT EXISTS `actes_base` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(7) NOT NULL,
  `activite` tinyint(1) NOT NULL DEFAULT '1',
  `phase` tinyint(1) NOT NULL DEFAULT '0',
  `codeProf` varchar(7) DEFAULT NULL,
  `label` text,
  `type` enum('NGAP','CCAM','Libre','mCCAM') NOT NULL DEFAULT 'CCAM',
  `dataYaml` text,
  `tarifUnit` enum('euro','pourcent') NOT NULL DEFAULT 'euro',
  `fromID` mediumint(7) unsigned NOT NULL DEFAULT '1',
  `creationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `code` (`code`,`activite`,`phase`,`type`,`codeProf`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table actes_cat
CREATE TABLE IF NOT EXISTS `actes_cat` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `module` varchar(20) NOT NULL DEFAULT 'base',
  `fromID` smallint(5) unsigned NOT NULL,
  `creationDate` datetime NOT NULL,
  `displayOrder` smallint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `displayOrder` (`displayOrder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table agenda
CREATE TABLE IF NOT EXISTS `agenda` (
  `id` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `externid` int(10) unsigned DEFAULT NULL,
  `userid` smallint(5) unsigned NOT NULL DEFAULT '3',
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `dateAdd` datetime DEFAULT NULL,
  `lastModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `patientid` mediumint(6) unsigned DEFAULT NULL,
  `fromID` mediumint(6) unsigned DEFAULT NULL,
  `statut` enum('actif','deleted') DEFAULT 'actif',
  `absente` enum('non','oui') DEFAULT 'non',
  `attente` enum('non','oui') NOT NULL DEFAULT 'non',
  `motif` text,
  PRIMARY KEY (`id`),
  KEY `patientid` (`patientid`),
  KEY `externid` (`externid`),
  KEY `userid` (`userid`),
  KEY `typeEtUserid` (`type`,`userid`),
  KEY `start` (`start`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table agenda_changelog
CREATE TABLE IF NOT EXISTS `agenda_changelog` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `eventID` int(12) unsigned NOT NULL,
  `userID` smallint(5) unsigned NOT NULL,
  `fromID` smallint(5) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `operation` enum('edit','move','delete','missing','waiting') NOT NULL,
  `olddata` mediumblob,
  PRIMARY KEY (`id`),
  KEY `eventID` (`eventID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table configuration
CREATE TABLE IF NOT EXISTS `configuration` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `level` enum('default','module','user') DEFAULT 'default',
  `toID` int(11) unsigned NOT NULL DEFAULT '0',
  `module` varchar(20) NOT NULL DEFAULT '',
  `cat` varchar(30) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `description` text,
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nameLevel` (`name`,`level`,`module`,`toID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table data_cat
CREATE TABLE IF NOT EXISTS `data_cat` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `groupe` enum('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user','relation','system') NOT NULL DEFAULT 'admin',
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('base','module','user') NOT NULL DEFAULT 'base',
  `fromID` smallint(5) unsigned NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table data_types
CREATE TABLE IF NOT EXISTS `data_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupe` enum('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user','relation','system') NOT NULL DEFAULT 'admin',
  `name` varchar(60) NOT NULL,
  `placeholder` varchar(255) DEFAULT NULL,
  `label` text,
  `description` varchar(255) DEFAULT NULL,
  `validationRules` varchar(255) DEFAULT NULL,
  `validationErrorMsg` varchar(255) DEFAULT NULL,
  `formType` enum('','date','email','number','select','submit','tel','text','textarea','password','checkbox','hidden','range','radio','reset','switch') NOT NULL DEFAULT '',
  `formValues` text,
  `module` varchar(20) NOT NULL DEFAULT 'base',
  `cat` smallint(5) unsigned NOT NULL,
  `fromID` smallint(5) unsigned DEFAULT NULL,
  `creationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `durationLife` int(9) unsigned NOT NULL DEFAULT '86400',
  `displayOrder` smallint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `groupe` (`groupe`),
  KEY `cat` (`cat`),
  KEY `groupe_2` (`groupe`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table dicomTags
CREATE TABLE IF NOT EXISTS `dicomTags` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `dicomTag` varchar(150) NOT NULL,
  `typeName` varchar(60) DEFAULT NULL,
  `dicomCodeMeaning` varchar(255) DEFAULT NULL,
  `dicomUnits` varchar(255) DEFAULT NULL,
  `returnValue` enum('min','max','avg') NOT NULL DEFAULT 'avg',
  `roundDecimal` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `dicomTag` (`dicomTag`,`typeName`),
  KEY `dicomTag_2` (`dicomTag`),
  KEY `typeName` (`typeName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table forms
CREATE TABLE IF NOT EXISTS `forms` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(20) NOT NULL DEFAULT 'base',
  `internalName` varchar(60) NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` varchar(250) NOT NULL,
  `dataset` varchar(60) NOT NULL DEFAULT 'data_types',
  `groupe` enum('admin','medical','mail','doc','courrier','ordo','reglement','relation') NOT NULL DEFAULT 'medical',
  `formMethod` enum('post','get') NOT NULL DEFAULT 'post',
  `formAction` varchar(255) DEFAULT '/patient/ajax/saveCsForm/',
  `cat` smallint(4) DEFAULT NULL,
  `type` enum('public','private') NOT NULL DEFAULT 'public',
  `yamlStructure` text,
  `options` text,
  `printModel` varchar(50) DEFAULT NULL,
  `cda` text,
  `javascript` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `internalName` (`internalName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table forms_cat
CREATE TABLE IF NOT EXISTS `forms_cat` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
  `fromID` smallint(5) unsigned NOT NULL,
  `creationDate` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table hprim
CREATE TABLE IF NOT EXISTS `hprim` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fromID` smallint(5) unsigned NOT NULL,
  `toID` smallint(5) unsigned NOT NULL,
  `date` date DEFAULT '1000-01-01',
  `objetID` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `labelStandard` varchar(255) NOT NULL,
  `typeResultat` varchar(2) NOT NULL,
  `resultat` varchar(255) NOT NULL,
  `unite` varchar(20) NOT NULL,
  `normaleInf` varchar(20) NOT NULL,
  `normaleSup` varchar(20) NOT NULL,
  `indicateurAnormal` varchar(5) NOT NULL,
  `statutRes` varchar(1) NOT NULL,
  `resAutreU` varchar(50) NOT NULL,
  `normaleInfAutreU` varchar(20) NOT NULL,
  `normalSupAutreU` varchar(20) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table inbox
CREATE TABLE IF NOT EXISTS `inbox` (
  `id` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `mailForUserID` smallint(5) unsigned NOT NULL DEFAULT '0',
  `txtFileName` varchar(30) NOT NULL,
  `mailHeaderInfos` blob,
  `txtDatetime` datetime NOT NULL,
  `txtNumOrdre` int(9) unsigned NOT NULL,
  `hprimIdentite` varchar(250) NOT NULL,
  `hprimExpediteur` varchar(250) NOT NULL,
  `hprimCodePatient` varchar(250) NOT NULL,
  `hprimDateDossier` varchar(30) NOT NULL,
  `hprimAllSerialize` blob NOT NULL,
  `pjNombre` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `pjSerializeName` blob NOT NULL,
  `archived` enum('y','c','n') NOT NULL DEFAULT 'n',
  `assoToID` mediumint(7) unsigned DEFAULT NULL,
  PRIMARY KEY (`txtFileName`,`mailForUserID`) USING BTREE,
  UNIQUE KEY `id` (`id`),
  KEY `archived` (`archived`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table objets_data
CREATE TABLE IF NOT EXISTS `objets_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fromID` int(11) unsigned NOT NULL DEFAULT '0',
  `byID` int(11) unsigned DEFAULT NULL,
  `toID` int(11) unsigned NOT NULL DEFAULT '0',
  `typeID` int(11) unsigned NOT NULL DEFAULT '0',
  `parentTypeID` int(11) unsigned DEFAULT '0',
  `instance` int(11) unsigned NOT NULL DEFAULT '0',
  `registerDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creationDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `updateDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `value` text,
  `outdated` enum('','y') NOT NULL DEFAULT '',
  `important` enum('n','y') DEFAULT 'n',
  `titre` varchar(255) NOT NULL DEFAULT '',
  `deleted` enum('','y') NOT NULL DEFAULT '',
  `deletedByID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `toID_typeID` (`toID`,`typeID`),
  KEY `typeID` (`typeID`),
  KEY `instance` (`instance`),
  KEY `parentTypeID` (`parentTypeID`),
  KEY `toID` (`toID`),
  KEY `toID_2` (`toID`,`outdated`,`deleted`),
  KEY `typeIDetValue` (`typeID`,`value`(15))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table people
CREATE TABLE IF NOT EXISTS `people` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `type` enum('patient','pro','externe','service','deleted','groupe','destroyed','registre') NOT NULL DEFAULT 'patient',
  `rank` enum('','admin') DEFAULT NULL,
  `module` varchar(20) DEFAULT 'base',
  `pass` varbinary(1000) DEFAULT NULL,
  `secret2fa` varbinary(1000) DEFAULT NULL,
  `registerDate` datetime DEFAULT NULL,
  `fromID` smallint(5) DEFAULT NULL,
  `lastLogIP` varchar(50) DEFAULT NULL,
  `lastLogDate` datetime DEFAULT NULL,
  `lastLogFingerprint` varchar(50) DEFAULT NULL,
  `lastLostPassDate` datetime DEFAULT NULL,
  `lastLostPassRandStr` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table prescriptions
CREATE TABLE IF NOT EXISTS `prescriptions` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `cat` smallint(5) unsigned NOT NULL DEFAULT '0',
  `label` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `fromID` smallint(5) unsigned NOT NULL,
  `toID` mediumint(7) unsigned NOT NULL DEFAULT '0',
  `creationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `toID` (`toID`),
  KEY `cat` (`cat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table prescriptions_cat
CREATE TABLE IF NOT EXISTS `prescriptions_cat` (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('nonlap','lap') NOT NULL DEFAULT 'nonlap',
  `fromID` smallint(5) unsigned NOT NULL,
  `toID` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `creationDate` datetime NOT NULL,
  `displayOrder` tinyint(2) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `displayOrder` (`displayOrder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table printed
CREATE TABLE IF NOT EXISTS `printed` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fromID` int(11) unsigned NOT NULL,
  `toID` int(11) unsigned NOT NULL,
  `type` enum('cr','ordo','courrier','ordoLAP','ordoLapExt') NOT NULL DEFAULT 'cr',
  `objetID` int(11) unsigned DEFAULT NULL,
  `creationDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `serializedTags` longblob,
  `outdated` enum('','y') NOT NULL,
  `anonyme` enum('','y') DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `examenID` (`objetID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table system
CREATE TABLE IF NOT EXISTS `system` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `groupe` enum('system','module','cron','lock','plugin') DEFAULT 'system',
  `value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nameGroupe` (`name`,`groupe`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table transmissions
CREATE TABLE IF NOT EXISTS `transmissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fromID` mediumint(6) unsigned DEFAULT NULL,
  `aboutID` int(6) unsigned DEFAULT NULL,
  `sujetID` int(10) unsigned DEFAULT NULL,
  `statut` enum('open','deleted') NOT NULL DEFAULT 'open',
  `priorite` tinyint(3) unsigned DEFAULT NULL,
  `registerDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updateDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sujet` varchar(255) DEFAULT NULL,
  `texte` text,
  PRIMARY KEY (`id`),
  KEY `fromID` (`fromID`),
  KEY `aboutID` (`aboutID`),
  KEY `sujetID` (`sujetID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table transmissions_to
CREATE TABLE IF NOT EXISTS `transmissions_to` (
  `sujetID` int(7) unsigned NOT NULL,
  `toID` mediumint(6) unsigned NOT NULL,
  `destinataire` enum('oui','non') NOT NULL DEFAULT 'non',
  `statut` enum('open','checked','deleted') NOT NULL DEFAULT 'open',
  `dateLecture` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`sujetID`,`toID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- actes_cat
INSERT IGNORE INTO `actes_cat` (`name`, `label`, `description`, `module`, `fromID`, `creationDate`, `displayOrder`) VALUES
('catConsult', 'Consultations', '', 'base', 1, '2019-01-01 00:00:00', '1');

-- actes_base
INSERT IGNORE INTO `actes_base` (`code`, `activite`, `phase`, `codeProf`, `label`, `type`, `dataYaml`, `tarifUnit`, `fromID`, `creationDate`) VALUES
('Consult', '1', '0', NULL, 'Consultation libre exemple', 'Libre', 'tarifBase: 50', 'euro', 1, '2019-01-01 00:00:00');

-- actes
SET @catID = (SELECT actes_cat.id FROM actes_cat WHERE actes_cat.name='catConsult');
INSERT IGNORE INTO `actes` (`cat`, `label`, `shortLabel`, `details`, `flagImportant`, `flagCmu`, `fromID`, `toID`, `creationDate`, `active`) VALUES
(@catID, 'Consultation de base', 'Cs base', 'Consult:\r\n  pourcents: 100\r\n  depassement: 15 \r\n', '1', '0', 1, '0', '2019-01-01 00:00:00', 'oui');

-- data_cat
INSERT IGNORE INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('admin', 'activity', 'Activités', 'Activités professionnelles et de loisir', 'base', '1', '2019-01-01 00:00:00'),
('admin', 'addressPerso', 'Adresse personnelle', 'datas de l\'adresse personnelle', 'base', '1', '2019-01-01 00:00:00'),
('admin', 'adressPro', 'Adresse professionnelle', 'Data de l\'adresse professionnelle', 'base', '1', '2019-01-01 00:00:00'),
('admin', 'catDataAdminGroupe', 'Datas groupe', 'datas relatives à l\'identification d\'un groupe', 'base', '1', '2019-01-01 00:00:00'),
('admin', 'catDataAdminRegistre', 'Datas registre', 'datas relatives à l\'identification d\'un registre', 'base', '1', '2019-01-01 00:00:00'),
('admin', 'catMarqueursAdminDossiers', 'Marqueurs', 'marqueurs dossiers', 'base', '1', '2019-01-01 00:00:00'),
('admin', 'contact', 'Contact', 'Moyens de contact', 'base', '1', '2019-01-01 00:00:00'),
('admin', 'divers', 'Divers', 'Divers', 'base', '1', '2019-01-01 00:00:00'),
('admin', 'identity', 'Etat civil', 'Datas relatives à l\'identité d\'une personne', 'base', '1', '2019-01-01 00:00:00'),
('admin', 'internet', 'Internet', 'Datas liées aux services internet', 'base', '1', '2019-01-01 00:00:00'),
('admin', 'numAdmin', 'Numéros administratifs', 'RPPS et compagnie', 'base', '1', '2019-01-01 00:00:00'),
('courrier', 'catModelesCertificats', 'Certificats', 'certificats divers', 'base', '1', '2019-01-01 00:00:00'),
('courrier', 'catModelesCourriers', 'Courriers', 'modèles de courrier libres', 'base', '1', '2019-01-01 00:00:00'),
('courrier', 'catModelesDocASigner', 'Documents à signer', 'documents à envoyer à la signature numérique', 'base', '1', '2019-01-01 00:00:00'),
('courrier', 'catModelesMailsToApicrypt', 'Mails aux praticiens', 'modèles de mails pour les praticien (apicrypt)', 'base', '1', '2019-01-01 00:00:00'),
('courrier', 'catModelesMailsToPatient', 'Mails aux patients', 'modèles de mail', 'base', '1', '2019-01-01 00:00:00'),
('dicom', 'idDicom', 'ID Dicom', 'ID du dicom', 'base', '1', '2019-01-01 00:00:00'),
('doc', 'docForm', 'Data documents importés / créés', 'données pour le formulaire documents importés ou créés', 'base', '1', '2019-01-01 00:00:00'),
('doc', 'docPorteur', 'Porteur', 'porteur pour doc importés', 'base', '1', '2019-01-01 00:00:00'),
('mail', 'dataSms', 'Data sms', 'data pour les sms envoyés', 'base', '1', '2019-01-01 00:00:00'),
('mail', 'mailForm', 'Data mail', 'data pour les mails expédiés', 'base', '1', '2019-01-01 00:00:00'),
('mail', 'porteursTech', 'Porteurs', 'porteurs pour les données enfants', 'base', '1', '2019-01-01 00:00:00'),
('medical', 'aldCat', 'ALD', 'paramètres pour la gestion des ALD', 'base', '1', '2019-01-01 00:00:00'),
('medical', 'atcd', 'Antécédents et synthèses', 'antécédents et synthèses', 'base', '1', '2019-01-01 00:00:00'),
('medical', 'catAtcdStruc', 'ATCD structurés', 'données pour antécédents structurés', 'base', '1', '2019-01-01 00:00:00'),
('medical', 'catDataTransversesFormCs', 'Données transverses', 'champs utilisables dans tous formulaires (codage des actes par exemple)', 'base', '1', '2019-01-01 00:00:00'),
('medical', 'dataBio', 'Données biologiques', 'Données biologiques habituelles', 'base', '1', '2019-01-01 00:00:00'),
('medical', 'dataCliniques', 'Données cliniques', 'Données cliniques', 'base', '1', '2019-01-01 00:00:00'),
('medical', 'dataCsBase', 'Données formulaire Cs', '', 'base', '1', '2019-01-01 00:00:00'),
('medical', 'grossesse', 'Grossesse', 'Données liées à la grossesse', 'base', '1', '2019-01-01 00:00:00'),
('ordo', 'lapCatLignePrescription', 'LAP ligne de prescription', 'data des lignes de prescription', 'base', '1', '2019-01-01 00:00:00'),
('ordo', 'lapCatMedicament', 'LAP médicament', 'data pour les médicaments', 'base', '1', '2019-01-01 00:00:00'),
('ordo', 'lapCatPorteurs', 'LAP porteurs', 'data pour les porteurs LAP', 'base', '1', '2019-01-01 00:00:00'),
('ordo', 'lapCatSams', 'LAP SAMs', 'data pour SAMs LAP', 'base', '1', '2019-01-01 00:00:00'),
('ordo', 'lapExterne', 'LAP Externe', '', 'base', '1', '2019-01-01 00:00:00'),
('ordo', 'ordoItems', 'Ordo', 'items d\'une ordonnance', 'base', '1', '2019-01-01 00:00:00'),
('ordo', 'porteursOrdo', 'Porteurs', 'porteurs ordonnance', 'base', '1', '2019-01-01 00:00:00'),
('reglement', 'porteursReglement', 'Porteurs', 'porteur d\'un règlement', 'base', '1', '2019-01-01 00:00:00'),
('reglement', 'reglementItems', 'Règlement', 'items d\'un réglement', 'base', '1', '2019-01-01 00:00:00'),
('relation', 'catAllergiesStruc', 'Allergies structurées', 'données pour allergies structurées', 'base', '1', '2019-01-01 00:00:00'),
('relation', 'relationRelations', 'Relations', 'types permettant de définir une relation', 'base', '1', '2019-01-01 00:00:00'),
('system', 'catTypesUsageSystem', 'Types à usage system', 'types à usage system', 'base', '1', '2019-01-01 00:00:00'),
('typecs', 'catTypeCsATCD', 'Antécédents et allergies', 'antécédents et allergies', 'base', '1', '2019-01-01 00:00:00'),
('typecs', 'csAutres', 'Autres', 'autres', 'base', '1', '2019-01-01 00:00:00'),
('typecs', 'csBase', 'Consultations', 'consultations possibles', 'base', '1', '2019-01-01 00:00:00'),
('typecs', 'declencheur', 'Déclencheur', '', 'base', '1', '2019-01-01 00:00:00'),
('typecs', 'declencheursHorsHistoriques', 'Déclencheurs hors historiques', 'ne donnent pas de ligne dans les historiques', 'base', '1', '2019-01-01 00:00:00'),
('admin', 'clicRDV', 'clicRDV', 'Paramètres pour clicRDV', 'base', '1', '2019-01-01 00:00:00');

-- data_types
SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='activity');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'job', 'activité professionnelle', 'Activité professionnelle', 'Activité professionnelle', '', 'L\'activité professionnelle n\'est pas correcte', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'sport', 'sport exercé', 'Sport', 'Sport exercé', '', 'Le sport indiqué n\'est pas correct', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='addressPerso');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'city', 'ville', 'Ville', 'Adresse perso : ville', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'country', '', 'Pays', 'Adresse perso : pays', '', '', 'select', '\'\': \'\'\nAFG: \"Afghanistan\"\nZAF: \"Afrique du Sud\"\nALB: \"Albanie\"\nDZA: \"Algérie\"\nDEU: \"Allemagne\"\nAND: \"Andorre\"\nAGO: \"Angola\"\nAIA: \"Anguilla\"\nATA: \"Antarctique\"\nATG: \"Antigua-et-Barbuda\"\nSAU: \"Arabie saoudite\"\nARG: \"Argentine\"\nARM: \"Arménie\"\nABW: \"Aruba\"\nAUS: \"Australie\"\nAUT: \"Autriche\"\nAZE: \"Azerbaïdjan\"\nBHS: \"Bahamas\"\nBHR: \"Bahreïn\"\nBGD: \"Bangladesh\"\nBRB: \"Barbade\"\nBEL: \"Belgique\"\nBLZ: \"Belize\"\nBEN: \"Bénin\"\nBMU: \"Bermudes\"\nBTN: \"Bhoutan\"\nBLR: \"Biélorussie\"\nMMR: \"Birmanie\"\nBOL: \"Bolivie\"\nBIH: \"Bosnie-Herzégovine\"\nBWA: \"Botswana\"\nBRA: \"Brésil\"\nBRN: \"Brunei\"\nBGR: \"Bulgarie\"\nBFA: \"Burkina Faso\"\nBDI: \"Burundi\"\nKHM: \"Cambodge\"\nCMR: \"Cameroun\"\nCAN: \"Canada\"\nCPV: \"Cap-Vert\"\nCHL: \"Chili\"\nCHN: \"Chine\"\nCYP: \"Chypre (pays)	\"\nCOL: \"Colombie\"\nCOM: \"Comores (pays)\"\nPRK: \"Corée du Nord\"\nKOR: \"Corée du Sud\"\nCRI: \"Costa Rica\"\nCIV: \"Côte d\'Ivoire\"\nHRV: \"Croatie\"\nCUB: \"Cuba\"\nCUW: \"Curaçao\"\nDNK: \"Danemark\"\nDJI: \"Djibouti\"\nDMA: \"Dominique\"\nEGY: \"Égypte\"\nARE: \"Émirats arabes unis\"\nECU: \"Équateur (pays)\"\nERI: \"Érythrée\"\nESP: \"Espagne\"\nEST: \"Estonie\"\nFSM: \"États fédérés de Micronésie (pays)\"\nUSA: \"États-Unis\"\nETH: \"Éthiopie\"\nFJI: \"Fidji\"\nFIN: \"Finlande\"\nFRA: \"France\"\nGAB: \"Gabon\"\nGMB: \"Gambie\"\nGEO: \"Géorgie (pays)\"\nSGS: \"Géorgie du Sud-et-les îles Sandwich du Sud\"\nGHA: \"Ghana\"\nGIB: \"Gibraltar\"\nGRC: \"Grèce\"\nGRD: \"Grenade (pays)	\"\nGRL: \"Groenland\"\nGLP: \"Guadeloupe\"\nGUM: \"Guam\"\nGTM: \"Guatemala\"\nGGY: \"Guernesey\"\nGIN: \"Guinée\"\nGNQ: \"Guinée équatoriale\"\nGNB: \"Guinée-Bissau\"\nGUY: \"Guyana\"\nGUF: \"Guyane\"\nHTI: \"Haïti\"\nHND: \"Honduras\"\nHKG: \"Hong Kong\"\nHUN: \"Hongrie\"\nBVT: \"  Île Bouvet\"\nCXR: \"Île Christmas\"\nIMN: \"Île de Man\"\nNFK: \"Île Norfolk\"\nALA: \"Îles Åland\"\nCYM: \"Îles Caïmans\"\nCCK: \"Îles Cocos\"\nCOK: \"Îles Cook\"\nFRO: \"Îles Féroé\"\nHMD: \"Îles Heard-et-MacDonald\"\nMNP: \"Îles Mariannes du Nord\"\nMHL: \"Îles Marshall (pays)\"\nUMI: \"  Îles mineures éloignées des États-Unis\"\nPCN: \"Îles Pitcairn\"\nTCA: \"Îles Turques-et-Caïques\"\nVGB: \"Îles Vierges britanniques\"\nVIR: \"Îles Vierges des États-Unis\"\nIND: \"Inde\"\nIDN: \"Indonésie\"\nIRQ: \"Irak\"\nIRN: \"Iran\"\nIRL: \"Irlande (pays)	\"\nISL: \"Islande\"\nISR: \"Israël\"\nITA: \"Italie\"\nJAM: \"Jamaïque\"\nJPN: \"Japon\"\nJEY: \"Jersey\"\nJOR: \"Jordanie\"\nKAZ: \"Kazakhstan\"\nKEN: \"Kenya\"\nKGZ: \"Kirghizistan\"\nKIR: \"Kiribati\"\nKWT: \"Koweït\"\nREU: \"La Réunion\"\nLAO: \"Laos\"\nLSO: \"Lesotho\"\nLVA: \"Lettonie\"\nLBN: \"Liban\"\nLBR: \"Liberia\"\nLBY: \"Libye\"\nLIE: \"Liechtenstein\"\nLTU: \"Lituanie\"\nLUX: \"Luxembourg (pays)\"\nMAC: \"Macao\"\nMKD: \"Macédoine du Nord\"\nMDG: \"Madagascar\"\nMYS: \"Malaisie\"\nMWI: \"Malawi\"\nMDV: \"Maldives\"\nMLI: \"Mali\"\nFLK: \"Malouines\"\nMLT: \"Malte\"\nMAR: \"Maroc\"\nMTQ: \"Martinique\"\nMUS: \"Maurice (pays)\"\nMRT: \"Mauritanie\"\nMYT: \"Mayotte\"\nMEX: \"Mexique\"\nMDA: \"Moldavie\"\nMCO: \"Monaco\"\nMNG: \"Mongolie\"\nMNE: \"Monténégro\"\nMSR: \"Montserrat\"\nMOZ: \"Mozambique\"\nNAM: \"Namibie\"\nNRU: \"Nauru\"\nNPL: \"Népal\"\nNIC: \"Nicaragua\"\nNER: \"Niger\"\nNGA: \"Nigeria\"\nNIU: \"Niue\"\nNOR: \"Norvège\"\nNCL: \"Nouvelle-Calédonie\"\nNZL: \"Nouvelle-Zélande\"\nOMN: \"Oman\"\nUGA: \"Ouganda\"\nUZB: \"Ouzbékistan\"\nPAK: \"Pakistan\"\nPLW: \"Palaos\"\nPSE: \"Palestine\"\nPAN: \"Panama\"\nPNG: \"Papouasie-Nouvelle-Guinée\"\nPRY: \"Paraguay\"\nNLD: \"Pays-Bas\"\nBES: \"Pays-Bas caribéens\"\nPER: \"Pérou\"\nPHL: \"Philippines\"\nPOL: \"Pologne\"\nPYF: \"Polynésie française\"\nPRI: \"Porto Rico\"\nPRT: \"Portugal\"\nQAT: \"Qatar\"\nESH: \"République arabe sahraouie démocratique\"\nCAF: \"République centrafricaine\"\nCOD: \"République démocratique du Congo\"\nDOM: \"République dominicaine\"\nCOG: \"République du Congo\"\nROU: \"Roumanie\"\nGBR: \"Royaume-Uni\"\nRUS: \"Russie\"\nRWA: \"Rwanda\"\nBLM: \"Saint-Barthélemy\"\nKNA: \"Saint-Christophe-et-Niévès\"\nSMR: \"Saint-Marin\"\nMAF: \"Saint-Martin\"\nSXM: \"Saint-Martin\"\nSPM: \"Saint-Pierre-et-Miquelon\"\nVAT: \"  Saint-Siège (État de la Cité du Vatican)\"\nVCT: \"Saint-Vincent-et-les-Grenadines\"\nSHN: \"Sainte-Hélène, Ascension et Tristan da Cunha\"\nLCA: \"Sainte-Lucie\"\nSLB: \"Salomon\"\nSLV: \"Salvador\"\nWSM: \"Samoa\"\nASM: \"Samoa américaines\"\nSTP: \"Sao Tomé-et-Principe\"\nSEN: \"Sénégal\"\nSRB: \"Serbie\"\nSYC: \"Seychelles\"\nSLE: \"Sierra Leone\"\nSGP: \"Singapour\"\nSVK: \"Slovaquie\"\nSVN: \"Slovénie\"\nSOM: \"Somalie\"\nSDN: \"Soudan\"\nSSD: \"Soudan du Sud\"\nLKA: \"Sri Lanka\"\nSWE: \"Suède\"\nCHE: \"Suisse\"\nSUR: \"Suriname\"\nSJM: \"Svalbard et ile Jan Mayen\"\nSWZ: \"Swaziland\"\nSYR: \"Syrie\"\nTJK: \"Tadjikistan\"\nTWN: \"Taïwan / (République de Chine (Taïwan))\"\nTZA: \"Tanzanie\"\nTCD: \"Tchad\"\nCZE: \"Tchéquie\"\nATF: \"Terres australes et antarctiques françaises\"\nIOT: \"Territoire britannique de l\'océan Indien\"\nTHA: \"Thaïlande\"\nTLS: \"Timor oriental\"\nTGO: \"Togo\"\nTKL: \"Tokelau\"\nTON: \"Tonga\"\nTTO: \"Trinité-et-Tobago\"\nTUN: \"Tunisie\"\nTKM: \"Turkménistan\"\nTUR: \"Turquie\"\nTUV: \"Tuvalu\"\nUKR: \"Ukraine\"\nURY: \"Uruguay\"\nVUT: \"Vanuatu\"\nVEN: \"Venezuela\"\nVNM: \"Viêt Nam\"\nWLF: \"Wallis-et-Futuna\"\nYEM: \"Yémen\"\nZMB: \"Zambie\"\nZWE: \"Zimbabwe\"', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '13'),
('admin', 'postalCodePerso', 'code postal', 'Code postal', 'Adresse perso : code postal', '', 'Le code postal n\'est pas correct', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'street', 'type et nom de la voie', 'Voie', 'Adresse perso : voie', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'streetNumber', 'n° dans la voie', 'n°', 'Adresse perso : n° dans la voie', '', 'Le numéro de voie est incorrect', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='adressPro');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'codePostalPro', 'code postal', 'Code postal', 'Adresse pro : code postal', 'alpha_space', 'Le code postal n\'est pas conforme', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'etablissementAdressePro', 'établissement', 'Établissement', 'Adresse pro : établissement', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'numAdressePro', 'n° dans la voie', 'n°', 'Adresse pro : n° dans la voie', 'alpha_space', 'Le numero n\'est pas conforme', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'paysAdressePro', '', 'Pays', 'Adresse pro : pays', '', '', 'select', '\'\': \'\'\nAFG: \"Afghanistan\"\nZAF: \"Afrique du Sud\"\nALB: \"Albanie\"\nDZA: \"Algérie\"\nDEU: \"Allemagne\"\nAND: \"Andorre\"\nAGO: \"Angola\"\nAIA: \"Anguilla\"\nATA: \"Antarctique\"\nATG: \"Antigua-et-Barbuda\"\nSAU: \"Arabie saoudite\"\nARG: \"Argentine\"\nARM: \"Arménie\"\nABW: \"Aruba\"\nAUS: \"Australie\"\nAUT: \"Autriche\"\nAZE: \"Azerbaïdjan\"\nBHS: \"Bahamas\"\nBHR: \"Bahreïn\"\nBGD: \"Bangladesh\"\nBRB: \"Barbade\"\nBEL: \"Belgique\"\nBLZ: \"Belize\"\nBEN: \"Bénin\"\nBMU: \"Bermudes\"\nBTN: \"Bhoutan\"\nBLR: \"Biélorussie\"\nMMR: \"Birmanie\"\nBOL: \"Bolivie\"\nBIH: \"Bosnie-Herzégovine\"\nBWA: \"Botswana\"\nBRA: \"Brésil\"\nBRN: \"Brunei\"\nBGR: \"Bulgarie\"\nBFA: \"Burkina Faso\"\nBDI: \"Burundi\"\nKHM: \"Cambodge\"\nCMR: \"Cameroun\"\nCAN: \"Canada\"\nCPV: \"Cap-Vert\"\nCHL: \"Chili\"\nCHN: \"Chine\"\nCYP: \"Chypre (pays)	\"\nCOL: \"Colombie\"\nCOM: \"Comores (pays)\"\nPRK: \"Corée du Nord\"\nKOR: \"Corée du Sud\"\nCRI: \"Costa Rica\"\nCIV: \"Côte d\'Ivoire\"\nHRV: \"Croatie\"\nCUB: \"Cuba\"\nCUW: \"Curaçao\"\nDNK: \"Danemark\"\nDJI: \"Djibouti\"\nDMA: \"Dominique\"\nEGY: \"Égypte\"\nARE: \"Émirats arabes unis\"\nECU: \"Équateur (pays)\"\nERI: \"Érythrée\"\nESP: \"Espagne\"\nEST: \"Estonie\"\nFSM: \"États fédérés de Micronésie (pays)\"\nUSA: \"États-Unis\"\nETH: \"Éthiopie\"\nFJI: \"Fidji\"\nFIN: \"Finlande\"\nFRA: \"France\"\nGAB: \"Gabon\"\nGMB: \"Gambie\"\nGEO: \"Géorgie (pays)\"\nSGS: \"Géorgie du Sud-et-les îles Sandwich du Sud\"\nGHA: \"Ghana\"\nGIB: \"Gibraltar\"\nGRC: \"Grèce\"\nGRD: \"Grenade (pays)	\"\nGRL: \"Groenland\"\nGLP: \"Guadeloupe\"\nGUM: \"Guam\"\nGTM: \"Guatemala\"\nGGY: \"Guernesey\"\nGIN: \"Guinée\"\nGNQ: \"Guinée équatoriale\"\nGNB: \"Guinée-Bissau\"\nGUY: \"Guyana\"\nGUF: \"Guyane\"\nHTI: \"Haïti\"\nHND: \"Honduras\"\nHKG: \"Hong Kong\"\nHUN: \"Hongrie\"\nBVT: \"  Île Bouvet\"\nCXR: \"Île Christmas\"\nIMN: \"Île de Man\"\nNFK: \"Île Norfolk\"\nALA: \"Îles Åland\"\nCYM: \"Îles Caïmans\"\nCCK: \"Îles Cocos\"\nCOK: \"Îles Cook\"\nFRO: \"Îles Féroé\"\nHMD: \"Îles Heard-et-MacDonald\"\nMNP: \"Îles Mariannes du Nord\"\nMHL: \"Îles Marshall (pays)\"\nUMI: \"  Îles mineures éloignées des États-Unis\"\nPCN: \"Îles Pitcairn\"\nTCA: \"Îles Turques-et-Caïques\"\nVGB: \"Îles Vierges britanniques\"\nVIR: \"Îles Vierges des États-Unis\"\nIND: \"Inde\"\nIDN: \"Indonésie\"\nIRQ: \"Irak\"\nIRN: \"Iran\"\nIRL: \"Irlande (pays)	\"\nISL: \"Islande\"\nISR: \"Israël\"\nITA: \"Italie\"\nJAM: \"Jamaïque\"\nJPN: \"Japon\"\nJEY: \"Jersey\"\nJOR: \"Jordanie\"\nKAZ: \"Kazakhstan\"\nKEN: \"Kenya\"\nKGZ: \"Kirghizistan\"\nKIR: \"Kiribati\"\nKWT: \"Koweït\"\nREU: \"La Réunion\"\nLAO: \"Laos\"\nLSO: \"Lesotho\"\nLVA: \"Lettonie\"\nLBN: \"Liban\"\nLBR: \"Liberia\"\nLBY: \"Libye\"\nLIE: \"Liechtenstein\"\nLTU: \"Lituanie\"\nLUX: \"Luxembourg (pays)\"\nMAC: \"Macao\"\nMKD: \"Macédoine du Nord\"\nMDG: \"Madagascar\"\nMYS: \"Malaisie\"\nMWI: \"Malawi\"\nMDV: \"Maldives\"\nMLI: \"Mali\"\nFLK: \"Malouines\"\nMLT: \"Malte\"\nMAR: \"Maroc\"\nMTQ: \"Martinique\"\nMUS: \"Maurice (pays)\"\nMRT: \"Mauritanie\"\nMYT: \"Mayotte\"\nMEX: \"Mexique\"\nMDA: \"Moldavie\"\nMCO: \"Monaco\"\nMNG: \"Mongolie\"\nMNE: \"Monténégro\"\nMSR: \"Montserrat\"\nMOZ: \"Mozambique\"\nNAM: \"Namibie\"\nNRU: \"Nauru\"\nNPL: \"Népal\"\nNIC: \"Nicaragua\"\nNER: \"Niger\"\nNGA: \"Nigeria\"\nNIU: \"Niue\"\nNOR: \"Norvège\"\nNCL: \"Nouvelle-Calédonie\"\nNZL: \"Nouvelle-Zélande\"\nOMN: \"Oman\"\nUGA: \"Ouganda\"\nUZB: \"Ouzbékistan\"\nPAK: \"Pakistan\"\nPLW: \"Palaos\"\nPSE: \"Palestine\"\nPAN: \"Panama\"\nPNG: \"Papouasie-Nouvelle-Guinée\"\nPRY: \"Paraguay\"\nNLD: \"Pays-Bas\"\nBES: \"Pays-Bas caribéens\"\nPER: \"Pérou\"\nPHL: \"Philippines\"\nPOL: \"Pologne\"\nPYF: \"Polynésie française\"\nPRI: \"Porto Rico\"\nPRT: \"Portugal\"\nQAT: \"Qatar\"\nESH: \"République arabe sahraouie démocratique\"\nCAF: \"République centrafricaine\"\nCOD: \"République démocratique du Congo\"\nDOM: \"République dominicaine\"\nCOG: \"République du Congo\"\nROU: \"Roumanie\"\nGBR: \"Royaume-Uni\"\nRUS: \"Russie\"\nRWA: \"Rwanda\"\nBLM: \"Saint-Barthélemy\"\nKNA: \"Saint-Christophe-et-Niévès\"\nSMR: \"Saint-Marin\"\nMAF: \"Saint-Martin\"\nSXM: \"Saint-Martin\"\nSPM: \"Saint-Pierre-et-Miquelon\"\nVAT: \"  Saint-Siège (État de la Cité du Vatican)\"\nVCT: \"Saint-Vincent-et-les-Grenadines\"\nSHN: \"Sainte-Hélène, Ascension et Tristan da Cunha\"\nLCA: \"Sainte-Lucie\"\nSLB: \"Salomon\"\nSLV: \"Salvador\"\nWSM: \"Samoa\"\nASM: \"Samoa américaines\"\nSTP: \"Sao Tomé-et-Principe\"\nSEN: \"Sénégal\"\nSRB: \"Serbie\"\nSYC: \"Seychelles\"\nSLE: \"Sierra Leone\"\nSGP: \"Singapour\"\nSVK: \"Slovaquie\"\nSVN: \"Slovénie\"\nSOM: \"Somalie\"\nSDN: \"Soudan\"\nSSD: \"Soudan du Sud\"\nLKA: \"Sri Lanka\"\nSWE: \"Suède\"\nCHE: \"Suisse\"\nSUR: \"Suriname\"\nSJM: \"Svalbard et ile Jan Mayen\"\nSWZ: \"Swaziland\"\nSYR: \"Syrie\"\nTJK: \"Tadjikistan\"\nTWN: \"Taïwan / (République de Chine (Taïwan))\"\nTZA: \"Tanzanie\"\nTCD: \"Tchad\"\nCZE: \"Tchéquie\"\nATF: \"Terres australes et antarctiques françaises\"\nIOT: \"Territoire britannique de l\'océan Indien\"\nTHA: \"Thaïlande\"\nTLS: \"Timor oriental\"\nTGO: \"Togo\"\nTKL: \"Tokelau\"\nTON: \"Tonga\"\nTTO: \"Trinité-et-Tobago\"\nTUN: \"Tunisie\"\nTKM: \"Turkménistan\"\nTUR: \"Turquie\"\nTUV: \"Tuvalu\"\nUKR: \"Ukraine\"\nURY: \"Uruguay\"\nVUT: \"Vanuatu\"\nVEN: \"Venezuela\"\nVNM: \"Viêt Nam\"\nWLF: \"Wallis-et-Futuna\"\nYEM: \"Yémen\"\nZMB: \"Zambie\"\nZWE: \"Zimbabwe\"', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '13'),
('admin', 'rueAdressePro', 'type et nom de la voie', 'Voie', 'Adresse pro : voie', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'serviceAdressePro', 'service', 'Service', 'Adresse pro : service', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'villeAdressePro', 'ville', 'Ville', 'Adresse pro : ville', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='aldCat');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'aldCIM10', '', 'Code CIM10 associé', 'Code CIM10 attaché à l\'ALD', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'aldCIM10label', '', 'Label CIM10 associé', 'Label CIM10 attaché à l\'ALD', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'aldDateDebutPriseEnCharge', '', 'Début de prise en charge', 'date de début de prise en charge', '', '', 'date', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'aldDateFinPriseEnCharge', '', 'Fin de prise en charge', 'date de fin de prise en charge', '', '', 'date', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'aldNumber', '', 'ALD', 'ALD choisie', '', '', 'select', '1: \"Accident vasculaire cérébral invalidant\"\n2: \"Insuffisances médullaires et autres cytopénies chroniques\"\n3: \"Artériopathies chroniques avec manifestations ischémiques\"\n4: \"Bilharziose compliquée\"\n5: \"Insuffisance cardiaque grave, troubles du rythme graves, cardiopathies valvulaires graves, cardiopathies  congénitales graves\"\n6: \"Maladies chroniques actives du foie et cirrhoses\"\n7: \"Déficit immunitaire primitif grave nécessitant un traitement prolongé, infection par le virus de 9: l\'immuno-déficience humaine (VIH)\"\n8: \"Diabète de type 1 et diabète de type 2\"\n9: \"Formes graves des affections neurologiques et musculaires (dont myopathie), épilepsie grave\"\n10: \"Hémoglobinopathies, hémolyses, chroniques constitutionnelles et acquises sévères\"\n11: \"Hémophilies et affections constitutionnelles de l\'hémostase graves\"\n12: \"Maladie coronaire\"\n13: \"Insuffisance respiratoire chronique grave\"\n14: \"Maladie d\'Alzheimer et autres démences\"\n15: \"Maladie de Parkinson\"\n16: \"Maladies métaboliques héréditaires nécessitant un traitement prolongé spécialisé\"\n17: \"Mucoviscidose\"\n18: \"Néphropathie chronique grave et syndrome néphrotique primitif\"\n19: \"Paraplégie\"\n20: \"Vascularites, lupus érythémateux systémique, sclérodermie systémique\"\n21: \"Polyarthrite rhumatoïde évolutive\"\n22: \"Affections psychiatriques de longue durée\"\n23: \"Rectocolite hémorragique et maladie de Crohn évolutives\"\n24: \"Sclérose en plaques\"\n25: \"Scoliose idiopathique structurale évolutive (dont l\'angle est égal ou supérieur à 25 degrés) jusqu\'à maturation rachidienne\"\n26: \"Spondylarthrite grave\"\n27: \"Suites de transplantation d\'organe\"\n28: \"Tuberculose active, lèpre\"\n29: \"Tumeur maligne, affection maligne du tissu lymphatique ou hématopoïétique\"\n31: \"Affection hors liste\"\n32: \"Etat polypathologique\"', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='atcd');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'allaitementActuel', '', 'Allaitement', 'allaitement actuel', '', '', 'switch', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'allergies', 'allergies et intolérances', 'Allergies', 'Allergies et intolérances du patient', '', '', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'atcdFamiliaux', 'Antécédents familiaux', 'Antécédents familiaux', 'Antécédents familiaux', '', '', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'atcdMedicChir', 'Antécédents médico-chirurgicaux personnels', 'Antécédents médico-chirurgicaux', 'Antécédents médico-chirurgicaux personnels', '', '', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'baseSynthese', 'synthèse sur le patient', 'Synthèse patient', 'Synthèse sur le patient', '', '', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'dataImport', '', 'Import', 'support pour consultations importées', '', '', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '84600', '1'),
('medical', 'grossesseActuelle', '', 'Grossesse en cours', 'grossesse actuelle (gestion ON/OFF de la grossesse)', '', '', 'switch', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'insuffisanceHepatique', '', 'Insuffisance hépatique', 'degré d\'insuffisance hépatique', '', '', 'select', '\'z\': \"?\"\n\'n\': \"Pas d\'insuffisance hépatique connue\"\n\'1\': \'Légère\'\n\'2\': \'Modérée\'\n\'3\': \'Sévère\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'toxiques', 'tabac et drogues', 'Toxiques', 'habitudes de consommation', '', '', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catAllergiesStruc');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('relation', 'allergieCodeTheriaque', '', 'Code Thériaque de l\'allergie', 'code Thériaque de l\'allergie', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('relation', 'allergieLibelleTheriaque', '', 'Libelle Thériaque de l\'allergie', 'libelle Thériaque de l\'allergie', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catAtcdStruc');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'atcdStrucCIM10', '', 'Code CIM 10', 'code CIM 10 de l\'atcd', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucCIM10InLap', '', 'A prendre en compte dans le LAP', 'prise en compte ou non dans le LAP', '', '', 'select', '\'o\': \'oui\'\n\'n\': \'non\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'atcdStrucCIM10Label', '', 'Label CIM 10', 'label CIM 10 de l\'atcd', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucDateDebutAnnee', '', 'Année', 'année de début de l\'atcd', '', '', 'number', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucDateDebutJour', '', 'Jour', 'jour de début de l\'atcd', '', '', 'number', '0', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucDateDebutMois', '', 'Mois', 'mois de début de l\'atcd', '', '', 'select', '\'0\' : \'non précisé\'\n\'1\' : \'janvier\'\n\'2\' : \'février\'\n\'3\' : \'mars\'\n\'4\' : \'avril\'\n\'5\' : \'mai\'\n\'6\' : \'juin\'\n\'7\' : \'juillet\'\n\'8\' : \'août\'\n\'9\' : \'septembre\'\n\'10\' : \'octobre\'\n\'11\' : \'novembre\'\n\'12\' : \'décembre\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucDateFinAnnee', '', 'Année', 'année de fin de l\'atcd', '', '', 'number', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucDateFinJour', '', 'Jour', 'jour de fin de l\'atcd', '', '', 'number', '0', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucDateFinMois', '', 'Mois', 'mois de fin de l\'atcd', '', '', 'select', '\'0\' : \'non précisé\'\n\'1\' : \'janvier\'\n\'2\' : \'février\'\n\'3\' : \'mars\'\n\'4\' : \'avril\'\n\'5\' : \'mai\'\n\'6\' : \'juin\'\n\'7\' : \'juillet\'\n\'8\' : \'août\'\n\'9\' : \'septembre\'\n\'10\' : \'octobre\'\n\'11\' : \'novembre\'\n\'12\' : \'décembre\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucNotes', 'notes concernant cet antécédent', 'Notes', 'notes concernant l\'atcd', '', '', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catDataAdminGroupe');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'groupname', 'nom du groupe', 'Nom du groupe', 'nom du groupe', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catDataAdminRegistre');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'registryAuthorisationDate', '', 'Date d\'autorisation du registre', 'date d\'autorisation du registre', '', '', 'date', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'registryAuthorisationEndDate', '', 'Date de fin d\'autorisation du registre', 'date de fin d\'autorisation du registre', '', '', 'date', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'registryPrefixTech', '', 'Préfixe technique', 'préfixe technique pour qualifier les éléments de structuration du registre', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '12'),
('admin', 'registryState', '', 'État du registre', 'état du registre', '', '', 'select', '\'actif\' : \'registre actif\'\n\'suspendu\' : \'registre suspendu\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '4'),
('admin', 'registryname', 'nom du registre', 'Nom du registre', 'nom du registre', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catDataTransversesFormCs');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'codeTechniqueExamen', '', 'Acte lié à l\'examen réalisé', 'code acte caractérisant l\'examen fait via le formulaire qui l\'emploie', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catMarqueursAdminDossiers');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'administratifMarqueurDestruction', '', 'Dossier détruit', 'marqueur pour la destruction d\'un dossier', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '11'),
('admin', 'administratifMarqueurPasRdv', '', 'Ne pas donner de rendez-vous', '', '', '', 'switch', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'administratifMarqueurSuppression', '', 'Dossier supprimé', 'marqueur pour la suppression d\'un dossier', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'peopleExportID', '', 'Id aléatoire export', 'id aléatoire export', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catModelesCertificats');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('courrier', 'modeleCertifVierge', '', 'Certificat', 'modèle de certificat vierge', '', '', '', 'certif-certificatVierge', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catModelesCourriers');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('courrier', 'modeleCourrierTtEnCours', '', 'Traitement en cours', 'modèle de courrier pour l\'impression du traitement en cours', '', '', '', 'courrier-ttEnCours', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '6'),
('courrier', 'modeleCourrierVierge', '', 'Courrier', 'modèle de courrier vierge', '', '', '', 'courrier-courrierVierge', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catModelesMailsToApicrypt');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('courrier', 'mmDefautApi', '', 'Défaut', 'modèle mail par défaut', 'base', '', '', 'Cher confrère,\n\nVeuillez trouver en pièce jointe un document concernant notre patient commun.\nVous souhaitant bonne réception.\n\nBien confraternellement\n\n', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catTypeCsATCD');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('typecs', 'csAldDeclaration', NULL, 'Déclaration ALD', 'support parent pour déclaration ALD', NULL, NULL, '', 'aldDeclaration', 'base', @catID, '1', '2019-01-01 00:00:00', '84600', '1'),
('typecs', 'csAtcdStrucDeclaration', NULL, 'Ajout d\'antécédent', 'support parent pour déclaration d\'antécédent structuré', NULL, NULL, '', 'atcdStrucDeclaration', 'base', @catID, '1', '2019-01-01 00:00:00', '84600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catTypesUsageSystem');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('system', 'ageCalcule', '', 'Age calculé', 'Age calculé (formulaire d\'affichage)', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('system', 'currentPassword', 'Mot de passe actuel', 'Mot de passe actuel', 'Mot de passe actuel de l\'utilisateur', 'required', 'Le mot de passe actuel est manquant', 'password', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'date', '', 'Début de période', '', '', '', 'date', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'identite', '', 'Identité', 'LASTNAME Firstname (BIRTHNAME) (formulaire d\'affichage)', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('system', 'module', '', 'Module', 'module', '', '', 'select', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'otpCode', 'code otp', 'code otp', 'code otp', '', 'Le code otp est manquant', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'password', 'mot de passe', 'Mot de passe', 'mot de passe utilisateur', '', 'Le mot de passe est manquant', 'password', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'submit', '', 'Valider', 'bouton submit de validation', '', '', 'submit', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'template', '', 'Template', 'template', '', '', 'select', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'username', 'nom d\'utilisateur', 'Nom d\'utilisateur', 'nom d\'utilisateur', 'required', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'verifPassword', 'confirmation du mot de passe', 'Confirmation du mot de passe', 'Confirmation du mot de passe utilisateur', 'required', 'La confirmation du mot de passe est manquante', 'password', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='clicRDV');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'clicRdvCalId', 'Agenda', 'Agenda', 'Agenda sélectionné', '', '', 'select', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '4'),
('admin', 'clicRdvConsultId', 'Consultations', 'Consultations', 'Correspondance entre consultations', '', '', 'select', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '5'),
('admin', 'clicRdvGroupId', 'Groupe', 'Groupe', 'Groupe Sélectionné', '', '', 'select', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '3'),
('admin', 'clicRdvPassword', 'Mot de passe', 'Mot de passe', 'Mot de passe (chiffré)', '', '', 'password', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '2'),
('admin', 'clicRdvUserId', 'identifiant', 'identifiant', 'email@address.com', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');



SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='contact');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'emailApicrypt', 'adresse mail apicript', 'Email apicrypt', 'Email apicrypt', 'valid_email', '', 'email', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'faxPro', 'fax professionel', 'Fax professionnel', 'FAx pro', 'phone', '', 'tel', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'homePhone', '0x xx xx xx xx', 'Téléphone domicile', 'Téléphone du domicile de la forme 0x xx xx xx xx', 'phone', 'Le numéro de téléphone du domicile n\'est pas correct', 'tel', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'mobilePhone', 'mobile: 0x xx xx xx xx', 'Téléphone mobile', 'Numéro de téléphone commençant par 06 ou 07', 'mobilphone', 'Le numéro de téléphone mobile est incorrect', 'tel', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'mobilePhonePro', '06 xx xx xx xx', 'Téléphone mobile pro.', 'Numéro de téléphone commençant par 06 ou 07', 'mobilphone', 'Le numéro de téléphone mobile pro est incorrect', 'tel', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'personalEmail', 'email@domain.ext', 'Email personnelle', 'Adresse email personnelle', 'valid_email', 'L\'adresse email n\'est pas correcte. Elle doit être de la forme email@domain.net', 'email', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'pgpPublicKey', '', 'Clef publique PGP', 'Clef publique PGP', '', '', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '14'),
('admin', 'profesionnalEmail', 'email@domain.ext', 'Email professionnelle', 'Adresse email professionnelle', 'valid_email', 'L\'adresse email n\'est pas correcte. Elle doit être de la forme email@domain.net', 'email', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'telPro', 'téléphone professionnel', 'Téléphone professionnel', 'Téléphone pro.', 'phone', '', 'tel', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'telPro2', 'téléphone professionnel 2', 'Téléphone professionnel 2', 'Téléphone pro. 2', 'phone', '', 'tel', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='csAutres');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('typecs', 'csImport', '', 'Import', 'support parent pour import', '', '', '', 'baseImportExternal', 'base', @catID, '1', '2019-01-01 00:00:00', '84600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='csBase');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('typecs', 'csBaseGroup', '', 'Consultation', 'support parent pour les consultations', '', '', '', 'baseConsult', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='dataBio');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'clairanceCreatinine', '', 'Clairance créatinine', 'clairance de la créatinine en mL/min', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'creatinineMgL', '', 'Créatinine', 'créatinine en mg/l', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'creatinineMicroMolL', '', 'Créatinine', 'créatinine en μmol/l', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='dataCliniques');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'freqCardiaque', '', 'FC', 'fréquence cardiaque en bpm', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '60', '1'),
('medical', 'imc', '', 'IMC', 'IMC (autocalcule)', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'poids', '', 'Poids', 'poids du patient en kg', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'spO2', '', 'SpO2', 'saturation en oxygène', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '60', '1'),
('medical', 'taDiastolique', '', 'TAD', 'tension artérielle diastolique en mm Hg', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '60', '1'),
('medical', 'taSystolique', '', 'TAS', 'tension artérielle systolique en mm Hg', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '60', '1'),
('medical', 'taillePatient', '', 'Taille', 'taille du patient en cm', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='dataCsBase');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'examenDuJour', 'examen du jour', 'Examen du jour', 'examen du jour', '', '', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='dataSms');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('mail', 'smsId', '', 'smsId', 'id du sms', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='declencheur');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('typecs', 'nouvelleGrossesse', '', 'Nouvelle grossesse', 'support parent pour nouvelle grossesse', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='divers');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'notes', 'notes', 'Notes', 'Zone de notes', '', '', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'notesPro', 'notes pros', 'Notes pros', 'Zone de notes pros', '', '', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='docForm');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('doc', 'docOriginalName', '', 'Nom original', 'nom original du document', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('doc', 'docOrigine', '', 'Origine du document', 'origine du document : interne ou externe(null)', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('doc', 'docRegistre', '', 'Registre lié au document', 'registre lié au document', '', '', 'number', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('doc', 'docTitle', '', 'Titre', 'titre du document', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('doc', 'docType', '', 'Type du document', 'type du document importé', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='docPorteur');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('doc', 'docPorteur', '', 'Document', 'porteur pour nouveau document importé', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='grossesse');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'DDR', 'ddr', 'DDR', 'date des dernières règles', '', 'validedate,\'d/m/Y\'', 'date', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'ddg', 'ddg', 'DDG (théorique)', 'date de début de grossesse', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'ddgReel', '', 'DDG (retenue)', 'date de début de grossesse corrigé', '', '', 'date', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'groFermetureSuivi', '', 'Fermeture de la grossesse', 'date de fermeture de la grossesse (porteur)', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'terme9mois', '', 'Terme (9 mois)', 'terme', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'termeDuJour', '', 'Terme du jour', 'terme du jour', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='idDicom');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('dicom', 'dicomInstanceID', '', 'InstanceID', '', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('dicom', 'dicomSerieID', '', 'SerieID', '', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('dicom', 'dicomStudyID', '', 'StudyID', '', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='identity');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'administrativeGenderCode', '', 'Sexe', 'Sexe', '', '', 'select', 'F: \'Femme\'\nM: \'Homme\'\nU: \'Inconnu\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'birthdate', 'naissance: dd/mm/YYYY', 'Date de naissance', 'Date de naissance au format dd/mm/YYYY', 'validedate,\'d/m/Y\'', 'La date de naissance indiquée n\'est pas valide', 'date', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'birthname', 'nom', 'Nom de naissance', 'Nom reçu à la naissance', 'identite', 'Le nom de naissance est indispensable et ne doit pas contenir de caractères interdits', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'deathdate', 'décès: dd/mm/YYYY', 'Date de décès', 'Date de décès au format dd/mm/YYYY', 'validedate,\'d/m/Y\'', 'La date de décès indiquée n\'est pas valide', 'date', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'firstname', 'prénom', 'Prénom', 'Prénom figurant sur la pièce d\'identité', 'identite', 'Le prénom est indispensable et ne doit pas contenir de caractères interdits', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'lastname', 'nom marital ou d\'usage', 'Nom d\'usage', 'Nom utilisé au quotidien', 'identite', 'Le nom d\'usage ne doit pas contenir de caractères interdits', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'othersfirstname', 'liste des prénoms secondaires', 'Autres prénoms', 'Les autres prénoms d\'une personne', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'titre', 'Dr, Pr ...', 'Titre', 'Titre du pro de santé', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='internet');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'twitterAccount', '', 'Twitter', 'Compte twitter', 'twitterAccount', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'website', '', 'Site web', 'Site web', 'url', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapCatLignePrescription');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'lapLignePrescriptionDatePriseDebut', '', 'Date de début de prise', 'date de début de prise', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionDatePriseFin', '', 'Date de fin de prise', 'date de fin de prise', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionDatePriseFinAvecRenouv', '', 'Date de fin de prise renouvellements inclus', 'date de fin de prise renouvellements inclus', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionDatePriseFinEffective', '', 'Date effective de fin de prise', 'date effective de fin de prise', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionDureeJours', '', 'Durée de la prescription en jours', 'durée de la prescription en jours', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionIsALD', '', 'isALD', 'ligne ALD ou non', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionIsChronique', '', 'isChronique', 'ligne TT chronique ou non', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionRenouvelle', '', 'ID de la ligne qui est renouvelée par cette ligne', 'ID de la ligne qui est renouvelée par cette ligne', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapCatMedicament');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'lapMedicamentCodeATC', '', 'Code ATC du médicament', 'code ATC du médicament', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapMedicamentCodeSubstanceActive', '', 'Code substance active du médicament', 'code substance active du médicament', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapMedicamentDC', '', 'DC du médicament', 'DC du médicament', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapMedicamentEstPrescriptibleEnDC', '', 'Médicament prescriptible en DC', 'médicament prescriptible en DC', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapMedicamentMotifPrescription', '', 'Motif de prescription du médicament', 'motif de prescription du médicament', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapMedicamentPresentationCodeTheriaque', '', 'Code Thériaque de la présentation', 'code Thériaque de la présentation (a priori le CIP7)', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapMedicamentSpecialiteCodeTheriaque', '', 'Code Thériaque de la spécialité', 'code Thériaque de la spécialité', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapMedicamentSpecialiteNom', '', 'Nom de la spécialité', 'nom de la spécialité', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapCatPorteurs');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'lapLigneMedicament', '', 'Médicament', 'médicament LAP', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescription', '', 'Ligne de prescription', 'ligne de prescription LAP', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapOrdonnance', '', 'Ordonnance', 'ordonnance LAP', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapSam', '', 'SAM', 'porteur SAM LAP', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapExterne');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'lapExtOrdonnance', '', 'Porteur', '', '', '', 'number', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='mailForm');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('mail', 'mailBody', 'texte du message', 'Message', 'texte du message', '', '', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailFrom', 'email@domain.net', 'De', 'mail from', '', '', 'email', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailModeles', '', 'Modèle', 'liste des modèles', '', '', 'select', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailPJ1', '', 'ID pièce jointe', 'id de la pièce jointe au mail', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailSujet', 'sujet du mail', 'Sujet', 'sujet du mail', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailTo', '', 'A', 'mail to', '', '', 'email', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailToApicrypt', '', 'A (correspondant apicrypt)', 'Champ pour les correspondants apicrypt', '', '', 'email', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailToEcofaxName', '', 'Destinataire du fax', 'Destinataire du fax (ecofax OVH)', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailToEcofaxNumber', '', 'Numéro de fax du destinataire', 'Numéro du destinataire du fax (ecofax OVH)', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailTrackingID', '', 'TrackingID', 'num de tracking du mail dans le service externe', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='numAdmin');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'PSCodeProSpe', '', 'Code normé de la profession/spécialité du praticien', 'code normé de la profession/spécialité du praticien', '', '', 'select', '\'Z\' : \'Jeu de valeurs normées absent\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'PSCodeStructureExercice', '', 'Code normé de la structure d\'exercice du praticien', 'code normé de la structure d\'exercice du praticien', '', '', 'select', '\'Z\' : \'Jeu de valeurs normées absent\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'PSIdNat', '', 'Identifiant national praticien santé', 'identifiant national praticien santé', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'adeli', 'adeli', 'Adeli', 'n° adeli', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'nReseau', '', 'Numéro de réseau', 'numéro de réseau (dépistage)', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'nss', '', 'Numéro de sécu', 'numéro de sécurité sociale', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'nmu', '', 'Numéro de mutuelle', 'numéro de mutelle', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'rpps', 'rpps', 'RPPS', 'rpps', 'numeric', '', 'number', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='ordoItems');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'ordoImpressionNbLignes', '', 'Imprimer le nombre de lignes de prescription', 'imprimer le nombre de lignes de prescription', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'ordoLigneOrdo', '', 'Ligne d\'ordonnance', 'porteur pour une ligne d\'ordo', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'ordoLigneOrdoALDouPas', '', 'Ligne d\'ordonnance : ald', '1 si ald', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'ordoTypeImpression', '', 'Type ordonnance impression', 'type d\'ordonnance pour impression', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='porteursOrdo');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'ordoPorteur', '', 'Ordonnance', 'Ordonnance simple', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='porteursReglement');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('reglement', 'reglePorteurLibre', '', 'Règlement', 'Règlement hors convention', '', '', '', 'baseReglementLibre', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'reglePorteurS1', '', 'Règlement', 'Règlement conventionné S1', '', '', '', 'baseReglementS1', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'reglePorteurS2', '', 'Règlement', 'Règlement conventionné S2', '', '', '', 'baseReglementS2', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='porteursTech');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('mail', 'mailPorteur', '', 'Mail', 'porteur pour les mails', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'smsPorteur', '', 'Mail', 'porteur pour les sms', '', '', '', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='reglementItems');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('reglement', 'regleCB', '', 'CB', 'montant versé en CB', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleCheque', '', 'Chèque', 'montant versé en chèque', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleDepaCejour', '', 'Dépassement', 'dépassement pratiqué ce jour', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleDetailsActes', '', 'Détails des actes', 'détails des actes de la facture', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleEspeces', '', 'Espèces', 'montant versé en espèce', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleFacture', '', 'Facturé', 'facturé ce jour', '', '', 'text', '0', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleFseData', '', 'FSE data', 'data de la FSE générée par service tiers', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleIdentiteCheque', 'n° de chèque, nom du payeur si différent du patient,...', 'Informations paiement', 'Information complémentaires sur le paiement', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleModulCejour', '', 'Modulation', 'modulation appliquée ce jour', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleSecteurGeoTarifaire', '', 'Secteur géographique tarifaire', 'secteur géographique tarifaire', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleSecteurHonoraires', '', 'Secteur tarifaire', 'secteur tarifaire appliqué', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleSecteurHonorairesNgap', '', 'Secteur tarifaire NGAP', 'secteur tarifaire NGAP appliqué', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleSecteurIK', '', 'Secteur tarifaire pour IK', 'secteur tarifaire IK appliqué', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleSituationPatient', '', 'Situation du patient', 'situation du patient : cmu / tp / tout venant', '', '', 'select', '\'G\' : \'Tout venant\'\n\'CMU\' : \'CMU\'\n\'TP\' : \'Tiers payant AMO\'\n\'TP ALD DEP\' : \'ALD : tiers payant AVEC dépassement \'\n\'TP ALD\' : \'ALD : tiers payant SANS dépassement \'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleTarifLibreCejour', '', 'Tarif', 'tarif appliqué ce jour', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleTarifSSCejour', '', 'Tarif SS', 'tarif SS appliqué ce jour', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleTiersPayeur', '', 'Tiers', 'part du tiers', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='relationRelations');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('relation', 'relationExternePatient', '', 'Relation externe patient', 'relation externe patient', '', '', 'number', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('relation', 'relationGroupeRegistre', '', 'Relation groupe registre', 'relation groupe registre', '', '', 'select', '\'membre\': \'Membre\'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('relation', 'relationID', '', 'Porteur de relation', 'porteur de relation entre patients ou entre patients et praticiens', '', '', 'number', '', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('relation', 'relationPatientGroupe', '', 'Relation patient groupes', 'relation patient groupes', '', '', 'select', '\'membre\': \'membre\'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('relation', 'relationPatientPatient', '', 'Relation patient patient', 'relation patient patient', '', '', 'select', '\'conjoint\': \'conjoint\'\n\'enfant\': \'parent\'\n\'parent\': \'enfant\'\n\'grand parent\': \'petit enfant\'\n\'petit enfant\': \'grand parent\'\n\'sœur / frère\': \'sœur / frère\' \n\'tante / oncle\': \'nièce / neveu\' \n\'nièce / neveu\': \'tante / oncle\' \n\'cousin\': \'cousin\'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('relation', 'relationPatientPraticien', '', 'Relation patient praticien', 'relation patient  praticien', '', '', 'select', '\'MTD\': \'Médecin traitant déclaré\'\n\'MT\': \'Médecin traitant\'\n\'MS\': \'Médecin spécialiste\'\n\'Autre\': \'Autre correspondant\'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('relation', 'relationPraticienGroupe', '', 'Relation praticien groupe', 'relation praticien groupe', '', '', 'select', '\'membre\': \'Membre\'\n\'admin\': \'Administrateur\'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('relation', 'relationRegistrePatient', '', 'Relation registre patient', 'relation registre patient', '', '', 'select', '\'inclus\': \'inclus\'\n\'exclu\': \'exclu\'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('relation', 'relationRegistrePraticien', '', 'Relation praticien registre', 'relation praticien registre', '', '', 'select', '\'admin\': \'Administrateur\'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');

-- configuration
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES
('PraticienPeutEtrePatient', 'default', '0', '', 'Options', 'true/false', 'si false, le praticien peut toujours avoir une fiche patient séparée', 'true'),
('VoirRouletteObstetricale', 'default', '0', '', 'Options', 'true/false', 'activer le lien roulette obstétricale du menu Outils', 'true'),
('activGenBarreCode', 'default', '0', '', 'Options', 'true/false', 'Activer ou non la fonctionnalité permettant de générer les codes barres RPPS et ADELI.', 'true'),
('administratifComptaPeutVoirRecettesDe', 'default', '0', '', 'Règlements', 'liste', 'ID des utilisateurs, séparés par des virgules (sans espace)', ''),
('administratifPeutAvoirAgenda', 'default', '0', '', 'Options', 'true/false', 'peut avoir un agenda à son nom', 'true'),
('administratifPeutAvoirFacturesTypes', 'default', '0', '', 'Règlements', 'true/false', 'peut avoir des factures types à son nom', 'false'),
('administratifPeutAvoirPrescriptionsTypes', 'default', '0', '', 'Options', 'true/false', 'peut avoir des prescriptions types à son nom', 'false'),
('administratifPeutAvoirRecettes', 'default', '0', '', 'Règlements', 'true/false', 'peut enregistrer des recettes à son nom', 'true'),
('administratifReglementFormulaires', 'default', '0', '', 'Règlements', 'liste', 'liste des formulaires de règlement disponible dans le dossier patient ', 'reglePorteurS1,reglePorteurS2,reglePorteurLibre'),
('administratifSecteurGeoTarifaire', 'default', '0', '', 'Règlements', 'dossier', 'zone géographique tarifaire (metro, 971, 972 ...)', 'metro'),
('administratifSecteurHonorairesCcam', 'default', '0', '', 'Règlements', '', 'grille tarifaire CCAM du praticien', '0'),
('administratifSecteurHonorairesNgap', 'default', '0', '', 'Règlements', 'texte', 'Code profession pour le secteur tarifaire NGAP', 'mspe'),
('administratifSecteurIK', 'default', '0', '', 'Règlements', 'texte', 'tarification des IK : indiquer plaine ou montagne', 'plaine'),
('agendaDistantLink', 'default', '0', '', 'Agenda', 'url', 'lien à appliquer à Agenda sur les pages MedShakeEHR. Si agendaService est configuré, alors agendaDistantLink doit être vide', ''),
('agendaDistantPatientsOfTheDay', 'default', '0', '', 'Agenda', 'url', 'url distante où l’on peut récupérer une liste horodatée des patients du jour', ''),
('agendaEnvoyerChiffreParMail', 'default', '0', '', 'Agenda', 'true/false', 'activer le service d\'envoi par mail de l\'agenda futur chiffré GPG', ''),
('agendaEnvoyerChiffreTo', 'default', '0', '', 'Agenda', 'texte', 'adresse email à laquelle envoyer l\'agenda chiffré GPG - séparer par virgule si plusieurs ', ''),
('agendaJoursFeriesAfficher', 'default', '0', '', 'Agenda', 'true/false', 'afficher les jours fériés sur l\'agenda', 'true'),
('agendaJoursFeriesFichier', 'default', '0', '', 'Agenda', 'fichier', 'fichier csv à considérer dans ressources/agenda/ soit jours-feries-seuls.csv ou jours-feries-seuls-alsace-moselle.csv', 'jours-feries-seuls.csv'),
('agendaLocalPatientsOfTheDay', 'default', '0', '', 'Agenda', 'fichier', 'fichier json de la liste horodatée des patients du jour', 'patientsOfTheDay.json'),
('agendaModePanneauLateral', 'default', '0', '', 'Agenda', 'true/false', 'Utilisation du panneau latéral (true) ou d\'une fenêtre contextuelle (false)', 'true'),
('agendaNumberForPatientsOfTheDay', 'default', '0', '', 'Agenda', 'nombre', 'Numéro d\'agenda pour générer à partir de l\'agenda interne concerné une liste des patients du jour pour le menu Patients', '0'),
('agendaPremierJour', 'default', '0', '', 'Agenda', 'vide/nombre', 'vide pour roulant, 0 pour dimanche, 1 pour lundi, etc...', '1'),
('agendaRefreshDelayEvents', 'default', '0', '', 'Agenda', 'int', 'délai en secondes du rafraîchissement live de l\'agenda - 0 pour jamais', '10'),
('agendaRefreshDelayMenuPOTD', 'default', '0', '', 'Agenda', 'nombre', 'délai en secondes du rafraîchissement du menu Patients du jour - 0 pour jamais', '5'),
('agendaService', 'default', '0', '', 'Agenda', 'vide/clicRDV', 'si non vide, active le service tiers concerné', ''),
('allMySmsApiKey', 'default', '0', '', 'Rappels SMS', 'texte', 'API key allMySMS', ''),
('allMySmsLogin', 'default', '0', '', 'Rappels SMS', 'texte', 'login allMySMS', ''),
('apiCcamNgapKey', 'default', '0', '', 'Règlements', 'string', 'Clef de l\'API CCAM NGAP MedShake', ''),
('apiCcamNgapUrl', 'default', '0', '', 'Règlements', 'url', 'URL de l\'API CCAM NGAP MedShake', ''),
('apicrypt2CertName', 'default', '0', '', 'Apicrypt', 'texte', 'nom du certificat à utiliser', ''),
('apicrypt2CertPassword', 'default', '0', '', 'Apicrypt', 'texte', 'mot de passe du certificat', ''),
('apicryptAdresse', 'default', '0', '', 'Apicrypt', 'texte', 'adresse complète apicrypt, ex :  prenom.NOM@medicalXX.apicrypt.org', ''),
('apicryptCheminArchivesInbox', 'default', '0', '', 'Apicrypt', 'dossier', 'chemin du répertoire qui sert à archiver par date de traitement les messages reçus, classés dans les dossiers comme non classés', ''),
('apicryptCheminFichierC', 'default', '0', '', 'Apicrypt', 'dossier', 'répertoire de travail apicrypt, fichiers chiffrés', ''),
('apicryptCheminFichierNC', 'default', '0', '', 'Apicrypt', 'dossier', 'répertoire de travail pour Apicrypt, fichier non chiffrés', ''),
('apicryptCheminInbox', 'default', '0', '', 'Apicrypt', 'dossier', 'chemin du répertoire qui sert de boite de réception, doit être en zone accessible web', ''),
('apicryptCheminVersBinaires', 'default', '0', '', 'Apicrypt', 'dossier', 'chemin vers le répertoire contenant les programmes Apicrypt en ligne de commande', ''),
('apicryptCheminVersClefs', 'default', '0', '', 'Apicrypt', 'dossier', 'chemin vers les répertoire Clefs Apicrypt contenant les clefs de l’utilisateur', ''),
('apicryptDefautSujet', 'default', '0', '', 'Apicrypt', 'texte', 'sujet par défaut des mails Apicrypt (attention, n\'est pas chiffré : jamais d\'éléments d\'identité dans le sujet !)', 'Document concernant votre patient'),
('apicryptInboxMailForUserID', 'default', '0', '', 'Apicrypt', 'nombre', 'ID ou IDs numériques des comptes utilisateurs (séparés par des virgules) pour lesquels l\'utilisateur courant peut voir les mails Apicrypt relevés en inbox', ''),
('apicryptPopHost', 'default', '0', '', 'Apicrypt', 'url/ip', 'serveur pop pour la réception des messages Apicrypt', 'pop.intermedic.org'),
('apicryptPopPassword', 'default', '0', '', 'Apicrypt', 'texte', 'mot de passe apicrypt', ''),
('apicryptPopPort', 'default', '0', '', 'Apicrypt', 'nombre', 'port du serveur pop', '110'),
('apicryptPopUser', 'default', '0', '', 'Apicrypt', 'texte', 'nom d\'utilisateur pour le serveur pop : prenom.NOM', ''),
('apicryptSmtpHost', 'default', '0', '', 'Apicrypt', 'url/ip', 'serveur smtp pour l\'envoi des messages Apicrypt, en règle générale : smtp.intermedic.org', 'smtp.intermedic.org'),
('apicryptSmtpPort', 'default', '0', '', 'Apicrypt', 'nombre', 'port du serveur SMTP', '587'),
('apicryptUtilisateur', 'default', '0', '', 'Apicrypt', 'texte', 'nom d\'utilisateur Apicrypt (portion devant le @ de l\'adresse)', ''),
('apicryptVersion', 'default', '0', '', 'Apicrypt', 'texte', 'version d\'Apicrypt à mettre en œuvre ', '1'),
('clicRdvApiKey', 'default', '0', '', 'clicRDV', 'texte', '', ''),
('clicRdvCalId', 'default', '0', '', 'clicRDV', 'nombre', '', ''),
('clicRdvConsultId', 'default', '0', '', 'clicRDV', 'JSON', '', ''),
('clicRdvGroupId', 'default', '0', '', 'clicRDV', 'nombre', '', ''),
('clicRdvPassword', 'default', '0', '', 'clicRDV', 'texte', '', ''),
('clicRdvUserId', 'default', '0', '', 'clicRDV', 'texte', '', ''),
('click2callService', 'default', '0', '', 'Click2call', 'string', 'nom du service Click2call à activer (OVH)', ''),
('designAppName', 'default', '0', '', 'Ergonomie et design', 'texte', 'nom de l\'application', 'MedShakeEHR'),
('designInboxMailsSortOrder', 'default', '0', '', 'Ergonomie et design', 'texte', 'sens du tri des mails en colonne latérale : date ascendante (asc) ou descendante (desc) ', 'desc'),
('designTopMenuDropboxCountDisplay', 'default', '0', '', 'Ergonomie et design', 'true/false', 'afficher dans le menu de navigation du haut de page le nombre de fichier dans la boite de dépôt', 'true'),
('designTopMenuInboxCountDisplay', 'default', '0', '', 'Ergonomie et design', 'true/false', 'afficher dans le menu de navigation du haut de page le nombre de nouveaux messages dans la boite de réception', 'true'),
('designTopMenuSections', 'default', '0', '', 'Ergonomie et design', 'textarea', 'éléments et ordre de la barre de navigation du menu supérieur (yaml : commenter avec #)', '- agenda\n- patients\n- praticiens\n- groupes\n- registres\n- compta\n- inbox\n- dropbox\n- transmissions\n- outils'),
('designTopMenuStyle', 'default', '0', '', 'Ergonomie et design', 'icones / textes', 'aspect du menu de navigation du haut de page', 'icones'),
('designTopMenuTooltipDisplay', 'default', '0', '', 'Ergonomie et design', 'true/false', 'si true, affiche les infos bulles sur icones du menu supérieur', 'false'),
('designTopMenuTransmissionsColorIconeImportant', 'default', '0', '', 'Ergonomie et design', 'true/false', 'colore l\'icône transmission si transmission importante non lue', 'true'),
('designTopMenuTransmissionsColorIconeUrgent', 'default', '0', '', 'Ergonomie et design', 'true/false', 'colore l\'icône transmission si transmission urgente non lue', 'true'),
('designTopMenuTransmissionsCountDisplay', 'default', '0', '', 'Ergonomie et design', 'true/false', 'afficher dans le menu de navigation du haut de page le nombre de transmissions non lues', 'true'),
('dicomAutoSendPatient', 'default', '0', '', 'DICOM', 'true/false', 'générer automatiquement le fichier worklist pour Orthanc à l\'ouverture d’un dossier patient. Ne pas mettre à true pour une secrétaire par exemple !', 'false'),
('dicomDiscoverNewTags', 'default', '0', '', 'DICOM', 'true/false', 'enregistrer automatiquement dans la base de données les nouveaux tags dicom rencontrés lors de la visualisation d\'études afin de pouvoir les associer par la suite automatiquement avec des données de formulaire MedShakeEHR', 'true'),
('dicomHost', 'default', '0', '', 'DICOM', 'url/ip', 'IP du serveur Orthanc', ''),
('dicomPort', 'default', '0', '', 'DICOM', 'nombre', 'port de l\'API Orthanc (défaut 8042)', '8042'),
('dicomPrefixIdPatient', 'default', '0', '', 'DICOM', 'texte', 'prefix à appliquer à l\'identifiant numérique MedShakeEHR pour en faire un identifiant DICOM unique', '1.100.100'),
('dicomProtocol', 'default', '0', '', 'DICOM', 'texte', 'http:// ou https:// ', 'http://'),
('dicomWorkListDirectory', 'default', '0', '', 'DICOM', 'dossier', 'chemin du répertoire où Orthanc va récupérer le fichier dicom worklist généré par MedShakeEHR pour le passer à l\'appareil d\'imagerie', ''),
('dicomWorkingDirectory', 'default', '0', '', 'DICOM', 'dossier', 'répertoire de travail local où on peut rapatrier des images à partir d\'Orthanc pour les parcourir ou les traiter (pdf, zip ...). Utiliser en général le même répertoire que celui indiqué dans workingDirectory des paramètres généraux. Doit être en zone web accessible', ''),
('droitDossierPeutAssignerPropresGroupesPraticienFils', 'default', '0', '', 'Droits', 'true/false', 'si true, peut assigner ses propres appartenances aux groupes à un praticien créé par lui-même', 'false'),
('droitDossierPeutCreerPraticien', 'default', '0', '', 'Droits', 'true/false', 'si true, peut créer des dossiers praticiens', 'true'),
('droitDossierPeutRechercherParPeopleExportID', 'default', '0', '', 'Droits', 'true/false', 'si true, autorisation à rechercher par peopleExportID', 'false'),
('droitDossierPeutRetirerPraticien', 'default', '0', '', 'Droits', 'true/false', 'si true, peut retirer le statut praticien à un dossier (retour à patient, réciproque de droitDossierPeutCreerPraticien)', 'true'),
('droitDossierPeutSupPatient', 'default', '0', '', 'Droits', 'true/false', 'si true, peut supprimer des dossiers patients (non définitivement)', 'true'),
('droitDossierPeutSupPraticien', 'default', '0', '', 'Droits', 'true/false', 'si true, peut supprimer des dossiers praticiens (non définitivement)', 'true'),
('droitDossierPeutTransformerPraticienEnUtilisateur', 'default', '0', '', 'Droits', 'true/false', 'si true, peut rendre utilisateur un praticien', 'false'),
('droitDossierPeutVoirUniquementPatientsGroupes', 'default', '0', '', 'Droits', 'true/false', 'si true, peut voir tous les dossiers créés par les autres praticiens des groupes', 'false'),
('droitDossierPeutVoirUniquementPatientsPropres', 'default', '0', '', 'Droits', 'true/false', 'si true, peut voir tous les dossiers créés par les autres praticiens', 'false'),
('droitDossierPeutVoirUniquementPraticiensGroupes', 'default', '0', '', 'Droits', 'true/false', 'si true, peut voir uniquement les praticiens appartenant aux mêmes groupes', 'false'),
('droitExportPeutExporterPropresData', 'default', '0', '', 'Droits', 'true/false', 'si true, peut exporter ses propres datas', 'true'),
('droitExportPeutExporterToutesDataGroupes', 'default', '0', '', 'Droits', 'true/false', 'si true, peut exporter les datas générées par les autres praticiens de ses groupes', 'false'),
('droitGroupePeutCreerGroupe', 'default', '0', '', 'Droits', 'true/false', 'si true, peut créer des groupes', 'false'),
('droitGroupePeutVoirTousGroupes', 'default', '0', '', 'Droits', 'true/false', 'si true, peut voir tous les groupes ', 'false'),
('droitRegistrePeutCreerRegistre', 'default', '0', '', 'Droits', 'true/false', 'si true, peut créer des registres', 'false'),
('droitRegistrePeutGererAdministrateurs', 'default', '0', '', 'Droits', 'true/false', 'si true, peut gérer les administrateurs registres', 'false'),
('droitRegistrePeutGererGroupes', 'default', '0', '', 'Droits', 'true/false', 'si true, peut gérer les groupes participant à un registre', 'false'),
('droitStatsPeutVoirStatsGenerales', 'default', '0', '', 'Droits', 'true/false', 'si true, peut voir les statistiques générales', 'true'),
('dropboxActiver', 'default', '0', '', 'Dropbox', 'true/false', 'permet d\'activer les fonctions de dropbox externe', 'false'),
('dropboxOptions', 'default', '0', '', 'Dropbox', 'textarea', 'options pour les fonctions de dropbox externe', ''),
('ecofaxMyNumber', 'default', '0', '', 'Fax', 'n° fax', 'numéro du fax en réception, ex: 0900000000', ''),
('ecofaxPassword', 'default', '0', '', 'Fax', 'texte', 'mot de passe du service de fax', ''),
('faxService', 'default', '0', '', 'Fax', 'vide/ecofaxOVH', 'si non vide, active le service tiers concerné', ''),
('formFormulaireListingGroupes', 'default', '0', '', 'Formulaires système', 'texte', 'nom du formulaire à utiliser pour le listing groupes', 'baseListingGroupes'),
('formFormulaireListingPatients', 'default', '0', '', 'Formulaires système', 'texte', 'nom du formulaire à utiliser pour le listing patients', 'baseListingPatients'),
('formFormulaireListingPraticiens', 'default', '0', '', 'Formulaires système', 'texte', 'nom du formulaire à utiliser pour le listing praticiens', 'baseListingPro'),
('formFormulaireListingRegistres', 'default', '0', '', 'Formulaires système', 'texte', 'nom du formulaire à utiliser pour le listing registres', 'baseListingRegistres'),
('formFormulaireNouveauGroupe', 'default', '0', '', 'Formulaires système', 'texte', 'nom du formulaire à utiliser pour la création d\'un nouveau groupe', 'baseNewGroupe'),
('formFormulaireNouveauPatient', 'default', '0', '', 'Formulaires système', 'texte', 'nom du formulaire à utiliser pour la création d\'un nouveau patient', 'baseNewPatient'),
('formFormulaireNouveauPraticien', 'default', '0', '', 'Formulaires système', 'texte', 'nom du formulaire à utiliser pour la création d\'un nouveau praticien', 'baseNewPro'),
('formFormulaireNouveauRegistre', 'default', '0', '', 'Formulaires système', 'texte', 'nom du formulaire à utiliser pour la création d\'un nouveau registre', 'baseNewRegistre'),
('groupesAutoAttachProGroupsToPatient', 'default', '0', '', 'Groupes', 'true/false', 'si true, attacher automatiquement les groupes du praticien aux patients créés', ''),
('groupesNbMaxGroupesParPro', 'default', '0', '', 'Groupes', 'nombre', 'nombre maximal de groupes qu\'un praticien peut intégrer (0 = non limité)', '1'),
('lapActiverAllergiesStrucSur', 'default', '0', '', 'LAP', 'texte', 'champs sur lesquels activer les Allergies structurées', ''),
('lapActiverAtcdStrucSur', 'default', '0', '', 'LAP', 'texte', 'champs sur lesquels activer les atcd structurés', ''),
('lapAlertPatientAllaitementSup3Ans', 'default', '0', '', 'LAP', 'true/false', 'alerte pour allaitement sup à 3 ans à l\'entrée dans le LAP', 'true'),
('lapAlertPatientTermeGrossesseSup46', 'default', '0', '', 'LAP', 'true/false', 'alerte pour terme sup à 46SA à l\'entrée dans le LAP', 'true'),
('lapAllergiesStrucPersoPourAnalyse', 'default', '0', '', 'LAP', 'texte', 'champs sur lesquels analyser les Allergies structurées', ''),
('lapAtcdStrucPersoPourAnalyse', 'default', '0', '', 'LAP', 'texte', 'champs sur lesquels analyser les atcd structurés', ''),
('lapPrintAllergyRisk', 'default', '0', '', 'LAP', 'true/false', 'imprimer les risques allergiques détectés', 'true'),
('lapSearchDefaultType', 'default', '0', '', 'LAP', 'texte', 'mode de recherche par défaut des médicaments', 'dci'),
('lapSearchResultsSortBy', 'default', '0', '', 'LAP', 'texte', 'ordre préférentiel d\'affichage des médicaments', 'nom'),
('mailRappelActiver', 'default', '0', '', 'Rappels mail', 'true/false', 'activer / désactiver les rappels par mail', 'false'),
('mailRappelDaysBeforeRDV', 'default', '0', '', 'Rappels mail', 'nombre', 'nombre de jours avant le rendez-vous pour l\'expédition du rappel', '3'),
('mailRappelLogCampaignDirectory', 'default', '0', '', 'Rappels mail', 'dossier', 'chemin du répertoire où on va loguer les rappels de rendez-vous par mail', ''),
('mailRappelMessage', 'default', '0', '', 'Rappels mail', 'textarea', 'Les balises #heureRdv, #jourRdv et #praticien seront automatiquement remplacées dans le message envoyé', 'Bonjour,\\n\\nNous vous rappelons votre RDV du #jourRdv à #heureRdv avec le Dr #praticien.\\nNotez bien qu’aucun autre rendez-vous ne sera donné à un patient n’ayant pas honoré le premier.\\n\\nMerci de votre confiance,\\nÀ bientôt !\\n\\nP.S. : Ceci est un mail automatique, merci de ne pas répondre.'),
('optionDossierPatientActiverCourriersCertificats', 'default', '0', '', 'Options dossier patient', 'true/false', 'si true, activer courriers et certificats', 'true'),
('optionDossierPatientActiverGestionALD', 'default', '0', '', 'Options dossier patient', 'true/false', 'si true, gérer les ALD', 'true'),
('optionDossierPatientInhiberHistoriquesParDefaut', 'default', '0', '', 'Options dossier patient', 'true/false', 'si true, déactive la production des informations pour les historiques par défaut', 'false'),
('optionGeActiverAgenda', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de la gestion agenda', 'true'),
('optionGeActiverApiRest', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de l\'API REST', 'true'),
('optionGeActiverCompta', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de la gestion compta', 'true'),
('optionGeActiverDicom', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation des fonctions liées au DICOM (nécessite Orthanc)', 'true'),
('optionGeActiverDropbox', 'default', '0', '', 'Activation services', 'true/false', 'permet d\'activer les fonctions de dropbox externe', 'false'),
('optionGeActiverGroupes', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de la gestion des groupes praticiens', 'false'),
('optionGeActiverInboxApicrypt', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de la inbox Apicrypt', 'true'),
('optionGeActiverLapExterne', 'default', '0', '', 'Activation services', 'true/false', 'activer / désactiver l\'utilisation d\'un LAP externe', 'false'),
('optionGeActiverLapInterne', 'default', '0', '', 'Activation services', 'true/false', 'activer / désactiver le LAP', 'false'),
('optionGeActiverPhonecapture', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de phonecapture (nécessite DICOM)', 'true'),
('optionGeActiverRappelsRdvMail', 'default', '0', '', 'Activation services', 'true/false', 'activer / désactiver les rappels par mail', 'false'),
('optionGeActiverRappelsRdvSMS', 'default', '0', '', 'Activation services', 'true/false', 'activer / désactiver les rappels par SMS', 'false'),
('optionGeActiverRegistres', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de la gestion de registres', 'false'),
('optionGeActiverSignatureNumerique', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation des fonctions de signature numérique de documents', 'true'),
('optionGeActiverTransmissions', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation des transmissions', 'true'),
('optionGeActiverVitaleLecture', 'default', '0', '', 'Activation services', 'true/false', 'activer / désactiver les services liés à la carte vitale', 'false'),
('optionGeAdminActiverLiensRendreUtilisateur', 'default', '0', '', 'Options', 'true/false', 'si true, l\'administrateur peut transformer des patients ou praticiens en utilisateur via les listings publiques', 'false'),
('optionGeCreationAutoPeopleExportID', 'default', '0', '', 'Options', 'true/false', 'si true, création automatique d\'un peopleExportID', 'true'),
('optionGeDestructionDataDossierPatient', 'default', '0', '', 'Options', 'true/false', 'si true, les options de destruction physique des dossiers patients sont activées', 'false'),
('optionGeExportDataConsentementOff', 'default', '0', '', 'Options', 'true/false', 'si true, exporter les données avec consentement non accepté ou retiré', 'true'),
('optionGeExportPratListSelection', 'default', '0', '', 'Options', 'true/false', 'si true, sélection possible des datas à exporter par liste praticiens, sinon auto déterminée par droits utilisateur courant', 'true'),
('optionGeLogin2FA', 'default', '0', '', 'Login', 'true/false', 'si true, activation du login à double facteur d\'authentification', 'false'),
('optionGeLoginCreationDefaultModule', 'default', '0', '', 'Login', 'texte', 'module par défaut pour création nouvel utilisateur', 'base'),
('optionGeLoginCreationDefaultTemplate', 'default', '0', '', 'Login', 'texte', 'template par défaut pour création nouvel utilisateur', ''),
('optionGeLoginPassAttribution', 'default', '0', '', 'Login', 'texte', 'méthode d\'attribution des mots de passe utilisateur : admin / random', 'admin'),
('optionGeLoginPassMinLongueur', 'default', '0', '', 'Login', 'int', 'longueur minimale autorisée du mot de passe utilisateur', '10'),
('optionGeLoginPassOnlineRecovery', 'default', '0', '', 'Login', 'true/false', 'possibilité de réinitialiser son mot de passe perdu via email ', 'false'),
('optionGePatientOuvrirApresCreation', 'default', '0', '', 'Options', 'dossier / liens', 'où rediriger après création d\'un nouveau patient', 'liens'),
('optionGePraticienMontrerPatientsLies', 'default', '0', '', 'Options', 'true/false', 'si true, montrer les patients liés au praticien sur la fiche pro', 'true'),
('ovhApplicationKey', 'default', '0', '', 'Click2call', 'string', 'OVH Application Key', ''),
('ovhApplicationSecret', 'default', '0', '', 'Click2call', 'string', 'OVH Application Secret', ''),
('ovhConsumerKey', 'default', '0', '', 'Click2call', 'string', 'OVH Consumer Key', ''),
('ovhTelecomBillingAccount', 'default', '0', '', 'Click2call', 'string', 'Informations sur la ligne > Nom du groupe', ''),
('ovhTelecomCallingNumber', 'default', '0', '', 'Click2call', 'string', 'Numéro de l\'appelant au format international 0033xxxxxxxxxx', ''),
('ovhTelecomIntercom', 'default', '0', '', 'Click2call', 'true/false', 'Activer le mode intercom', ''),
('ovhTelecomServiceName', 'default', '0', '', 'Click2call', 'string', 'Numéro de la ligne au format international 0033xxxxxxxxxx', ''),
('phonecaptureCookieDuration', 'default', '0', '', 'Phonecapture', 'nombre', 'durée de vie d\'identification d\'un périphérique pour PhoneCapture', '31104000'),
('phonecaptureFingerprint', 'default', '0', '', 'Phonecapture', 'texte', 'chaîne aléatoire permettant une sécurisation de l\'identification des périphériques PhoneCapture', 'phonecapture'),
('phonecaptureResolutionHeight', 'default', '0', '', 'Phonecapture', 'nombre', ' résolution des photos, hauteur', '1080'),
('phonecaptureResolutionWidth', 'default', '0', '', 'Phonecapture', 'nombre', 'résolution des photos, largeur', '1920'),
('signPeriphName', 'default', '0', '', 'Options', 'texte', 'nom du périphérique pour signature (caractères alphanumériques, sans espaces ni accents)', 'default'),
('smsCreditsFile', 'default', '0', '', 'Rappels SMS', 'fichier', 'nom du fichier qui contient le nombre de SMS restants', 'creditsSMS.txt'),
('smsDaysBeforeRDV', 'default', '0', '', 'Rappels SMS', 'nombre', 'nombre de jours avant le rendez-vous pour l\'expédition du rappel SMS', '3'),
('smsLogCampaignDirectory', 'default', '0', '', 'Rappels SMS', 'dossier', 'chemin du répertoire où on va loguer les rappels de rendez-vous par SMS', ''),
('smsProvider', 'default', '0', '', 'Rappels SMS', 'url/ip', 'active le service tiers concerné', ''),
('smsRappelActiver', 'default', '0', '', 'Rappels SMS', 'true/false', 'activer / désactiver les rappels par SMS', 'false'),
('smsRappelMessage', 'default', '0', '', 'Rappels SMS', 'textarea', 'Les balises #heureRdv, #jourRdv et #praticien seront automatiquement remplacées dans le message envoyé', 'Rappel: Vous avez rdv à #heureRdv le #jourRdv avec le Dr #praticien'),
('smsSeuilCreditsAlerte', 'default', '0', '', 'Rappels SMS', 'nombre', 'prévenir dans l\'interface du logiciel si crédit inférieur ou égale à', '150'),
('smsTpoa', 'default', '0', '', 'Rappels SMS', 'texte', 'La balise #praticien sera automatiquement remplacée dans le message envoyé', 'Dr #praticien'),
('smsTypeRdvPourRappel', 'default', '0', '', 'Rappels SMS', 'vide/text', 'N\'envoyer les rappels SMS que pour les types de rendez-vous listés (placer les types de RDV entre "[]" et séparés par des virgules, ex : "[C1],[C2]"), laisser vide pour envoyer des rappels pour tous les types de rendez-vous.', ''),
('smtpDefautSujet', 'default', '0', '', 'Mail', 'texte', 'titre par défaut du mail expédié', 'Document vous concernant'),
('smtpFrom', 'default', '0', '', 'Mail', 'email', 'adresse de l’expéditeur des messages, ex: user@domain.net', ''),
('smtpFromName', 'default', '0', '', 'Mail', 'texte', 'nom en clair de l\'expéditeur des messages', ''),
('smtpHost', 'default', '0', '', 'Mail', 'url/ip', 'serveur SMTP', ''),
('smtpOptions', 'default', '0', '', 'Mail', 'texte', 'options pour désactiver quelques options de sécurités', 'off'),
('smtpPassword', 'default', '0', '', 'Mail', 'texte', 'mot de passe pour le serveur SMTP', ''),
('smtpPort', 'default', '0', '', 'Mail', 'nombre', 'port du serveur SMTP', '587'),
('smtpSecureType', 'default', '0', '', 'Mail', 'texte', 'protocole ssl ou tls (ou rien)', 'tls'),
('smtpTracking', 'default', '0', '', 'Mail', 'texte', 'permet d\'activer le tracking des mails envoyés via un service tiers', ''),
('smtpUsername', 'default', '0', '', 'Mail', 'texte', 'login pour le serveur SMTP', ''),
('statsExclusionCats', 'default', '0', '', 'Statistiques', 'liste', 'liste des noms des catégories de formulaires à exclure des statistiques ', 'catTypeCsATCD,csAutres,declencheur'),
('statsExclusionPatients', 'default', '0', '', 'Statistiques', 'liste', 'liste des ID des dossiers tests à exclure des statistiques ', ''),
('templateCourrierHeadAndFoot', 'default', '0', '', 'Modèles de documents', 'fichier', 'template pour les courriers', 'base-page-headAndNoFoot.html.twig'),
('templateCrHeadAndFoot', 'default', '0', '', 'Modèles de documents', 'fichier', 'template pour les compte-rendus', 'base-page-headAndNoFoot.html.twig'),
('templateDefautPage', 'default', '0', '', 'Modèles de documents', 'fichier', 'template par défaut pour l\'impression', 'base-page-headAndFoot.html.twig'),
('templateOrdoALD', 'default', '0', '', 'Modèles de documents', 'fichier', 'template (complet) pour les ordonnances bizones ALD', 'ordonnanceALD.html.twig'),
('templateOrdoBody', 'default', '0', '', 'Modèles de documents', 'fichier', 'template pour le corps des ordonnances standards', 'ordonnanceBody.html.twig'),
('templateOrdoHeadAndFoot', 'default', '0', '', 'Modèles de documents', 'fichier', 'template pour header et footer des ordonnances standards (non ALD)', 'base-page-headAndFoot.html.twig'),
('templatesCdaFolder', 'default', '0', '', 'Modèles de documents', 'dossier', 'répertoire des fichiers de template pour la génération de XML CDA', ''),
('templatesPdfFolder', 'default', '0', '', 'Modèles de documents', 'dossier', 'répertoire des fichiers de template pour la génération de PDF', ''),
('theriaqueMode', 'default', '0', '', 'LAP', 'texte', 'ode d\'utilisation de Thériaque : WS (webservice) ou PG (base postgre en local)', ''),
('theriaquePgDbName', 'default', '0', '', 'LAP', 'texte', 'nom de la base postgre', ''),
('theriaquePgDbPassword', 'default', '0', '', 'LAP', 'texte', 'mot de passe postgre', ''),
('theriaquePgDbUser', 'default', '0', '', 'LAP', 'texte', 'nom d\'utilisateur postgre', ''),
('theriaqueShowMedicHospi', 'default', '0', '', 'LAP', 'true/false', 'voir les médicaments hospitaliers', 'true'),
('theriaqueShowMedicNonComer', 'default', '0', '', 'LAP', 'true/false', 'voir les médicaments non commercialisés', 'false'),
('theriaqueWsURL', 'default', '0', '', 'LAP', 'texte', 'url du webservice Thériaque', ''),
('transmissionsDefautDestinataires', 'default', '0', '', 'Transmissions', 'liste', 'ID des utilisateurs, séparés par des virgules (sans espace)', ''),
('transmissionsNbParPage', 'default', '0', '', 'Transmissions', 'nombre entier', 'nombre de transmissions par page', '30'),
('transmissionsPeutCreer', 'default', '0', '', 'Transmissions', 'true/false', 'peut créer des transmissions', 'true'),
('transmissionsPeutRecevoir', 'default', '0', '', 'Transmissions', 'true/false', 'peut recevoir des transmissions', 'true'),
('transmissionsPeutVoir', 'default', '0', '', 'Transmissions', 'true/false', 'peut accéder aux transmissions', 'true'),
('transmissionsPurgerNbJours', 'default', '0', '', 'Transmissions', 'nombre entier', 'nombre de jours sans update après lequel une transmission sera supprimée de la base de données (0 = jamais)', '365'),
('utiliserLap', 'default', '0', '', 'LAP', 'true/false', 'activer / désactiver le LAP', 'false'),
('utiliserLapExterne', 'default', '0', '', 'LAP', 'true/false', 'activer / désactiver l\'utilisation d\'un LAP externe', 'false'),
('utiliserLapExterneName', 'default', '0', '', 'LAP', 'texte', 'nom du LAP externe', ''),
('vitaleActiver', 'default', '0', '', 'Vitale', 'true/false', 'activer / désactiver les services liés à la carte vitale', 'false'),
('vitaleHoteLecteurIP', 'default', '0', '', 'Vitale', 'texte', 'IP sur le réseau interne de la machine supportant le lecteur', ''),
('vitaleMode', 'default', '0', '', 'Vitale', 'texte', 'simple / complet', 'simple'),
('vitaleNomRessourceLecteur', 'default', '0', '', 'Vitale', 'texte', 'nomRessourceLecteur', ''),
('vitaleNomRessourcePS', 'default', '0', '', 'Vitale', 'texte', 'nomRessourcePS', ''),
('vitaleService', 'default', '0', '', 'Vitale', 'texte', 'service tiers de gestion vitale', '');

-- forms_cat
INSERT IGNORE INTO `forms_cat` (`name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('displayforms', 'Formulaires d\'affichage', 'Formulaires liés à l\'affichage d\'informations', 'user', '1', '2019-01-01 00:00:00'),
('formATCD', 'Formulaires d\'antécédents', 'Formulaires pour construire les antécédents', 'user', '1', '2019-01-01 00:00:00'),
('formCS', 'Formulaires de consultation', 'Formulaires pour construire les consultations', 'user', '1', '2019-01-01 00:00:00'),
('formSynthese', 'Formulaires de synthèse', 'Formulaires pour construire les synthèses', 'user', '1', '2019-01-01 00:00:00'),
('formsProdOrdoEtDoc', 'Formulaires de production d\'ordonnances', 'formulaires de production d\'ordonnances et de documents', 'user', '1', '2019-01-01 00:00:00'),
('patientforms', 'Formulaires de saisie', 'Formulaire liés à la saisie de données', 'user', '1', '2019-01-01 00:00:00'),
('systemForm', 'Formulaires système', 'formulaires système', 'user', '1', '2019-01-01 00:00:00');

-- forms
SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='displayforms');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseListingGroupes', 'Listing des groupes', 'description des colonnes affichées en résultat d\'une recherche groupes', 'data_types', 'medical', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'col1:\r\n    head: \"Nom du centre\" \r\n    bloc: \r\n        - groupname                                		#16412 Nom du groupe\ncol2:\r\n    head: \"Pays\"\r\n    bloc:\r\n        - country,text-uppercase                   		#1656 Pays\ncol3:\r\n    head: \"Ville\"\r\n    bloc:\r\n        - city,text-uppercase                      		#12   Ville', '', '', '', ''),
('base', 'baseListingPatients', 'Listing des patients', 'description des colonnes affichées en résultat d\'une recherche patient', 'data_types', 'admin', 'post', '', @catID, 'public', 'col1:\r\n    head: \"Identité\" \r\n    blocseparator: \" - \"\r\n    bloc: \r\n        - identite                                 		#16413 Identité\ncol2:\r\n    head: \"Date de naissance\" \r\n    blocseparator: \" - \"\r\n    bloc: \r\n        - birthdate                                		#8    Date de naissance\n        - ageCalcule                               		#1799 Age calculé\ncol3:\r\n    head: \"Tel\" \r\n    blocseparator: \" - \"\r\n    bloc: \r\n        - mobilePhone,click2call                   		#7    Téléphone mobile\n        - homePhone,click2call                     		#10   Téléphone domicile\ncol4:\r\n    head: \"Email\"\r\n    bloc:\r\n        - personalEmail                            		#4    Email personnelle\ncol5:\r\n    head: \"Ville\"\r\n    bloc:\r\n        - city,text-uppercase                      		#12   Ville', '', '', '', ''),
('base', 'baseListingPro', 'Listing des praticiens', 'description des colonnes affichées en résultat d\'une recherche praticien', 'data_types', 'admin', 'post', '', @catID, 'public', 'col1:\r\n    head: \"Identité\" \r\n    bloc: \r\n        - identite                                 		#16413 Identité\ncol2:\r\n    head: \"Activité pro\" \r\n    bloc: \r\n        - job                                      		#19   Activité professionnelle\ncol3:\r\n    head: \"Tel\" \r\n    bloc: \r\n        - telPro,click2call                        		#57   Téléphone professionnel\ncol4:\r\n    head: \"Fax\" \r\n    bloc: \r\n        - faxPro                                   		#58   Fax professionnel\ncol5:\r\n    head: \"Email\"\r\n    blocseparator: \" - \"\r\n    bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - personalEmail                            		#4    Email personnelle\n\r\ncol6:\r\n    head: \"Pays\"\r\n    bloc:\r\n        - paysAdressePro,text-uppercase            		#1657 Pays\n\r\ncol7:\r\n    head: \"Ville\"\r\n    bloc:\r\n        - villeAdressePro,text-uppercase           		#56   Ville', NULL, '', NULL, NULL),
('base', 'baseListingRegistres', 'Listing des registres', 'description des colonnes affichées en résultat d\'une recherche registres', 'data_types', 'medical', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'col1:\r\n    head: \"Nom du registre\" \r\n    bloc: \r\n        - registryname                             		#16415 Nom du registre\n        \r\ncol2:\r\n    head: \"État du registre\" \r\n    bloc:         \r\n        - registryState                            		#16421 État du registre', '', '', '', '');


SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='formATCD');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseATCD', 'Formulaire latéral écran patient principal (atcd)', 'formulaire en colonne latéral du dossier patient (atcd)', 'data_types', 'medical', 'post', '', @catID, 'public', 'structure:\r\n  row1: \r\n    col1: \r\n      size: 12 col-12 col-sm-4 col-lg-4\r\n      bloc: \r\n        - poids,plus={<i class=\"fas fa-clone duplicate\"></i>} #34   Poids\r\n    col2: \r\n      size: 12 col-12 col-sm-4 col-lg-4\r\n      bloc: \r\n       - taillePatient,plus={<i class=\"fas fa-clone duplicate\"></i>} #35   Taille\r\n    col3: \r\n      size: 12 col-12 col-sm-4 col-lg-4\r\n      bloc: \r\n       - imc,readonly,plus={<i class=\"fas fa-chart-line graph\"></i>} #43   IMC\r\n  row2: \r\n   col1: \r\n     size: 12\r\n     bloc: \r\n       - job,rows=1                                       		#19   Activité professionnelle\r\n       - allergies,rows=1                          		#66   Allergies\r\n       - toxiques,rows=1                                  		#42   Toxiques\r\n  row3: \r\n    col1: \r\n     size: 12\r\n     bloc: \r\n       - atcdMedicChir,rows=2                      		#41   Antécédents médico-chirurgicaux\r\n       - atcdFamiliaux,rows=2                      		#38   Antécédents familiaux', NULL, '', NULL, '$(document).ready(function() {\r\n\r\n  //calcul IMC\r\n  if ($(\'#id_imc_id\').length > 0) {\r\n\r\n    imc = imcCalc($(\'#id_poids_id\').val(), $(\'#id_taillePatient_id\').val());\r\n    if (imc > 0) {\r\n      $(\'#id_imc_id\').val(imc);\r\n    }\r\n\r\n    $(\"#patientLatCol\").on(\"keyup\", \"#id_poids_id , #id_taillePatient_id\", function() {\r\n      poids = $(\'#id_poids_id\').val();\r\n      taille = $(\'#id_taillePatient_id\').val();\r\n      imc = imcCalc(poids, taille);\r\n      $(\'#id_imc_id\').val(imc);\r\n      patientID = $(\'#identitePatient\').attr(\"data-patientID\");\r\n      setPeopleDataByTypeName(imc, patientID, \'imc\', \'#id_imc_id\', \'0\');\r\n\r\n    });\r\n  }\r\n\r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseATCD textarea\')); \r\n  \r\n});');


SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='formCS');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'aldDeclaration', 'Déclaration d\'ALD', 'formulaire d\'enregistrement d\'une ALD', 'data_types', 'medical', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'structure:\r\n  row1:\r\n    head: Enregistrement d\'une prise en charge en ALD\r\n    col1:\r\n     size: 12\r\n     bloc:\r\n       - aldNumber                                 		#886  ALD\n  row2:\r\n    col1:\r\n     size: 4\r\n     bloc:\r\n       - aldDateDebutPriseEnCharge                 		#887  Début de prise en charge\n    col2:\r\n      size: 4\r\n      bloc:\r\n       - aldDateFinPriseEnCharge                   		#888  Fin de prise en charge\n  row3:\r\n    col1:\r\n     size: 2\r\n     bloc:\r\n       - aldCIM10,plus={<i class=\"fas fa-search\"></i>} 		#889  Code CIM10 associé\n    col2:\r\n     size: 10\r\n     bloc:\r\n       - aldCIM10label,readonly                    		#890  Label CIM10 associé', NULL, '', '', '$(\"#nouvelleCs\").on(\"click\",\"#id_aldCIM10_idAddOn\", function() {\r\n  $(\'#searchCIM10\').modal(\'show\');\r\n});\r\n\r\n$(\'#searchCIM10\').on(\'shown.bs.modal\', function() {\r\n  $(\'#searchCIM10 #texteRechercheCIM10\').focus();\r\n});\r\n\r\n$(\"#nouvelleCs\").on(\"keyup\",\"#id_aldCIM10_id\", function() {\r\n  if ($(\"#id_aldCIM10_id\").val() == \'\') $(\"#id_aldCIM10label_id\").val(\'\');\r\n});\r\n\r\n$(\"#texteRechercheCIM10\").typeWatch({\r\n  wait: 1000,\r\n  highlight: false,\r\n  allowSubmit: false,\r\n  captureLength: 3,\r\n  callback: function(value) {\r\n    $.ajax({\r\n      url: urlBase+\'/lap/ajax/cim10search/\',\r\n      type: \'post\',\r\n      data: {\r\n        term: value\r\n      },\r\n      dataType: \"html\",\r\n      beforeSend: function() {\r\n        $(\'#codeCIM10trouves\').html(\'<div class=\"col-md-12\">Attente des résultats de la recherche ...</div>\');\r\n      },\r\n      success: function(data) {\r\n        $(\'#codeCIM10trouves\').html(data);\r\n      },\r\n      error: function() {\r\n        alert(\'Problème, rechargez la page !\');\r\n      }\r\n    });\r\n  }\r\n});\r\n\r\n$(\'#searchCIM10\').on(\"click\", \"button.catchCIM10\", function() {\r\n  code = $(this).attr(\'data-code\');\r\n  label = $(this).attr(\'data-label\');\r\n  $(\"#id_aldCIM10_id\").val(code);\r\n  $(\"#id_aldCIM10label_id\").val(label);\r\n  $(\'#searchCIM10\').modal(\'toggle\');\r\n  $(\'#codeCIM10trouves\').html(\'\');\r\n  $(\"#texteRechercheCIM10\").val(\'\');\r\n\r\n});'),
('base', 'atcdStrucDeclaration', 'Déclaration d\'atcd structuré', 'ajout d\'antécédents structuré et codé CIM 10', 'data_types', 'medical', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'global:\r\n  formClass: \'ignoreReturn\'\r\nstructure: \r\n  row1:\r\n   head : Ajout d\'un antécédent à partir de la classification CIM 10\r\n   col1: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucCIM10,plus={<i class=\"fas fa-search\"></i>} 		#891  Code CIM 10\n   col2: \r\n     size: 7\r\n     bloc:\r\n       - atcdStrucCIM10Label,readonly              		#892  Label CIM 10\n   col3:\r\n     size: 3\r\n     bloc:\r\n       - atcdStrucCIM10InLap                       		#925  A prendre en compte dans le LAP\n\r\n  row2:\r\n    col1: \r\n     size: 5\r\n     head: \'Début\'\r\n    col2: \r\n     size: 2\r\n     head: \'\'\r\n    col3:\r\n     size: 5\r\n     head: \'Fin\'\r\n  \r\n   \r\n  row3:\r\n    col1: \r\n     size: 1\r\n     bloc:\r\n       - atcdStrucDateDebutJour                    		#893  Jour\n    col2: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateDebutMois                    		#895  Mois\n    col3: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateDebutAnnee,min=1910,step=1   		#897  Année\n    col4: \r\n     size: 2\r\n    col5: \r\n     size: 1\r\n     bloc:\r\n       - atcdStrucDateFinJour                      		#894  Jour\n    col6: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateFinMois                      		#896  Mois\n    col7: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateFinAnnee,min=1910,step=1     		#898  Année\n  row4:\r\n    col1: \r\n      head: \"Notes\"\r\n      size: 12\r\n      bloc:\r\n        - atcdStrucNotes,nolabel                   		#899  Notes', NULL, '', '', '$(document).ready(function() {\r\n  $(\"#nouvelleCs\").on(\"click\",\"#id_atcdStrucCIM10_idAddOn\", function() {\r\n    $(\'#searchCIM10\').modal(\'show\');\r\n  });\r\n\r\n  $(\'#searchCIM10\').on(\'shown.bs.modal\', function() {\r\n    $(\'#searchCIM10 #texteRechercheCIM10\').focus();\r\n  });\r\n\r\n  $(\"#texteRechercheCIM10\").typeWatch({\r\n    wait: 1000,\r\n    highlight: false,\r\n    allowSubmit: false,\r\n    captureLength: 3,\r\n    callback: function(value) {\r\n      $.ajax({\r\n        url: urlBase+\'/lap/ajax/cim10search/\',\r\n        type: \'post\',\r\n        data: {\r\n          term: value\r\n        },\r\n        dataType: \"html\",\r\n        beforeSend: function() {\r\n          $(\'#codeCIM10trouves\').html(\'<div class=\"col-md-12\">Attente des résultats de la recherche ...</div>\');\r\n        },\r\n        success: function(data) {\r\n          $(\'#codeCIM10trouves\').html(data);\r\n        },\r\n        error: function() {\r\n          alert(\'Problème, rechargez la page !\');\r\n        }\r\n      });\r\n    }\r\n  });\r\n\r\n  $(\'#searchCIM10\').on(\"click\", \"button.catchCIM10\", function() {\r\n    code = $(this).attr(\'data-code\');\r\n    label = $(this).attr(\'data-label\');\r\n    $(\"#id_atcdStrucCIM10_id\").val(code);\r\n    $(\"#id_atcdStrucCIM10Label_id\").val(label);\r\n    $(\'#searchCIM10\').modal(\'toggle\');\r\n    $(\'#codeCIM10trouves\').html(\'\');\r\n    $(\"#texteRechercheCIM10\").val(\'\');\r\n\r\n  });\r\n  \r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#id_atcdStrucNotes_id\'));\r\n  \r\n});'),
('base', 'baseConsult', 'Formulaire CS', 'formulaire basique de consultation', 'data_types', 'medical', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'global:\r\n  formClass: \'newCS\' \r\nstructure:\r\n####### INTRODUCTION ######\r\n  row1:                              \r\n    head: \'Consultation\'\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n        - examenDuJour,rows=10                     		#716  Examen du jour', NULL, 'csBase', NULL, NULL);


SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='formSynthese');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseSynthese', 'Synthèse patiente', 'formulaire fixe de synthèse', 'data_types', 'medical', 'post', '', @catID, 'public', 'structure:\r\n  row1:                              \r\n    col1:                             \r\n      size: 12\r\n      bloc:                          \r\n        - baseSynthese,rows=2                      		#718  Synthèse patient', NULL, '', NULL, '$(document).ready(function() {  \r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseSynthese textarea\'));\r\n });');


SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='patientforms');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseModalNewPatient', 'Formulaire patient pour agenda', 'formulaire patient pour agenda', 'data_types', 'admin', 'post', '', @catID, 'public', 'global:\r\n  noFormTags: true\r\nstructure:\r\n  row1:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 12\r\n      bloc:                          \r\n        - administrativeGenderCode,nolabel         		#14   Sexe\n  row2:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 6\r\n      bloc:                          \r\n        - birthname,required,nolabel,class={font-weight-bold},autocomplete,data-acTypeID=lastname:birthname 		#1    Nom de naissance\n    col2:\r\n      size: 6\r\n      bloc:                          \r\n        - lastname,nolabel,class={font-weight-bold},autocomplete,data-acTypeID=lastname:birthname 		#2    Nom d usage\n  row3:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 12\r\n      bloc:                          \r\n        - firstname,nolabel,class={font-weight-bold},required,autocomplete,data-acTypeID=firstname:othersfirstname:igPrenomFA:igPrenomFB:igPrenomFC 		#3    Prénom\n  row4:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 6\r\n      bloc:                          \r\n        - birthdate,nolabel,required,class=pick-years 		#8    Date de naissance\n    col2:\r\n      size: 6\r\n      bloc:                          \r\n        - nss,nolabel,class=updatable,plus={<i class=\"far fa-address-card\"></i>} 		#180  Numéro de sécu\n  row5:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 12\r\n      bloc:                          \r\n        - personalEmail,nolabel,class=updatable    		#4    Email personnelle\n  row6:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 6\r\n      bloc:                          \r\n        - mobilePhone,nolabel,class=updatable      		#7    Téléphone mobile\n    col2:\r\n      size: 6\r\n      bloc:                          \r\n        - homePhone,nolabel,class=updatable        		#10   Téléphone domicile\n\r\n  row7:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 4\r\n      bloc: \r\n        - streetNumber,nolabel,class=updatable     		#9    n°\n        - postalCodePerso,nolabel,class=updatable  		#13   Code postal\n    col2:\r\n      size: 8\r\n      bloc: \r\n        - street,nolabel,autocomplete,data-acTypeID=street:rueAdressePro,class=updatable 		#11   Voie\n        - city,nolabel,autocomplete,data-acTypeID=city:villeAdressePro,class=updatable 		#12   Ville\n  row8:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,nolabel,rows=2,class=updatable     		#21   Notes', NULL, '', NULL, NULL),
('base', 'baseNewGroupe', 'Formulaire de création d\'un groupe', 'formulaire de création d\'un nouveau groupe', 'data_types', 'admin', 'post', '/groupe/register/', @catID, 'public', 'structure:\r\n  row1:                              \r\n    col1:                            \r\n      size: 4\r\n      bloc:                          \r\n        - groupname,required                       		#16412 Nom du groupe\n    col2:                            \r\n      size: 4\r\n      bloc:                          \r\n        - country                                  		#1656 Pays\n    col3:                            \r\n      size: 4\r\n      bloc:                          \r\n        - city                                     		#12   Ville', '', '', '', ''),
('base', 'baseNewPatient', 'Formulaire nouveau patient', 'formulaire d\'enregistrement d\'un nouveau patient', 'data_types', 'admin', 'post', '/patient/register/', @catID, 'public', 'structure:\r\n  row1:                              \r\n    col1:                              \r\n      head: \'Etat civil\'             \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - birthname,required,autocomplete,data-acTypeID=lastname:birthname 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=lastname:birthname 		#2    Nom d usage\n        - firstname,required,autocomplete,data-acTypeID=firstname:othersfirstname:igPrenomFA:igPrenomFB:igPrenomFC 		#3    Prénom\n        - birthdate,class=pick-year                		#8    Date de naissance\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - personalEmail                            		#4    Email personnelle\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\n        - nss                                      		#180  Numéro de sécu\n        - nmu                                      		#251  Numéro de mutuelle\n    col3:\r\n      head: \'Adresse personnelle\'\r\n      size: 4\r\n      bloc: \r\n        - streetNumber                             		#9    n°\n        - street,autocomplete,data-acTypeID=street:rueAdressePro 		#11   Voie\n        - postalCodePerso                          		#13   Code postal\n        - city,autocomplete,data-acTypeID=city:villeAdressePro 		#12   Ville\n        - deathdate                                		#516  Date de décès\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,rows=3                             		#21   Notes', NULL, '', NULL, '$(document).ready(function() {\r\n\r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseNewPatient textarea\')); \r\n\r\n  // modal edit data admin patient\r\n  $(\'#editAdmin\').on(\'shown.bs.modal\', function (e) {\r\n    autosize.update($(\'#editAdmin textarea\'));\r\n  });\r\n  \r\n});'),
('base', 'baseNewPro', 'Formulaire nouveau pro', 'formulaire d\'enregistrement d\'un nouveau pro', 'data_types', 'admin', 'post', '/pro/register/', @catID, 'public', 'structure:\r\n  row1:                              \r\n    col1:                            \r\n      head: \'Etat civil\'            \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - job,autocomplete,rows=1                         		#19   Activité professionnelle\n        - titre,autocomplete                       		#51   Titre\n        - birthname,autocomplete,data-acTypeID=firstname:othersfirstname:igPrenomFA:igPrenomFB:igPrenomFC 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=lastname:birthname 		#2    Nom d usage\n        - firstname,autocomplete,data-acTypeID=firstname:othersfirstname:igPrenomFA:igPrenomFB:igPrenomFC 		#3    Prénom\n\r\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - profesionnalEmail                        		#5    Email professionnelle\n        - personalEmail                            		#4    Email personnelle\n        - telPro                                   		#57   Téléphone professionnel\n        - telPro2                                  		#248  Téléphone professionnel 2\n        - mobilePhonePro                           		#247  Téléphone mobile pro.\n        - faxPro                                   		#58   Fax professionnel\n    col3:\r\n      head: \'Adresse professionnelle\'\r\n      size: 4\r\n      bloc: \r\n        - numAdressePro                            		#54   Numéro\n        - rueAdressePro,autocomplete,data-acTypeID=street:rueAdressePro 		#55   Rue\n        - codePostalPro                            		#53   Code postal\n        - villeAdressePro,autocomplete,data-acTypeID=city:villeAdressePro 		#56   Ville\n        - serviceAdressePro,autocomplete           		#249  Service\n        - etablissementAdressePro,autocomplete     		#250  Établissement\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notesPro,rows=3                          		#443  Notes pros\n\r\n  row3:\r\n    col1:\r\n      size: 4\r\n      bloc:\r\n        - rpps                                     		#103  RPPS\n        - PSIdNat                                  		#1602 Identifiant national praticien santé\n    col2:\r\n      size: 4\r\n      bloc:\r\n        - adeli                                    		#104  Adeli\n        - PSCodeProSpe,plus={<i class=\"fas fa-pen\"></i>} 		#1603 Code normé de la profession/spécialité du praticien\n    col3:\r\n      size: 4\r\n      bloc:\r\n        - nReseau                                  		#477  Numéro de réseau\n        - PSCodeStructureExercice,plus={<i class=\"fas fa-pen\"></i>} 		#1604 Code normé de la structure d exercice du praticien', NULL, '', '', '$(document).ready(function() {\r\n\r\n   // modal edit data admin patient\r\n  $(\'#newPro\').on(\'shown.bs.modal\', function (e) {\r\n    autosize.update($(\'#newPro textarea\'));\r\n  });\r\n  \r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseNewPro textarea\')); \r\n\r\n});'),
('base', 'baseNewRegistre', 'Formulaire nouveau registre', 'formulaire nouveau registre', 'data_types', 'admin', 'post', '/registre/register/', @catID, 'public',  'structure:\r\n  row1:                              \r\n    col1:                            \r\n      size: col-12\r\n      bloc:                          \r\n        - registryname,required                    		#293  Nom du registre\n  row2:                              \r\n    col1:                            \r\n      size: col-12 col-md-3\r\n      bloc:                          \r\n        - registryAuthorisationDate,required       		#291  Date d autorisation du registre\n    col2:                            \r\n      size: col-12 col-md-3\r\n      bloc:                          \r\n        - registryAuthorisationEndDate             		#292  Date de fin d autorisation du registre\n    col3:                            \r\n      size: col-12 col-md-3\r\n      bloc:                          \r\n        - registryState                            		#294  État du registre\n        \r\n  row3:                              \r\n    col1:                            \r\n      size: col-6 col-md-4\r\n      bloc:                          \r\n        - registryPrefixTech                       		#295  Préfixe technique', '', '', '', ''),
('base', 'basePeopleComplement', 'Formulaire patient/pro complémentaire', 'formulaire patient/pro complémentaire', 'data_types', 'admin', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'structure:\r\n  row1:                              \r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n        - pgpPublicKey,rows=20,class={text-monospace} 		#1787 Clef publique PGP', '', '', '', '');


SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='systemForm');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseAgendaPriseRDV', 'Agenda prise rdv', 'formulaire latéral de prise de rdv', 'data_types', 'admin', 'post', '', @catID, 'public', 'global:\r\n  noFormTags: true\r\nstructure:\r\n  row1:                              \r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - birthname,readonly                       		#1    Nom de naissance\n    col2:                              \r\n      size: 6\r\n      bloc:                          \r\n        - firstname,readonly                       		#3    Prénom\n  row2:\r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - lastname,readonly                        		#2    Nom d usage\n    col2:                              \r\n      size: 6\r\n      bloc: \r\n        - birthdate,readonly                       		#8    Date de naissance\n  row3:\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n         - personalEmail                           		#4    Email personnelle\n\r\n  row4:                              \r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - mobilePhone                              		#7    Téléphone mobile\n    col2:                              \r\n      size: 6\r\n      bloc:                          \r\n        - homePhone                                		#10   Téléphone domicile', NULL, '', NULL, NULL),
('base', 'baseAskUserPassword', 'Demande du mot de passe', 'demande du mot de passe à l\'utilisateur courant', 'data_types', 'medical', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'global:\r\n  noFormTags: true\r\nstructure:\r\n  row1:\r\n    col1:\r\n      size: col\r\n      bloc:\r\n        - password,required                        		#1789 Mot de passe', '', '', '', ''),
('base', 'baseFax', 'Formulaire écofax', 'formulaire pour ecofax OVH', 'data_types', 'mail', 'post', '/patient/actions/sendMail/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: col\r\n    bloc: \r\n      - mailToEcofaxName,required                  		#484  Destinataire du fax\n row2:\r\n   col1: \r\n    size: col\r\n    bloc: \r\n      - mailToEcofaxNumber,required                		#481  Numéro de fax du destinataire', NULL, '', NULL, NULL),
('base', 'baseFirstLogin', 'Premier utilisateur', 'Création du premier utilisateur', 'data_types', 'admin', 'post', '/login/logInFirstDo/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: \"Premier utilisateur\"\r\n    size: 3\r\n    bloc:\r\n      - username,required                          		#1    Identifiant\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe\n      - submit,Valider,class={btn-primary}         		#3    Valider', NULL, NULL, NULL, NULL),
('base', 'baseImportExternal', 'Import', 'formulaire pour consultation importée d\'une source externe', 'data_types', 'medical', 'post', '', @catID, 'public', 'global:\r\n  formClass: \'newCS\' \r\nstructure:\r\n####### INTRODUCTION ######\r\n  row1:                              \r\n    head: \'Consultation importée\'\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n        - dataImport,rows=10                       		#252  Import', NULL, 'csImportee', NULL, NULL),
('base', 'baseLogin', 'Login', 'formulaire login utilisateur', 'data_types', 'admin', 'post', '/login/logInDo/', @catID, 'public', 'global:\r\n  formClass: \'form-signin\' \r\nstructure:\r\n row1:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - username,required,nolabel                  		#1    Identifiant\n      - password,required,nolabel                  		#2    Mot de passe\n      - otpCode,nolabel                            		#7    code otp\n      - submit,Connexion,class=btn-primary,class=btn-block 		#3    Valider', NULL, '', NULL, NULL),
('base', 'baseNewUser', 'Formulaire nouvel utilisateur', 'formulaire nouvel utilisateur', 'data_types', 'admin', 'post', '/configuration/ajax/configUserCreate/', @catID, 'public', 'structure:\r\n  row1:\r\n   class: \'mb-4\'\r\n   col1: \r\n     size: col-3\r\n     bloc:\r\n       - administrativeGenderCode,tabindex=1       		#14   Sexe\n       - personalEmail,tabindex=4                  		#4    Email personnelle\n   col2: \r\n     size: col-3\r\n     bloc:\r\n       - birthname,tabindex=1                      		#1    Nom de naissance\n       - profesionnalEmail,tabindex=5              		#5    Email professionnelle\n   col3: \r\n     size: col-3\r\n     bloc:\r\n       - lastname,tabindex=2                       		#2    Nom d usage\n   col4: \r\n     size: col-3\r\n     bloc:\r\n       - firstname,required,tabindex=3             		#3    Prénom\n       \r\n  row2:\r\n   col1: \r\n     size: col-3\r\n     bloc:\r\n       - username,required,tabindex=6              		#1788 Nom d utilisateur\n   col2: \r\n     size: col-3\r\n     bloc:\r\n       - password,tabindex=7                       		#1789 Mot de passe\n   col3: \r\n     size: col-3\r\n     bloc:\r\n       - module,tabindex=8                         		#1795 Module\n   col4: \r\n     size: col-3\r\n     bloc:\r\n       - template,tabindex=9                       		#1796 Template', '', '', '', ''),
('base', 'baseNewUserFromPeople', 'Formulaire nouvel utilisateur pour un individu déjà existant', 'formulaire nouvel utilisateur pour un individu déjà existant', 'data_types', 'admin', 'post', '/configuration/ajax/configUserCreate/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: col-4\r\n    bloc:\r\n      - username,required,tabindex=1               		#1788 Nom d utilisateur\n      - template,tabindex=4                       		#1796 Templates utilisables\n  col2: \r\n    size: col-4\r\n    bloc:\r\n      - password,tabindex=2               		#1789 Mot de passe\n  col3: \r\n    size: col-4\r\n    bloc:\r\n      - module,tabindex=3                         		#1795 Modules', '', '', '', ''),
('base', 'baseReglementLibre', 'Formulaire règlement', 'formulaire pour le règlement d\'honoraires libres', 'data_types', 'reglement', 'post', '/patient/ajax/saveReglementForm/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 4\r\n    bloc: \r\n      - regleTarifLibreCejour,readonly,plus={€},class=regleTarifCejour  		#199  Tarif\n  col2: \r\n    size: 4\r\n    bloc: \r\n      - regleModulCejour,plus={€},class=regleDepaCejour                 		#201  Dépassement\n  col3: \r\n    size: 4\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 4\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 4\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 4\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur', NULL, '', NULL, NULL),
('base', 'baseReglementS1', 'Règlement conventionné S1', 'Formulaire pour le règlement d\'honoraires conventionnés secteur 1', 'data_types', 'reglement', 'post', '/patient/ajax/saveReglementForm/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient,class=regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifSSCejour,required,readonly,plus={€},class=regleTarifCejour       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€},class=regleDepaCejour                 		#200  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€},class=regleTiersPayeur                		#202  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur', NULL, '', NULL, NULL),
('base', 'baseReglementS2', 'Règlement conventionné S2', 'Formulaire pour le règlement d\'honoraires conventionnés secteur 2', 'data_types', 'reglement', 'post', '/patient/ajax/saveReglementForm/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient,class=regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifSSCejour,required,readonly,plus={€},class=regleTarifCejour       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€},class=regleDepaCejour                 		#200  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€},class=regleTiersPayeur                		#202  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur', NULL, '', NULL, NULL),
('base', 'baseReglementSearch', 'Recherche règlements', 'formulaire recherche règlement', 'data_types', 'admin', 'post', '', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - date                                       		#4    Début de période\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - date                                       		#4    Début de période\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - submit                                     		#3    Valider', NULL, '', NULL, NULL),
('base', 'baseSendMail', 'Formulaire mail', 'formulaire pour mail', 'data_types', 'mail', 'post', '/patient/actions/sendMail/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - mailFrom,required                          		#109  De\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - mailTo,required                            		#110  A\n row2:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailSujet,required                         		#112  Sujet\n row3:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailModeles                                		#446  Modèle\n row4:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailBody,rows=10                           		#111  Message', NULL, '', NULL, NULL),
('base', 'baseSendMailApicrypt', 'Formulaire mail Apicrypt', 'formulaire pour expédier un mail vers un correspondant apicrypt', 'data_types', 'mail', 'post', '/patient/actions/sendMail/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - mailFrom,required                          		#109  De\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - mailToApicrypt,required                    		#179  A (correspondant apicrypt)\n row2:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailSujet,required                         		#112  Sujet\n row3:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailModeles                                		#446  Modèle\n row4:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailBody,rows=10                           		#111  Message', NULL, '', NULL, NULL),
('base', 'baseUserParametersClicRdv', 'Paramètres utilisateur clicRDV', 'Paramètres utilisateur clicRDV', 'data_types', 'admin', 'post', '/user/ajax/userParametersClicRdv/', @catID, 'public', 'global:\n  formClass:\'ajaxForm\'\nstructure:\n row1:\n  col1: \n    head: \"Compte clicRDV\"\n    size: 3\n    bloc:\n      - clicRdvUserId\n      - clicRdvPassword\n      - clicRdvGroupId\n      - clicRdvCalId\n      - clicRdvConsultId,nolabel', NULL, NULL, NULL, NULL),
('base', 'baseUserParametersPassword', 'Changement mot de passe utilisateur', 'Changement mot de passe utilisateur', 'data_types', 'admin', 'post', '/user/actions/userParametersPassword/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: col-12\r\n    bloc:\r\n      - currentPassword,required                   		#6    Mot de passe actuel\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe', NULL, NULL, NULL, NULL),
('base', 'baseUserPasswordRecovery', 'Nouveau password après perte', 'saisie d\'un nouveau password en zone publique après perte', 'data_types', 'admin', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: col-12\r\n    bloc:\r\n      - password,required                          		#1789 Mot de passe\n      - verifPassword                              		#1791 Confirmation du mot de passe', '', '', '', '$(document).ready(function() {\r\n  $(\"#treatNewPass\").on(\"click\", function(e) {\r\n    e.preventDefault();\r\n    password = $(\'#id_password_id\').val();\r\n    verifPassword = $(\'#id_verifPassword_id\').val();\r\n	randStringControl = $(\'input[name=\"randStringControl\"]\').val();\r\n\r\n    $.ajax({\r\n      url: urlBase + \'/public/ajax/publicLostPasswordNewPassTreat/\',\r\n      type: \'post\',\r\n      data: {\r\n        p_password: password,\r\n        p_verifPassword: verifPassword,\r\n        randStringControl: randStringControl,\r\n      },\r\n      dataType: \"json\",\r\n      success: function(data) {\r\n        \r\n       if (data.status == \'ok\') {\r\n         $(\'i.fa-lock\').addClass(\'text-success fa-unlock\').removeClass(\'text-warning fa-lock\');\r\n         $(\'#newPassAskForm\').addClass(\'d-none\');\r\n         $(\'#newPassTreatConfirmation\').removeClass(\'d-none\');\r\n       } else {\r\n         $(\'#newPassAskForm div.alert.cleanAndHideOnModalHide\').removeClass(\'d-none\');\r\n         $(\'#newPassAskForm div.alert.cleanAndHideOnModalHide ul\').html(\'\');\r\n         $.each(data.msg, function(index, value) {\r\n           $(\'#newPassAskForm div.alert.cleanAndHideOnModalHide ul\').append(\'<li>\' + value + \'</li>\');\r\n         });\r\n         $(\'#newPassAskForm .is-invalid\').removeClass(\'is-invalid\');\r\n         $.each(data.code, function(index, value) {\r\n           $(\'#newPassAskForm *[name=\"\' + value + \'\"]\').addClass(\'is-invalid\');\r\n         });\r\n       }        \r\n        \r\n\r\n      },\r\n      error: function() {\r\n        alert(\'Problème, rechargez la page !\');\r\n      }\r\n    });\r\n\r\n  });\r\n});');

-- people
INSERT IGNORE INTO `people` (`name`, `type`, `rank`, `module`, `pass`, `secret2fa`, `registerDate`, `fromID`, `lastLogIP`, `lastLogDate`, `lastLogFingerprint`, `lastLostPassDate`, `lastLostPassRandStr`) VALUES
('clicRDV', 'service', '', 'base', '', NULL, '2019-01-01 00:00:00', 1, '', '2019-01-01 00:00:00', '', NULL, NULL),
('medshake', 'service', '', 'base', '', NULL, '2019-01-01 00:00:00', 1, '', '2019-01-01 00:00:00', '', NULL, NULL);

-- prescriptions_cat
INSERT IGNORE INTO `prescriptions_cat` (`name`, `label`, `description`, `type`, `fromID`, `toID`, `creationDate`, `displayOrder`) VALUES
('prescriNonMedic', 'Prescriptions non médicamenteuses', 'prescriptions non médicamenteuses', 'nonlap', 1, 0, '2019-01-01 00:00:00', '1'),
('prescripMedic', 'Prescriptions médicamenteuses', 'prescriptions médicamenteuses', 'nonlap', 1, 0, '2019-01-01 00:00:00', '1');

-- prescriptions
SET @catID = (SELECT prescriptions_cat.id FROM prescriptions_cat WHERE prescriptions_cat.name='prescriNonMedic');
INSERT IGNORE INTO `prescriptions` (`cat`, `label`, `description`, `fromID`, `toID`, `creationDate`) VALUES
(@catID, 'Ligne vierge', '', 1, 0, '2019-01-01 00:00:00');


SET @catID = (SELECT prescriptions_cat.id FROM prescriptions_cat WHERE prescriptions_cat.name='prescripMedic');
INSERT IGNORE INTO `prescriptions` (`cat`, `label`, `description`, `fromID`, `toID`, `creationDate`) VALUES
(@catID, 'Ligne vierge', '', 1, 0, '2019-01-01 00:00:00');

-- system
INSERT IGNORE INTO `system` (`name`, `groupe`, `value`) VALUES
('base', 'module', 'v7.1.1'),
('state', 'system', 'normal');
