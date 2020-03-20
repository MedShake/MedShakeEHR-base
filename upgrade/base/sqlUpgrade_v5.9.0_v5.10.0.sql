-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.10.0' WHERE `name`='base' and `groupe`='module';

ALTER TABLE `agenda` ADD `attente` ENUM('non','oui') NOT NULL DEFAULT 'non' AFTER `absente`;
ALTER TABLE `agenda_changelog` CHANGE `operation` `operation` ENUM('edit','move','delete','missing','waiting') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `agenda` ADD INDEX (`start`, `userid`);

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('agendaRefreshDelayMenuPOTD', 'default', '0', '', 'Agenda', 'nombre', 'délai en secondes du rafraîchissement du menu Patients du jour - 0 pour jamais', '5');

-- Envoyer l'agenda chiffré
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('agendaEnvoyerChiffreParMail', 'default', '0', '', 'Agenda', 'true/false', 'activer le service d\'envoi par mail de l\'agenda futur chiffré GPG', 'false');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('agendaEnvoyerChiffreTo', 'default', '0', '', 'Agenda', 'texte', 'adresse email à laquelle envoyer l\'agenda chiffré GPG - séparer par virgule si plusieurs ', '');

-- gestion clef GPG par people
-- data_types
SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='contact');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'pgpPublicKey', '', 'Clef publique PGP', 'Clef publique PGP', '', '', 'textarea', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '14');
-- forms
SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='patientforms');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'basePeopleComplement', 'Formulaire patient/pro complémentaire', 'formulaire patient/pro complémentaire', 'data_types', 'admin', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'structure:\r\n  row1:                              \r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n        - pgpPublicKey,rows=20,class={text-monospace} 		#1787 Clef publique PGP', '', '', '', '');
