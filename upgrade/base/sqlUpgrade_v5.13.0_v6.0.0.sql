-- Mise à jour n° de version
UPDATE `system` SET `value`='v6.0.0' WHERE `name`='base' and `groupe`='module';
-- login double facteur authentification

ALTER TABLE `people` ADD `secret2fa` VARBINARY(1000) NULL AFTER `pass`;

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeLogin2FA', 'default', '0', '', 'Options', 'true/false', 'si true, activation du login à double facteur d\'authentification', 'false');

INSERT IGNORE INTO `form_basic_types` (`name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `type`, `cat`, `fromID`, `creationDate`, `deleteByID`, `deleteDate`) VALUES ('otpCode', 'code otp', 'code otp', 'code otp', '', 'Le code otp est manquant', 'text', '', 'base', '0', '1', '2018-01-01 00:00:00', '0', '2018-01-01 00:00:00');

UPDATE forms set `yamlStructure` = 'global:\r\n  formClass: \'form-signin\' \r\nstructure:\r\n row1:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - username,required,nolabel                  		#1    Identifiant\n      - password,required,nolabel                  		#2    Mot de passe\n      - otpCode,nolabel                            		#7    code otp\n      - submit,Connexion,class=btn-primary,class=btn-block 		#3    Valider' where `internalName`='baseLogin';

-- révision du form utilisateur modification password
UPDATE `forms` set `name`='Changement mot de passe utilisateur', `description`='Changement mot de passe utilisateur',  `yamlStructure` = 'structure:\r\n row1:\r\n  col1: \r\n    size: col-12\r\n    bloc:\r\n      - currentPassword,required                   		#6    Mot de passe actuel\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe' where `internalName` = 'baseUserParametersPassword';
