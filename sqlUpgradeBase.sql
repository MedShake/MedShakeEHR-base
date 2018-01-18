-- Modifications de structure de la bdd d'une version à la suivante

-- 2.3.0 to 3.0.0

ALTER TABLE `actes_cat` CHANGE `type` `module` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'base';

ALTER TABLE `actes_cat` ADD UNIQUE(`name`);

ALTER TABLE `data_types` CHANGE `type` `module` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'base';

UPDATE `data_types` SET `name` = 'firstname' WHERE `data_types`.`id` = 3;

INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('user', 'administratifPeutAvoirAgenda', '', 'administratifPeutAvoirAgenda', 'permet à l\'utilisateur sélectionné d\'avoir son agenda', '', '', 'checkbox', 'false', 'base', 64, 1, '2018-01-01 00:00:00', 3600, 1);


ALTER TABLE `people` ADD `module` varchar(20) DEFAULT NULL after `rank`;

INSERT INTO `form_basic_types` (`id`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `type`, `cat`, `fromID`, `creationDate`, `deleteByID`, `deleteDate`) VALUES
(5, 'verifPassword', 'confirmation du mot de passe', 'Confirmation du mot de passe', 'Confirmation du mot de passe utilisateur', 'required', 'La confirmation du mot de passe est manquante', 'password', '', 'base', 0, 0, '2018-01-01 00:00:00', 0, '2018-01-01 00:00:00'),
(6, 'module', '', 'Module', '', '', '', 'hidden', '', 'base', 0, 0, '2018-01-01 00:00:00', 0, '2018-01-01 00:00:00');

ALTER TABLE `forms` ADD `module` VARCHAR(20) NOT NULL DEFAULT 'base' AFTER `id`;

UPDATE `forms` set `yamlStructure`='structure:\r\n row1:\r\n  col1: \r\n    head: "Identifiant et mot de passe"\r\n    size: 3\r\n    bloc: \r\n      - userid,required\r\n      - password\r\n      - module,nolabel\r\n      - submit' , `yamlStructureDefaut`='structure:\r\n row1:\r\n  col1: \r\n    head: "Identifiant et mot de passe"\r\n    size: 3\r\n    bloc: \r\n      - userid,required\r\n      - password\r\n      - module,nolabel\r\n      - submit' WHERE `name`='basePasswordChange';

