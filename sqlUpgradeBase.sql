-- Modifications de structure de la bdd d'une version à la suivante

-- 3.0.0 to next

INSERT INTO `forms` ( `module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
('base', 'aldDeclaration', 'Déclaration d\'ALD', 'formulaire d\'enregistrement d\'une ALD', 'data_types', 'medical', 'post', '/patient/actions/saveCsForm/', 4, 'public', 'structure:\r\n  row1:\r\n    head: Enregistrement d\'une prise en charge en ALD\r\n    col1:\r\n     size: 12\r\n     bloc:\r\n       - aldNumber                                 		#878  ALD\n  row2:\r\n    col1:\r\n     size: 4\r\n     bloc:\r\n       - aldDateDebutPriseEnCharge                 		#879  Début de prise en charge\n    col2:\r\n      size: 4\r\n      bloc:\r\n       - aldDateFinPriseEnCharge                   		#880  Fin de prise en charge\n  row3:\r\n    col1:\r\n     size: 2\r\n     bloc:\r\n       - aldCIM10,plus={<i class="glyphicon glyphicon-search"></i>} 		#881  Code CIM10 associé\n    col2:\r\n     size: 10\r\n     bloc:\r\n       - aldCIM10label,readonly                    		#883  Label CIM10 associé', NULL, ''),
('base', 'atcdStrucDeclaration', 'Déclaration d\'atcd structuré', 'ajout d\'antécédents structuré et codé CIM 10', 'data_types', 'medical', 'post', '/patient/actions/saveCsForm/', 4, 'public', 'structure: \r\n  row1:\r\n   head : Ajout d\'un antécédent à partir de la classification CIM 10\r\n   col1: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucCIM10,plus={<i class="glyphicon glyphicon-search"></i>} 		#884  Code CIM 10\n   col2: \r\n     size: 10\r\n     bloc:\r\n       - atcdStrucCIM10Label,readonly              		#885  Label CIM 10\n  row2:\r\n    head: "Début"                  		\r\n    col1: \r\n     size: 1\r\n     bloc:\r\n       - atcdStrucDateDebutJour                    		#886  Jour\n    col2: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateDebutMois                    		#888  Mois\n    col3: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateDebutAnnee,min=1910,step=1   		#890  Année\n  row3:\r\n    head: "Fin"\r\n    col1: \r\n     size: 1\r\n     bloc:\r\n       - atcdStrucDateFinJour                      		#887  Jour\n    col2: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateFinMois                      		#889  Mois\n    col3: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateFinAnnee,min=1910,step=1     		#891  Année\n  row4:\r\n    head: "Notes"\r\n    col1: \r\n     size: 12\r\n     bloc:\r\n       - atcdStrucNotes,nolabel                    		#893  Notes', NULL, '');

INSERT INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('medical', 'aldCat', 'ALD', 'paramètres pour la gestion des ALD', 'base', 1, '2018-01-19 10:29:09'),
('medical', 'catAtcdStruc', 'ATCD structurés', 'données pour antécédents structurés', 'base', 1, '2018-01-22 12:45:18'),
('typecs', 'catTypeCsATCD', 'Antécédents et allergies', 'antécédents et allergies', 'base', 1, '2018-01-22 20:31:57'),
('relation', 'catAllergiesStruc', 'Allergies structurées', 'données pour allergies structurées', 'base', 1, '2018-01-23 10:21:09');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='aldCat');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'aldNumber', '', 'ALD', 'ALD choisie', '', '', 'select', '1: "Accident vasculaire cérébral invalidant"\n2: "Insuffisances médullaires et autres cytopénies chroniques"\n3: "Artériopathies chroniques avec manifestations ischémiques"\n4: "Bilharziose compliquée"\n5: "Insuffisance cardiaque grave, troubles du rythme graves, cardiopathies valvulaires graves, cardiopathies  congénitales graves"\n6: "Maladies chroniques actives du foie et cirrhoses"\n7: "Déficit immunitaire primitif grave nécessitant un traitement prolongé, infection par le virus de 9: l\'immuno-déficience humaine (VIH)"\n8: "Diabète de type 1 et diabète de type 2"\n9: "Formes graves des affections neurologiques et musculaires (dont myopathie), épilepsie grave"\n10: "Hémoglobinopathies, hémolyses, chroniques constitutionnelles et acquises sévères"\n11: "Hémophilies et affections constitutionnelles de l\'hémostase graves"\n12: "Maladie coronaire"\n13: "Insuffisance respiratoire chronique grave"\n14: "Maladie d\'Alzheimer et autres démences"\n15: "Maladie de Parkinson"\n16: "Maladies métaboliques héréditaires nécessitant un traitement prolongé spécialisé"\n17: "Mucoviscidose"\n18: "Néphropathie chronique grave et syndrome néphrotique primitif"\n19: "Paraplégie"\n20: "Vascularites, lupus érythémateux systémique, sclérodermie systémique"\n21: "Polyarthrite rhumatoïde évolutive"\n22: "Affections psychiatriques de longue durée"\n23: "Rectocolite hémorragique et maladie de Crohn évolutives"\n24: "Sclérose en plaques"\n25: "Scoliose idiopathique structurale évolutive (dont l\'angle est égal ou supérieur à 25 degrés) jusqu\'à maturation rachidienne"\n26: "Spondylarthrite grave"\n27: "Suites de transplantation d\'organe"\n28: "Tuberculose active, lèpre"\n29: "Tumeur maligne, affection maligne du tissu lymphatique ou hématopoïétique"\n31: "Affection hors liste"\n32: "Etat polypathologique"', 'base', @catID, 1, '2018-01-19 15:45:35', 3600, 0),
('medical', 'aldDateDebutPriseEnCharge', '', 'Début de prise en charge', 'date de début de prise en charge', '', '', 'date', '', 'base', @catID, 1, '2018-01-19 10:35:18', 3600, 0),
('medical', 'aldDateFinPriseEnCharge', '', 'Fin de prise en charge', 'date de fin de prise en charge', '', '', 'date', '', 'base', @catID, 1, '2018-01-19 10:35:57', 3600, 0),
('medical', 'aldCIM10', '', 'Code CIM10 associé', 'Code CIM10 attaché à l\'ALD', '', '', 'text', '', 'base', @catID, 1, '2018-01-19 15:54:50', 3600, 0),
('medical', 'aldCIM10label', '', 'Label CIM10 associé', 'Label CIM10 attaché à l\'ALD', '', '', 'text', '', 'base', @catID, 1, '2018-01-19 16:48:49', 3600, 0);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catAtcdStruc');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'atcdStrucCIM10', '', 'Code CIM 10', 'code CIM 10 de l\'atcd', '', '', 'text', '', 'base', @catID, 1, '2018-01-22 13:32:53', 3600, 0),
('medical', 'atcdStrucCIM10Label', '', 'Label CIM 10', 'label CIM 10 de l\'atcd', '', '', 'text', '', 'base', @catID, 1, '2018-01-22 13:33:11', 3600, 0),
('medical', 'atcdStrucDateDebutJour', '', 'Jour', 'jour de début de l\'atcd', '', '', 'number', '0', 'base', @catID, 1, '2018-01-22 13:32:59', 3600, 0),
('medical', 'atcdStrucDateFinJour', '', 'Jour', 'jour de fin de l\'atcd', '', '', 'number', '0', 'base', @catID, 1, '2018-01-22 13:33:05', 3600, 0),
('medical', 'atcdStrucDateDebutMois', '', 'Mois', 'mois de début de l\'atcd', '', '', 'select', '\'0\' : \'non précisé\'\n\'1\' : \'janvier\'\n\'2\' : \'février\'\n\'3\' : \'mars\'\n\'4\' : \'avril\'\n\'5\' : \'mai\'\n\'6\' : \'juin\'\n\'7\' : \'juillet\'\n\'8\' : \'août\'\n\'9\' : \'septembre\'\n\'10\' : \'octobre\'\n\'11\' : \'novembre\'\n\'12\' : \'décembre\'', 'base', 80, 1, '2018-01-22 13:33:16', 3600, 0),
('medical', 'atcdStrucDateFinMois', '', 'Mois', 'mois de fin de l\'atcd', '', '', 'select', '\'0\' : \'non précisé\'\n\'1\' : \'janvier\'\n\'2\' : \'février\'\n\'3\' : \'mars\'\n\'4\' : \'avril\'\n\'5\' : \'mai\'\n\'6\' : \'juin\'\n\'7\' : \'juillet\'\n\'8\' : \'août\'\n\'9\' : \'septembre\'\n\'10\' : \'octobre\'\n\'11\' : \'novembre\'\n\'12\' : \'décembre\'', 'base', @catID, 1, '2018-01-22 13:33:22', 3600, 0),
('medical', 'atcdStrucDateDebutAnnee', '', 'Année', 'année de début de l\'atcd', '', '', 'number', '', 'base', @catID, 1, '2018-01-22 13:32:41', 3600, 0),
('medical', 'atcdStrucDateFinAnnee', '', 'Année', 'année de fin de l\'atcd', '', '', 'number', '', 'base', @catID, 1, '2018-01-22 13:32:47', 3600, 0),
('medical', 'atcdStrucNotes', 'notes concernant cet antécédents', 'Notes', 'notes concernant l\'atcd', '', '', 'textarea', '', 'base', @catID, 1, '2018-01-22 13:33:28', 3600, 0);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catTypeCsATCD');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('typecs', 'csAtcdStrucDeclaration', NULL, 'Ajout d\'antécédent', 'support parent pour déclaration d\'antécédent structuré', NULL, NULL, '', 'atcdStrucDeclaration', 'base', @catID, 1, '2018-01-22 20:32:12', 84600, 1),
('typecs', 'csAldDeclaration', NULL, 'Déclaration ALD', 'support parent pour déclaration ALD', NULL, NULL, '', 'aldDeclaration', 'base', @catID, 1, '2018-01-22 20:32:22', 84600, 1);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catAllergiesStruc');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('relation', 'allergieLibelleTheriaque', '', 'Libelle Thériaque de l\'allergie', 'libelle Thériaque de l\'allergie', '', '', 'text', '', 'base', @catID, 1, '2018-01-23 10:21:58', 3600, 0),
('relation', 'allergieCodeTheriaque', '', 'Code Thériaque de l\'allergie', 'codee Thériaque de l\'allergie', '', '', 'text', '', 'base', @catID, 1, '2018-01-23 10:22:21', 3600, 0);

-- hors LAP

ALTER TABLE `objets_data` ADD `deletedByID` INT NULL DEFAULT NULL AFTER `deleted`;

--people
ALTER TABLE `people` ADD `name` varchar(30) DEFAULT NULL after `id`;
ALTER TABLE `people` ADD UNIQUE KEY `name` (`name`);
ALTER TABLE `people` CHANGE `type` `type` enum('patient','pro','externe','service', 'deleted') NOT NULL DEFAULT 'patient';

UPDATE `people` SET `name`=CONCAT('MedShake',`id`) WHERE name='' and `pass`!='';
INSERT IGNORE INTO `people` (`name`, `type`, `rank`, `module`, `pass`, `registerDate`, `fromID`, `lastLogIP`, `lastLogDate`, `lastLogFingerprint`) VALUES
('medshake', 'service', '', 'base', '', '2018-01-01 00:00:00', '1', '', '2018-01-01 00:00:00', ''),
('clicRDV', 'service', '', 'base', '', '2018-01-01 00:00:00', '1', '', '2018-01-01 00:00:00', '');
SET @medshakeid=(SELECT `id` from `people` WHERE `name`='medshake');

--agenda
ALTER TABLE `agenda` ADD `externid` int UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `agenda` ADD `lastModified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `dateAdd`;
ALTER TABLE `agenda` ADD KEY `externid` (`externid`);

--data_cat
INSERT IGNORE INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('user', 'clicRDV', 'clicRDV', 'Paramètres pour clicRDV', 'base', 1, '2018-01-01 00:00:00');
UPDATE `data_cat` SET `fromID`=@medshakeid WHERE `fromID` in ('0','1');

--data_types
ALTER TABLE `data_types` CHANGE  `formType` `formType` enum('','date','email','lcc','number','select','submit','tel','text','textarea','password','checkbox') NOT NULL DEFAULT '';
UPDATE `data_types` SET `fromID`='1' WHERE `fromID`='0';
UPDATE `data_types` SET `module`='base' WHERE `internalName`='baseSynthese';
UPDATE `data_types` SET `label` = 'agendaForPatientsOfTheDay', `description` = 'permet d\'indiquer l\'agenda à utiliser pour la liste patients du jour pour cet utilisateur', `formType` = 'select', `formValues` = '' WHERE `name` = 'agendaNumberForPatientsOfTheDay';
SET @cat=(SELECT `id` FROM `data_cat` WHERE `name`='clicRDV');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('user', 'clicRdvUserId', 'identifiant', 'identifiant', 'email@address.com', '', '', 'text', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 1),
('user', 'clicRdvPassword', 'Mot de passe', 'Mot de passe', 'Mot de passe (chiffré)', '', '', 'password', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 2),
('user', 'clicRdvGroupId', 'Groupe', 'Groupe', 'Groupe Sélectionné', '', '', 'select', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 3),
('user', 'clicRdvCalId', 'Agenda', 'Agenda', 'Agenda sélectionné', '', '', 'select', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 4),
('user', 'clicRdvConsultId', 'Consultations', 'Consultations', 'Correspondance entre consultations', '', '', 'select', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 5);

SET @cat=(SELECT `id` FROM `data_cat` WHERE `name`='divers');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'clicRdvPatientId', 'ID patient', 'ID patient', 'ID patient', '', '', 'text', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 1);

