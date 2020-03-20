-- modifications de la table actes_base
ALTER TABLE `actes_base` ADD `codeProf` VARCHAR(7) NULL AFTER `phase`;
UPDATE `actes_base` set codeProf='mspe' where type='NGAP';
ALTER TABLE `actes_base` DROP INDEX `code`, ADD UNIQUE `code` (`code`, `activite`, `phase`, `type`, `codeProf`) USING BTREE;

-- modification de la config
UPDATE `configuration` SET `name` = 'administratifSecteurHonorairesCcam' WHERE `name` = 'administratifSecteurHonoraires';
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ( 'administratifSecteurHonorairesNgap', 'default', '0', '', 'Règlements', 'texte', 'Code profession pour le secteur tarifaire NGAP', 'mspe');

-- ajout de regleSecteurHonorairesNgap 
SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='reglementItems');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('reglement', 'regleSecteurHonorairesNgap', '', 'Secteur tarifaire NGAP', 'secteur tarifaire NGAP appliqué', '', '', 'text', '', 'base', @catID, 0, '2019-03-01 10:24:47', 1576800000, 1);

-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.3.0' WHERE `name`='base' and `groupe`='module';
