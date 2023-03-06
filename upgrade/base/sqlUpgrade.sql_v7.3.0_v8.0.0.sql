-- Passage à InnoDB et ajustement des interclassements
SET @tablesList = 'actes,actes_base,actes_cat,agenda,agenda_changelog,bdpm_compositions,bdpm_conditions,bdpm_groupesGeneriques,bdpm_presentations,bdpm_specialites,bdpm_updates,configuration,data_cat,data_types,dicomTags,forms,forms_cat,hprim,inbox,motsuivi,objets_data,people,prescriptions,prescriptions_cat,printed,system,transmissions,transmissions_to,univtags_join,univtags_tag,univtags_type';

DROP PROCEDURE IF EXISTS iterate_tablesList;
DELIMITER $$
CREATE PROCEDURE iterate_tablesList()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE tableName VARCHAR(255);
    DECLARE tableCount INT;

    SET @tablesList = CONCAT(@tablesList, ',');
    SET tableCount = LENGTH(@tablesList) - LENGTH(REPLACE(@tablesList, ',', ''));

    WHILE i <= tableCount DO
        SET tableName = SUBSTRING_INDEX(SUBSTRING_INDEX(@tablesList, ',', i), ',', -1);

        SET @sql = CONCAT('ALTER TABLE ', tableName, ' ENGINE=InnoDB;');
        EXECUTE IMMEDIATE @sql;

        SET @sql = CONCAT('ALTER TABLE ', tableName, ' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;');
        EXECUTE IMMEDIATE @sql;

        SET i = i + 1;
    END WHILE;
END$$
DELIMITER ;

CALL iterate_tablesList();

-- Corrections table actes
ALTER TABLE `actes` CHANGE `label` `label` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `actes` CHANGE `details` `details` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `actes` CHANGE `fromID` `fromID` SMALLINT(5) UNSIGNED NULL DEFAULT NULL; 

-- Corrections table actes_base
ALTER TABLE `actes_base` CHANGE `code` `code` VARCHAR(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

-- Corrections configuration
ALTER TABLE `configuration` CHANGE `name` `name` VARCHAR(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

-- Corrections data_type
ALTER TABLE `data_types` CHANGE `cat` `cat` SMALLINT(5) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `data_types` CHANGE `name` `name` VARCHAR(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

-- Corrections forms
ALTER TABLE `forms` CHANGE `internalName` `internalName` VARCHAR(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `forms` CHANGE `name` `name` VARCHAR(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `forms` CHANGE `description` `description` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;


-- Mise à jour n° de version
UPDATE `system` SET `value`='v8.0.0' WHERE `name`='base' and `groupe`='module';