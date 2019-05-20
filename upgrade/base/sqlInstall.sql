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
  `label` varchar(255) DEFAULT NULL,
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
  `motif` text,
  PRIMARY KEY (`id`),
  KEY `patientid` (`patientid`),
  KEY `externid` (`externid`),
  KEY `userid` (`userid`),
  KEY `typeEtUserid` (`type`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- création de la table agenda_changelog
CREATE TABLE IF NOT EXISTS `agenda_changelog` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `eventID` int(12) unsigned NOT NULL,
  `userID` smallint(5) unsigned NOT NULL,
  `fromID` smallint(5) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `operation` enum('edit','move','delete','missing') NOT NULL,
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
  `groupe` enum('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user','relation') NOT NULL DEFAULT 'admin',
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
  `groupe` enum('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user','relation') NOT NULL DEFAULT 'admin',
  `name` varchar(60) NOT NULL,
  `placeholder` varchar(255) DEFAULT NULL,
  `label` varchar(60) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `validationRules` varchar(255) DEFAULT NULL,
  `validationErrorMsg` varchar(255) DEFAULT NULL,
  `formType` enum('','date','email','number','select','submit','tel','text','textarea','password','checkbox','hidden','range','radio','reset','switch') NOT NULL DEFAULT '',
  `formValues` text,
  `module` varchar(20) NOT NULL DEFAULT 'base',
  `cat` smallint(5) unsigned NOT NULL,
  `fromID` smallint(5) unsigned NOT NULL,
  `creationDate` datetime NOT NULL,
  `durationLife` int(9) unsigned NOT NULL DEFAULT '86400',
  `displayOrder` tinyint(3) NOT NULL DEFAULT '1',
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

-- création de la table form_basic_types
CREATE TABLE IF NOT EXISTS `form_basic_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `placeholder` varchar(255) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `validationRules` varchar(255) NOT NULL,
  `validationErrorMsg` varchar(255) NOT NULL,
  `formType` varchar(255) NOT NULL,
  `formValues` text NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
  `cat` smallint(5) unsigned NOT NULL,
  `fromID` smallint(5) unsigned NOT NULL,
  `creationDate` datetime NOT NULL,
  `deleteByID` smallint(5) unsigned NOT NULL,
  `deleteDate` datetime NOT NULL,
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
  `txtNumOrdre` smallint(4) unsigned NOT NULL,
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
  `type` enum('patient','pro','externe','service','deleted') NOT NULL DEFAULT 'patient',
  `rank` enum('','admin') DEFAULT NULL,
  `module` varchar(20) DEFAULT 'base',
  `pass` varbinary(1000) DEFAULT NULL,
  `registerDate` datetime DEFAULT NULL,
  `fromID` smallint(5) DEFAULT NULL,
  `lastLogIP` varchar(50) DEFAULT NULL,
  `lastLogDate` datetime DEFAULT NULL,
  `lastLogFingerprint` varchar(50) DEFAULT NULL,
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
  `type` enum('cr','ordo','courrier','ordoLAP') NOT NULL DEFAULT 'cr',
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
  `groupe` enum('system','module','cron','lock') DEFAULT 'system',
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
('catConsult', 'Consultations', NULL, 'base', 1, '2019-01-01 00:00:00', '1');

-- actes_base
INSERT IGNORE INTO `actes_base` (`code`, `activite`, `phase`, `codeProf`, `label`, `type`, `dataYaml`, `tarifUnit`, `fromID`, `creationDate`) VALUES
('Consult', '1', '0', NULL, 'Consultation libre exemple', 'Libre', 'tarifBase: 50', 'euro', 1, '2019-01-01 00:00:00');

-- actes
SET @catID = (SELECT actes_cat.id FROM actes_cat WHERE actes_cat.name='catConsult');
INSERT IGNORE INTO `actes` (`cat`, `label`, `shortLabel`, `details`, `flagImportant`, `flagCmu`, `fromID`, `toID`, `creationDate`) VALUES
(@catID, 'Consultation de base', 'Cs base', 'Consult:\r\n  pourcents: 100\r\n  depassement: 15 \r\n', '1', '0', 1, '0', '2019-01-01 00:00:00');

-- data_cat
INSERT IGNORE INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('admin', 'activity', 'Activités', 'Activités professionnelles et de loisir', 'base', '1', '2019-01-01 00:00:00'),
('admin', 'addressPerso', 'Adresse personnelle', 'datas de l\'adresse personnelle', 'base', '1', '2019-01-01 00:00:00'),
('admin', 'adressPro', 'Adresse professionnelle', 'Data de l\'adresse professionnelle', 'base', '1', '2019-01-01 00:00:00'),
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
('medical', 'dataCsBase', 'Données formulaire Cs', NULL, 'base', '1', '2019-01-01 00:00:00'),
('medical', 'grossesse', 'Grossesse', 'Données liées à la grossesse', 'base', '1', '2019-01-01 00:00:00'),
('ordo', 'OrdoItems', 'Ordo', 'items d\'une ordonnance', 'base', '1', '2019-01-01 00:00:00'),
('ordo', 'lapCatLignePrescription', 'LAP ligne de prescription', 'data des lignes de prescription', 'base', '1', '2019-01-01 00:00:00'),
('ordo', 'lapCatMedicament', 'LAP médicament', 'data pour les médicaments', 'base', '1', '2019-01-01 00:00:00'),
('ordo', 'lapCatPorteurs', 'LAP porteurs', 'data pour les porteurs LAP', 'base', '1', '2019-01-01 00:00:00'),
('ordo', 'lapCatSams', 'LAP SAMs', 'data pour SAMs LAP', 'base', '1', '2019-01-01 00:00:00'),
('ordo', 'porteursOrdo', 'Porteurs', 'porteurs ordonnance', 'base', '1', '2019-01-01 00:00:00'),
('reglement', 'porteursReglement', 'Porteurs', 'porteur d\'un règlement', 'base', '1', '2019-01-01 00:00:00'),
('reglement', 'reglementItems', 'Règlement', 'items d\'un réglement', 'base', '1', '2019-01-01 00:00:00'),
('relation', 'catAllergiesStruc', 'Allergies structurées', 'données pour allergies structurées', 'base', '1', '2019-01-01 00:00:00'),
('relation', 'relationRelations', 'Relations', 'types permettant de définir une relation', 'base', '1', '2019-01-01 00:00:00'),
('typecs', 'catTypeCsATCD', 'Antécédents et allergies', 'antécédents et allergies', 'base', '1', '2019-01-01 00:00:00'),
('typecs', 'csAutres', 'Autres', 'autres', 'base', '1', '2019-01-01 00:00:00'),
('typecs', 'csBase', 'Consultations', 'consultations possibles', 'base', '1', '2019-01-01 00:00:00'),
('typecs', 'declencheur', 'Déclencheur', NULL, 'base', '1', '2019-01-01 00:00:00');

-- data_types
SET @catID = 0;
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'submit', NULL, NULL, NULL, NULL, NULL, 'submit', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='identity');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'administrativeGenderCode', NULL, 'Sexe', 'Sexe', NULL, NULL, 'select', 'F: \'Femme\'\nM: \'Homme\'\nU: \'Inconnu\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'birthdate', 'naissance: dd/mm/YYYY', 'Date de naissance', 'Date de naissance au format dd/mm/YYYY', 'validedate,\'d/m/Y\'', 'La date de naissance indiquée n\'est pas valide', 'date', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'birthname', 'nom', 'Nom de naissance', 'Nom reçu à la naissance', 'identite', 'Le nom de naissance est indispensable et ne doit pas contenir de caractères interdits', 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'deathdate', 'décès: dd/mm/YYYY', 'Date de décès', 'Date de décès au format dd/mm/YYYY', 'validedate,\'d/m/Y\'', 'La date de décès indiquée n\'est pas valide', 'date', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'firstname', 'prénom', 'Prénom', 'Prénom figurant sur la pièce d\'identité', 'identite', 'Le prénom est indispensable et ne doit pas contenir de caractères interdits', 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'lastname', 'nom marital ou d\'usage', 'Nom d\'usage', 'Nom utilisé au quotidien', 'identite', 'Le nom d\'usage ne doit pas contenir de caractères interdits', 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'othersfirstname', 'liste des prénoms secondaires', 'Autres prénoms', 'Les autres prénoms d\'une personne', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'titre', 'Dr, Pr ...', 'Titre', 'Titre du pro de santé', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='addressPerso');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'city', 'ville', 'Ville', 'Adresse perso : ville', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'postalCodePerso', 'code postal', 'Code postal', 'Adresse perso : code postal', NULL, 'Le code postal n\'est pas correct', 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'street', 'type et nom de la voie', 'Voie', 'Adresse perso : voie', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'streetNumber', 'n° dans la voie', 'n°', 'Adresse perso : n° dans la voie', NULL, 'Le numéro de voie est incorrect', 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='internet');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'twitterAccount', NULL, 'Twitter', 'Compte twitter', 'twitterAccount', NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'website', NULL, 'Site web', 'Site web', 'url', NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='grossesse');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'DDR', 'ddr', 'DDR', 'date des dernières règles', NULL, 'validedate,\'d/m/Y\'', 'date', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'ddg', 'ddg', 'DDG (théorique)', 'date de début de grossesse', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'ddgReel', NULL, 'DDG (retenue)', 'date de début de grossesse corrigé', NULL, NULL, 'date', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'groFermetureSuivi', NULL, 'Fermeture de la grossesse', 'date de fermeture de la grossesse (porteur)', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'terme9mois', NULL, 'Terme (9 mois)', 'terme', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'termeDuJour', NULL, 'Terme du jour', 'terme du jour', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catDataTransversesFormCs');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'codeTechniqueExamen', NULL, 'Acte lié à l\'examen réalisé', 'code acte caractérisant l\'examen fait via le formulaire qui l\'emploie', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='contact');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'emailApicrypt', 'adresse mail apicript', 'Email apicrypt', 'Email apicrypt', 'valid_email', NULL, 'email', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'faxPro', 'fax professionel', 'Fax professionnel', 'FAx pro', 'phone', NULL, 'tel', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'homePhone', '0x xx xx xx xx', 'Téléphone domicile', 'Téléphone du domicile de la forme 0x xx xx xx xx', 'phone', 'Le numéro de téléphone du domicile n\'est pas correct', 'tel', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'mobilePhone', 'mobile: 0x xx xx xx xx', 'Téléphone mobile', 'Numéro de téléphone commençant par 06 ou 07', 'mobilphone', 'Le numéro de téléphone mobile est incorrect', 'tel', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'mobilePhonePro', '06 xx xx xx xx', 'Téléphone mobile pro.', 'Numéro de téléphone commençant par 06 ou 07', 'mobilphone', 'Le numéro de téléphone mobile pro est incorrect', 'tel', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'personalEmail', 'email@domain.ext', 'Email personnelle', 'Adresse email personnelle', 'valid_email', 'L\'adresse email n\'est pas correcte. Elle doit être de la forme email@domain.net', 'email', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'profesionnalEmail', 'email@domain.ext', 'Email professionnelle', 'Adresse email professionnelle', 'valid_email', 'L\'adresse email n\'est pas correcte. Elle doit être de la forme email@domain.net', 'email', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'telPro', 'téléphone professionnel', 'Téléphone professionnel', 'Téléphone pro.', 'phone', NULL, 'tel', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'telPro2', 'téléphone professionnel 2', 'Téléphone professionnel 2', 'Téléphone pro. 2', 'phone', NULL, 'tel', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='activity');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'job', 'activité professionnelle', 'Activité professionnelle', 'Activité professionnelle', NULL, 'L\'activité professionnelle n\'est pas correcte', 'textarea', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'sport', 'sport exercé', 'Sport', 'Sport exercé', NULL, 'Le sport indiqué n\'est pas correct', 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='divers');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'notes', 'notes', 'Notes', 'Zone de notes', NULL, NULL, 'textarea', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'notesPro', 'notes pros', 'Notes pros', 'Zone de notes pros', NULL, NULL, 'textarea', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='dataCliniques');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'freqCardiaque', NULL, 'FC', 'fréquence cardiaque en bpm', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '60', '1'),
('medical', 'imc', NULL, 'IMC', 'IMC (autocalcule)', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'poids', NULL, 'Poids', 'poids du patient en kg', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'spO2', NULL, 'SpO2', 'saturation en oxygène', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '60', '1'),
('medical', 'taDiastolique', NULL, 'TAD', 'tension artérielle diastolique en mm Hg', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '60', '1'),
('medical', 'taSystolique', NULL, 'TAS', 'tension artérielle systolique en mm Hg', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '60', '1'),
('medical', 'taillePatient', NULL, 'Taille', 'taille du patient en cm', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='atcd');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'allaitementActuel', NULL, 'Allaitement', 'allaitement actuel', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'allergies', 'allergies et intolérances', 'Allergies', 'Allergies et intolérances du patient', NULL, NULL, 'textarea', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'atcdFamiliaux', 'Antécédents familiaux', 'Antécédents familiaux', 'Antécédents familiaux', NULL, NULL, 'textarea', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'atcdMedicChir', 'Antécédents médico-chirurgicaux personnels', 'Antécédents médico-chirurgicaux', 'Antécédents médico-chirurgicaux personnels', NULL, NULL, 'textarea', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'baseSynthese', 'synthèse sur le patient', 'Synthèse patient', 'Synthèse sur le patient', NULL, NULL, 'textarea', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'dataImport', NULL, 'Import', 'support pour consultations importées', NULL, NULL, 'textarea', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '84600', '1'),
('medical', 'insuffisanceHepatique', NULL, 'Insuffisance hépatique', 'degré d\'insuffisance hépatique', NULL, NULL, 'select', '\'z\': \"?\"\n\'n\': \"Pas d\'insuffisance hépatique connue\"\n\'1\': \'Légère\'\n\'2\': \'Modérée\'\n\'3\': \'Sévère\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'toxiques', 'tabac et drogues', 'Toxiques', 'habitudes de consommation', NULL, NULL, 'textarea', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='dataBio');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'clairanceCreatinine', NULL, 'Clairance créatinine', 'clairance de la créatinine en mL/min', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='csBase');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('typecs', 'csBaseGroup', NULL, 'Consultation', 'support parent pour les consultations', NULL, NULL, NULL, 'baseConsult', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='dataCsBase');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'examenDuJour', 'examen du jour', 'Examen du jour', 'examen du jour', NULL, NULL, 'textarea', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='numAdmin');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'PSCodeProSpe', NULL, 'Code normé de la profession/spécialité du praticien', 'code normé de la profession/spécialité du praticien', NULL, NULL, 'select', '\'Z\' : \'Jeu de valeurs normées absent\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'PSCodeStructureExercice', NULL, 'Code normé de la structure d\'exercice du praticien', 'code normé de la structure d\'exercice du praticien', NULL, NULL, 'select', '\'Z\' : \'Jeu de valeurs normées absent\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'PSIdNat', NULL, 'Identifiant national praticien santé', 'identifiant national praticien santé', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'adeli', 'adeli', 'Adeli', 'n° adeli', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'nReseau', NULL, 'Numéro de réseau', 'numéro de réseau (dépistage)', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'nss', NULL, 'Numéro de sécu', 'numéro de sécurité sociale', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'rpps', 'rpps', 'RPPS', 'rpps', 'numeric', NULL, 'number', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catModelesCertificats');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('courrier', 'modeleCertifVierge', NULL, 'Certificat', 'modèle de certificat vierge', NULL, NULL, NULL, 'certif-certificatVierge', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catModelesCourriers');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('courrier', 'modeleCourrierTtEnCours', NULL, 'Traitement en cours', 'modèle de courrier pour l\'impression du traitement en cours', NULL, NULL, NULL, 'courrier-ttEnCours', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '6'),
('courrier', 'modeleCourrierVierge', NULL, 'Courrier', 'modèle de courrier vierge', NULL, NULL, NULL, 'courrier-courrierVierge', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='mailForm');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('mail', 'mailBody', 'texte du message', 'Message', 'texte du message', NULL, NULL, 'textarea', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailFrom', 'email@domain.net', 'De', 'mail from', NULL, NULL, 'email', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailModeles', NULL, 'Modèle', 'liste des modèles', NULL, NULL, 'select', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailPJ1', NULL, 'ID pièce jointe', 'id de la pièce jointe au mail', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailSujet', 'sujet du mail', 'Sujet', 'sujet du mail', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailTo', NULL, 'A', 'mail to', NULL, NULL, 'email', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailToApicrypt', NULL, 'A (correspondant apicrypt)', 'Champ pour les correspondants apicrypt', NULL, NULL, 'email', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailToEcofaxName', NULL, 'Destinataire du fax', 'Destinataire du fax (ecofax OVH)', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailToEcofaxNumber', NULL, 'Numéro de fax du destinataire', 'Numéro du destinataire du fax (ecofax OVH)', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'mailTrackingID', NULL, 'TrackingID', 'num de tracking du mail dans le service externe', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='porteursTech');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('mail', 'mailPorteur', NULL, 'Mail', 'porteur pour les mails', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('mail', 'smsPorteur', NULL, 'Mail', 'porteur pour les sms', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='docForm');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('doc', 'docOriginalName', NULL, 'Nom original', 'nom original du document', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('doc', 'docOrigine', NULL, 'Origine du document', 'origine du document : interne ou externe(null)', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('doc', 'docTitle', NULL, 'Titre', 'titre du document', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('doc', 'docType', NULL, 'Type du document', 'type du document importé', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='docPorteur');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('doc', 'docPorteur', NULL, 'Document', 'porteur pour nouveau document importé', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='porteursOrdo');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'ordoPorteur', NULL, 'Ordonnance', 'Ordonnance simple', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='porteursReglement');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('reglement', 'reglePorteurLibre', NULL, 'Règlement', 'Règlement hors convention', NULL, NULL, NULL, 'baseReglementLibre', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'reglePorteurS1', NULL, 'Règlement', 'Règlement conventionné S1', NULL, NULL, NULL, 'baseReglementS1', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'reglePorteurS2', NULL, 'Règlement', 'Règlement conventionné S2', NULL, NULL, NULL, 'baseReglementS2', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='reglementItems');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('reglement', 'regleCB', NULL, 'CB', 'montant versé en CB', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleCheque', NULL, 'Chèque', 'montant versé en chèque', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleDepaCejour', NULL, 'Dépassement', 'dépassement pratiqué ce jour', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleDetailsActes', NULL, 'Détails des actes', 'détails des actes de la facture', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleEspeces', NULL, 'Espèces', 'montant versé en espèce', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleFacture', NULL, 'Facturé', 'facturé ce jour', NULL, NULL, 'text', '0', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleFseData', NULL, 'FSE data', 'data de la FSE générée par service tiers', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleIdentiteCheque', 'n° de chèque, nom du payeur si différent du patient,...', 'Informations paiement', 'Information complémentaires sur le paiement', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleModulCejour', NULL, 'Modulation', 'modulation appliquée ce jour', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleSecteurGeoTarifaire', NULL, 'Secteur géographique tarifaire', 'secteur géographique tarifaire', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleSecteurHonoraires', NULL, 'Secteur tarifaire', 'secteur tarifaire appliqué', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleSecteurHonorairesNgap', NULL, 'Secteur tarifaire NGAP', 'secteur tarifaire NGAP appliqué', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleSecteurIK', NULL, 'Secteur tarifaire pour IK', 'secteur tarifaire IK appliqué', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleSituationPatient', NULL, 'Situation du patient', 'situation du patient : cmu / tp / tout venant', NULL, NULL, 'select', '\'G\' : \'Tout venant\'\n\'CMU\' : \'CMU\'\n\'TP\' : \'Tiers payant AMO\'\n\'TP ALD DEP\' : \'ALD : tiers payant AVEC dépassement \'\n\'TP ALD\' : \'ALD : tiers payant SANS dépassement \'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleTarifLibreCejour', NULL, 'Tarif', 'tarif appliqué ce jour', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleTarifSSCejour', NULL, 'Tarif SS', 'tarif SS appliqué ce jour', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('reglement', 'regleTiersPayeur', NULL, 'Tiers', 'part du tiers', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='adressPro');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'codePostalPro', 'code postal', 'Code postal', 'Adresse pro : code postal', 'alpha_space', 'Le code postal n\'est pas conforme', 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'etablissementAdressePro', 'établissement', 'Établissement', 'Adresse pro : établissement', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'numAdressePro', 'n° dans la voie', 'n°', 'Adresse pro : n° dans la voie', 'alpha_space', 'Le numero n\'est pas conforme', 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'rueAdressePro', 'type et nom de la voie', 'Voie', 'Adresse pro : voie', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'serviceAdressePro', 'service', 'Service', 'Adresse pro : service', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'villeAdressePro', 'ville', 'Ville', 'Adresse pro : ville', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='csAutres');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('typecs', 'csImport', NULL, 'Import', 'support parent pour import', NULL, NULL, NULL, 'baseImportExternal', 'base', @catID, '1', '2019-01-01 00:00:00', '84600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='idDicom');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('dicom', 'dicomInstanceID', NULL, 'InstanceID', NULL, NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('dicom', 'dicomSerieID', NULL, 'SerieID', NULL, NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('dicom', 'dicomStudyID', NULL, 'StudyID', NULL, NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='declencheur');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('typecs', 'nouvelleGrossesse', NULL, 'Nouvelle grossesse', 'support parent pour nouvelle grossesse', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catModelesMailsToApicrypt');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('courrier', 'mmDefautApi', NULL, 'Défaut', 'modèle mail par défaut', 'base', NULL, NULL, 'Cher confrère,\n\nVeuillez trouver en pièce jointe un document concernant notre patient commun.\nVous souhaitant bonne réception.\n\nBien confraternellement\n\n', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='dataSms');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('mail', 'smsId', NULL, 'smsId', 'id du sms', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='relationRelations');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('relation', 'relationExternePatient', NULL, 'Relation externe patient', 'relation externe patient', NULL, NULL, 'number', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('relation', 'relationID', NULL, 'Porteur de relation', 'porteur de relation entre patients ou entre patients et praticiens', NULL, NULL, 'number', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('relation', 'relationPatientPatient', NULL, 'Relation patient patient', 'relation patient patient', NULL, NULL, 'select', '\'conjoint\': \'conjoint\'\n\'enfant\': \'parent\'\n\'parent\': \'enfant\'\n\'grand parent\': \'petit enfant\'\n\'petit enfant\': \'grand parent\'\n\'sœur / frère\': \'sœur / frère\' \n\'tante / oncle\': \'nièce / neveu\' \n\'nièce / neveu\': \'tante / oncle\' \n\'cousin\': \'cousin\'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('relation', 'relationPatientPraticien', NULL, 'Relation patient praticien', 'relation patient  praticien', NULL, NULL, 'select', '\'MTD\': \'Médecin traitant déclaré\'\n\'MT\': \'Médecin traitant\'\n\'MS\': \'Médecin spécialiste\'\n\'Autre\': \'Autre correspondant\'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catMarqueursAdminDossiers');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'administratifMarqueurSuppression', NULL, 'Dossier supprimé', 'marqueur pour la suppression d\'un dossier', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='OrdoItems');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'ordoLigneOrdo', NULL, 'Ligne d\'ordonnance', 'porteur pour une ligne d\'ordo', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'ordoLigneOrdoALDouPas', NULL, 'Ligne d\'ordonnance : ald', '1 si ald', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'ordoTypeImpression', NULL, 'Type ordonnance impression', 'type d\'ordonnance pour impression', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='aldCat');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'aldCIM10', NULL, 'Code CIM10 associé', 'Code CIM10 attaché à l\'ALD', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'aldCIM10label', NULL, 'Label CIM10 associé', 'Label CIM10 attaché à l\'ALD', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'aldDateDebutPriseEnCharge', NULL, 'Début de prise en charge', 'date de début de prise en charge', NULL, NULL, 'date', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'aldDateFinPriseEnCharge', NULL, 'Fin de prise en charge', 'date de fin de prise en charge', NULL, NULL, 'date', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'aldNumber', NULL, 'ALD', 'ALD choisie', NULL, NULL, 'select', '1: \"Accident vasculaire cérébral invalidant\"\n2: \"Insuffisances médullaires et autres cytopénies chroniques\"\n3: \"Artériopathies chroniques avec manifestations ischémiques\"\n4: \"Bilharziose compliquée\"\n5: \"Insuffisance cardiaque grave, troubles du rythme graves, cardiopathies valvulaires graves, cardiopathies  congénitales graves\"\n6: \"Maladies chroniques actives du foie et cirrhoses\"\n7: \"Déficit immunitaire primitif grave nécessitant un traitement prolongé, infection par le virus de 9: l\'immuno-déficience humaine (VIH)\"\n8: \"Diabète de type 1 et diabète de type 2\"\n9: \"Formes graves des affections neurologiques et musculaires (dont myopathie), épilepsie grave\"\n10: \"Hémoglobinopathies, hémolyses, chroniques constitutionnelles et acquises sévères\"\n11: \"Hémophilies et affections constitutionnelles de l\'hémostase graves\"\n12: \"Maladie coronaire\"\n13: \"Insuffisance respiratoire chronique grave\"\n14: \"Maladie d\'Alzheimer et autres démences\"\n15: \"Maladie de Parkinson\"\n16: \"Maladies métaboliques héréditaires nécessitant un traitement prolongé spécialisé\"\n17: \"Mucoviscidose\"\n18: \"Néphropathie chronique grave et syndrome néphrotique primitif\"\n19: \"Paraplégie\"\n20: \"Vascularites, lupus érythémateux systémique, sclérodermie systémique\"\n21: \"Polyarthrite rhumatoïde évolutive\"\n22: \"Affections psychiatriques de longue durée\"\n23: \"Rectocolite hémorragique et maladie de Crohn évolutives\"\n24: \"Sclérose en plaques\"\n25: \"Scoliose idiopathique structurale évolutive (dont l\'angle est égal ou supérieur à 25 degrés) jusqu\'à maturation rachidienne\"\n26: \"Spondylarthrite grave\"\n27: \"Suites de transplantation d\'organe\"\n28: \"Tuberculose active, lèpre\"\n29: \"Tumeur maligne, affection maligne du tissu lymphatique ou hématopoïétique\"\n31: \"Affection hors liste\"\n32: \"Etat polypathologique\"', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catAtcdStruc');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'atcdStrucCIM10', NULL, 'Code CIM 10', 'code CIM 10 de l\'atcd', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucCIM10InLap', NULL, 'A prendre en compte dans le LAP', 'prise en compte ou non dans le LAP', NULL, NULL, 'select', '\'o\': \'oui\'\n\'n\': \'non\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'atcdStrucCIM10Label', NULL, 'Label CIM 10', 'label CIM 10 de l\'atcd', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucDateDebutAnnee', NULL, 'Année', 'année de début de l\'atcd', NULL, NULL, 'number', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucDateDebutJour', NULL, 'Jour', 'jour de début de l\'atcd', NULL, NULL, 'number', '0', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucDateFinAnnee', NULL, 'Année', 'année de fin de l\'atcd', NULL, NULL, 'number', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucDateFinJour', NULL, 'Jour', 'jour de fin de l\'atcd', NULL, NULL, 'number', '0', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucDateFinMois', NULL, 'Mois', 'mois de fin de l\'atcd', NULL, NULL, 'select', '\'0\' : \'non précisé\'\n\'1\' : \'janvier\'\n\'2\' : \'février\'\n\'3\' : \'mars\'\n\'4\' : \'avril\'\n\'5\' : \'mai\'\n\'6\' : \'juin\'\n\'7\' : \'juillet\'\n\'8\' : \'août\'\n\'9\' : \'septembre\'\n\'10\' : \'octobre\'\n\'11\' : \'novembre\'\n\'12\' : \'décembre\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('medical', 'atcdStrucNotes', 'notes concernant cet antécédent', 'Notes', 'notes concernant l\'atcd', NULL, NULL, 'textarea', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catTypeCsATCD');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('typecs', 'csAldDeclaration', NULL, 'Déclaration ALD', 'support parent pour déclaration ALD', NULL, NULL, NULL, 'aldDeclaration', 'base', @catID, '1', '2019-01-01 00:00:00', '84600', '1'),
('typecs', 'csAtcdStrucDeclaration', NULL, 'Ajout d\'antécédent', 'support parent pour déclaration d\'antécédent structuré', NULL, NULL, NULL, 'atcdStrucDeclaration', 'base', @catID, '1', '2019-01-01 00:00:00', '84600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catAllergiesStruc');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('relation', 'allergieCodeTheriaque', NULL, 'Code Thériaque de l\'allergie', 'code Thériaque de l\'allergie', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0'),
('relation', 'allergieLibelleTheriaque', NULL, 'Libelle Thériaque de l\'allergie', 'libelle Thériaque de l\'allergie', NULL, NULL, 'text', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0');

SET @catID = 0;
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('user', 'lapAlertPatientAllaitementSup3Ans', NULL, 'lapAlertPatientAllaitementSup3Ans', 'alerte pour allaitement supérieur à 3 ans', NULL, NULL, 'checkbox', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('user', 'lapAlertPatientTermeGrossesseSup46', NULL, 'lapAlertPatientTermeGrossesseSup46', 'alerte pour terme de grossesse supérieur à 46SA', NULL, NULL, 'checkbox', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('user', 'theriaqueShowMedicHospi', NULL, 'theriaqueShowMedicHospi', 'montrer les médicaments hospitaliers', NULL, NULL, 'checkbox', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('user', 'theriaqueShowMedicNonComer', NULL, 'theriaqueShowMedicNonComer', 'montrer les médicaments non commercialisés', NULL, NULL, 'checkbox', NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapCatPorteurs');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'lapLigneMedicament', NULL, 'Médicament', 'médicament LAP', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescription', NULL, 'Ligne de prescription', 'ligne de prescription LAP', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapOrdonnance', NULL, 'Ordonnance', 'ordonnance LAP', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapSam', NULL, 'SAM', 'porteur SAM LAP', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapCatLignePrescription');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'lapLignePrescriptionDatePriseDebut', NULL, 'Date de début de prise', 'date de début de prise', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionDatePriseFin', NULL, 'Date de fin de prise', 'date de fin de prise', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionDatePriseFinAvecRenouv', NULL, 'Date de fin de prise renouvellements inclus', 'date de fin de prise renouvellements inclus', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionDatePriseFinEffective', NULL, 'Date effective de fin de prise', 'date effective de fin de prise', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionDureeJours', NULL, 'Durée de la prescription en jours', 'durée de la prescription en jours', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionIsALD', NULL, 'isALD', 'ligne ALD ou non', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionIsChronique', NULL, 'isChronique', 'ligne TT chronique ou non', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapLignePrescriptionRenouvelle', NULL, 'ID de la ligne qui est renouvelée par cette ligne', 'ID de la ligne qui est renouvelée par cette ligne', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapCatMedicament');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'lapMedicamentCodeSubstanceActive', NULL, 'Code substance active du médicament', 'code substance active du médicament', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapMedicamentDC', NULL, 'DC du médicament', 'DC du médicament', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapMedicamentEstPrescriptibleEnDC', NULL, 'Médicament prescriptible en DC', 'médicament prescriptible en DC', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapMedicamentMotifPrescription', NULL, 'Motif de prescription du médicament', 'motif de prescription du médicament', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapMedicamentPresentationCodeTheriaque', NULL, 'Code Thériaque de la présentation', 'code Thériaque de la présentation (a priori le CIP7)', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapMedicamentSpecialiteCodeTheriaque', NULL, 'Code Thériaque de la spécialité', 'code Thériaque de la spécialité', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('ordo', 'lapMedicamentSpecialiteNom', NULL, 'Nom de la spécialité', 'nom de la spécialité', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = 0;
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'atcdStrucDateDebutMois', NULL, 'Mois', 'mois de début de l\'atcd', NULL, NULL, 'select', '\'0\' : \'non précisé\'\n\'1\' : \'janvier\'\n\'2\' : \'février\'\n\'3\' : \'mars\'\n\'4\' : \'avril\'\n\'5\' : \'mai\'\n\'6\' : \'juin\'\n\'7\' : \'juillet\'\n\'8\' : \'août\'\n\'9\' : \'septembre\'\n\'10\' : \'octobre\'\n\'11\' : \'novembre\'\n\'12\' : \'décembre\'', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '0');

SET @catID = 0;
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'lapMedicamentCodeATC', NULL, 'Code ATC du médicament', 'code ATC du médicament', NULL, NULL, NULL, NULL, 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

-- configuration
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES
('PraticienPeutEtrePatient', 'default', '0', NULL, 'Options', 'true/false', 'si false, le praticien peut toujours avoir une fiche patient séparée', 'true'),
('VoirRouletteObstetricale', 'default', '0', NULL, 'Options', 'true/false', 'activer le lien roulette obstétricale du menu Outils', 'true'),
('administratifComptaPeutVoirRecettesDe', 'default', '0', NULL, 'Règlements', 'liste', 'ID des utilisateurs, séparés par des virgules (sans espace)', NULL),
('administratifPeutAvoirAgenda', 'default', '0', NULL, 'Options', 'true/false', 'peut avoir un agenda à son nom', 'true'),
('administratifPeutAvoirFacturesTypes', 'default', '0', NULL, 'Règlements', 'true/false', 'peut avoir des factures types à son nom', 'false'),
('administratifPeutAvoirPrescriptionsTypes', 'default', '0', NULL, 'Options', 'true/false', 'peut avoir des prescriptions types à son nom', 'false'),
('administratifPeutAvoirRecettes', 'default', '0', NULL, 'Règlements', 'true/false', 'peut enregistrer des recettes à son nom', 'true'),
('administratifReglementFormulaires', 'default', '0', NULL, 'Règlements', 'liste', 'liste des formulaires de règlement disponible dans le dossier patient ', 'reglePorteurS1,reglePorteurS2,reglePorteurLibre'),
('administratifSecteurGeoTarifaire', 'default', '0', NULL, 'Règlements', 'dossier', 'zone géographique tarifaire (metro, 971, 972 ...)', 'metro'),
('administratifSecteurHonorairesCcam', 'default', '0', NULL, 'Règlements', NULL, 'grille tarifaire CCAM du praticien', '9'),
('administratifSecteurHonorairesNgap', 'default', '0', NULL, 'Règlements', 'texte', 'Code profession pour le secteur tarifaire NGAP', 'mspe'),
('administratifSecteurIK', 'default', '0', NULL, 'Règlements', 'texte', 'tarification des IK : indiquer plaine ou montagne', 'plaine'),
('agendaDistantLink', 'default', '0', NULL, 'Agenda', 'url', 'lien à appliquer à Agenda sur les pages MedShakeEHR. Si agendaService est configuré, alors agendaDistantLink doit être vide', NULL),
('agendaDistantPatientsOfTheDay', 'default', '0', NULL, 'Agenda', 'url', 'url distante où l’on peut récupérer une liste horodatée des patients du jour', NULL),
('agendaLocalPatientsOfTheDay', 'default', '0', NULL, 'Agenda', 'fichier', 'fichier json de la liste horodatée des patients du jour', 'patientsOfTheDay.json'),
('agendaModePanneauLateral', 'default', '0', NULL, 'Agenda', 'true/false', 'Utilisation du panneau latéral (true) ou d\'une fenêtre contextuelle (false)', 'true'),
('agendaNumberForPatientsOfTheDay', 'default', '0', NULL, 'Agenda', 'nombre', 'Numéro d\'agenda pour générer à partir de l\'agenda interne concerné une liste des patients du jour pour le menu Patients', '0'),
('agendaPremierJour', 'default', '0', NULL, 'Agenda', 'vide/nombre', 'vide pour roulant, 0 pour dimanche, 1 pour lundi, etc...', '1'),
('agendaService', 'default', '0', NULL, 'Agenda', 'vide/clicRDV', 'si non vide, active le service tiers concerné', NULL),
('allMySmsApiKey', 'default', '0', NULL, 'Rappels SMS', 'texte', 'API key allMySMS', NULL),
('allMySmsLogin', 'default', '0', NULL, 'Rappels SMS', 'texte', 'login allMySMS', NULL),
('apiCcamNgapKey', 'default', '0', NULL, 'Règlements', 'string', 'Clef de l\'API CCAM NGAP MedShake', NULL),
('apiCcamNgapUrl', 'default', '0', NULL, 'Règlements', 'url', 'URL de l\'API CCAM NGAP MedShake', NULL),
('apicryptAdresse', 'default', '0', NULL, 'Apicrypt', 'texte', 'adresse complète apicrypt, ex :  prenom.NOM@medicalXX.apicrypt.org', NULL),
('apicryptCheminArchivesInbox', 'default', '0', NULL, 'Apicrypt', 'dossier', 'chemin du répertoire qui sert à archiver par date de traitement les messages reçus, classés dans les dossiers comme non classés', NULL),
('apicryptCheminFichierC', 'default', '0', NULL, 'Apicrypt', 'dossier', 'répertoire de travail apicrypt, fichiers chiffrés', NULL),
('apicryptCheminFichierNC', 'default', '0', NULL, 'Apicrypt', 'dossier', 'répertoire de travail pour Apicrypt, fichier non chiffrés', NULL),
('apicryptCheminInbox', 'default', '0', NULL, 'Apicrypt', 'dossier', 'chemin du répertoire qui sert de boite de réception, doit être en zone accessible web', NULL),
('apicryptCheminVersBinaires', 'default', '0', NULL, 'Apicrypt', 'dossier', 'chemin vers le répertoire contenant les programmes Apicrypt en ligne de commande', NULL),
('apicryptCheminVersClefs', 'default', '0', NULL, 'Apicrypt', 'dossier', 'chemin vers les répertoire Clefs Apicrypt contenant les clefs de l’utilisateur', NULL),
('apicryptDefautSujet', 'default', '0', NULL, 'Apicrypt', 'texte', 'sujet par défaut des mails Apicrypt (attention, n\'est pas chiffré : jamais d\'éléments d\'identité dans le sujet !)', 'Document concernant votre patient'),
('apicryptInboxMailForUserID', 'default', '0', NULL, 'Apicrypt', 'nombre', 'ID ou IDs numériques des comptes utilisateurs (séparés par des virgules) pour lesquels l\'utilisateur courant peut voir les mails Apicrypt relevés en inbox', NULL),
('apicryptPopHost', 'default', '0', NULL, 'Apicrypt', 'url/ip', 'serveur pop pour la réception des messages Apicrypt', 'pop.intermedic.org'),
('apicryptPopPassword', 'default', '0', NULL, 'Apicrypt', 'texte', 'mot de passe apicrypt', NULL),
('apicryptPopPort', 'default', '0', NULL, 'Apicrypt', 'nombre', 'port du serveur pop', '110'),
('apicryptPopUser', 'default', '0', NULL, 'Apicrypt', 'texte', 'nom d\'utilisateur pour le serveur pop : prenom.NOM', NULL),
('apicryptSmtpHost', 'default', '0', NULL, 'Apicrypt', 'url/ip', 'serveur smtp pour l\'envoi des messages Apicrypt, en règle générale : smtp.intermedic.org', 'smtp.intermedic.org'),
('apicryptSmtpPort', 'default', '0', NULL, 'Apicrypt', 'nombre', 'port du serveur SMTP', '587'),
('apicryptUtilisateur', 'default', '0', NULL, 'Apicrypt', 'texte', 'nom d\'utilisateur Apicrypt (portion devant le @ de l\'adresse)', NULL),
('clicRdvApiKey', 'default', '0', NULL, 'clicRDV', 'texte', NULL, NULL),
('clicRdvCalId', 'default', '0', NULL, 'clicRDV', 'nombre', NULL, NULL),
('clicRdvConsultId', 'default', '0', NULL, 'clicRDV', 'JSON', NULL, NULL),
('clicRdvGroupId', 'default', '0', NULL, 'clicRDV', 'nombre', NULL, NULL),
('clicRdvPassword', 'default', '0', NULL, 'clicRDV', 'texte', NULL, NULL),
('clicRdvUserId', 'default', '0', NULL, 'clicRDV', 'texte', NULL, NULL),
('click2callService', 'default', '0', 'base', 'Click2call', 'string', 'nom du service Click2call à activer (OVH)', NULL),
('designTopMenuInboxCountDisplay', 'default', '0', NULL, 'Ergonomie et design', 'true/false', 'afficher dans le menu de navigation du haut de page le nombre de nouveaux messages dans la boite de réception', 'true'),
('designTopMenuStyle', 'default', '0', NULL, 'Ergonomie et design', 'icones / textes', 'aspect du menu de navigation du haut de page', 'icones'),
('designTopMenuTransmissionsColorIconeImportant', 'default', '0', NULL, 'Ergonomie et design', 'true/false', 'colore l\'icône transmission si transmission importante non lue', 'true'),
('designTopMenuTransmissionsColorIconeUrgent', 'default', '0', NULL, 'Ergonomie et design', 'true/false', 'colore l\'icône transmission si transmission urgente non lue', 'true'),
('designTopMenuTransmissionsCountDisplay', 'default', '0', NULL, 'Ergonomie et design', 'true/false', 'afficher dans le menu de navigation du haut de page le nombre de transmissions non lues', 'true'),
('dicomAutoSendPatient', 'default', '0', NULL, 'DICOM', 'true/false', 'générer automatiquement le fichier worklist pour Orthanc à l\'ouverture d’un dossier patient. Ne pas mettre à true pour une secrétaire par exemple !', 'false'),
('dicomDiscoverNewTags', 'default', '0', NULL, 'DICOM', 'true/false', 'enregistrer automatiquement dans la base de données les nouveaux tags dicom rencontrés lors de la visualisation d\'études afin de pouvoir les associer par la suite automatiquement avec des données de formulaire MedShakeEHR', 'true'),
('dicomHost', 'default', '0', NULL, 'DICOM', 'url/ip', 'IP du serveur Orthanc', NULL),
('dicomPrefixIdPatient', 'default', '0', NULL, 'DICOM', 'texte', 'prefix à appliquer à l\'identifiant numérique MedShakeEHR pour en faire un identifiant DICOM unique', '1.100.100'),
('dicomWorkListDirectory', 'default', '0', NULL, 'DICOM', 'dossier', 'chemin du répertoire où Orthanc va récupérer le fichier dicom worklist généré par MedShakeEHR pour le passer à l\'appareil d\'imagerie', NULL),
('dicomWorkingDirectory', 'default', '0', NULL, 'DICOM', 'dossier', 'répertoire de travail local où on peut rapatrier des images à partir d\'Orthanc pour les parcourir ou les traiter (pdf, zip ...). Utiliser en général le même répertoire que celui indiqué dans workingDirectory des paramètres généraux. Doit être en zone web accessible', NULL),
('droitDossierPeutCreerPraticien', 'default', '0', NULL, 'Droits', 'true/false', 'si true, peut créer des dossiers praticiens', 'true'),
('droitDossierPeutRetirerPraticien', 'default', '0', NULL, 'Droits', 'true/false', 'si true, peut retirer le statut praticien à un dossier (retour à patient, réciproque de droitDossierPeutCreerPraticien)', 'true'),
('droitDossierPeutSupPatient', 'default', '0', NULL, 'Droits', 'true/false', 'si true, peut supprimer des dossiers patients (non définitivement)', 'true'),
('droitDossierPeutSupPraticien', 'default', '0', NULL, 'Droits', 'true/false', 'si true, peut supprimer des dossiers praticiens (non définitivement)', 'true'),
('droitDossierPeutVoirTousPatients', 'default', '0', NULL, 'Droits', 'true/false', 'si true, peut voir tous les dossiers créés par les autres praticiens', 'true'),
('droitExportPeutExporterAutresData', 'default', '0', NULL, 'Droits', 'true/false', 'si true, peut exporter les datas générées par les autres praticiens', 'false'),
('droitExportPeutExporterPropresData', 'default', '0', NULL, 'Droits', 'true/false', 'si true, peut exporter ses propres datas', 'true'),
('droitStatsPeutVoirStatsGenerales', 'default', '0', NULL, 'Droits', 'true/false', 'si true, peut voir les statistiques générales', 'true'),
('ecofaxMyNumber', 'default', '0', NULL, 'Fax', 'n° fax', 'numéro du fax en réception, ex: 0900000000', NULL),
('ecofaxPassword', 'default', '0', NULL, 'Fax', 'texte', 'mot de passe du service de fax', NULL),
('faxService', 'default', '0', NULL, 'Fax', 'vide/ecofaxOVH', 'si non vide, active le service tiers concerné', NULL),
('lapActiverAllergiesStrucSur', 'default', '0', NULL, 'LAP', 'texte', 'champs sur lesquels activer les Allergies structurées', NULL),
('lapActiverAtcdStrucSur', 'default', '0', NULL, 'LAP', 'texte', 'champs sur lesquels activer les atcd structurés', NULL),
('lapAlertPatientAllaitementSup3Ans', 'default', '0', NULL, 'LAP', 'true/false', 'alerte pour allaitement sup à 3 ans à l\'entrée dans le LAP', 'true'),
('lapAlertPatientTermeGrossesseSup46', 'default', '0', NULL, 'LAP', 'true/false', 'alerte pour terme sup à 46SA à l\'entrée dans le LAP', 'true'),
('lapAllergiesStrucPersoPourAnalyse', 'default', '0', NULL, 'LAP', 'texte', 'champs sur lesquels analyser les Allergies structurées', NULL),
('lapAtcdStrucPersoPourAnalyse', 'default', '0', NULL, 'LAP', 'texte', 'champs sur lesquels analyser les atcd structurés', NULL),
('lapPrintAllergyRisk', 'default', '0', NULL, 'LAP', 'true/false', 'imprimer les risques allergiques détectés', 'true'),
('lapSearchDefaultType', 'default', '0', NULL, 'LAP', 'texte', 'mode de recherche par défaut des médicaments', 'dci'),
('lapSearchResultsSortBy', 'default', '0', NULL, 'LAP', 'texte', 'ordre préférentiel d\'affichage des médicaments', 'nom'),
('mailRappelActiver', 'default', '0', NULL, 'Rappels mail', 'true/false', 'activer / désactiver les rappels par mail', 'false'),
('mailRappelDaysBeforeRDV', 'default', '0', NULL, 'Rappels mail', 'nombre', 'nombre de jours avant le rendez-vous pour l\'expédition du rappel', '3'),
('mailRappelLogCampaignDirectory', 'default', '0', NULL, 'Rappels mail', 'dossier', 'chemin du répertoire où on va loguer les rappels de rendez-vous par mail', NULL),
('mailRappelMessage', 'default', '0', NULL, 'Rappels mail', 'textarea', 'Les balises #heureRdv, #jourRdv et #praticien seront automatiquement remplacées dans le message envoyé', 'Bonjour,\\n\\nNous vous rappelons votre RDV du #jourRdv à #heureRdv avec le Dr #praticien.\\nNotez bien qu’aucun autre rendez-vous ne sera donné à un patient n’ayant pas honoré le premier.\\n\\nMerci de votre confiance,\\nÀ bientôt !\\n\\nP.S. : Ceci est un mail automatique, merci de ne pas répondre.'),
('optionGeAdminActiverLiensRendreUtilisateur', 'default', '0', NULL, 'Options', 'true/false', 'si true, l\'administrateur peut transformer des patients ou praticiens en utilisateur via les listings publiques', 'false'),
('ovhApplicationKey', 'default', '0', 'base', 'Click2call', 'string', 'OVH Application Key', NULL),
('ovhApplicationSecret', 'default', '0', 'base', 'Click2call', 'string', 'OVH Application Secret', NULL),
('ovhConsumerKey', 'default', '0', 'base', 'Click2call', 'string', 'OVH Consumer Key', NULL),
('ovhTelecomBillingAccount', 'default', '0', 'base', 'Click2call', 'string', 'Informations sur la ligne > Nom du groupe', NULL),
('ovhTelecomCallingNumber', 'default', '0', 'base', 'Click2call', 'string', 'Numéro de l\'appelant au format international 0033xxxxxxxxxx', NULL),
('ovhTelecomIntercom', 'default', '0', 'base', 'Click2call', 'true/false', 'Activer le mode intercom', NULL),
('ovhTelecomServiceName', 'default', '0', 'base', 'Click2call', 'string', 'Numéro de la ligne au format international 0033xxxxxxxxxx', NULL),
('phonecaptureCookieDuration', 'default', '0', NULL, 'Phonecapture', 'nombre', 'durée de vie d\'identification d\'un périphérique pour PhoneCapture', '31104000'),
('phonecaptureFingerprint', 'default', '0', NULL, 'Phonecapture', 'texte', 'chaîne aléatoire permettant une sécurisation de l\'identification des périphériques PhoneCapture', 'phonecapture'),
('phonecaptureResolutionHeight', 'default', '0', NULL, 'Phonecapture', 'nombre', ' résolution des photos, hauteur', '1080'),
('phonecaptureResolutionWidth', 'default', '0', NULL, 'Phonecapture', 'nombre', 'résolution des photos, largeur', '1920'),
('signPeriphName', 'default', '0', NULL, 'Options', 'texte', 'nom du périphérique pour signature (caractères alphanumériques, sans espaces ni accents)', 'default'),
('smsCreditsFile', 'default', '0', NULL, 'Rappels SMS', 'fichier', 'nom du fichier qui contient le nombre de SMS restants', 'creditsSMS.txt'),
('smsDaysBeforeRDV', 'default', '0', NULL, 'Rappels SMS', 'nombre', 'nombre de jours avant le rendez-vous pour l\'expédition du rappel SMS', '3'),
('smsLogCampaignDirectory', 'default', '0', NULL, 'Rappels SMS', 'dossier', 'chemin du répertoire où on va loguer les rappels de rendez-vous par SMS', NULL),
('smsProvider', 'default', '0', NULL, 'Rappels SMS', 'url/ip', 'active le service tiers concerné', NULL),
('smsRappelActiver', 'default', '0', NULL, 'Rappels SMS', 'true/false', 'activer / désactiver les rappels par SMS', 'false'),
('smsRappelMessage', 'default', '0', NULL, 'Rappels SMS', 'textarea', 'Les balises #heureRdv, #jourRdv et #praticien seront automatiquement remplacées dans le message envoyé', 'Rappel: Vous avez rdv à #heureRdv le #jourRdv avec le Dr #praticien'),
('smsSeuilCreditsAlerte', 'default', '0', NULL, 'Rappels SMS', 'nombre', 'prévenir dans l\'interface du logiciel si crédit inférieur ou égale à', '150'),
('smsTpoa', 'default', '0', NULL, 'Rappels SMS', 'texte', 'La balise #praticien sera automatiquement remplacée dans le message envoyé', 'Dr #praticien'),
('smtpDefautSujet', 'default', '0', NULL, 'Mail', 'texte', 'titre par défaut du mail expédié', 'Document vous concernant'),
('smtpFrom', 'default', '0', NULL, 'Mail', 'email', 'adresse de l’expéditeur des messages, ex: user@domain.net', NULL),
('smtpFromName', 'default', '0', NULL, 'Mail', 'texte', 'nom en clair de l\'expéditeur des messages', NULL),
('smtpHost', 'default', '0', NULL, 'Mail', 'url/ip', 'serveur SMTP', NULL),
('smtpOptions', 'default', '0', NULL, 'Mail', 'texte', 'options pour désactiver quelques options de sécurités', 'off'),
('smtpPassword', 'default', '0', NULL, 'Mail', 'texte', 'mot de passe pour le serveur SMTP', NULL),
('smtpPort', 'default', '0', NULL, 'Mail', 'nombre', 'port du serveur SMTP', '587'),
('smtpSecureType', 'default', '0', NULL, 'Mail', 'texte', 'protocole ssl ou tls (ou rien)', 'tls'),
('smtpTracking', 'default', '0', NULL, 'Mail', 'texte', 'permet d\'activer le tracking des mails envoyés via un service tiers', NULL),
('smtpUsername', 'default', '0', NULL, 'Mail', 'texte', 'login pour le serveur SMTP', NULL),
('statsExclusionCats', 'default', '0', NULL, 'Statistiques', 'liste', 'liste des noms des catégories de formulaires à exclure des statistiques ', 'catTypeCsATCD,csAutres,declencheur'),
('statsExclusionPatients', 'default', '0', NULL, 'Statistiques', 'liste', 'liste des ID des dossiers tests à exclure des statistiques ', NULL),
('templateCourrierHeadAndFoot', 'default', '0', NULL, 'Modèles de documents', 'fichier', 'template pour les courriers', 'base-page-headAndNoFoot.html.twig'),
('templateCrHeadAndFoot', 'default', '0', NULL, 'Modèles de documents', 'fichier', 'template pour les compte-rendus', 'base-page-headAndNoFoot.html.twig'),
('templateDefautPage', 'default', '0', NULL, 'Modèles de documents', 'fichier', 'template par défaut pour l\'impression', 'base-page-headAndFoot.html.twig'),
('templateOrdoALD', 'default', '0', NULL, 'Modèles de documents', 'fichier', 'template (complet) pour les ordonnances bizones ALD', 'ordonnanceALD.html.twig'),
('templateOrdoBody', 'default', '0', NULL, 'Modèles de documents', 'fichier', 'template pour le corps des ordonnances standards', 'ordonnanceBody.html.twig'),
('templateOrdoHeadAndFoot', 'default', '0', NULL, 'Modèles de documents', 'fichier', 'template pour header et footer des ordonnances standards (non ALD)', 'base-page-headAndFoot.html.twig'),
('templatesCdaFolder', 'default', '0', NULL, 'Modèles de documents', 'dossier', 'répertoire des fichiers de template pour la génération de XML CDA', NULL),
('templatesPdfFolder', 'default', '0', NULL, 'Modèles de documents', 'dossier', 'répertoire des fichiers de template pour la génération de PDF', NULL),
('theriaqueMode', 'default', '0', NULL, 'LAP', 'texte', 'ode d\'utilisation de Thériaque : WS (webservice) ou PG (base postgre en local)', NULL),
('theriaqueShowMedicHospi', 'default', '0', NULL, 'LAP', 'true/false', 'voir les médicaments hospitaliers', 'true'),
('theriaqueShowMedicNonComer', 'default', '0', NULL, 'LAP', 'true/false', 'voir les médicaments non commercialisés', 'false'),
('theriaqueWsURL', 'default', '0', NULL, 'LAP', 'texte', 'url du webservice Thériaque', NULL),
('transmissionsDefautDestinataires', 'default', '0', NULL, 'Transmissions', 'liste', 'ID des utilisateurs, séparés par des virgules (sans espace)', NULL),
('transmissionsNbParPage', 'default', '0', NULL, 'Transmissions', 'nombre entier', 'nombre de transmissions par page', '30'),
('transmissionsPeutCreer', 'default', '0', NULL, 'Transmissions', 'true/false', 'peut créer des transmissions', 'true'),
('transmissionsPeutRecevoir', 'default', '0', NULL, 'Transmissions', 'true/false', 'peut recevoir des transmissions', 'true'),
('transmissionsPeutVoir', 'default', '0', NULL, 'Transmissions', 'true/false', 'peut accéder aux transmissions', 'true'),
('transmissionsPurgerNbJours', 'default', '0', NULL, 'Transmissions', 'nombre entier', 'nombre de jours sans update après lequel une transmission sera supprimée de la base de données (0 = jamais)', '365'),
('utiliserLap', 'default', '0', NULL, 'LAP', 'true/false', 'activer / désactiver le LAP', 'false'),
('vitaleActiver', 'default', '0', NULL, 'Vitale', 'true/false', 'activer / désactiver les services liés à la carte vitale', 'false'),
('vitaleHoteLecteurIP', 'default', '0', NULL, 'Vitale', 'texte', 'IP sur le réseau interne de la machine supportant le lecteur', NULL),
('vitaleMode', 'default', '0', NULL, 'Vitale', 'texte', 'simple / complet', 'simple'),
('vitaleNomRessourceLecteur', 'default', '0', NULL, 'Vitale', 'texte', 'nomRessourceLecteur', NULL),
('vitaleNomRessourcePS', 'default', '0', NULL, 'Vitale', 'texte', 'nomRessourcePS', NULL),
('vitaleService', 'default', '0', NULL, 'Vitale', 'texte', 'service tiers de gestion vitale', NULL);

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
SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='patientforms');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseModalNewPatient', 'Formulaire nouveau patient pour modal', 'formulaire d\'enregistrement d\'un nouveau patient dans fenêtre modale', 'data_types', 'admin', 'post', NULL, @catID, 'public', 'structure:\r\n  row1:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 12\r\n      bloc:                          \r\n        - administrativeGenderCode,nolabel      		#14   Sexe\r\n  row2:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 6\r\n      bloc:                          \r\n        - birthname,required,nolabel,autocomplete,data-acTypeID=lastname:birthname #1    Nom de naissance\r\n    col2:\r\n      size: 6\r\n      bloc:                          \r\n        - lastname,nolabel,autocomplete,data-acTypeID=lastname:birthname  		#2    Nom d usage\r\n  row3:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 12\r\n      bloc:                          \r\n        - firstname,nolabel,required,autocomplete,data-acTypeID=firstname:othersfirstname:igPrenomFA:igPrenomFB:igPrenomFC 		#3    Prénom\r\n        - birthdate,nolabel,required,class=pick-years                   		#8    Date de naissance\r\n        - personalEmail,nolabel,class=updatable             		#4    Email personnelle\r\n  row4:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 6\r\n      bloc:                          \r\n        - mobilePhone,nolabel,class=updatable               		#7    Téléphone mobile\r\n    col2:\r\n      size: 6\r\n      bloc:                          \r\n        - homePhone,nolabel,class=updatable                 		#10   Téléphone domicile\r\n\r\n  row5:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 4\r\n      bloc: \r\n        - streetNumber,nolabel,class=updatable              		#9    Numéro\r\n        - postalCodePerso,nolabel,class=updatable           		#13   Code postal\r\n    col2:\r\n      size: 8\r\n      bloc: \r\n        - street,nolabel,autocomplete,data-acTypeID=street:rueAdressePro,class=updatable #11   Rue\r\n        - city,nolabel,autocomplete,data-acTypeID=city:villeAdressePro,class=updatable #12   Ville\r\n  row6:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,nolabel,rows=5,class=updatable             		#21   Notes', NULL, NULL, NULL, NULL),
('base', 'baseNewPatient', 'Formulaire nouveau patient', 'formulaire d\'enregistrement d\'un nouveau patient', 'data_types', 'admin', 'post', '/patient/register/', @catID, 'public', 'structure:\r\n  row1:                              \r\n    col1:                              \r\n      head: \'Etat civil\'             \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - birthname,required,autocomplete,data-acTypeID=lastname:birthname 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=lastname:birthname 		#2    Nom d usage\n        - firstname,required,autocomplete,data-acTypeID=firstname:othersfirstname:igPrenomFA:igPrenomFB:igPrenomFC 		#3    Prénom\n        - birthdate,class=pick-year                		#8    Date de naissance\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - personalEmail                            		#4    Email personnelle\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\n        - nss                                      		#180  Numéro de sécu\n    col3:\r\n      head: \'Adresse personnelle\'\r\n      size: 4\r\n      bloc: \r\n        - streetNumber                             		#9    n°\n        - street,autocomplete,data-acTypeID=street:rueAdressePro 		#11   Voie\n        - postalCodePerso                          		#13   Code postal\n        - city,autocomplete,data-acTypeID=city:villeAdressePro 		#12   Ville\n        - deathdate                                		#516  Date de décès\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,rows=3                             		#21   Notes', NULL, NULL, NULL, '$(document).ready(function() {\r\n\r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseNewPatient textarea\')); \r\n\r\n  // modal edit data admin patient\r\n  $(\'#editAdmin\').on(\'shown.bs.modal\', function (e) {\r\n    autosize.update($(\'#editAdmin textarea\'));\r\n  });\r\n  \r\n});'),
('base', 'baseNewPro', 'Formulaire nouveau pro', 'formulaire d\'enregistrement d\'un nouveau pro', 'data_types', 'admin', 'post', '/pro/register/', @catID, 'public', 'structure:\r\n  row1:                              \r\n    col1:                            \r\n      head: \'Etat civil\'            \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - job,autocomplete,rows=1                         		#19   Activité professionnelle\n        - titre,autocomplete                       		#51   Titre\n        - birthname,autocomplete,data-acTypeID=firstname:othersfirstname:igPrenomFA:igPrenomFB:igPrenomFC 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=lastname:birthname 		#2    Nom d usage\n        - firstname,autocomplete,data-acTypeID=firstname:othersfirstname:igPrenomFA:igPrenomFB:igPrenomFC 		#3    Prénom\n\r\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - profesionnalEmail                        		#5    Email professionnelle\n        - personalEmail                            		#4    Email personnelle\n        - telPro                                   		#57   Téléphone professionnel\n        - telPro2                                  		#248  Téléphone professionnel 2\n        - mobilePhonePro                           		#247  Téléphone mobile pro.\n        - faxPro                                   		#58   Fax professionnel\n    col3:\r\n      head: \'Adresse professionnelle\'\r\n      size: 4\r\n      bloc: \r\n        - numAdressePro                            		#54   Numéro\n        - rueAdressePro,autocomplete,data-acTypeID=street:rueAdressePro 		#55   Rue\n        - codePostalPro                            		#53   Code postal\n        - villeAdressePro,autocomplete,data-acTypeID=city:villeAdressePro 		#56   Ville\n        - serviceAdressePro,autocomplete           		#249  Service\n        - etablissementAdressePro,autocomplete     		#250  Établissement\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notesPro,rows=3                          		#443  Notes pros\n\r\n  row3:\r\n    col1:\r\n      size: 4\r\n      bloc:\r\n        - rpps                                     		#103  RPPS\n        - PSIdNat                                  		#1602 Identifiant national praticien santé\n    col2:\r\n      size: 4\r\n      bloc:\r\n        - adeli                                    		#104  Adeli\n        - PSCodeProSpe,plus={} 		#1603 Code normé de la profession/spécialité du praticien\n    col3:\r\n      size: 4\r\n      bloc:\r\n        - nReseau                                  		#477  Numéro de réseau\n        - PSCodeStructureExercice,plus={} 		#1604 Code normé de la structure d exercice du praticien', NULL, NULL, NULL, '$(document).ready(function() {\r\n\r\n   // modal edit data admin patient\r\n  $(\'#newPro\').on(\'shown.bs.modal\', function (e) {\r\n    autosize.update($(\'#newPro textarea\'));\r\n  });\r\n  \r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseNewPro textarea\')); \r\n\r\n});');

SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='displayforms');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseListingPatients', 'Listing des patients', 'description des colonnes affichées en résultat d\'une recherche patient', 'data_types', 'admin', 'post', NULL, @catID, 'public', 'col1:\r\n    head: \"Date de naissance\" \r\n    bloc: \r\n        - birthdate                                		#8    Date de naissance\ncol2:\r\n    head: \"Tel\" \r\n    blocseparator: \" - \"\r\n    bloc: \r\n        - mobilePhone,click2call                              		#7    Téléphone mobile\n        - homePhone,click2call                                		#10   Téléphone domicile\ncol3:\r\n    head: \"Email\"\r\n    bloc:\r\n        - personalEmail                            		#4    Email personnelle\ncol4:\r\n    head: \"Ville\"\r\n    bloc:\r\n        - city,text-uppercase                      		#12   Ville', NULL, NULL, NULL, NULL),
('base', 'baseListingPro', 'Listing des praticiens', 'description des colonnes affichées en résultat d\'une recherche praticien', 'data_types', 'admin', 'post', NULL, @catID, 'public', 'col1:\r\n    head: \"Activité pro\" \r\n    bloc: \r\n        - job                                      		#19   Activité professionnelle\ncol2:\r\n    head: \"Tel\" \r\n    bloc: \r\n        - telPro,click2call                                   		#57   Téléphone professionnel\ncol3:\r\n    head: \"Fax\" \r\n    bloc: \r\n        - faxPro                                   		#58   Fax professionnel\ncol4:\r\n    head: \"Email\"\r\n    bloc-separator: \" - \"\r\n    bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - personalEmail                            		#4    Email personnelle\ncol5:\r\n    head: \"Ville\"\r\n    bloc:\r\n        - villeAdressePro,text-uppercase           		#56   Ville', NULL, NULL, NULL, NULL);

SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='formCS');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'aldDeclaration', 'Déclaration d\'ALD', 'formulaire d\'enregistrement d\'une ALD', 'data_types', 'medical', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'structure:\r\n  row1:\r\n    head: Enregistrement d\'une prise en charge en ALD\r\n    col1:\r\n     size: 12\r\n     bloc:\r\n       - aldNumber                                 		#886  ALD\n  row2:\r\n    col1:\r\n     size: 4\r\n     bloc:\r\n       - aldDateDebutPriseEnCharge                 		#887  Début de prise en charge\n    col2:\r\n      size: 4\r\n      bloc:\r\n       - aldDateFinPriseEnCharge                   		#888  Fin de prise en charge\n  row3:\r\n    col1:\r\n     size: 2\r\n     bloc:\r\n       - aldCIM10,plus={} 		#889  Code CIM10 associé\n    col2:\r\n     size: 10\r\n     bloc:\r\n       - aldCIM10label,readonly                    		#890  Label CIM10 associé', NULL, NULL, NULL, '$(\"#nouvelleCs\").on(\"click\",\"#id_aldCIM10_idAddOn\", function() {\r\n  $(\'#searchCIM10\').modal(\'show\');\r\n});\r\n\r\n$(\'#searchCIM10\').on(\'shown.bs.modal\', function() {\r\n  $(\'#searchCIM10 #texteRechercheCIM10\').focus();\r\n});\r\n\r\n$(\"#nouvelleCs\").on(\"keyup\",\"#id_aldCIM10_id\", function() {\r\n  if ($(\"#id_aldCIM10_id\").val() == \'\') $(\"#id_aldCIM10label_id\").val(\'\');\r\n});\r\n\r\n$(\"#texteRechercheCIM10\").typeWatch({\r\n  wait: 1000,\r\n  highlight: false,\r\n  allowSubmit: false,\r\n  captureLength: 3,\r\n  callback: function(value) {\r\n    $.ajax({\r\n      url: urlBase+\'/lap/ajax/cim10search/\',\r\n      type: \'post\',\r\n      data: {\r\n        term: value\r\n      },\r\n      dataType: \"html\",\r\n      beforeSend: function() {\r\n        $(\'#codeCIM10trouves\').html(\'
Attente des résultats de la recherche ...
\');\r\n      },\r\n      success: function(data) {\r\n        $(\'#codeCIM10trouves\').html(data);\r\n      },\r\n      error: function() {\r\n        alert(\'Problème, rechargez la page !\');\r\n      }\r\n    });\r\n  }\r\n});\r\n\r\n$(\'#searchCIM10\').on(\"click\", \"button.catchCIM10\", function() {\r\n  code = $(this).attr(\'data-code\');\r\n  label = $(this).attr(\'data-label\');\r\n  $(\"#id_aldCIM10_id\").val(code);\r\n  $(\"#id_aldCIM10label_id\").val(label);\r\n  $(\'#searchCIM10\').modal(\'toggle\');\r\n  $(\'#codeCIM10trouves\').html(\'\');\r\n  $(\"#texteRechercheCIM10\").val(\'\');\r\n\r\n});'),
('base', 'atcdStrucDeclaration', 'Déclaration d\'atcd structuré', 'ajout d\'antécédents structuré et codé CIM 10', 'data_types', 'medical', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'global:\r\n  formClass: \'ignoreReturn\'\r\nstructure: \r\n  row1:\r\n   head : Ajout d\'un antécédent à partir de la classification CIM 10\r\n   col1: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucCIM10,plus={} 		#891  Code CIM 10\n   col2: \r\n     size: 7\r\n     bloc:\r\n       - atcdStrucCIM10Label,readonly              		#892  Label CIM 10\n   col3:\r\n     size: 3\r\n     bloc:\r\n       - atcdStrucCIM10InLap                       		#925  A prendre en compte dans le LAP\n\r\n  row2:\r\n    col1: \r\n     size: 5\r\n     head: \'Début\'\r\n    col2: \r\n     size: 2\r\n     head: \'\'\r\n    col3:\r\n     size: 5\r\n     head: \'Fin\'\r\n  \r\n   \r\n  row3:\r\n    col1: \r\n     size: 1\r\n     bloc:\r\n       - atcdStrucDateDebutJour                    		#893  Jour\n    col2: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateDebutMois                    		#895  Mois\n    col3: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateDebutAnnee,min=1910,step=1   		#897  Année\n    col4: \r\n     size: 2\r\n    col5: \r\n     size: 1\r\n     bloc:\r\n       - atcdStrucDateFinJour                      		#894  Jour\n    col6: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateFinMois                      		#896  Mois\n    col7: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateFinAnnee,min=1910,step=1     		#898  Année\n  row4:\r\n    col1: \r\n      head: \"Notes\"\r\n      size: 12\r\n      bloc:\r\n        - atcdStrucNotes,nolabel                   		#899  Notes', NULL, NULL, NULL, '$(document).ready(function() {\r\n  $(\"#nouvelleCs\").on(\"click\",\"#id_atcdStrucCIM10_idAddOn\", function() {\r\n    $(\'#searchCIM10\').modal(\'show\');\r\n  });\r\n\r\n  $(\'#searchCIM10\').on(\'shown.bs.modal\', function() {\r\n    $(\'#searchCIM10 #texteRechercheCIM10\').focus();\r\n  });\r\n\r\n  $(\"#texteRechercheCIM10\").typeWatch({\r\n    wait: 1000,\r\n    highlight: false,\r\n    allowSubmit: false,\r\n    captureLength: 3,\r\n    callback: function(value) {\r\n      $.ajax({\r\n        url: urlBase+\'/lap/ajax/cim10search/\',\r\n        type: \'post\',\r\n        data: {\r\n          term: value\r\n        },\r\n        dataType: \"html\",\r\n        beforeSend: function() {\r\n          $(\'#codeCIM10trouves\').html(\'
Attente des résultats de la recherche ...
\');\r\n        },\r\n        success: function(data) {\r\n          $(\'#codeCIM10trouves\').html(data);\r\n        },\r\n        error: function() {\r\n          alert(\'Problème, rechargez la page !\');\r\n        }\r\n      });\r\n    }\r\n  });\r\n\r\n  $(\'#searchCIM10\').on(\"click\", \"button.catchCIM10\", function() {\r\n    code = $(this).attr(\'data-code\');\r\n    label = $(this).attr(\'data-label\');\r\n    $(\"#id_atcdStrucCIM10_id\").val(code);\r\n    $(\"#id_atcdStrucCIM10Label_id\").val(label);\r\n    $(\'#searchCIM10\').modal(\'toggle\');\r\n    $(\'#codeCIM10trouves\').html(\'\');\r\n    $(\"#texteRechercheCIM10\").val(\'\');\r\n\r\n  });\r\n  \r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#id_atcdStrucNotes_id\'));\r\n  \r\n});'),
('base', 'baseConsult', 'Formulaire CS', 'formulaire basique de consultation', 'data_types', 'medical', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'global:\r\n  formClass: \'newCS\' \r\nstructure:\r\n####### INTRODUCTION ######\r\n  row1:                              \r\n    head: \'Consultation\'\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n        - examenDuJour,rows=10                     		#716  Examen du jour', NULL, 'csBase', NULL, NULL);

SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='systemForm');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseAgendaPriseRDV', 'Agenda prise rdv', 'formulaire latéral de prise de rdv', 'data_types', 'admin', 'post', NULL, @catID, 'public', 'global:\r\n  noFormTags: true\r\nstructure:\r\n  row1:                              \r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - birthname,readonly                       		#1    Nom de naissance\n    col2:                              \r\n      size: 6\r\n      bloc:                          \r\n        - firstname,readonly                       		#3    Prénom\n  row2:\r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - lastname,readonly                        		#2    Nom d usage\n    col2:                              \r\n      size: 6\r\n      bloc: \r\n        - birthdate,readonly                       		#8    Date de naissance\n  row3:\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n         - personalEmail                           		#4    Email personnelle\n\r\n  row4:                              \r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - mobilePhone                              		#7    Téléphone mobile\n    col2:                              \r\n      size: 6\r\n      bloc:                          \r\n        - homePhone                                		#10   Téléphone domicile', NULL, NULL, NULL, NULL),
('base', 'baseFax', 'Formulaire écofax', 'formulaire pour ecofax OVH', 'data_types', 'mail', 'post', '/patient/actions/sendMail/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: col\r\n    bloc: \r\n      - mailToEcofaxName,required                  		#484  Destinataire du fax\n row2:\r\n   col1: \r\n    size: col\r\n    bloc: \r\n      - mailToEcofaxNumber,required                		#481  Numéro de fax du destinataire', NULL, NULL, NULL, NULL),
('base', 'baseFirstLogin', 'Premier utilisateur', 'Création du premier utilisateur', 'form_basic_types', 'admin', 'post', '/login/logInFirstDo/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: \"Premier utilisateur\"\r\n    size: 3\r\n    bloc:\r\n      - username,required                            		#1    Identifiant\r\n\r\n      - password,required                          		#2    Mot de passe\r\n      - verifPassword,required                     		#5    Confirmation du mot de passe\r\n      - submit,Créer,class=btn-primary,class=btn-sm             #3    Valider', NULL, NULL, NULL, NULL),
('base', 'baseImportExternal', 'Import', 'formulaire pour consultation importée d\'une source externe', 'data_types', 'medical', 'post', NULL, @catID, 'public', 'global:\r\n  formClass: \'newCS\' \r\nstructure:\r\n####### INTRODUCTION ######\r\n  row1:                              \r\n    head: \'Consultation importée\'\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n        - dataImport,rows=10                       		#252  Import', NULL, 'csImportee', NULL, NULL),
('base', 'baseLogin', 'Login', 'formulaire login utilisateur', 'form_basic_types', 'admin', 'post', '/login/logInDo/', @catID, 'public', 'global:\r\n  formClass: \'form-signin\' \r\nstructure:\r\n row1:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - username,required,nolabel                            		#1    Identifiant\n      - password,required,nolabel                          		#2    Mot de passe\n      - submit,Connexion,class=btn-primary,class=btn-block                                     		#3    Valider', NULL, NULL, NULL, NULL),
('base', 'baseReglementLibre', 'Formulaire règlement', 'formulaire pour le règlement d\'honoraires libres', 'data_types', 'reglement', 'post', '/patient/ajax/saveReglementForm/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 4\r\n    bloc: \r\n      - regleTarifLibreCejour,readonly,plus={€},class=regleTarifCejour  		#199  Tarif\n  col2: \r\n    size: 4\r\n    bloc: \r\n      - regleModulCejour,plus={€},class=regleDepaCejour                 		#201  Dépassement\n  col3: \r\n    size: 4\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 4\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 4\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 4\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur', NULL, NULL, NULL, NULL),
('base', 'baseReglementS1', 'Règlement conventionné S1', 'Formulaire pour le règlement d\'honoraires conventionnés secteur 1', 'data_types', 'reglement', 'post', '/patient/ajax/saveReglementForm/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient,class=regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifSSCejour,required,readonly,plus={€},class=regleTarifCejour       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€},class=regleDepaCejour                 		#200  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€},class=regleTiersPayeur                		#202  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur', NULL, NULL, NULL, NULL),
('base', 'baseReglementS2', 'Règlement conventionné S2', 'Formulaire pour le règlement d\'honoraires conventionnés secteur 2', 'data_types', 'reglement', 'post', '/patient/ajax/saveReglementForm/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient,class=regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifSSCejour,required,readonly,plus={€},class=regleTarifCejour       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€},class=regleDepaCejour                 		#200  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€},class=regleTiersPayeur                		#202  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur', NULL, NULL, NULL, NULL),
('base', 'baseReglementSearch', 'Recherche règlements', 'formulaire recherche règlement', 'form_basic_types', 'admin', 'post', NULL, @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - date                                       		#4    Début de période\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - date                                       		#4    Début de période\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - submit                                     		#3    Valider', NULL, NULL, NULL, NULL),
('base', 'baseSendMail', 'Formulaire mail', 'formulaire pour mail', 'data_types', 'mail', 'post', '/patient/actions/sendMail/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - mailFrom,required                          		#109  De\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - mailTo,required                            		#110  A\n row2:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailSujet,required                         		#112  Sujet\n row3:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailModeles                                		#446  Modèle\n row4:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailBody,rows=10                           		#111  Message', NULL, NULL, NULL, NULL),
('base', 'baseSendMailApicrypt', 'Formulaire mail Apicrypt', 'formulaire pour expédier un mail vers un correspondant apicrypt', 'data_types', 'mail', 'post', '/patient/actions/sendMail/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - mailFrom,required                          		#109  De\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - mailToApicrypt,required                    		#179  A (correspondant apicrypt)\n row2:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailSujet,required                         		#112  Sujet\n row3:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailModeles                                		#446  Modèle\n row4:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailBody,rows=10                           		#111  Message', NULL, NULL, NULL, NULL),
('base', 'baseUserParametersClicRdv', 'Paramètres utilisateur clicRDV', 'Paramètres utilisateur clicRDV', 'data_types', 'admin', 'post', '/user/ajax/userParametersClicRdv/', @catID, 'public', 'global:\n  formClass:\'ajaxForm\'\nstructure:\n row1:\n  col1: \n    head: \"Compte clicRDV\"\n    size: 3\n    bloc:\n      - clicRdvUserId\n      - clicRdvPassword\n      - clicRdvGroupId\n      - clicRdvCalId\n      - clicRdvConsultId,nolabel', NULL, NULL, NULL, NULL),
('base', 'baseUserParametersPassword', 'Paramètres utilisateur MedShakeEHR', 'Paramètres utilisateur MedShakeEHR', 'form_basic_types', 'admin', 'post', '/user/actions/userParametersPassword/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: \"Paramètres MedShakeEHR\"\r\n    size: 3\r\n    bloc:\r\n      - currentPassword,required                            		#6    Mot de passe actuel\n\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe', NULL, NULL, NULL, NULL);

SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='formATCD');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseATCD', 'Formulaire latéral écran patient principal (atcd)', 'formulaire en colonne latéral du dossier patient (atcd)', 'data_types', 'medical', 'post', NULL, @catID, 'public', 'structure:\r\n  row1: \r\n    col1: \r\n      size: 12 col-12 col-sm-4 col-lg-4\r\n      bloc: \r\n        - poids,plus={} #34   Poids\r\n    col2: \r\n      size: 12 col-12 col-sm-4 col-lg-4\r\n      bloc: \r\n       - taillePatient,plus={} #35   Taille\r\n    col3: \r\n      size: 12 col-12 col-sm-4 col-lg-4\r\n      bloc: \r\n       - imc,readonly,plus={} #43   IMC\r\n  row2: \r\n   col1: \r\n     size: 12\r\n     bloc: \r\n       - job,rows=1                                       		#19   Activité professionnelle\r\n       - allergies,rows=1                          		#66   Allergies\r\n       - toxiques,rows=1                                  		#42   Toxiques\r\n  row3: \r\n    col1: \r\n     size: 12\r\n     bloc: \r\n       - atcdMedicChir,rows=2                      		#41   Antécédents médico-chirurgicaux\r\n       - atcdFamiliaux,rows=2                      		#38   Antécédents familiaux', NULL, NULL, NULL, '$(document).ready(function() {\r\n\r\n  //calcul IMC\r\n  if ($(\'#id_imc_id\').length > 0) {\r\n\r\n    imc = imcCalc($(\'#id_poids_id\').val(), $(\'#id_taillePatient_id\').val());\r\n    if (imc > 0) {\r\n      $(\'#id_imc_id\').val(imc);\r\n    }\r\n\r\n    $(\"#patientLatCol\").on(\"keyup\", \"#id_poids_id , #id_taillePatient_id\", function() {\r\n      poids = $(\'#id_poids_id\').val();\r\n      taille = $(\'#id_taillePatient_id\').val();\r\n      imc = imcCalc(poids, taille);\r\n      $(\'#id_imc_id\').val(imc);\r\n      patientID = $(\'#identitePatient\').attr(\"data-patientID\");\r\n      setPeopleDataByTypeName(imc, patientID, \'imc\', \'#id_imc_id\', \'0\');\r\n\r\n    });\r\n  }\r\n\r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseATCD textarea\')); \r\n  \r\n});');

SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='formSynthese');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseSynthese', 'Synthèse patiente', 'formulaire fixe de synthèse', 'data_types', 'medical', 'post', NULL, @catID, 'public', 'structure:\r\n  row1:                              \r\n    col1:                             \r\n      size: 12\r\n      bloc:                          \r\n        - baseSynthese,rows=2                      		#718  Synthèse patient', NULL, NULL, NULL, '$(document).ready(function() {  \r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseSynthese textarea\'));\r\n });');

-- form_basic_types
INSERT IGNORE INTO `form_basic_types` (`name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `type`, `cat`, `fromID`, `creationDate`, `deleteByID`, `deleteDate`) VALUES
('currentPassword', 'Mot de passe actuel', 'Mot de passe actuel', 'Mot de passe actuel de l\'utilisateur', 'required', 'Le mot de passe actuel est manquant', 'password', NULL, 'base', '0', 1, '2019-01-01 00:00:00', '0', '2019-01-01 00:00:00'),
('date', NULL, 'Début de période', NULL, NULL, NULL, 'date', NULL, 'base', '0', 1, '2019-01-01 00:00:00', '0', '2019-01-01 00:00:00'),
('password', 'mot de passe', 'Mot de passe', 'mot de passe utilisateur', 'required', 'Le mot de passe est manquant', 'password', NULL, 'base', '0', 1, '2019-01-01 00:00:00', '0', '2019-01-01 00:00:00'),
('submit', NULL, 'Valider', 'bouton submit de validation', NULL, NULL, 'submit', NULL, 'base', '0', 1, '2019-01-01 00:00:00', '0', '2019-01-01 00:00:00'),
('username', 'identifiant', 'Identifiant', 'identifiant utilisateur', 'required', 'L\'identifiant utilisateur est manquant', 'text', NULL, 'base', '0', 1, '2019-01-01 00:00:00', '0', '2019-01-01 00:00:00'),
('verifPassword', 'confirmation du mot de passe', 'Confirmation du mot de passe', 'Confirmation du mot de passe utilisateur', 'required', 'La confirmation du mot de passe est manquante', 'password', NULL, 'base', '0', 1, '2019-01-01 00:00:00', '0', '2019-01-01 00:00:00');