SET @cat=(SELECT `id` FROM `data_cat` WHERE `name`='relationRelations');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('relation', 'relationExternePatient', '', 'Relation externe patient', 'relation externe patient', '', '', 'number', '', 'base', @cat, 1, '2018-01-01 00:00:00', 1576800000, 1);

UPDATE `data_types` SET `fromID`=@medshakeid WHERE `fromID` in ('0','1');

--forms_cat
UPDATE `forms_cat` SET `fromID`=@medshakeid WHERE `fromID` in ('0','1');

--forms
DELETE FROM `forms` WHERE `internalName`='basePasswordChange';

INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
('base', 'baseUserParameters', 'Paramètres utilisateur', 'Paramètres utilisateur', 'data_types', 'admin', 'post', '/user/configuration/', 5, 'public', 'global:\n  noFormTags: true\nstructure:\n row1:\n  col1: \n    head: "Compte clicRDV"\n    size: 3\n    bloc:\n      - clicRdvUserId\n      - clicRdvPassword\n      - clicRdvGroupId\n      - clicRdvCalId\n      - clicRdvConsultId,nolabel', 'global:\n  noFormTags: true\nstructure:\n row1:\n  col1: \n    head: "Compte clicRDV"\n    size: 3\n    bloc:\n      - clicRdvUserId\n      - clicRdvPassword\n      - clicRdvGroupId\n      - clicRdvCalId\n      - clicRdvConsultId,nolabel', NULL);


