-- Mise à jour n° de version
UPDATE `system` SET `value`='v7.1.2' WHERE `name`='base' and `groupe`='module';

-- création de la table motsuivi
CREATE TABLE IF NOT EXISTS `motsuivi` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fromID` INT(11) UNSIGNED NOT NULL,
  `toID` INT(11) UNSIGNED NOT NULL,
  `dateTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `texte` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fromID` (`fromID`),
  KEY `toID` (`toID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- actout des options relative au mot de suivi
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionsDossierPatientActiverMotSuivi', 'default', '0', '', 'Options dossier patient', 'true/false', 'activier / désactiver le mot suivi sur le dossier d\'un patient', 'false');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionsDossierPatientNbMotSuiviAfficher', 'default', '0', '', 'Options dossier patient', 'int', 'nombre de mot suivi à afficher par défaut sur un dossier patient', '6');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitMotSuiviPeutModifierSuprimerDunAutre', 'default', '0', '', 'Droits', 'true/false', 'si coché, l\'utilisateur peut suprimer et modifier un mot de suivi crée par un autre', 'false');
