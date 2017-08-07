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
  `creationDate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `actes_base` (
  `id` mediumint(6) UNSIGNED NOT NULL,
  `code` varchar(7) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `type` enum('NGAP','CCAM') NOT NULL DEFAULT 'CCAM',
  `tarifs1` float NOT NULL,
  `tarifs2` float NOT NULL,
  `fromID` mediumint(7) UNSIGNED NOT NULL DEFAULT '1',
  `creationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `actes_cat` (
  `id` smallint(5) NOT NULL,
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL,
  `displayOrder` smallint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `data_cat` (
  `id` smallint(5) NOT NULL,
  `groupe` enum('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user','relation') NOT NULL DEFAULT 'admin',
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `data_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `groupe` enum('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user','relation') NOT NULL DEFAULT 'admin',
  `name` varchar(60) NOT NULL,
  `placeholder` varchar(255) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `validationRules` varchar(255) NOT NULL,
  `validationErrorMsg` varchar(255) NOT NULL,
  `formType` enum('','date','email','lcc','number','select','submit','tel','text','textarea') NOT NULL,
  `formValues` text NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
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
  `type` enum('patient','pro','deleted') NOT NULL DEFAULT 'patient',
  `rank` enum('','admin') DEFAULT NULL,
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


ALTER TABLE `actes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `toID` (`toID`),
  ADD KEY `cat` (`cat`);

ALTER TABLE `actes_base`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

ALTER TABLE `actes_cat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `displayOrder` (`displayOrder`);

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
  ADD PRIMARY KEY (`id`);

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


ALTER TABLE `actes`
  MODIFY `id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `actes_base`
  MODIFY `id` mediumint(6) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `actes_cat`
  MODIFY `id` smallint(5) NOT NULL AUTO_INCREMENT;
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
