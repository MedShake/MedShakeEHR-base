SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `actes` (
  `id` smallint(4) UNSIGNED NOT NULL,
  `cat` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `label` varchar(250) NOT NULL,
  `shortLabel` varchar(255) DEFAULT NULL,
  `details` text NOT NULL,
  `flagImportant` tinyint(1) NOT NULL DEFAULT '0',
  `flagCmu` tinyint(1) NOT NULL DEFAULT '0',
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `toID` mediumint(6) NOT NULL DEFAULT '0',
  `creationDate` datetime NOT NULL DEFAULT '2018-01-01 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `actes_base` (
  `id` mediumint(6) UNSIGNED NOT NULL,
  `code` varchar(7) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `type` enum('NGAP','CCAM') NOT NULL DEFAULT 'CCAM',
  `tarifs1` float DEFAULT NULL,
  `tarifs2` float DEFAULT NULL,
  `fromID` mediumint(7) UNSIGNED NOT NULL DEFAULT '1',
  `creationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `actes_cat` (
  `id` smallint(5) NOT NULL,
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `module` varchar(20) NOT NULL DEFAULT 'base',
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL,
  `displayOrder` smallint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `agenda` (
  `id` int(12) UNSIGNED NOT NULL,
  `externid` int UNSIGNED DEFAULT NULL,
  `userid` smallint(5) UNSIGNED NOT NULL DEFAULT '3',
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `dateAdd` datetime DEFAULT NULL,
  `patientid` mediumint(6) UNSIGNED DEFAULT NULL,
  `fromID` mediumint(6) UNSIGNED DEFAULT NULL,
  `statut` enum('actif','deleted') DEFAULT 'actif',
  `absente` enum('non','oui') DEFAULT 'non',
  `motif` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `agenda_changelog` (
  `id` int(8) UNSIGNED NOT NULL,
  `eventID` int(12) UNSIGNED NOT NULL,
  `userID` smallint(5) UNSIGNED NOT NULL,
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `operation` enum('edit','move','delete','missing') NOT NULL,
  `olddata` mediumblob
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `data_cat` (
  `id` smallint(5) NOT NULL,
  `groupe` enum('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user','relation') NOT NULL DEFAULT 'admin',
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'base',
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `data_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `groupe` enum('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user','relation') NOT NULL DEFAULT 'admin',
  `name` varchar(60) NOT NULL,
  `placeholder` varchar(255) DEFAULT NULL,
  `label` varchar(60) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `validationRules` varchar(255) DEFAULT NULL,
  `validationErrorMsg` varchar(255) DEFAULT NULL,
  `formType` enum('','date','email','lcc','number','select','submit','tel','text','textarea','password','checkbox') NOT NULL DEFAULT '',
  `formValues` text,
  `module` varchar(20) NOT NULL DEFAULT 'base',
  `cat` smallint(5) UNSIGNED NOT NULL,
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL,
  `durationLife` int(9) UNSIGNED NOT NULL DEFAULT '86400',
  `displayOrder` tinyint(3) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `dicomTags` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `dicomTag` varchar(150) NOT NULL,
  `typeID` mediumint(5) UNSIGNED NOT NULL DEFAULT '0',
  `dicomCodeMeaning` varchar(255) DEFAULT NULL,
  `dicomUnits` varchar(255) DEFAULT NULL,
  `returnValue` enum('min','max','avg') NOT NULL DEFAULT 'avg',
  `roundDecimal` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `forms` (
  `id` smallint(4) UNSIGNED NOT NULL,
  `module` varchar(20) NOT NULL DEFAULT 'base',
  `internalName` varchar(60) NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` varchar(250) NOT NULL,
  `dataset` varchar(60) NOT NULL DEFAULT 'data_types',
  `groupe` enum('admin','medical','mail','doc','courrier','ordo','reglement','relation') NOT NULL DEFAULT 'medical',
  `formMethod` enum('post','get') NOT NULL DEFAULT 'post',
  `formAction` varchar(255) DEFAULT '/patient/actions/saveCsForm/',
  `cat` smallint(4) DEFAULT NULL,
  `type` enum('public','private') NOT NULL DEFAULT 'public',
  `yamlStructure` text,
  `yamlStructureDefaut` text,
  `printModel` varchar(25) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `forms_cat` (
  `id` smallint(5) NOT NULL,
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `form_basic_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `placeholder` varchar(255) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `validationRules` varchar(255) NOT NULL,
  `validationErrorMsg` varchar(255) NOT NULL,
  `formType` varchar(255) NOT NULL,
  `formValues` text NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
  `cat` smallint(5) UNSIGNED NOT NULL,
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL,
  `deleteByID` smallint(5) UNSIGNED NOT NULL,
  `deleteDate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `hprim` (
  `id` int(11) NOT NULL,
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `toID` smallint(5) UNSIGNED NOT NULL,
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
  `normalSupAutreU` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `inbox` (
  `id` int(7) UNSIGNED NOT NULL,
  `mailForUserID` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `txtFileName` varchar(30) NOT NULL,
  `mailHeaderInfos` blob,
  `txtDatetime` datetime NOT NULL,
  `txtNumOrdre` smallint(4) UNSIGNED NOT NULL,
  `hprimIdentite` varchar(250) NOT NULL,
  `hprimExpediteur` varchar(250) NOT NULL,
  `hprimCodePatient` varchar(250) NOT NULL,
  `hprimDateDossier` varchar(30) NOT NULL,
  `hprimAllSerialize` blob NOT NULL,
  `pjNombre` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `pjSerializeName` blob NOT NULL,
  `archived` enum('y','c','n') NOT NULL DEFAULT 'n',
  `assoToID` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `objets_data` (
  `id` int(11) UNSIGNED NOT NULL,
  `fromID` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `toID` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `typeID` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `parentTypeID` int(11) UNSIGNED DEFAULT '0',
  `instance` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `registerDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creationDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `updateDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `value` text,
  `outdated` enum('','y') NOT NULL DEFAULT '',
  `important` enum('n','y') DEFAULT 'n',
  `titre` varchar(255) NOT NULL DEFAULT '',
  `deleted` enum('','y') NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `people` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `type` enum('patient','pro','externe','service', 'deleted') NOT NULL DEFAULT 'patient',
  `rank` enum('','admin') DEFAULT NULL,
  `module` varchar(20) DEFAULT 'base',
  `pass` varbinary(60) DEFAULT NULL,
  `registerDate` datetime DEFAULT NULL,
  `fromID` smallint(5) DEFAULT NULL,
  `lastLogIP` varchar(50) DEFAULT NULL,
  `lastLogDate` datetime DEFAULT NULL,
  `lastLogFingerprint` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `prescriptions` (
  `id` smallint(5) NOT NULL,
  `cat` smallint(3) UNSIGNED NOT NULL DEFAULT '0',
  `label` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `toID` mediumint(7) UNSIGNED NOT NULL DEFAULT '0',
  `creationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `prescriptions_cat` (
  `id` smallint(5) NOT NULL,
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL,
  `displayOrder` tinyint(2) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `printed` (
  `id` int(11) UNSIGNED NOT NULL,
  `fromID` int(11) UNSIGNED NOT NULL,
  `toID` int(11) UNSIGNED NOT NULL,
  `type` enum('cr','ordo','courrier') NOT NULL DEFAULT 'cr',
  `objetID` int(11) UNSIGNED DEFAULT NULL,
  `creationDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `serializedTags` longblob,
  `outdated` enum('','y') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `system` (
  `id` smallint(4) UNSIGNED NOT NULL,
  `module` varchar(20) DEFAULT 'base',
  `version` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


ALTER TABLE `actes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `toID` (`toID`),
  ADD KEY `cat` (`cat`);

ALTER TABLE `actes_base`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

ALTER TABLE `actes_cat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `displayOrder` (`displayOrder`);

ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patientid` (`patientid`),
  ADD KEY `externid` (`externid`),
  ADD KEY `userid` (`userid`);

ALTER TABLE `agenda_changelog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eventID` (`eventID`);

ALTER TABLE `data_cat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `data_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `groupe` (`groupe`),
  ADD KEY `cat` (`cat`),
  ADD KEY `groupe_2` (`groupe`,`id`);

ALTER TABLE `dicomTags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dicomTag` (`dicomTag`,`typeID`),
  ADD KEY `dicomTag_2` (`dicomTag`),
  ADD KEY `typeID` (`typeID`);

ALTER TABLE `forms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `internalName` (`internalName`);

ALTER TABLE `forms_cat`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `form_basic_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `hprim`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `inbox`
  ADD PRIMARY KEY (`txtFileName`,`mailForUserID`) USING BTREE,
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `archived` (`archived`);

ALTER TABLE `objets_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `toID_typeID` (`toID`,`typeID`),
  ADD KEY `typeID` (`typeID`),
  ADD KEY `instance` (`instance`),
  ADD KEY `parentTypeID` (`parentTypeID`),
  ADD KEY `toID` (`toID`),
  ADD KEY `toID_2` (`toID`,`outdated`,`deleted`),
  ADD KEY `typeIDetValue` (`typeID`,`value`(15));

ALTER TABLE `people`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `type` (`type`);

ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `toID` (`toID`),
  ADD KEY `cat` (`cat`);

ALTER TABLE `prescriptions_cat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `displayOrder` (`displayOrder`);

ALTER TABLE `printed`
  ADD PRIMARY KEY (`id`),
  ADD KEY `examenID` (`objetID`);

ALTER TABLE `system`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `module` (`module`);


ALTER TABLE `actes`
  MODIFY `id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `actes_base`
  MODIFY `id` mediumint(6) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `actes_cat`
  MODIFY `id` smallint(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `agenda`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `agenda_changelog`
  MODIFY `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `data_cat`
  MODIFY `id` smallint(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `data_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `dicomTags`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `forms`
  MODIFY `id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `forms_cat`
  MODIFY `id` smallint(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `form_basic_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `hprim`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `inbox`
  MODIFY `id` int(7) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `objets_data`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `people`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `prescriptions`
  MODIFY `id` smallint(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `prescriptions_cat`
  MODIFY `id` smallint(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `printed`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `system`
  MODIFY `id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;




INSERT INTO `actes` (`id`, `cat`, `label`, `shortLabel`, `details`, `flagImportant`, `flagCmu`, `fromID`, `toID`, `creationDate`) VALUES
(1, 1, 'Consultation de base', 'Cs base', 'CS:\n  pourcents: 100\n  depassement: 15 \nMCS:\n  pourcents: 100\n  depassement: 0\nMPC:\n  pourcents: 100\n  depassement: 0', 1, 0, 1, 0, '2018-01-01 00:00:00'),
(2, 1, 'Consultation de base CMU', 'Cs CMU', 'CS:\n  pourcents: 100\n  depassement: 0 \nMCS:\n  pourcents: 100\n  depassement: 0\nMPC:\n  pourcents: 100\n  depassement: 0', 0, 1, 1, 0, '2018-01-01 00:00:00');

INSERT INTO `actes_base` (`id`, `code`, `label`, `type`, `tarifs1`, `tarifs2`, `fromID`, `creationDate`) VALUES
(1, 'CS', 'Consultation', 'NGAP', 23, 23, 1, '2018-01-01 00:00:00'),
(2, 'MPC', 'Majoration forfaitaire transitoire', 'NGAP', 2, 2, 1, '2018-01-01 00:00:00'),
(3, 'MCS', 'Majoration de Coordination Spécialiste', 'NGAP', 5, 5, 1, '2018-01-01 00:00:00'),
(4, 'C2', 'Consultation expert', 'NGAP', 46, 46, 1, '2018-01-01 00:00:00'),
(5, 'AG', 'Acte gratuit', 'NGAP', 0, 0, 1, '2018-01-01 00:00:00');

INSERT INTO `actes_cat` (`id`, `name`, `label`, `description`, `module`, `fromID`, `creationDate`, `displayOrder`) VALUES
(1, 'catConsult', 'Consultations', '', 'base', 1, '2018-01-01 00:00:00', 1);

INSERT INTO `data_cat` (`id`, `groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
(1, 'admin', 'identity', 'Etat civil', 'Datas relatives à l\'identité d\'une personne', 'base', 1, '2018-01-01 00:00:00'),
(2, 'admin', 'addressPerso', 'Adresse personnelle', 'datas de l\'adresse personnelle', 'base', 1, '2018-01-01 00:00:00'),
(3, 'admin', 'internet', 'Internet', 'Datas liées aux services internet', 'base', 1, '2018-01-01 00:00:00'),
(24, 'admin', 'contact', 'Contact', 'Moyens de contact', 'base', 1, '2018-01-01 00:00:00'),
(25, 'admin', 'activity', 'Activités', 'Activités professionnelles et de loisir', 'base', 1, '2018-01-01 00:00:00'),
(26, 'admin', 'divers', 'Divers', 'Divers', 'base', 1, '2018-01-01 00:00:00'),
(28, 'medical', 'dataCliniques', 'Données cliniques', 'Données cliniques', 'user', 1, '2018-01-01 00:00:00'),
(29, 'medical', 'atcd', 'Antécédents et synthèses', 'antécédents et synthèses', 'user', 1, '2018-01-01 00:00:00'),
(31, 'medical', 'dataBio', 'Données biologiques', 'Données biologiques habituelles', 'user', 1, '2018-01-01 00:00:00'),
(33, 'typecs', 'csBase', 'Consultations', 'consultations possibles', 'user', 1, '2018-01-01 00:00:00'),
(35, 'medical', 'dataCsBase', 'Données formulaire Cs', '', 'user', 1, '2018-01-01 00:00:00'),
(36, 'admin', 'numAdmin', 'Numéros administratifs', 'RPPS et compagnie', 'base', 1, '2018-01-01 00:00:00'),
(37, 'courrier', 'catModelesCertificats', 'Certificats', 'certificats divers', 'base', 1, '2018-01-01 00:00:00'),
(38, 'courrier', 'catModelesCourriers', 'Courriers', 'modèles de courrier libres', 'base', 1, '2018-01-01 00:00:00'),
(39, 'mail', 'mailForm', 'Data mail', 'data pour les mails expédiés', 'base', 1, '2018-01-01 00:00:00'),
(41, 'mail', 'porteursTech', 'Porteurs', 'porteurs pour les données enfants', 'base', 1, '2018-01-01 00:00:00'),
(42, 'doc', 'docForm', 'Data documents importés / créés', 'données pour le formulaire documents importés ou créés', 'base', 1, '2018-01-01 00:00:00'),
(43, 'doc', 'docPorteur', 'Porteur', 'porteur pour doc importés', 'base', 1, '2018-01-01 00:00:00'),
(44, 'ordo', 'poteursOrdo', 'Porteurs', 'porteurs ordonnance', 'base', 1, '2018-01-01 00:00:00'),
(45, 'reglement', 'porteursReglement', 'Porteurs', 'porteur d\'un règlement', 'base', 1, '2018-01-01 00:00:00'),
(46, 'reglement', 'reglementItems', 'Règlement', 'items d\'un réglement', 'base', 1, '2018-01-01 00:00:00'),
(47, 'admin', 'adressPro', 'Adresse professionnelle', 'Data de l\'adresse professionnelle', 'base', 1, '2018-01-01 00:00:00'),
(50, 'typecs', 'csAutres', 'Autres', 'autres', 'user', 1, '2018-01-01 00:00:00'),
(55, 'dicom', 'idDicom', 'ID Dicom', 'ID du dicom', 'base', 1, '2018-01-01 00:00:00'),
(56, 'user', 'catDicomUserParams', 'Dicom', 'Paramètres dicom', 'base', 1, '2018-01-01 00:00:00'),
(57, 'typecs', 'declencheur', 'Déclencheur', '', 'user', 1, '2018-01-01 00:00:00'),
(58, 'courrier', 'catModelesMailsToPatient', 'Mails aux patients', 'modèles de mail', 'base', 1, '2018-01-01 00:00:00'),
(59, 'courrier', 'catModelesMailsToApicrypt', 'Mails aux praticiens', 'modèles de mails pour les praticien (apicrypt)', 'base', 1, '2018-01-01 00:00:00'),
(61, 'mail', 'dataSms', 'Data sms', 'data pour les sms envoyés', 'user', 1, '2018-01-01 00:00:00'),
(63, 'relation', 'relationRelations', 'Relations', 'types permettant de définir une relation', 'user', 1, '2018-01-01 00:00:00'),
(64, 'user', 'catParamsUsersAdmin', 'Services du logiciel', 'paramètres pour activation service par utilisateur', 'user', 1, '2018-01-01 00:00:00'),
(65, 'admin', 'catMarqueursAdminDossiers', 'Marqueurs', 'marqueurs dossiers', 'user', 1, '2018-01-01 00:00:00'),
(66, 'user', 'clicRDV', 'clicRDV', 'Paramètres pour clicRDV', 'base', 1, '2018-01-01 00:00:00');

INSERT INTO `data_types` (`id`, `groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
(0, 'admin', 'submit', '', '', '', '', '', 'submit', '', 'base', 0, 1, '2018-01-01 00:00:00', 3600, 1),
(1, 'admin', 'birthname', 'nom reçu à la naissance', 'Nom de naissance', 'Nom reçu à la naissance', 'identite', 'Le nom de naissance est indispensable et ne doit pas contenir de caractères interdits', 'text', '', 'base', 1, 1, '2018-01-01 00:00:00', 3600, 1),
(2, 'admin', 'lastname', 'nom utilisé au quotidien', 'Nom d\'usage', 'Nom utilisé au quotidien', 'identite', 'Le nom d\'usage ne doit pas contenir de caractères interdits', 'text', '', 'base', 1, 1, '2018-01-01 00:00:00', 3600, 1),
(3, 'admin', 'firstname', 'prénom', 'Prénom', 'Prénom figurant sur la pièce d\'identité', 'identite', 'Le prénom est indispensable et ne doit pas contenir de caractères interdits', 'text', '', 'base', 1, 1, '2018-01-01 00:00:00', 3600, 1),
(4, 'admin', 'personalEmail', 'email@domain.ext', 'Email personnelle', 'Adresse email personnelle', 'valid_email', 'L\'adresse email n\'est pas correcte. Elle doit être de la forme email@domain.net', 'email', '', 'base', 24, 1, '2018-01-01 00:00:00', 3600, 1),
(5, 'admin', 'profesionnalEmail', 'email@domain.ext', 'Email professionnelle', 'Adresse email professionnelle', 'valid_email', 'L\'adresse email n\'est pas correcte. Elle doit être de la forme email@domain.net', 'email', '', 'base', 24, 1, '2018-01-01 00:00:00', 3600, 1),
(6, 'admin', 'twitterAccount', '', 'Twitter', 'Compte twitter', 'twitterAccount', '', 'text', '', 'base', 3, 1, '2018-01-01 00:00:00', 3600, 1),
(7, 'admin', 'mobilePhone', '06 xx xx xx xx', 'Téléphone mobile', 'Numéro de téléphone commençant par 06 ou 07', 'mobilphone', 'Le numéro de téléphone mobile est incorrect', 'tel', '', 'base', 24, 1, '2018-01-01 00:00:00', 3600, 1),
(8, 'admin', 'birthdate', 'dd/mm/YYYY', 'Date de naissance', 'Date de naissance au format dd/mm/YYYY', 'validedate,\'d/m/Y\'', 'La date de naissance indiquée n\'est pas valide', 'date', '', 'base', 1, 1, '2018-01-01 00:00:00', 3600, 1),
(9, 'admin', 'streetNumber', 'numéro dans la rue', 'Numéro', 'Adresse perso : numéro dans la rue', '', 'Le numéro de rue est incorrect', 'text', '', 'base', 2, 1, '2018-01-01 00:00:00', 3600, 1),
(10, 'admin', 'homePhone', '0x xx xx xx xx', 'Téléphone domicile', 'Téléphone du domicile de la forme 0x xx xx xx xx', 'phone', 'Le numéro de téléphone du domicile n\'est pas correct', 'tel', '', 'base', 24, 1, '2018-01-01 00:00:00', 3600, 1),
(11, 'admin', 'street', 'rue', 'Rue', 'Adresse perso : rue', '', '', 'text', '', 'base', 2, 1, '2018-01-01 00:00:00', 3600, 1),
(12, 'admin', 'city', 'ville', 'Ville', 'Adresse perso : ville', '', '', 'text', '', 'base', 2, 1, '2018-01-01 00:00:00', 3600, 1),
(13, 'admin', 'postalCodePerso', 'code postal', 'Code postal', 'Adresse perso : code postal', '', 'Le code postal n\'est pas correct', 'text', '', 'base', 2, 1, '2018-01-01 00:00:00', 3600, 1),
(14, 'admin', 'administrativeGenderCode', '', 'Sexe', 'Sexe', '', '', 'select', 'F: \'Femme\'\nM: \'Homme\'\nU: \'Inconnu\'', 'base', 1, 1, '2018-01-01 00:00:00', 3600, 1),
(15, 'admin', 'website', '', 'Site web', 'Site web', 'url', '', 'text', '', 'base', 3, 1, '2018-01-01 00:00:00', 3600, 1),
(19, 'admin', 'job', 'activité professionnelle', 'Activité professionnelle', 'Activité professionnelle', '', 'L\'activité professionnelle n\'est pas correcte', 'text', '', 'base', 25, 1, '2018-01-01 00:00:00', 3600, 1),
(20, 'admin', 'sport', 'sport exercé', 'Sport', 'Sport exercé', '', 'Le sport indiqué n\'est pas correct', 'text', '', 'base', 25, 1, '2018-01-01 00:00:00', 3600, 1),
(21, 'admin', 'notes', 'notes', 'Notes', 'Zone de notes', '', '', 'textarea', '', 'base', 26, 1, '2018-01-01 00:00:00', 3600, 1),
(22, 'admin', 'othersfirstname', 'liste des prénoms secondaires', 'Autres prénoms', 'Les autres prénoms d\'une personne', '', '', 'text', '', 'base', 1, 1, '2018-01-01 00:00:00', 3600, 1),
(34, 'medical', 'poids', 'kg', 'Poids', 'poids du patient', '', '', 'text', '', 'base', 28, 1, '2018-01-01 00:00:00', 3600, 1),
(35, 'medical', 'taillePatient', 'cm', 'Taille', 'taille du patient', '', '', 'text', '', 'base', 28, 1, '2018-01-01 00:00:00', 3600, 1),
(38, 'medical', 'atcdFamiliaux', 'Antécédents familiaux', 'Antécédents familiaux', 'Antécédents familiaux', '', '', 'textarea', '', 'base', 29, 1, '2018-01-01 00:00:00', 3600, 1),
(41, 'medical', 'atcdMedicChir', 'Antécédents médico-chirugicaux personnels', 'Antécédents médico-chirugicaux', 'Antécédents médico-chirugicaux personnels', '', '', 'textarea', '', 'base', 29, 1, '2018-01-01 00:00:00', 3600, 1),
(42, 'medical', 'toxiques', 'tabac et drogues', 'Toxiques', 'habitudes de consommation', '', '', 'text', '', 'base', 29, 1, '2018-01-01 00:00:00', 3600, 1),
(43, 'medical', 'imc', 'imc', 'IMC', 'IMC (autocalcule)', '', '', 'text', '', 'base', 28, 1, '2018-01-01 00:00:00', 3600, 1),
(51, 'admin', 'titre', 'Dr, Pr ...', 'Titre', 'Titre du pro de santé', '', '', 'text', '', 'base', 1, 1, '2018-01-01 00:00:00', 3600, 1),
(53, 'admin', 'codePostalPro', 'code postal', 'Code postal', 'Adresse pro : code postal', 'alpha_space', 'Le code postal n\'est pas conforme', 'text', '', 'base', 47, 1, '2018-01-01 00:00:00', 3600, 1),
(54, 'admin', 'numAdressePro', 'n°', 'Numéro', 'Adresse pro : numéro dans la rue', 'alpha_space', 'Le numero n\'est pas conforme', 'text', '', 'base', 47, 1, '2018-01-01 00:00:00', 3600, 1),
(55, 'admin', 'rueAdressePro', 'rue', 'Rue', 'Adresse pro : rue', '', '', 'text', '', 'base', 47, 1, '2018-01-01 00:00:00', 3600, 1),
(56, 'admin', 'villeAdressePro', 'ville', 'Ville', 'Adresse pro : ville', '', '', 'text', '', 'base', 47, 1, '2018-01-01 00:00:00', 3600, 1),
(57, 'admin', 'telPro', 'téléphone professionnel', 'Téléphone professionnel', 'Téléphone pro.', 'phone', '', 'tel', '', 'base', 24, 1, '2018-01-01 00:00:00', 3600, 1),
(58, 'admin', 'faxPro', 'fax professionel', 'Fax professionnel', 'FAx pro', 'phone', '', 'tel', '', 'base', 24, 1, '2018-01-01 00:00:00', 3600, 1),
(59, 'admin', 'emailApicrypt', 'adresse mail apicript', 'Email apicrypt', 'Email apicrypt', 'valid_email', '', 'email', '', 'base', 24, 1, '2018-01-01 00:00:00', 3600, 1),
(66, 'medical', 'allergies', 'allergies et intolérances', 'Allergies', 'Allergies et intolérances du patient', '', '', 'textarea', '', 'base', 29, 1, '2018-01-01 00:00:00', 3600, 1),
(103, 'admin', 'rpps', 'rpps', 'RPPS', 'rpps', 'numeric', '', 'number', '', 'base', 36, 1, '2018-01-01 00:00:00', 3600, 1),
(104, 'admin', 'adeli', 'adeli', 'Adeli', 'n° adeli', '', '', 'text', '', 'base', 36, 1, '2018-01-01 00:00:00', 3600, 1),
(107, 'courrier', 'modeleCourrierVierge', '', 'Courrier', 'modèle de courrier vierge', '', '', '', 'courrier-courrierVierge', 'base', 38, 1, '2018-01-01 00:00:00', 3600, 0),
(108, 'courrier', 'modeleCertifVierge', '', 'Certificat', 'modèle de certificat vierge', '', '', '', 'certif-certificatVierge', 'base', 37, 1, '2018-01-01 00:00:00', 3600, 0),
(109, 'mail', 'mailFrom', 'email@domain.net', 'De', 'mail from', '', '', 'email', '', 'base', 39, 1, '2018-01-01 00:00:00', 1576800000, 1),
(110, 'mail', 'mailTo', '', 'A', 'mail to', '', '', 'email', '', 'base', 39, 1, '2018-01-01 00:00:00', 1576800000, 1),
(111, 'mail', 'mailBody', 'texte du message', 'Message', 'texte du message', '', '', 'textarea', '', 'base', 39, 1, '2018-01-01 00:00:00', 1576800000, 1),
(112, 'mail', 'mailSujet', 'sujet du mail', 'Sujet', 'sujet du mail', '', '', 'text', '', 'base', 39, 1, '2018-01-01 00:00:00', 1576800000, 1),
(177, 'mail', 'mailPorteur', '', 'Mail', 'porteur pour les mails', '', '', '', '', 'base', 41, 1, '2018-01-01 00:00:00', 1576800000, 1),
(178, 'mail', 'mailPJ1', '', 'ID pièce jointe', 'id de la pièce jointe au mail', '', '', '', '', 'base', 39, 1, '2018-01-01 00:00:00', 1576800000, 1),
(179, 'mail', 'mailToApicrypt', '', 'A (correspondant apicrypt)', 'Champ pour les correspondants apicrypt', '', '', 'email', '', 'base', 39, 1, '2018-01-01 00:00:00', 1576800000, 1),
(180, 'admin', 'nss', '', 'Numéro de sécu', 'numéro de sécurité sociale', '', '', 'text', '', 'base', 36, 1, '2018-01-01 00:00:00', 3600, 1),
(181, 'doc', 'docTitle', '', 'Titre', 'titre du document', '', '', '', '', 'base', 42, 1, '2018-01-01 00:00:00', 3600, 1),
(182, 'doc', 'docOrigine', '', 'Origine du document', 'origine du document : interne ou externe(null)', '', '', 'text', '', 'base', 42, 1, '2018-01-01 00:00:00', 3600, 1),
(183, 'doc', 'docType', '', 'Type du document', 'type du document importé', '', '', 'text', '', 'base', 42, 1, '2018-01-01 00:00:00', 3600, 1),
(184, 'doc', 'docPorteur', '', 'Document', 'porteur pour nouveau document importé', '', '', '', '', 'base', 43, 1, '2018-01-01 00:00:00', 1576800000, 1),
(185, 'doc', 'docOriginalName', '', 'Nom original', 'nom original du document', '', '', '', '', 'base', 42, 1, '2018-01-01 00:00:00', 3600, 1),
(186, 'ordo', 'ordoPorteur', '', 'Ordonnance', 'porteur ordonnance', '', '', '', '', 'base', 44, 1, '2018-01-01 00:00:00', 3600, 1),
(189, 'ordo', 'ordoTypeImpression', '', 'Type ordonnance impression', 'type d\'ordonnance pour impression', '', '', '', '', 'base', 44, 1, '2018-01-01 00:00:00', 3600, 1),
(190, 'ordo', 'ordoLigneOrdo', '', 'Ligne d\'ordonnance', 'porteur pour une ligne d\'ordo', '', '', '', '', 'base', 44, 1, '2018-01-01 00:00:00', 3600, 1),
(191, 'ordo', 'ordoLigneOrdoALDouPas', '', 'Ligne d\'ordonnance : ald', '1 si ald', '', '', '', '', 'base', 44, 1, '2018-01-01 00:00:00', 3600, 1),
(192, 'reglement', 'reglePorteur', '', 'Règlement', '', '', '', '', '', 'base', 45, 1, '2018-01-01 00:00:00', 1576800000, 1),
(193, 'reglement', 'regleCheque', '', 'Chèque', 'montant versé en chèque', '', '', 'text', '', 'base', 46, 1, '2018-01-01 00:00:00', 1576800000, 1),
(194, 'reglement', 'regleCB', '', 'CB', 'montant versé en CB', '', '', 'text', '', 'base', 46, 1, '2018-01-01 00:00:00', 1576800000, 1),
(195, 'reglement', 'regleEspeces', '', 'Espèces', 'montant versé en espèce', '', '', 'text', '', 'base', 46, 1, '2018-01-01 00:00:00', 1576800000, 1),
(196, 'reglement', 'regleFacture', '', 'Facturé', 'facturé ce jour', '', '', 'text', '0', 'base', 46, 1, '2018-01-01 00:00:00', 1576800000, 1),
(197, 'reglement', 'regleSituationPatient', '', 'Situation du patient', 'situation du patient : cmu / tp / tout venant', '', '', 'select', '\'G\' : \'Tout venant\'\n\'CMU\' : \'CMU\'\n\'TP\' : \'Tiers payant\'\n\'TP ALD\' : \'Tiers payant + ALD\'', 'base', 46, 1, '2018-01-01 00:00:00', 1576800000, 1),
(198, 'reglement', 'regleTarifCejour', '', 'Tarif SS', 'tarif SS appliqué ce jour', '', '', 'text', '', 'base', 46, 1, '2018-01-01 00:00:00', 1576800000, 1),
(199, 'reglement', 'regleDepaCejour', '', 'Dépassement', 'dépassement pratiqué ce jour', '', '', 'text', '', 'base', 46, 1, '2018-01-01 00:00:00', 1576800000, 1),
(200, 'reglement', 'regleTiersPayeur', '', 'Tiers', 'part du tiers', '', '', 'text', '', 'base', 46, 1, '2018-01-01 00:00:00', 1576800000, 1),
(205, 'reglement', 'regleIdentiteCheque', 'si différent patient', 'Identité payeur', 'identité du payeur si différente', '', '', 'text', '', 'base', 46, 1, '2018-01-01 00:00:00', 1576800000, 1),
(247, 'admin', 'mobilePhonePro', '06 xx xx xx xx', 'Téléphone mobile pro.', 'Numéro de téléphone commençant par 06 ou 07', 'mobilphone', 'Le numéro de téléphone mobile pro est incorrect', 'tel', '', 'base', 24, 1, '2018-01-01 00:00:00', 3600, 1),
(248, 'admin', 'telPro2', 'téléphone professionnel 2', 'Téléphone professionnel 2', 'Téléphone pro. 2', 'phone', '', 'tel', '', 'base', 24, 1, '2018-01-01 00:00:00', 3600, 1),
(249, 'admin', 'serviceAdressePro', 'service', 'Service', 'Adresse pro : service', '', '', 'text', '', 'base', 47, 1, '2018-01-01 00:00:00', 3600, 1),
(250, 'admin', 'etablissementAdressePro', 'établissement', 'Établissement', 'Adresse pro : établissement', '', '', 'text', '', 'base', 47, 1, '2018-01-01 00:00:00', 3600, 1),
(251, 'typecs', 'csImport', '', 'Import', 'support parent pour import', '', '', '', 'baseImportExternal', 'base', 50, 1, '2018-01-01 00:00:00', 84600, 1),
(252, 'medical', 'dataImport', '', 'Import', 'support pour consultations importées', '', '', 'textarea', '', 'base', 29, 1, '2018-01-01 00:00:00', 84600, 1),
(433, 'dicom', 'dicomStudyID', '', 'StudyID', '', '', '', 'text', '', 'base', 55, 1, '2018-01-01 00:00:00', 3600, 1),
(434, 'dicom', 'dicomSerieID', '', 'SerieID', '', '', '', 'text', '', 'base', 55, 1, '2018-01-01 00:00:00', 3600, 1),
(435, 'dicom', 'dicomInstanceID', '', 'InstanceID', '', '', '', 'text', '', 'base', 55, 1, '2018-01-01 00:00:00', 3600, 1),
(436, 'user', 'dicomAutoSendPatient2Echo', '', 'dicomAutoSendPatient2Echo', 'Pousser le dossier patient à l\'ouverture dans le serveur DICOM', '', '', 'checkbox', 'false', 'base', 56, 1, '2018-01-01 00:00:00', 3600, 1),
(443, 'admin', 'notesPro', 'notes pros', 'Notes pros', 'Zone de notes pros', '', '', 'textarea', '', 'base', 26, 1, '2018-01-01 00:00:00', 3600, 1),
(446, 'mail', 'mailModeles', '', 'Modèle', 'liste des modèles', '', '', 'select', '', 'base', 39, 1, '2018-01-01 00:00:00', 1576800000, 1),
(477, 'admin', 'nReseau', '', 'Numéro de réseau', 'numéro de réseau (dépistage)', '', '', 'text', '', 'base', 36, 1, '2018-01-01 00:00:00', 3600, 1),
(479, 'courrier', 'mmDefautApi', '', 'Défaut', 'modèle mail par défaut', 'base', '', '', 'Cher confrère,\n\nVeuillez trouver en pièce jointe un document concernant notre patient commun.\nVous souhaitant bonne réception.\n\nBien confraternellement\n\n', 'base', 59, 1, '2018-01-01 00:00:00', 3600, 0),
(481, 'mail', 'mailToEcofaxNumber', '', 'Numéro de fax du destinataire', 'Numéro du destinataire du fax (ecofax OVH)', '', '', 'text', '', 'base', 39, 1, '2018-01-01 00:00:00', 1576800000, 1),
(484, 'mail', 'mailToEcofaxName', '', 'Destinataire du fax', 'Destinataire du fax (ecofax OVH)', '', '', 'text', '', 'base', 39, 1, '2018-01-01 00:00:00', 1576800000, 1),
(486, 'mail', 'smsPorteur', '', 'Mail', 'porteur pour les sms', '', '', '', '', 'base', 41, 1, '2018-01-01 00:00:00', 1576800000, 1),
(487, 'mail', 'smsId', '', 'smsId', 'id du sms', '', '', '', '', 'base', 61, 1, '2018-01-01 00:00:00', 1576800000, 1),
(488, 'relation', 'relationID', '', 'Porteur de relation', 'porteur de relation entre patients ou entre patients et praticiens', '', '', 'number', '', 'base', 63, 1, '2018-01-01 00:00:00', 1576800000, 1),
(489, 'relation', 'relationPatientPatient', '', 'Relation patient patient', 'relation patient patient', '', '', 'select', '\'conjoint\': \'conjoint\'\n\'enfant\': \'parent\'\n\'parent\': \'enfant\'\n\'grand parent\': \'petit enfant\'\n\'petit enfant\': \'grand parent\'\n\'sœur / frère\': \'sœur / frère\' \n\'tante / oncle\': \'nièce / neveu\' \n\'cousin\': \'cousin\'', 'base', 63, 1, '2018-01-01 00:00:00', 1576800000, 1),
(490, 'relation', 'relationPatientPraticien', '', 'Relation patient praticien', 'relation patient  praticien', '', '', 'select', '\'MT\': \'Médecin traitant\'\n\'MS\': \'Médecin spécialiste\'\n\'Autre\': \'Autre correspondant\'', 'base', 63, 1, '2018-01-01 00:00:00', 1576800000, 1),
(492, 'user', 'administratifPeutAvoirPrescriptionsTypes', '', 'administratifPeutAvoirPrescriptionsTypes', 'permet à l\'utilisateur sélectionné d\'avoir des prescriptions types', '', '', 'checkbox', 'false', 'base', 64, 1, '2018-01-01 00:00:00', 3600, 1),
(493, 'user', 'administratifPeutAvoirFacturesTypes', '', 'administratifPeutAvoirFacturesTypes', 'permet à l\'utilisateur sélectionné d\'avoir des factures types', '', '', 'checkbox', 'false', 'base', 64, 1, '2018-01-01 00:00:00', 3600, 1),
(494, 'admin', 'administratifMarqueurSuppression', '', 'Dossier supprimé', 'marqueur pour la suppression d\'un dossier', '', '', 'text', '', 'base', 65, 1, '2018-01-01 00:00:00', 3600, 1),
(495, 'mail', 'mailTrackingID', '', 'TrackingID', 'num de tracking du mail dans le service externe', '', '', '', '', 'base', 39, 1, '2018-01-01 00:00:00', 1576800000, 1),
(496, 'user', 'administratifPeutAvoirAgenda', '', 'administratifPeutAvoirAgenda', 'permet à l\'utilisateur sélectionné d\'avoir son agenda', '', '', 'checkbox', 'false', 'base', 64, 1, '2018-01-01 00:00:00', 3600, 1),
(497, 'user', 'agendaNumberForPatientsOfTheDay', '', 'agendaNumberForPatientsOfTheDay', 'permet d\'indiquer le n° d\'agenda à utiliser pour la liste patients du jour pour cet utilisateur', '', '', 'number', '1', 'base', 64, 1, '2018-01-01 00:00:00', 3600, 1),
(501, 'user', 'phonecaptureFingerprint', 'indiquer une chaine aléatoire de caractères', 'phonecaptureFingerprint', 'clef utilisateur pour l\'identification des périphériques phonecapture', NULL, NULL, 'text', NULL, 'base', 56, 1, '2018-01-01 00:00:00', 3600, 0),
(505, 'medical', 'examenDuJour', 'examen du jour', 'Examen du jour', 'examen du jour', '', '', 'textarea', '', 'base', 35, 1, '2018-01-01 00:00:00', 3600, 1),
(506, 'medical', 'baseSynthese', 'synthèse sur le patient', 'Synthèse patient', 'Synthèse sur le patient', '', '', 'textarea', '', 'base', 29, 1, '2018-01-01 00:00:00', 3600, 1),
(507, 'user', 'clicRdvUserId', 'identifiant', 'identifiant', 'email@address.com', '', '', 'text', '', 'base', 66, 1, '2018-01-01 00:00:00', 3600, 1),
(508, 'user', 'clicRdvPassword', 'Mot de passe', 'Mot de passe', 'Mot de passe (chiffré)', '', '', 'password', '', 'base', 66, 1, '2018-01-01 00:00:00', 3600, 2),
(509, 'user', 'clicRdvGroupId', 'Groupe', 'Groupe', 'Groupe Sélectionné', '', '', 'select', '', 'base', 66, 1, '2018-01-01 00:00:00', 3600, 3),
(510, 'user', 'clicRdvCalId', 'Agenda', 'Agenda', 'Agenda sélectionné', '', '', 'select', '', 'base', 66, 1, '2018-01-01 00:00:00', 3600, 4),
(511, 'user', 'clicRdvConsultId', 'Consultations', 'Consultations', 'Correspondance entre consultations', '', '', 'select', '', 'base', 66, 1, '2018-01-01 00:00:00', 3600, 5),
(512, 'admin', 'clicRdvPatientId', 'ID patient', 'ID patient', 'ID patient', '', '', 'text', '', 'base', 26, 1, '2018-01-01 00:00:00', 3600, 1),
(513, 'relation', 'relationExternePatient', '', 'Relation externe patient', 'relation externe patient', '', '', 'number', '', 'base', 63, 1, '2018-01-01 00:00:00', 1576800000, 1);

INSERT INTO `forms` (`id`, `module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
(1, 'base', 'baseNewPatient', 'Formulaire nouveau patient', 'formulaire d\'enregistrement d\'un nouveau patient', 'data_types', 'admin', 'post', '/patient/register/', 1, 'public', 'structure:\r\n  row1:                              \r\n    col1:                              \r\n      head: \'Etat civil\'             \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - birthname,required,autocomplete,data-acTypeID=2:1 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=2:1  		#2    Nom d usage\n        - firstname,required,autocomplete,data-acTypeID=3:22:230:235:241 		#3    Prénom\n        - birthdate                                		#8    Date de naissance\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - personalEmail                            		#4    Email personnelle\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\n    col3:\r\n      head: \'Adresse personnelle\'\r\n      size: 4\r\n      bloc: \r\n        - streetNumber                             		#9    Numéro\n        - street,autocomplete,data-acTypeID=11:55  		#11   Rue\n        - postalCodePerso                          		#13   Code postal\n        - city,autocomplete,data-acTypeID=12:56    		#12   Ville\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,rows=5                             		#21   Notes', 'structure:\r\n  row1:                              \r\n    col1:                              \r\n      head: \'Etat civil\'             \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - birthname,required,autocomplete,data-acTypeID=2:1 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=2:1  		#2    Nom d usage\n        - firstname,required,autocomplete,data-acTypeID=3:22:230:235:241 		#3    Prénom\n        - birthdate                                		#8    Date de naissance\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - personalEmail                            		#4    Email personnelle\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\n    col3:\r\n      head: \'Adresse personnelle\'\r\n      size: 4\r\n      bloc: \r\n        - streetNumber                             		#9    Numéro\n        - street,autocomplete,data-acTypeID=11:55  		#11   Rue\n        - postalCodePerso                          		#13   Code postal\n        - city,autocomplete,data-acTypeID=12:56    		#12   Ville\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,rows=5                             		#21   Notes', ''),
(2, 'base', 'baseListingPatients', 'Listing des patients', 'description des colonnes affichées en résultat d\'une recherche patient', 'data_types', 'admin', 'post', '', 2, 'public', 'col1:\r\n    head: "Nom de naissance"\r\n    bloc:\r\n        - birthname,text-uppercase,gras            		#1    Nom de naissance\ncol2:\r\n    head: "Nom d\'usage"\r\n    bloc:\r\n        - lastname,text-uppercase,gras             		#2    Nom d usage\n\r\ncol3:\r\n    head: "Prénom"\r\n    bloc:\r\n        - firstname,text-capitalize,gras           		#3    Prénom\ncol4:\r\n    head: "Date de naissance" \r\n    bloc: \r\n        - birthdate                                		#8    Date de naissance\ncol5:\r\n    head: "Tel" \r\n    blocseparator: " - "\r\n    bloc: \r\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\ncol6:\r\n    head: "Email"\r\n    bloc:\r\n        - personalEmail                            		#4    Email personnelle\ncol7:\r\n    head: "Ville"\r\n    bloc:\r\n        - city,text-uppercase                      		#12   Ville', 'col1:\r\n    head: "Nom de naissance"\r\n    bloc:\r\n        - birthname,text-uppercase,gras            		#1    Nom de naissance\ncol2:\r\n    head: "Nom d\'usage"\r\n    bloc:\r\n        - lastname,text-uppercase,gras             		#2    Nom d usage\n\r\ncol3:\r\n    head: "Prénom"\r\n    bloc:\r\n        - firstname,text-capitalize,gras           		#3    Prénom\ncol4:\r\n    head: "Date de naissance" \r\n    bloc: \r\n        - birthdate                                		#8    Date de naissance\ncol5:\r\n    head: "Tel" \r\n    blocseparator: " - "\r\n    bloc: \r\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\ncol6:\r\n    head: "Email"\r\n    bloc:\r\n        - personalEmail                            		#4    Email personnelle\ncol7:\r\n    head: "Ville"\r\n    bloc:\r\n        - city,text-uppercase                      		#12   Ville', ''),
(3, 'base', 'baseLogin', 'Login', 'formulaire login utilisateur', 'form_basic_types', 'admin', 'post', '/login/logInDo/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Identification utilisateur"\r\n    size: 3\r\n    bloc: \r\n      - username,required                            		#1    Identifiant\n      - password,required                          		#2    Mot de passe\n      - submit                                     		#3    Valider', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Identification utilisateur"\r\n    size: 3\r\n    bloc: \r\n      - username,required                            		#1    Identifiant\n      - password,required                          		#2    Mot de passe\n      - submit                                     		#3    Valider', ''),
(7, 'base', 'baseNewPro', 'Formulaire nouveau pro', 'formulaire d\'enregistrement d\'un nouveau pro', 'data_types', 'admin', 'post', '/pro/register/', 1, 'public', 'structure:\r\n  row1:                              \r\n    col1:                            \r\n      head: \'Etat civil\'            \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - job,autocomplete                         		#19   Activité professionnelle\n        - titre,autocomplete                       		#51   Titre\n        - birthname,autocomplete,data-acTypeID=3:22:230:235:241 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=2:1 		#2    Nom d usage\n        - firstname,autocomplete,data-acTypeID=3:22:230:235:241 		#3    Prénom\n\r\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - profesionnalEmail                        		#5    Email professionnelle\n        - personalEmail                            		#4    Email personnelle\n        - telPro                                   		#57   Téléphone professionnel\n        - telPro2                                  		#248  Téléphone professionnel 2\n        - mobilePhonePro                           		#247  Téléphone mobile pro.\n        - faxPro                                   		#58   Fax professionnel\n    col3:\r\n      head: \'Adresse professionnelle\'\r\n      size: 4\r\n      bloc: \r\n        - numAdressePro                            		#54   Numéro\n        - rueAdressePro,autocomplete,data-acTypeID=11:55 		#55   Rue\n        - codePostalPro                            		#53   Code postal\n        - villeAdressePro,autocomplete,data-acTypeID=12:56 		#56   Ville\n        - serviceAdressePro,autocomplete           		#249  Service\n        - etablissementAdressePro,autocomplete     		#250  Établissement\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notesPro,rows=5                          		#443  Notes pros\n\r\n  row3:\r\n    col1:\r\n      size: 4\r\n      bloc:\r\n        - rpps                                     		#103  RPPS\n    col2:\r\n      size: 4\r\n      bloc:\r\n        - adeli                                    		#104  Adeli\n    col3:\r\n      size: 4\r\n      bloc:\r\n        - nReseau                                  		#477  Numéro de réseau', 'structure:\r\n  row1:                              \r\n    col1:                            \r\n      head: \'Etat civil\'            \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - job,autocomplete                         		#19   Activité professionnelle\n        - titre,autocomplete                       		#51   Titre\n        - birthname,autocomplete,data-acTypeID=3:22:230:235:241 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=2:1 		#2    Nom d usage\n        - firstname,autocomplete,data-acTypeID=3:22:230:235:241 		#3    Prénom\n\r\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - profesionnalEmail                        		#5    Email professionnelle\n        - personalEmail                            		#4    Email personnelle\n        - telPro                                   		#57   Téléphone professionnel\n        - telPro2                                  		#248  Téléphone professionnel 2\n        - mobilePhonePro                           		#247  Téléphone mobile pro.\n        - faxPro                                   		#58   Fax professionnel\n    col3:\r\n      head: \'Adresse professionnelle\'\r\n      size: 4\r\n      bloc: \r\n        - numAdressePro                            		#54   Numéro\n        - rueAdressePro,autocomplete,data-acTypeID=11:55 		#55   Rue\n        - codePostalPro                            		#53   Code postal\n        - villeAdressePro,autocomplete,data-acTypeID=12:56 		#56   Ville\n        - serviceAdressePro,autocomplete           		#249  Service\n        - etablissementAdressePro,autocomplete     		#250  Établissement\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notesPro,rows=5                          		#443  Notes pros\n\r\n  row3:\r\n    col1:\r\n      size: 4\r\n      bloc:\r\n        - rpps                                     		#103  RPPS\n    col2:\r\n      size: 4\r\n      bloc:\r\n        - adeli                                    		#104  Adeli\n    col3:\r\n      size: 4\r\n      bloc:\r\n        - nReseau                                  		#477  Numéro de réseau', ''),
(8, 'base', 'baseListingPro', 'Listing des praticiens', 'description des colonnes affichées en résultat d\'une recherche praticien', 'data_types', 'admin', 'post', '', 2, 'public', 'col1:\r\n    head: "Identité"\r\n    blocseparator: " "\r\n    bloc:\r\n        - titre,gras                               		#51   Titre\n        - lastname,text-uppercase,gras             		#2    Nom d usage\n        - birthname,text-uppercase,gras            		#1    Nom de naissance\n        - firstname,text-capitalize,gras           		#3    Prénom\ncol2:\r\n    head: "Activité pro" \r\n    bloc: \r\n        - job                                      		#19   Activité professionnelle\ncol3:\r\n    head: "Tel" \r\n    bloc: \r\n        - telPro                                   		#57   Téléphone professionnel\ncol4:\r\n    head: "Fax" \r\n    bloc: \r\n        - faxPro                                   		#58   Fax professionnel\ncol5:\r\n    head: "Email"\r\n    bloc-separator: " - "\r\n    bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - personalEmail                            		#4    Email personnelle\ncol6:\r\n    head: "Ville"\r\n    bloc:\r\n        - villeAdressePro,text-uppercase           		#56   Ville', 'col1:\r\n    head: "Identité"\r\n    blocseparator: " "\r\n    bloc:\r\n        - titre,gras                               		#51   Titre\n        - lastname,text-uppercase,gras             		#2    Nom d usage\n        - birthname,text-uppercase,gras            		#1    Nom de naissance\n        - firstname,text-capitalize,gras           		#3    Prénom\ncol2:\r\n    head: "Activité pro" \r\n    bloc: \r\n        - job                                      		#19   Activité professionnelle\ncol3:\r\n    head: "Tel" \r\n    bloc: \r\n        - telPro                                   		#57   Téléphone professionnel\ncol4:\r\n    head: "Fax" \r\n    bloc: \r\n        - faxPro                                   		#58   Fax professionnel\ncol5:\r\n    head: "Email"\r\n    bloc-separator: " - "\r\n    bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - personalEmail                            		#4    Email personnelle\ncol6:\r\n    head: "Ville"\r\n    bloc:\r\n        - villeAdressePro,text-uppercase           		#56   Ville', ''),
(11, 'base', 'baseSendMail', 'Formulaire mail', 'formulaire pour mail', 'data_types', 'mail', 'post', '/patient/actions/sendMail/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - mailFrom,required                          		#109  De\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - mailTo,required                            		#110  A\n row2:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailSujet,required                         		#112  Sujet\n row3:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailModeles                                		#446  Modèle\n row4:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailBody,rows=10                           		#111  Message', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - mailFrom,required                          		#109  De\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - mailTo,required                            		#110  A\n row2:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailSujet,required                         		#112  Sujet\n row3:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailModeles                                		#446  Modèle\n row4:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailBody,rows=10                           		#111  Message', ''),
(14, 'base', 'baseSendMailApicrypt', 'Formulaire mail Apicrypt', 'formulaire pour expédier un mail vers un correspondant apicrypt', 'data_types', 'mail', 'post', '/patient/actions/sendMail/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - mailFrom,required                          		#109  De\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - mailToApicrypt,required                    		#179  A (correspondant apicrypt)\n row2:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailSujet,required                         		#112  Sujet\n row3:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailModeles                                		#446  Modèle\n row4:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailBody,rows=10                           		#111  Message', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - mailFrom,required                          		#109  De\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - mailToApicrypt,required                    		#179  A (correspondant apicrypt)\n row2:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailSujet,required                         		#112  Sujet\n row3:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailModeles                                		#446  Modèle\n row4:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - mailBody,rows=10                           		#111  Message', ''),
(17, 'base', 'baseReglement', 'Formulaire règlement', 'formulaire pour le règlement', 'data_types', 'reglement', 'post', '/patient/actions/saveReglementForm/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifCejour,readonly,plus={€}       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€}                 		#199  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€}           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€}                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€}                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€}                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€}                		#200  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque                        		#205  Identité payeur', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifCejour,readonly,plus={€}       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€}                 		#199  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€}           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€}                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€}                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€}                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€}                		#200  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque                        		#205  Identité payeur', ''),
(18, 'base', 'baseReglementSimple', 'Formulaire simplifié règlement (page compta)', 'formulaire simplifié pour le règlement', 'data_types', 'reglement', 'post', '/compta/actions/saveReglementForm/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - 193,plus={€}\r\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - 194,plus={€}\r\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - 195,plus={€}\r\n row2:\r\n  col1: \r\n    size: 9\r\n    bloc: \r\n      - 205', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - 193,plus={€}\r\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - 194,plus={€}\r\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - 195,plus={€}\r\n row2:\r\n  col1: \r\n    size: 9\r\n    bloc: \r\n      - 205', ''),
(19, 'base', 'baseReglementSearch', 'Recherche règlements', 'formulaire recherche règlement', 'form_basic_types', 'admin', 'post', '', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - date                                       		#4    Début de période\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - date                                       		#4    Début de période\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - submit                                     		#3    Valider', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - date                                       		#4    Début de période\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - date                                       		#4    Début de période\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - submit                                     		#3    Valider', ''),
(22, 'base', 'baseImportExternal', 'Import', 'formulaire pour consultation importée d\'une source externe', 'data_types', 'medical', 'post', '', 5, 'public', 'global:\r\n  formClass: \'newCS\' \r\nstructure:\r\n####### INTRODUCTION ######\r\n  row1:                              \r\n    head: \'Consultation importée\'\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n        - dataImport,rows=10                       		#252  Import', 'global:\r\n  formClass: \'newCS\' \r\nstructure:\r\n####### INTRODUCTION ######\r\n  row1:                              \r\n    head: \'Consultation importée\'\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n        - dataImport,rows=10                       		#252  Import', 'csImportee'),
(25, 'base', 'basePasswordChange', 'Assigner un mot de passe', 'formulaire assigner un password à un utilisateur', 'form_basic_types', 'admin', 'post', '/configuration/actions/configUpdatePassword/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Identifiant et mot de passe"\r\n    size: 3\r\n    bloc: \r\n      - username,required                            		#1    Identifiant\n      - password                          		#2    Mot de passe\n      - module,nolabel                             		#5    Module\n      - submit                                     		#3    Valider', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Identifiant et mot de passe"\r\n    size: 3\r\n    bloc: \r\n      - username,required                            		#1    Identifiant\n      - password                          		#2    Mot de passe\n      - module,nolabel                             		#5    Module\n      - submit                                     		#3    Valider', ''),
(29, 'base', 'baseFax', 'Formulaire écofax', 'formulaire pour ecofax OVH', 'data_types', 'mail', 'post', '/patient/actions/sendMail/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 4\r\n    bloc: \r\n      - mailToEcofaxName,required                  		#484  Destinataire du fax\n  col2: \r\n    size: 4\r\n    bloc: \r\n      - mailToEcofaxNumber,required                		#481  Numéro de fax du destinataire', 'structure:\r\n row1:\r\n  col1: \r\n    size: 4\r\n    bloc: \r\n      - mailToEcofaxName,required                  		#484  Destinataire du fax\n  col2: \r\n    size: 4\r\n    bloc: \r\n      - mailToEcofaxNumber,required                		#481  Numéro de fax du destinataire', ''),
(30, 'base', 'baseAgendaPriseRDV', 'Agenda prise rdv', 'formulaire latéral de prise de rdv', 'data_types', 'admin', 'post', '', 5, 'public', 'global:\r\n  noFormTags: true\r\nstructure:\r\n  row1:                              \r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - birthname,readonly                       		#1    Nom de naissance\n    col2:                              \r\n      size: 6\r\n      bloc:                          \r\n        - firstname,readonly                       		#3    Prénom\n  row2:\r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - lastname,readonly                        		#2    Nom d usage\n    col2:                              \r\n      size: 6\r\n      bloc: \r\n        - birthdate,readonly                       		#8    Date de naissance\n  row3:\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n         - personalEmail                           		#4    Email personnelle\n\r\n  row4:                              \r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - mobilePhone                              		#7    Téléphone mobile\n    col2:                              \r\n      size: 6\r\n      bloc:                          \r\n        - homePhone                                		#10   Téléphone domicile', 'global:\r\n  noFormTags: true\r\nstructure:\r\n  row1:                              \r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - birthname,readonly                       		#1    Nom de naissance\n    col2:                              \r\n      size: 6\r\n      bloc:                          \r\n        - firstname,readonly                       		#3    Prénom\n  row2:\r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - lastname,readonly                        		#2    Nom d usage\n    col2:                              \r\n      size: 6\r\n      bloc: \r\n        - birthdate,readonly                       		#8    Date de naissance\n  row3:\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n         - personalEmail                           		#4    Email personnelle\n\r\n  row4:                              \r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - mobilePhone                              		#7    Téléphone mobile\n    col2:                              \r\n      size: 6\r\n      bloc:                          \r\n        - homePhone                                		#10   Téléphone domicile', ''),
(33, 'base', 'baseSynthese', 'Synthèse patiente', 'formulaire fixe de synthèse', 'data_types', 'medical', 'post', '', 4, 'public', 'structure:\r\n  row1:                              \r\n    col1:                             \r\n      size: 12\r\n      bloc:                          \r\n        - baseSynthese,rows=8                      		#718  Synthèse patient', 'structure:\r\n  row1:                              \r\n    col1:                             \r\n      size: 12\r\n      bloc:                          \r\n        - baseSynthese,rows=8                      		#718  Synthèse patient', ''),
(35, 'base', 'baseATCD', 'Formulaire latéral écran patient principal (atcd)', 'formulaire en colonne latéral du dossier patient (atcd)', 'data_types', 'medical', 'post', '', 4, 'public', 'structure:\r\n  row1: \r\n    col1: \r\n      size: 4\r\n      bloc: \r\n        - poids                                    		#34   Poids\r\n    col2: \r\n      size: 4\r\n      bloc: \r\n       - taillePatient                             		#35   Taille\r\n    col3: \r\n      size: 4\r\n      bloc: \r\n       - imc,readonly                              		#43   IMC\r\n  row2: \r\n   col1: \r\n     size: 12\r\n     bloc: \r\n       - job                                       		#19   Activité professionnelle\r\n       - allergies,rows=2                          		#66   Allergies\r\n       - toxiques                                  		#42   Toxiques\r\n  row3: \r\n    col1: \r\n     size: 12\r\n     bloc: \r\n       - atcdMedicChir,rows=6                      		#41   Antécédents médico-chirugicaux\r\n       - atcdFamiliaux,rows=6                      		#38   Antécédents familiaux', 'structure:\r\n  row1: \r\n    col1: \r\n      size: 4\r\n      bloc: \r\n        - poids                                    		#34   Poids\r\n    col2: \r\n      size: 4\r\n      bloc: \r\n       - taillePatient                             		#35   Taille\r\n    col3: \r\n      size: 4\r\n      bloc: \r\n       - imc,readonly                              		#43   IMC\r\n  row2: \r\n   col1: \r\n     size: 12\r\n     bloc: \r\n       - job                                       		#19   Activité professionnelle\r\n       - allergies,rows=2                          		#66   Allergies\r\n       - toxiques                                  		#42   Toxiques\r\n  row3: \r\n    col1: \r\n     size: 12\r\n     bloc: \r\n       - atcdMedicChir,rows=6                      		#41   Antécédents médico-chirugicaux\r\n       - atcdFamiliaux,rows=6                      		#38   Antécédents familiaux', ''),
(36, 'base', 'baseConsult', 'Formulaire CS', 'formulaire basique de consultation', 'data_types', 'medical', 'post', '/patient/actions/saveCsForm/', 4, 'public', 'global:\r\n  formClass: \'newCS\' \r\nstructure:\r\n####### INTRODUCTION ######\r\n  row1:                              \r\n    head: \'Consultation\'\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n        - examenDuJour,rows=10                     		#716  Examen du jour', 'global:\r\n  formClass: \'newCS\' \r\nstructure:\r\n####### INTRODUCTION ######\r\n  row1:                              \r\n    head: \'Consultation\'\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n        - examenDuJour,rows=10                     		#716  Examen du jour', 'csBase'),
(37, 'base', 'firstLogin', 'Premier utilisateur', 'Création premier utilisateur', 'form_basic_types', 'admin', 'post', '/login/logInFirstDo/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Mot de passe de l\'utilisateur 1"\r\n    size: 3\r\n    bloc:\r\n      - username,required                            		#1    Identifiant\n      - moduleSelect                               		#7    Module\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe\n      - submit                                     		#3    Valider', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Mot de passe de l\'utilisateur 1"\r\n    size: 3\r\n    bloc:\r\n      - username,required                            		#1    Identifiant\n      - moduleSelect                               		#7    Module\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe\n      - submit                                     		#3    Valider', NULL),
(38, 'base', 'userParameters', 'Paramètres utilisateur', 'Paramètres utilisateur', 'data_types', 'admin', 'post', '/user/configuration/', 5, 'public', 'global:\n  noFormTags: true\nstructure:\n row1:\n  col1: \n    head: "Compte clicRDV"\n    size: 3\n    bloc:\n      - clicRdvUserId\n      - clicRdvPassword\n      - clicRdvGroupId\n      - clicRdvCalId\n      - clicRdvConsultId,nolabel', 'global:\n  noFormTags: true\nstructure:\n row1:\n  col1: \n    head: "Compte clicRDV"\n    size: 3\n    bloc:\n      - clicRdvUserId\n      - clicRdvPassword\n      - clicRdvGroupId\n      - clicRdvCalId\n      - clicRdvConsultId,nolabel', NULL);


INSERT INTO `forms_cat` (`id`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
(1, 'patientforms', 'Formulaires de saisie', 'Formulaire liés à la saisie de données', 'user', 1, '2018-01-01 00:00:00'),
(2, 'displayforms', 'Formulaires d\'affichage', 'Formulaires liés à l\'affichage d\'informations', 'user', 1, '2018-01-01 00:00:00'),
(4, 'formCS', 'Formulaires de consultation', 'Formulaires pour construire les consultations', 'user', 1, '2018-01-01 00:00:00'),
(5, 'systemForm', 'Formulaires système', 'formulaires système', 'user', 1, '2018-01-01 00:00:00');

INSERT INTO `form_basic_types` (`id`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `type`, `cat`, `fromID`, `creationDate`, `deleteByID`, `deleteDate`) VALUES
(1, 'username', 'identifiant', 'Identifiant', 'identifiant utilisateur', 'required', 'L\'identifiant utilisateur est manquant', 'text', '', 'base', 0, 1, '2018-01-01 00:00:00', 1, '2018-01-01 00:00:00'),
(2, 'password', 'mot de passe', 'Mot de passe', 'mot de passe utilisateur', 'required', 'Le mot de passe est manquant', 'password', '', 'base', 0, 1, '2018-01-01 00:00:00', 1, '2018-01-01 00:00:00'),
(3, 'submit', '', 'Valider', 'bouton submit de validation', '', '', 'submit', '', 'base', 0, 1, '2018-01-01 00:00:00', 1, '2018-01-01 00:00:00'),
(4, 'date', '', 'Début de période', '', '', '', 'date', '', 'base', 0, 1, '2018-01-01 00:00:00', 1, '2018-01-01 00:00:00'),
(5, 'verifPassword', 'confirmation du mot de passe', 'Confirmation du mot de passe', 'Confirmation du mot de passe utilisateur', 'required', 'La confirmation du mot de passe est manquante', 'password', '', 'base', 0, 1, '2018-01-01 00:00:00', 1, '2018-01-01 00:00:00'),
(6, 'module', '', 'Module', '', '', '', 'hidden', '', 'base', 0, 1, '2018-01-01 00:00:00', 1, '2018-01-01 00:00:00'),
(7, 'moduleSelect', '', 'Module', '', '', '', 'select', '', 'base', 0, 1, '2018-01-01 00:00:00', 1, '2018-01-01 00:00:00');

INSERT INTO `prescriptions` (`id`, `cat`, `label`, `description`, `fromID`, `toID`, `creationDate`) VALUES
(1, 2, 'Ligne vierge', '', 1, 0, '2018-01-01 00:00:00'),
(2, 4, 'Ligne vierge', '', 1, 0, '2018-01-01 00:00:00');

INSERT INTO `prescriptions_cat` (`id`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`, `displayOrder`) VALUES
(2, 'prescripMedic', 'Prescriptions médicamenteuses', 'prescriptions médicamenteuses', 'user', 1, '2018-01-01 00:00:00', 1),
(4, 'prescriNonMedic', 'Prescriptions non médicamenteuses', 'prescriptions non médicamenteuses', 'user', 1, '2018-01-01 00:00:00', 1);

INSERT INTO `people` (`id`, `name`, `type`, `rank`, `module`, `pass`, `registerDate`, `fromID`, `lastLogIP`, `lastLogDate`, `lastLogFingerprint`) VALUES
(1, 'medshake', 'service', '', 'base', '', '2018-01-01 00:00:00', '1', '', '2018-01-01 00:00:00', ''),
(2, 'clicRDV', 'service', '', 'base', '', '2018-01-01 00:00:00', '1', '', '2018-01-01 00:00:00', '');

INSERT INTO `system` (`id`,`module`,`version`) VALUES (1, 'base', 'v3.0.0');
