-- Mise à jour n° de version
UPDATE `system` SET `value`='v6.6.0' WHERE `name`='base' and `groupe`='module';

ALTER TABLE `system` CHANGE `groupe` `groupe` ENUM('system','module','cron','lock','plugin') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'system';
