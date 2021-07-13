-- Mise à jour n° de version
UPDATE `system` SET `value`='v7.1.2' WHERE `name`='base' and `groupe`='module';

-- data_types
SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='divers');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'clicRdvPatientId', 'ID patient', 'ID patient', 'ID patient', '', '', 'text', '', 'base', @catID, '1', '2018-01-01 00:00:00', '3600', '1');
