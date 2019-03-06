ALTER TABLE `data_types` CHANGE `formType` `formType` ENUM('','date','email','number','select','submit','tel','text','textarea','password','checkbox','hidden','range','radio','reset','switch') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';

-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.2.0' WHERE `name`='base' and `groupe`='module';
