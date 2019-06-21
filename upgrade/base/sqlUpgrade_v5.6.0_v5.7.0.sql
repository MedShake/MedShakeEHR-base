-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.7.0' WHERE `name`='base' and `groupe`='module';

ALTER TABLE `actes` ADD `active` ENUM('oui','non') NOT NULL DEFAULT 'oui' AFTER `creationDate`;