UPDATE `forms` SET `yamlStructureDefaut` = 'col1:\r\n    head: "Nom de naissance"\r\n    bloc:\r\n        - birthname,text-uppercase,gras            		#1    Nom de naissance\ncol2:\r\n    head: "Nom d\'usage"\r\n    bloc:\r\n        - lastname,text-uppercase,gras             		#2    Nom d usage\n\r\ncol3:\r\n    head: "Prénom"\r\n    bloc:\r\n        - firstname,text-capitalize,gras           		#3    Prénom\ncol4:\r\n    head: "Date de naissance" \r\n    bloc: \r\n        - birthdate                                		#8    Date de naissance\ncol5:\r\n    head: "Tel" \r\n    blocseparator: " - "\r\n    bloc: \r\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\ncol6:\r\n    head: "Email"\r\n    bloc:\r\n        - personalEmail                            		#4    Email personnelle\ncol7:\r\n    head: "Ville"\r\n    bloc:\r\n        - city,text-uppercase                      		#12   Ville'
WHERE `internalName`='baseListingPatients';

UPDATE `forms` SET `yamlStructure`='structure:\r\n row1:\r\n  col1: \r\n    head: "Identification utilisateur"\r\n    size: 3\r\n    bloc: \r\n      - username,required                            		#1    Identifiant\n      - password,required                          		#2    Mot de passe\n      - submit                                     		#3    Valider', `yamlStructureDefaut`='structure:\r\n row1:\r\n  col1: \r\n    head: "Identification utilisateur"\r\n    size: 3\r\n    bloc: \r\n      - username,required                            		#1    Identifiant\n      - password,required                          		#2    Mot de passe\n      - submit                                     		#3    Valider' WHERE `internalName`='baseLogin';

