-- Mise à jour n° de version
UPDATE `system` SET `value`='v6.3.0' WHERE `name`='base' and `groupe`='module';

INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('utiliserLapExterne', 'default', '0', '', 'LAP', 'true/false', 'activer / désactiver l\'utilisation d\'un LAP externe', 'false');

INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('utiliserLapExterneName', 'default', '0', '', 'LAP', 'texte', 'nom du LAP externe', '');
ALTER TABLE `printed` CHANGE `type` `type` ENUM('cr','ordo','courrier','ordoLAP','ordoLapExt') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'cr';

UPDATE `data_types` SET `formType` = 'switch' WHERE `name` LIKE 'allaitementActuel';


INSERT IGNORE INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('ordo', 'lapExterne', 'LAP Externe', '', 'base', '1', '2019-01-01 00:00:00');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapExterne');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'lapExtOrdonnance', '', 'Porteur', '', '', '', 'number', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='dataBio');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'creatinineMgL', '', 'Créatinine', 'créatinine en mg/l', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('medical', 'creatinineMicroMolL', '', 'Créatinine', 'créatinine en μmol/l', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');
