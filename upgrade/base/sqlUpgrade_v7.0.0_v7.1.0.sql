-- Mise à jour n° de version
UPDATE `system` SET `value`='v7.1.0' WHERE `name`='base' and `groupe`='module';


INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('designTopMenuTooltipDisplay', 'default', '0', '', 'Ergonomie et design', 'true/false', 'si true, affiche les infos bulles sur icones du menu supérieur', 'false');
