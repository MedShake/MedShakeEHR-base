-- Correction table printed 
ALTER TABLE `printed` CHANGE `type` `type` ENUM('cr','ordo','courrier','ordoLAP','ordoLapExt','doc','reglement') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'cr'; 

-- Mise à jour n° de version
UPDATE `system` SET `value`='v7.3.2' WHERE `name`='base' and `groupe`='module';
