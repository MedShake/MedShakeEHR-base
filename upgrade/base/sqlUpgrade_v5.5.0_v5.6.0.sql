-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.6.0' WHERE `name`='base' and `groupe`='module';

-- modification du label datatype pour label long
ALTER TABLE `data_types` CHANGE `label` `label` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
