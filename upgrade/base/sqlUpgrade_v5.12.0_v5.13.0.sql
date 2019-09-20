-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.13.0' WHERE `name`='base' and `groupe`='module';

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('theriaquePgDbName', 'default', '0', '', 'LAP', 'texte', 'nom de la base postgre', '');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('theriaquePgDbUser', 'default', '0', '', 'LAP', 'texte', 'nom d\'utilisateur postgre', '');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('theriaquePgDbPassword', 'default', '0', '', 'LAP', 'texte', 'mot de passe postgre', '');
