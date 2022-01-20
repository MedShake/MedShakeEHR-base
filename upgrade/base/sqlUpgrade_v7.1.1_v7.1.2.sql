-- Mise à jour n° de version
UPDATE `system` SET `value`='v7.1.2' WHERE `name`='base' and `groupe`='module';

---------------------------
-- Ajout du système de tags
---------------------------

-- Création de la table tags
CREATE TABLE IF NOT EXISTS `univtags_tag` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`typeID` INT UNSIGNED NOT NULL,
	`name` VARCHAR(64) NOT NULL,
	`description` VARCHAR(256),
	`color` VARCHAR(7) NOT NULL DEFAULT '#B6B6B6',
	PRIMARY KEY (`id`),
	KEY `typeID` (`typeID`),
	CONSTRAINT UniqUnivTagTypeName UNIQUE(`typeID`, `name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Création de la table pour les type de tags
CREATE TABLE IF NOT EXISTS `univtags_type` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(64) NOT NULL,
	`description` VARCHAR(255) NOT NULL,
	`actif` BOOLEAN NOT NULL DEFAULT true,
	`droitCreSup` VARCHAR(128) NOT NULL,
	`droitAjoRet` VARCHAR(128) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Table de jointure tags <->  patient (tags pour un dossier médical)
CREATE TABLE IF NOT EXISTS `univtags_join` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`tagID` INT NOT NULL,
	`toID` INT NOT NULL,
	PRIMARY KEY (`id`),
	KEY `tagID` (`tagID`),
	KEY `toID` (`toID`),
	CONSTRAINT UniqUnivTagsJoin UNIQUE(`tagID`, `toID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Ajoute le type de tag pour le dossier patient
INSERT IGNORE INTO `univtags_type` (`name`, `description`, `droitCreSup`, `droitAjoRet`) VALUES ('patients', 'Étiquettes pour catégoriser le dossier médical d\'un patient', 'droitUnivTagPatientPeutCreerSuprimer', 'droitUnivTagPatientPeutAjouterRetirer');
INSERT IGNORE INTO `univtags_type` (`name`, `description`, `droitCreSup`, `droitAjoRet`) VALUES ('pros', 'Étiquettes pour catégoriser un fiche pro.', 'droitUnivTagProPeutCreerSuprimer', 'droitUnivTagProPeutAjouterRetirer');

-- Ajoute de nouvelle option de de configuration pour les tags universelle
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES
('optionGeActiverUnivTags', 'default', '0', '', 'Activation services', 'true/false', 'activier / désactiver l\'utilisation des tags universel', 'true'),
('droitUnivTagPatientPeutAjouterRetirer', 'default', '0', '', 'Droits', 'true/false', 'peut ajouter ou retirer une étiquette sur un dossier patient', 'true'),
('droitUnivTagPatientPeutCreerSuprimer', 'default', '0', '', 'Droits', 'true/false', 'peut créer et supprimer des étiquettes pour les dossier patients', 'true'),
('droitUnivTagProPeutAjouterRetirer', 'default', '0', '', 'Droits', 'true/false', 'peut ajouter ou retirer une étiquette sur un pro', 'true'),
('droitUnivTagProPeutCreerSuprimer', 'default', '0', '', 'Droits', 'true/false', 'peut créer et supprimer des étiquettes pour les pro', 'true');
