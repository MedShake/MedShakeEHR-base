-- Modifications table actes_base
ALTER TABLE `actes_base` ADD `activite` TINYINT(1) NOT NULL DEFAULT '1' AFTER `code`, ADD `phase` TINYINT(1) NOT NULL DEFAULT '0' AFTER `activite`;
ALTER TABLE `actes_base` DROP COLUMN tarifs1, DROP COLUMN tarifs2, DROP COLUMN F, DROP COLUMN P, DROP COLUMN S, DROP COLUMN M, DROP COLUMN R, DROP COLUMN D, DROP COLUMN E, DROP COLUMN C, DROP COLUMN U;
ALTER TABLE `actes_base` DROP INDEX `code`;
ALTER TABLE `actes_base` ADD UNIQUE (`code`, `activite`, `phase`, `type`);

-- Zone géographique tarifaire
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ( 'administratifSecteurGeoTarifaire', 'default', '0', '', 'Règlements', 'dossier', 'zone géographique tarifaire (metro, 971, 972 ...)', 'metro');

-- Configuration : liste des formulaires de règlement dispo
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ( 'administratifReglementFormulaires', 'default', '0', '', 'Règlements', 'liste', 'liste des formulaires de règlement disponible dans le dossier patient ', 'reglePorteurS1,reglePorteurS2,reglePorteurLibre');

-- Paramètres de configuration : API CCAM NGAP
INSERT IGNORE INTO `configuration`(`name`, `cat`, `level`, `type`, `description`, `value`) VALUES
('apiCcamNgapUrl', 'Règlements', 'default', 'url', 'URL de l\'API CCAM NGAP MedShake', '');
INSERT IGNORE INTO `configuration`(`name`, `cat`, `level`, `type`, `description`, `value`) VALUES
('apiCcamNgapKey', 'Règlements', 'default', 'string', 'Clef de l\'API CCAM NGAP MedShake', '');

-- Data types pour précision règlement
SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='reglementItems');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('reglement', 'regleSecteurHonoraires', '', 'Secteur tarifaire', 'secteur tarifaire appliqué', '', '', 'text', '', 'base', @catID, 0, '2018-12-03 15:02:36', 1576800000, 1),
('reglement', 'regleSecteurIK', '', 'Secteur tarifaire pour IK', 'secteur tarifaire IK appliqué', '', '', 'text', '', 'base', @catID, 0, '2018-12-03 15:03:20', 1576800000, 1),
('reglement', 'regleSecteurGeoTarifaire', '', 'Secteur géographique tarifaire', 'secteur géographique tarifaire', '', '', 'text', '', 'base', @catID, 0, '2018-12-03 15:04:12', 1576800000, 1);

-- changement intitulés règlements
update `data_types` set label='Règlement', description='Règlement conventionné S1' WHERE `name` = 'reglePorteurS1';
update `data_types` set label='Règlement', description='Règlement conventionné S2' WHERE `name` = 'reglePorteurS2';
update `data_types` set label='Règlement', description='Règlement hors convention' WHERE `name` = 'reglePorteurLibre';

-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.0.0' WHERE `name`='base' and `groupe`='module';
