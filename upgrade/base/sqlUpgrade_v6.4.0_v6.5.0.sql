-- Mise à jour n° de version
UPDATE `system` SET `value`='v6.5.0' WHERE `name`='base' and `groupe`='module';

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('dropboxActiver', 'default', '0', '', 'Dropbox', 'true/false', 'permet d\'activer les fonctions de dropbox externe', 'false');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('dropboxOptions', 'default', '0', '', 'Dropbox', 'textarea', 'options pour les fonctions de dropbox externe', '');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('designTopMenuDropboxCountDisplay', 'default', '0', '', 'Ergonomie et design', 'true/false', 'afficher dans le menu de navigation du haut de page le nombre de fichier dans la boite de dépôt', 'true');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='ordoItems');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES ('ordo', 'ordoImpressionNbLignes', '', 'Imprimer le nombre de lignes de prescription', 'imprimer le nombre de lignes de prescription', '', '', '', '', 'base', @catID, '1', '2020-01-01 00:00:00', '3600', '1');