UPDATE `forms` SET `internalName`='baseFirstLogin' WHERE `internalName`='firstLogin';

UPDATE `forms` SET `yamlStructure`='structure:\r\n row1:\r\n  col1: \r\n    head: "Premier utilisateur"\r\n    size: 3\r\n    bloc:\r\n      - username,required                            		#1    Identifiant\n      - moduleSelect                               		#7    Module\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe\n      - submit                                     		#3    Valider', `yamlStructureDefaut`='structure:\r\n row1:\r\n  col1: \r\n    head: "Premier utilisateur 1"\r\n    size: 3\r\n    bloc:\r\n      - username,required                            		#1    Identifiant\n      - moduleSelect                               		#7    Module\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe\n      - submit                                     		#3    Valider' WHERE `internalName`='baseFirstLogin';

UPDATE `forms` SET `fromID`=@medshakeid WHERE `fromID` in ('0','1');

--forms_basic_types
UPDATE `form_basic_types` SET `name`='username', `description`='identifiant utilisateur', `validationRules`='required', `validationErrorMsg`='L\'identifiant utilisateur est manquant' WHERE `name`='userid';
UPDATE `form_basic_types` SET `fromID`=@medshakeid WHERE `fromID` in ('0','1');

-- 2.3.0 to 3.0.0

