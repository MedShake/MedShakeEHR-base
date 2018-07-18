
-- Retrait des placeholders
update data_types set placeholder="", description="poids du patient en kg" where name="poids";
update data_types set placeholder="", description="taille du patient en cm" where name="taillePatient";
update data_types set placeholder="", description="clairance de la créatinine en mL/min" where name="clairanceCreatinine";
update data_types set placeholder="" where name="imc";

-- Ajout du secteur plaine / montagne pour tarification IK
INSERT IGNORE INTO `configuration`(`name`, `cat`, `level`, `type`, `description`, `value`) VALUES ('administratifSecteurIK', 'Options', 'default', 'texte', 'tarification des IK : indiquer plaine ou montagne', 'plaine');

-- Modification de la table actes_base pour détails sur actes ccam
ALTER TABLE `actes_base` ADD `C` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `E` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `D` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `R` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `M` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `S` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `P` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `F` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `U` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;

-- Modification pour gestion des modificateurs CCAM
ALTER TABLE `actes_base` CHANGE `type` `type` ENUM('NGAP','CCAM','Libre','mCCAM') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'CCAM';
ALTER TABLE `actes_base` ADD `tarifUnit` ENUM('euro','pourcent') NOT NULL DEFAULT 'euro' AFTER `tarifs2`;

-- Ajout d'un champ pour détails de la facture du règlement
SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='reglementItems');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('reglement', 'regleDetailsActes', '', 'Détails des actes', 'détails des actes de la facture', '', '', 'text', '', 'base', @catID, 1, '2018-07-04 15:31:47', 1576800000, 1);

-- Mise à jour n° de version
UPDATE `system` SET `value`='v4.1.0' WHERE `name`='base' and `groupe`='module';
