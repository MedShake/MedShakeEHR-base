-- Mise à jour n° de version
UPDATE `system` SET `value`='v6.2.0' WHERE `name`='base' and `groupe`='module';

-- Ajustement taille pour label de l'acte
ALTER TABLE `actes_base` CHANGE `label` `label` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
