-- Mise à jour n° de version
UPDATE `system` SET `value`='v6.1.0' WHERE `name`='base' and `groupe`='module';

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catTypesUsageSystem');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('system', 'ageCalcule', '', 'Age calculé', 'Age calculé (formulaire d\'affichage)', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');
