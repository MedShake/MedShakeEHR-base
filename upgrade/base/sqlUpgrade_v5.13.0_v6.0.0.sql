-- Mise à jour n° de version
UPDATE `system` SET `value`='v6.0.0' WHERE `name`='base' and `groupe`='module';

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeDestructionDataDossierPatient', 'default', '0', '', 'Options', 'true/false', 'si true, les options de destruction physique des dossiers patients sont activées', 'false');

-- login double facteur authentification
-- anticipée en PHP
-- ALTER TABLE `people` ADD `secret2fa` VARBINARY(1000) NULL AFTER `pass`;

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeLogin2FA', 'default', '0', '', 'Options', 'true/false', 'si true, activation du login à double facteur d\'authentification', 'false');

UPDATE forms set `yamlStructure` = 'global:\r\n  formClass: \'form-signin\' \r\nstructure:\r\n row1:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - username,required,nolabel                  		#1    Identifiant\n      - password,required,nolabel                  		#2    Mot de passe\n      - otpCode,nolabel                            		#7    code otp\n      - submit,Connexion,class=btn-primary,class=btn-block 		#3    Valider' where `internalName`='baseLogin';

-- révision du form utilisateur modification password
UPDATE `forms` set `name`='Changement mot de passe utilisateur', `description`='Changement mot de passe utilisateur',  `yamlStructure` = 'structure:\r\n row1:\r\n  col1: \r\n    size: col-12\r\n    bloc:\r\n      - currentPassword,required                   		#6    Mot de passe actuel\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe' where `internalName` = 'baseUserParametersPassword';

 -- longueur minimale du password utilisateur
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeLoginPassMinLongueur', 'default', '0', '', 'Options', 'int', 'longueur minimale autorisée du mot de passe utilisateur', '10');

-- pour la forme : formulaire de 1er login
UPDATE `forms` set `yamlStructure` = 'structure:\r\n row1:\r\n  col1: \r\n    head: \"Premier utilisateur\"\r\n    size: 3\r\n    bloc:\r\n      - username,required                          		#1    Identifiant\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe\n      - submit,Valider,class={btn-primary}         		#3    Valider' where `internalName` = 'baseFirstLogin';


-- modifications pour la sup de dataset et double table de types
-- anticipée en PHP
-- ALTER TABLE `data_cat` CHANGE `groupe` `groupe` ENUM('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user','relation','system') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'admin';

-- anticipée en PHP
-- ALTER TABLE `data_types` CHANGE `groupe` `groupe` ENUM('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user','relation','system') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'admin';

DROP TABLE `form_basic_types`;

DELETE from `data_types` where name = 'submit';

INSERT IGNORE INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('system', 'catTypesUsageSystem', 'Types à usage system', 'types à usage system', 'base', 1, '2019-09-27 21:42:35');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catTypesUsageSystem');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('system', 'currentPassword', 'Mot de passe actuel', 'Mot de passe actuel', 'Mot de passe actuel de l\'utilisateur', 'required', 'Le mot de passe actuel est manquant', 'password', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'date', '', 'Début de période', '', '', '', 'date', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'modules', '', 'Modules', 'modules utilisables', '', '', 'select', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'otpCode', 'code otp', 'code otp', 'code otp', '', 'Le code otp est manquant', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'password', 'mot de passe', 'Mot de passe', 'mot de passe utilisateur', 'required', 'Le mot de passe est manquant', 'password', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'submit', '', 'Valider', 'bouton submit de validation', '', '', 'submit', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'templates', '', 'Templates utilisables', 'template utilisables', '', '', 'select', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'username', 'nom d\'utilisateur', 'Nom d\'utilisateur', 'nom d\'utilisateur', 'required', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'verifPassword', 'confirmation du mot de passe', 'Confirmation du mot de passe', 'Confirmation du mot de passe utilisateur', 'required', 'La confirmation du mot de passe est manquante', 'password', '', 'base', @catID, '1', '2019-01-01 00:00:00', '86400', '1');

-- Formulaires pour nouvel utilisateur, depuis config ou listes publiques
SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='systemForm');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseNewUser', 'Formulaire nouvel utilisateur', 'formulaire nouvel utilisateur', 'data_types', 'admin', 'post', '/configuration/ajax/configUserCreate/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: col-4\r\n    bloc:\r\n      - username,required,tabindex=1               		#1788 Nom d utilisateur\n      - birthname,tabindex=4                       		#1    Nom de naissance\n      - templates,tabindex=7                       		#1796 Templates utilisables\n  col2: \r\n    size: col-4\r\n    bloc:\r\n      - password,required,tabindex=2               		#1789 Mot de passe\n      - lastname,tabindex=5                        		#2    Nom d usage\n  col3: \r\n    size: col-4\r\n    bloc:\r\n      - modules,tabindex=3                         		#1795 Modules\n      - firstname,required,tabindex=6              		#3    Prénom', '', '', '', ''),
('base', 'baseNewUserFromPeople', 'Formulaire nouvel utilisateur pour un individu déjà existant', 'formulaire nouvel utilisateur pour un individu déjà existant', 'data_types', 'admin', 'post', '/configuration/ajax/configUserCreate/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: col-4\r\n    bloc:\r\n      - username,required,tabindex=1               		#1788 Nom d utilisateur\n      - templates,tabindex=4                       		#1796 Templates utilisables\n  col2: \r\n    size: col-4\r\n    bloc:\r\n      - password,required,tabindex=2               		#1789 Mot de passe\n  col3: \r\n    size: col-4\r\n    bloc:\r\n      - modules,tabindex=3                         		#1795 Modules', '', '', '', '');

-- Marqueur
SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catMarqueursAdminDossiers');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'administratifMarqueurDestruction', '', 'Dossier détruit', 'marqueur pour la destruction d\'un dossier', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '11'),
('admin', 'administratifMarqueurPasRdv', '', 'Ne pas donner de rendez-vous', '', '', '', 'switch', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

-- Ajout de types à people
ALTER TABLE `people` CHANGE `type` `type` ENUM('patient','pro','externe','service','deleted','groupe','destroyed') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'patient';

-- Formulaire demande de password
SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='systemForm');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseAskUserPassword', 'Demande du mot de passe', 'demande du mot de passe à l\'utilisateur courant', 'data_types', 'medical', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'global:\r\n  noFormTags: true\r\nstructure:\r\n  row1:\r\n    col1:\r\n      size: col\r\n      bloc:\r\n        - password,required                        		#1789 Mot de passe', '', '', '', '');

-- base url Orthanc API : protocol et port
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('dicomProtocol', 'default', '0', '', 'DICOM', 'texte', 'http:// ou https:// ', 'http://');
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('dicomPort', 'default', '0', '', 'DICOM', 'nombre', 'port de l\'API Orthanc (défaut 8042)', '8042');

-- ajout valeur défaut data_types.creationDate
ALTER TABLE `data_types` CHANGE `creationDate` `creationDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- correction dataset pour forms
UPDATE forms set dataset = 'data_types' where dataset = 'form_basic_types';