-- people
INSERT IGNORE INTO `people` (`name`, `type`, `rank`, `module`, `pass`, `registerDate`, `fromID`, `lastLogIP`, `lastLogDate`, `lastLogFingerprint`) VALUES
('clicRDV', 'service', NULL, 'base', NULL, '2019-01-01 00:00:00', 1, NULL, '2019-01-01 00:00:00', NULL),
('medshake', 'service', NULL, 'base', NULL, '2019-01-01 00:00:00', 1, NULL, '2019-01-01 00:00:00', NULL);

-- prescriptions_cat
INSERT IGNORE INTO `prescriptions_cat` (`name`, `label`, `description`, `type`, `fromID`, `toID`, `creationDate`, `displayOrder`) VALUES
('prescriNonMedic', 'Prescriptions non médicamenteuses', 'prescriptions non médicamenteuses', 'nonlap', 1, NULL, '2019-01-01 00:00:00', '1'),
('prescripMedic', 'Prescriptions médicamenteuses', 'prescriptions médicamenteuses', 'nonlap', 1, NULL, '2019-01-01 00:00:00', '1');

-- prescriptions
SET @catID = (SELECT prescriptions_cat.id FROM prescriptions_cat WHERE prescriptions_cat.name='prescripMedic');
INSERT IGNORE INTO `prescriptions` (`cat`, `label`, `description`, `fromID`, `toID`, `creationDate`) VALUES
(@catID, 'Ligne vierge', NULL, 1, NULL, '2019-01-01 00:00:00');

SET @catID = (SELECT prescriptions_cat.id FROM prescriptions_cat WHERE prescriptions_cat.name='prescriNonMedic');
INSERT IGNORE INTO `prescriptions` (`cat`, `label`, `description`, `fromID`, `toID`, `creationDate`) VALUES
(@catID, 'Ligne vierge', NULL, 1, NULL, '2019-01-01 00:00:00');

-- system
INSERT IGNORE INTO `system` (`name`, `groupe`, `value`) VALUES
('base', 'module', 'v5.5.0'),
('state', 'system', 'normal');
