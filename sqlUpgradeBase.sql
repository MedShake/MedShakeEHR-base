-- Modifications de structure de la bdd d'une version à la suivante

-- 2.3.0 to 2.x.0

update data_types set placeholder='nom reçu à la naissance', label='Nom de naissance', description='Nom reçu à la naissance' where name='birthname';
update data_types set placeholder='nom utilisé au quotidien', label='Nom d\'usage', description='Nom utilisé au quotidien' where name='lastname';

-- ALTER TABLE `actes` ADD `module` VARCHAR(20) NOT NULL DEFAULT 'base' AFTER `id`;
ALTER TABLE `actes_cat` CHANGE `type` `module` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'base';

ALTER TABLE `actes_cat` ADD UNIQUE(`name`);

ALTER TABLE `data_types` CHANGE `type` `module` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'base';

UPDATE `data_types` SET `name` = 'firstname' WHERE `data_types`.`id` = 3;

ALTER TABLE `forms` ADD `module` VARCHAR(20) NOT NULL DEFAULT 'base' AFTER `id`;

CREATE TABLE `system` (
  `id` smallint(4) UNSIGNED NOT NULL,
  `module` varchar(20) DEFAULT 'base',
  `version` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `system` (`id`,`module`,`version`) VALUES
(1, 'base', 'v2.4.0'),
(2, 'public', 'v2.4.0');

ALTER TABLE `system` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `module` (`module`);

ALTER TABLE `system`
  MODIFY `id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `people` ADD `module` varchar(20) DEFAULT NULL after `rank`;

INSERT INTO `form_basic_types` (`id`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `type`, `cat`, `fromID`, `creationDate`, `deleteByID`, `deleteDate`) VALUES
(5, 'verifPassword', 'confirmation du mot de passe', 'Confirmation du mot de passe', 'Confirmation du mot de passe utilisateur', 'required', 'La confirmation du mot de passe est manquante', 'password', '', 'base', 0, 0, '2018-01-06 12:41:50', 0, '1970-01-01 00:00:00'),
(6, 'module', '', 'Module', '', '', '', 'hidden', '', 'base', 0, 0, '2017-03-27 00:00:00', 0, '2017-03-27 00:00:00');

UPDATE `forms` set `yamlStructure`='structure:\r\n row1:\r\n  col1: \r\n    head: "Identifiant et mot de passe"\r\n    size: 3\r\n    bloc: \r\n      - userid,required\r\n      - password\r\n      - module,nolabel\r\n      - submit' , `yamlStructureDefaut`='structure:\r\n row1:\r\n  col1: \r\n    head: "Identifiant et mot de passe"\r\n    size: 3\r\n    bloc: \r\n      - userid,required\r\n      - password\r\n      - module,nolabel\r\n      - submit' WHERE `name`='basePasswordChange';

INSERT INTO `forms` (`internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
('firstLogin', 'Premier utilisateur', 'Création premier utilisateur', 'form_basic_types', 'admin', 'post', '/login/logInFirstDo/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Mot de passe de l\'utilisateur 1"\r\n    size: 3\r\n    bloc:\r\n      - userid,readonly \r\n      - password,required\r\n      - verifPassword,required\r\n      - submit', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Mot de passe de l\'utilisateur 1"\r\n    size: 3\r\n    bloc: \r\n      - userid,readonly \r\n      - password,required\r\n      - verifPassword,required\r\n      - submit', NULL);

-- 2.1.0 to 2.2.0

ALTER TABLE data_types CHANGE `formType` `formType` ENUM('','date','email','lcc','number','select','submit','tel','text','textarea','checkbox','hidden','range','radio','reset') NOT NULL DEFAULT '';

ALTER TABLE `objets_data` ADD `registerDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `instance`;
update objets_data set registerDate=creationDate;

update forms set yamlStructure=REPLACE(yamlStructure, 'size: 3', 'size: 4'), yamlStructureDefaut=REPLACE(yamlStructureDefaut, 'size: 3', 'size: 4') where id in ('1','7');
update forms set yamlStructure=REPLACE(yamlStructure, 'size: 9', 'size: 12'), yamlStructureDefaut=REPLACE(yamlStructureDefaut, 'size: 9', 'size: 12') where id in ('1','7');

-- 2.0.0. to 2.1.0

ALTER TABLE actes_base MODIFY `tarifs1` float DEFAULT NULL;
ALTER TABLE actes_base MODIFY `tarifs2` float DEFAULT NULL;
ALTER TABLE actes MODIFY `details` text DEFAULT NULL;
ALTER TABLE data_types CHANGE `formType` `formType` ENUM('','date','email','lcc','number','select','submit','tel','text','textarea','checkbox') NOT NULL DEFAULT '';
UPDATE data_types set formType='checkbox' where id in ('436','492','493','496');

-- 1.4.0 to 2.0.0

ALTER TABLE forms ADD internalName varchar(60) after id;

CREATE TABLE agenda_changelog (
  `id` int(8) UNSIGNED NOT NULL,
  `eventID` int(12) UNSIGNED NOT NULL,
  `userID` smallint(5) UNSIGNED NOT NULL,
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `operation` enum('edit','move','delete','missing') NOT NULL,
  `olddata` mediumblob DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE agenda_changelog
  ADD PRIMARY KEY (`id`),
  ADD KEY `eventID` (`eventID`);
ALTER TABLE agenda_changelog
  MODIFY `id` int(8) UNSIGNED NOT NULL AUTO_INCREMENT;

update forms set internalName='baseNewPatient' where id='1';
update forms set internalName='baseListingPatients' where id='2';
update forms set internalName='baseLogin' where id='3';
update forms set internalName='baseATCD' where id='4';
update forms set internalName='baseSynthese' where id='5';
update forms set internalName='baseNewPro' where id='7';
update forms set internalName='baseListingPro' where id='8';
update forms set internalName='baseConsult' where id='10';
update forms set internalName='baseSendMail' where id='11';
update forms set internalName='baseSendMailApicrypt' where id='14';
update forms set internalName='baseImportDocExterne' where id='15';
update forms set internalName='baseOrdonnance' where id='16';
update forms set internalName='baseReglement' where id='17';
update forms set internalName='baseReglementSimple' where id='18';
update forms set internalName='baseReglementSearch' where id='19';
update forms set internalName='baseImportExternal' where id='22';
update forms set internalName='basePasswordChange' where id='25';
update forms set internalName='baseFax' where id='29';
update forms set internalName='baseAgendaPriseRDV' where id='30';



ALTER TABLE agenda ADD fromID mediumint(6) UNSIGNED DEFAULT NULL after patientid;
ALTER TABLE agenda DROP PRIMARY KEY;
ALTER TABLE agenda ADD PRIMARY KEY (`id`), ADD KEY `userid` (`userid`); ;
ALTER TABLE agenda MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;

-- 1.3.0 to 1.4.0

ALTER TABLE data_types MODIFY COLUMN placeholder VARCHAR(255) DEFAULT null;
ALTER TABLE data_types MODIFY COLUMN label VARCHAR(60) DEFAULT null;
ALTER TABLE data_types MODIFY COLUMN description VARCHAR(255) DEFAULT null;
ALTER TABLE data_types MODIFY COLUMN validationRules VARCHAR(255) DEFAULT null;
ALTER TABLE data_types MODIFY COLUMN validationErrorMsg VARCHAR(255) DEFAULT null;
ALTER TABLE `data_types` CHANGE `formType` `formType` ENUM('','date','email','lcc','number','select','submit','tel','text','textarea') NOT NULL DEFAULT '';
ALTER TABLE `data_types` CHANGE `formValues` `formValues` TEXT NULL DEFAULT NULL;

-- 1.1.0 to 1.2.0

CREATE TABLE `agenda` (
  `id` int(12) UNSIGNED NOT NULL,
  `userid` smallint(5) UNSIGNED NOT NULL DEFAULT '3',
  `start` datetime DEFAULT NULL,
  `end` datetime DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `dateAdd` datetime DEFAULT NULL,
  `patientid` mediumint(6) UNSIGNED DEFAULT NULL,
  `statut` enum('actif','deleted') DEFAULT 'actif',
  `absente` enum('non','oui') DEFAULT 'non',
  `motif` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `agenda`
  ADD PRIMARY KEY (`id`,`userid`) USING BTREE,
  ADD KEY `patientid` (`patientid`);

-- 1.0.1 to 1.1.0
ALTER TABLE inbox ADD COLUMN mailHeaderInfos blob AFTER txtFileName;