ALTER TABLE `actes_cat` CHANGE `type` `module` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'base';

ALTER TABLE `actes_cat` ADD UNIQUE(`name`);

ALTER TABLE `data_types` CHANGE `type` `module` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'base';

UPDATE `data_types` SET `name` = 'firstname' WHERE `data_types`.`id` = 3;

INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('user', 'administratifPeutAvoirAgenda', '', 'administratifPeutAvoirAgenda', 'permet à l\'utilisateur sélectionné d\'avoir son agenda', '', '', 'checkbox', 'false', 'base', 64, 1, '2018-01-01 00:00:00', 3600, 1),
('user', 'clicRdvUserId', 'identifiant', 'identifiant', 'email@address.com', '', '', 'text', '', 'base', 66, 1, '2018-01-01 00:00:00', 3600, 1),
('user', 'clicRdvPassword', 'Mot de passe', 'Mot de passe', 'Mot de passe (chiffré)', '', '', 'password', '', 'base', 66, 1, '2018-01-01 00:00:00', 3600, 2),
('user', 'clicRdvGroupId', 'Groupe', 'Groupe', 'Groupe Sélectionné', '', '', 'select', '', 'base', 66, 1, '2018-01-01 00:00:00', 3600, 3),
('user', 'clicRdvCalId', 'Agenda', 'Agenda', 'Agenda sélectionné', '', '', 'select', '', 'base', 66, 1, '2018-01-01 00:00:00', 3600, 4),
('user', 'clicRdvConsultId', 'Consultations', 'Consultations', 'Correspondance entre consultations', '', '', 'select', '', 'base', 66, 1, '2018-01-01 00:00:00', 3600, 5),
('admin', 'clicRdvPatientId', 'ID patient', 'ID patient', 'ID patient', '', '', 'text', '', 'base', 26, 1, '2018-01-01 00:00:00', 3600, 1),
('relation', 'relationExternePatient', '', 'Relation externe patient', 'relation externe patient', '', '', 'number', '', 'base', 63, 1, '2018-01-01 00:00:00', 1576800000, 1);


ALTER TABLE `people` ADD `module` varchar(20) DEFAULT NULL after `rank`;

