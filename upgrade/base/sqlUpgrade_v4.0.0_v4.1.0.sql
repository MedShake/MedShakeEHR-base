-- Procédure pour créer index manquant

DELIMITER $$

DROP PROCEDURE IF EXISTS `CreerIndexMsEHR` $$
CREATE PROCEDURE `CreerIndexMsEHR`
(
    given_type     VARCHAR(64),
    given_table    VARCHAR(64),
    given_index    VARCHAR(64),
    given_columns  VARCHAR(64)
)
BEGIN

    DECLARE IndexIsThere INTEGER;

    SELECT COUNT(1) INTO IndexIsThere
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE table_name   = given_table
    AND   index_name   = given_index;

    IF IndexIsThere = 0 THEN
        IF given_type = 'unique' THEN
          SET @sqlstmt = CONCAT('CREATE UNIQUE INDEX ',given_index,' ON ',given_table,' (',given_columns,')');
        END IF;
        IF given_type = 'index' THEN
          SET @sqlstmt = CONCAT('CREATE INDEX ',given_index,' ON ',given_table,' (',given_columns,')');
        END IF;
        PREPARE st FROM @sqlstmt;
        EXECUTE st;
        DEALLOCATE PREPARE st;
    END IF;

END $$

DELIMITER ;

-- créer index sur le name de forms_cat si besoin
call CreerIndexMsEHR('unique','forms_cat','name','name');

-- créer index sur name de prescriptions_cat si besoin
call CreerIndexMsEHR('unique','prescriptions_cat','name','name');

DROP PROCEDURE IF EXISTS `CreerIndexMsEHR`;

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
