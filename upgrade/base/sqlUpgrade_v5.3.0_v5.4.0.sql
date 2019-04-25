-- modifications de la table forms
ALTER TABLE `forms` ADD `exportData` ENUM('non','oui') NOT NULL DEFAULT 'non' AFTER `type`;

-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.4.0' WHERE `name`='base' and `groupe`='module';