INSERT INTO `form_basic_types` (`id`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `type`, `cat`, `fromID`, `creationDate`, `deleteByID`, `deleteDate`) VALUES
(5, 'verifPassword', 'confirmation du mot de passe', 'Confirmation du mot de passe', 'Confirmation du mot de passe utilisateur', 'required', 'La confirmation du mot de passe est manquante', 'password', '', 'base', 0, 0, '2018-01-01 00:00:00', 0, '2018-01-01 00:00:00'),
(6, 'module', '', 'Module', '', '', '', 'hidden', '', 'base', 0, 0, '2018-01-01 00:00:00', 0, '2018-01-01 00:00:00'),
(7, 'moduleSelect', '', 'Module', '', '', '', 'select', '', 'base', 0, 0, '2018-01-01 00:00:00', 0, '2018-01-01 00:00:00');

ALTER TABLE `forms` ADD `module` VARCHAR(20) NOT NULL DEFAULT 'base' AFTER `id`;

INSERT INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
('base', 'firstLogin', 'Premier utilisateur', 'Création premier utilisateur', 'form_basic_types', 'admin', 'post', '/login/logInFirstDo/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Mot de passe de l\'utilisateur 1"\r\n    size: 3\r\n    bloc:\r\n      - userid,readonly                            		#1    Identifiant\n      - moduleSelect                               		#7    Module\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe\n      - submit                                     		#3    Valider', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Mot de passe de l\'utilisateur 1"\r\n    size: 3\r\n    bloc:\r\n      - userid,readonly                            		#1    Identifiant\n      - moduleSelect                               		#7    Module\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe\n      - submit                                     		#3    Valider', '');



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

ALTER TABLE `system`
  MODIFY `id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `people` ADD `module` varchar(20) DEFAULT NULL after `rank`;
ALTER TABLE `people` CHANGE `type` `type` enum('patient','pro','externe','deleted') NOT NULL DEFAULT 'patient';

INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('user', 'clicRdvUserId', 'identifiant', 'identifiant clicRDV', 'email@address.com', '', '', 'text', '', 'base', 0, 0, '2017-03-10 23:49:02', 3600, 1),
('user', 'clicRdvPassword', 'Mot de passe', 'Mot de passe clicRDV', 'Mot de passe', '', '', 'password', '', 'base', 0, 0, '2017-03-10 23:49:02', 3600, 1);

INSERT INTO `form_basic_types` (`name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `type`, `cat`, `fromID`, `creationDate`, `deleteByID`, `deleteDate`) VALUES
('actualPassword', 'Mot de passe de passe actuel', 'Mot de passe actuel', 'Mot de passe actuel', '', '', 'password', '', 'base', 0, 0, '2018-01-06 12:41:50', 0, '1970-01-01 00:00:00'),
('verifPassword', 'confirmation du mot de passe', 'Confirmation du mot de passe', 'Confirmation du mot de passe utilisateur', 'required', 'La confirmation du mot de passe est manquante', 'password', '', 'base', 0, 0, '2018-01-06 12:41:50', 0, '1970-01-01 00:00:00'),
('module', '', 'Module', '', '', '', 'hidden', '', 'base', 0, 0, '2017-03-27 00:00:00', 0, '2017-03-27 00:00:00');

INSERT INTO `forms` (`internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
('firstLogin', 'Premier utilisateur', 'Création premier utilisateur', 'form_basic_types', 'admin', 'post', '/login/logInFirstDo/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Mot de passe de l\'utilisateur 1"\r\n    size: 3\r\n    bloc:\r\n      - userid,readonly \r\n      - password,required\r\n      - verifPassword,required\r\n      - submit', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Mot de passe de l\'utilisateur 1"\r\n    size: 3\r\n    bloc: \r\n      - userid,readonly \r\n      - password,required\r\n      - verifPassword,required\r\n      - submit', NULL),
('base', 'userParameters', 'Paramètres utilisateur', 'Paramètres utilisateur', 'data_types', 'admin', 'post', '/user/configuration/', 5, 'public', 'global:\n  noFormsTags: true\nstructure:\n row1:\n  col1: \n    head: "Compte clicRDV"\n    size: 3\n    bloc:\n      - clicRdvUserId\n      - clicRdvPassword', 'global:\n  noFormsTags: true\nstructure:\n row1:\n  col1: \n    head: "Compte clicRDV"\n    size: 3\n    bloc:\n      - clicRdvUserId\n      - clicRdvPassword', NULL);

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