INSERT INTO `forms` (`internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
('firstLogin', 'Premier utilisateur', 'Création premier utilisateur', 'form_basic_types', 'admin', 'post', '/login/logInFirstDo/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Mot de passe de l\'utilisateur 1"\r\n    size: 3\r\n    bloc:\r\n      - userid,readonly \r\n      - password,required\r\n      - verifPassword,required\r\n      - submit', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Mot de passe de l\'utilisateur 1"\r\n    size: 3\r\n    bloc: \r\n      - userid,readonly \r\n      - password,required\r\n      - verifPassword,required\r\n      - submit', NULL);

update `forms` set yamlStructure = 'col1:\r\n    head: "Nom de naissance"\r\n    bloc:\r\n        - birthname,text-uppercase,gras            		#1    Nom de naissance\ncol2:\r\n    head: "Nom d\'usage"\r\n    bloc:\r\n        - lastname,text-uppercase,gras             		#2    Nom d usage\n\r\ncol3:\r\n    head: "Prénom"\r\n    bloc:\r\n        - firstname,text-capitalize,gras           		#3    Prénom\ncol4:\r\n    head: "Date de naissance" \r\n    bloc: \r\n        - birthdate                                		#8    Date de naissance\ncol5:\r\n    head: "Tel" \r\n    blocseparator: " - "\r\n    bloc: \r\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\ncol6:\r\n    head: "Email"\r\n    bloc:\r\n        - personalEmail                            		#4    Email personnelle\ncol7:\r\n    head: "Ville"\r\n    bloc:\r\n        - city,text-uppercase                      		#12   Ville'
where internalName='baseListingPatients';

update `forms` set yamlStructure = 'col1:\r\n    head: "Identité"\r\n    blocseparator: " "\r\n    bloc:\r\n        - titre,gras                               		#51   Titre\n        - lastname,text-uppercase,gras             		#2    Nom d usage\n        - birthname,text-uppercase,gras            		#1    Nom de naissance\n        - firstname,text-capitalize,gras           		#3    Prénom\ncol2:\r\n    head: "Activité pro" \r\n    bloc: \r\n        - job                                      		#19   Activité professionnelle\ncol3:\r\n    head: "Tel" \r\n    bloc: \r\n        - telPro                                   		#57   Téléphone professionnel\ncol4:\r\n    head: "Fax" \r\n    bloc: \r\n        - faxPro                                   		#58   Fax professionnel\ncol5:\r\n    head: "Email"\r\n    bloc-separator: " - "\r\n    bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - personalEmail                            		#4    Email personnelle\ncol6:\r\n    head: "Ville"\r\n    bloc:\r\n        - villeAdressePro,text-uppercase           		#56   Ville'
where internalName='baseListingPro';

update `forms` set yamlStructure = 'global:\r\n  noFormTags: true\r\nstructure:\r\n  row1:                              \r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - birthname,readonly                       		#1    Nom de naissance\n    col2:                              \r\n      size: 6\r\n      bloc:                          \r\n        - firstname,readonly                       		#3    Prénom\n  row2:\r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - lastname,readonly                        		#2    Nom d usage\n    col2:                              \r\n      size: 6\r\n      bloc: \r\n        - birthdate,readonly                       		#8    Date de naissance\n  row3:\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n         - personalEmail                           		#4    Email personnelle\n\r\n  row4:                              \r\n    col1:                              \r\n      size: 6\r\n      bloc:                          \r\n        - mobilePhone                              		#7    Téléphone mobile\n    col2:                              \r\n      size: 6\r\n      bloc:                          \r\n        - homePhone                                		#10   Téléphone domicile'
where internalName='baseAgendaPriseRDV';


update `forms` set yamlStructure =  'structure:\r\n  row1:                              \r\n    col1:                              \r\n      head: \'Etat civil\'             \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - birthname,required,autocomplete,data-acTypeID=2:1 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=2:1  		#2    Nom d usage\n        - firstname,required,autocomplete,data-acTypeID=3:22:230:235:241 		#3    Prénom\n        - birthdate                                		#8    Date de naissance\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - personalEmail                            		#4    Email personnelle\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\n    col3:\r\n      head: \'Adresse personnelle\'\r\n      size: 4\r\n      bloc: \r\n        - streetNumber                             		#9    Numéro\n        - street,autocomplete,data-acTypeID=11:55  		#11   Rue\n        - postalCodePerso                          		#13   Code postal\n        - city,autocomplete,data-acTypeID=12:56    		#12   Ville\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,rows=5                             		#21   Notes'
where internalName='baseNewPatient';

update `forms` set yamlStructure = 'structure:\r\n  row1:                              \r\n    col1:                            \r\n      head: \'Etat civil\'            \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - job,autocomplete                         		#19   Activité professionnelle\n        - titre,autocomplete                       		#51   Titre\n        - birthname,autocomplete,data-acTypeID=3:22:230:235:241 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=2:1 		#2    Nom d usage\n        - firstname,autocomplete,data-acTypeID=3:22:230:235:241 		#3    Prénom\n\r\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - profesionnalEmail                        		#5    Email professionnelle\n        - personalEmail                            		#4    Email personnelle\n        - telPro                                   		#57   Téléphone professionnel\n        - telPro2                                  		#248  Téléphone professionnel 2\n        - mobilePhonePro                           		#247  Téléphone mobile pro.\n        - faxPro                                   		#58   Fax professionnel\n    col3:\r\n      head: \'Adresse professionnelle\'\r\n      size: 4\r\n      bloc: \r\n        - numAdressePro                            		#54   Numéro\n        - rueAdressePro,autocomplete,data-acTypeID=11:55 		#55   Rue\n        - codePostalPro                            		#53   Code postal\n        - villeAdressePro,autocomplete,data-acTypeID=12:56 		#56   Ville\n        - serviceAdressePro,autocomplete           		#249  Service\n        - etablissementAdressePro,autocomplete     		#250  Établissement\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notesPro,rows=5                          		#443  Notes pros\n\r\n  row3:\r\n    col1:\r\n      size: 4\r\n      bloc:\r\n        - rpps                                     		#103  RPPS\n    col2:\r\n      size: 4\r\n      bloc:\r\n        - adeli                                    		#104  Adeli\n    col3:\r\n      size: 4\r\n      bloc:\r\n        - nReseau                                  		#477  Numéro de réseau'
where internalName='baseNewPro';


CREATE TABLE `system` (
  `id` smallint(4) UNSIGNED NOT NULL,
  `module` varchar(20) DEFAULT 'base',
  `version` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `system` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `module` (`module`);
ALTER TABLE `system` MODIFY `id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;
INSERT INTO `system` (`id`,`module`,`version`) VALUES (1, 'base', 'v3.0.0');



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
