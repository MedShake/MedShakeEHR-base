-- Tables pour Transmissions

CREATE TABLE `transmissions` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `fromID` mediumint(6) unsigned DEFAULT NULL,
 `aboutID` int(6) unsigned DEFAULT NULL,
 `sujetID` int(10) unsigned DEFAULT NULL,
 `statut` enum('open','deleted') NOT NULL DEFAULT 'open',
 `priorite` tinyint(3) unsigned DEFAULT NULL,
 `registerDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updateDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 `sujet` varchar(255) DEFAULT NULL,
 `texte` text,
 PRIMARY KEY (`id`),
 KEY `fromID` (`fromID`),
 KEY `aboutID` (`aboutID`),
 KEY `sujetID` (`sujetID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `transmissions_to` (
 `sujetID` int(7) unsigned NOT NULL,
 `toID` mediumint(6) unsigned NOT NULL,
 `destinataire` enum('oui','non') NOT NULL DEFAULT 'non',
 `statut` enum('open','checked','deleted') NOT NULL DEFAULT 'open',
 `dateLecture` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`sujetID`,`toID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Paramètres de configuration pour les transmissions
INSERT IGNORE INTO `configuration`(`name`, `cat`, `level`, `type`, `description`, `value`) VALUES
('transmissionsDefautDestinataires', 'Transmissions', 'default', 'liste', 'ID des utilisateurs, séparés par des virgules (sans espace)', ''),
('transmissionsPeutVoir', 'Transmissions', 'default', 'true/false', 'peut accéder aux transmissions', 'true'),
('transmissionsPeutCreer', 'Transmissions', 'default', 'true/false', 'peut créer des transmissions', 'true'),
('transmissionsPeutRecevoir', 'Transmissions', 'default', 'true/false', 'peut recevoir des transmissions', 'true'),
('transmissionsNbParPage', 'Transmissions', 'default', 'nombre entier', 'nombre de transmissions par page', '30'),
('transmissionsPurgerNbJours', 'Transmissions', 'default', 'nombre entier', 'nombre de jours sans update après lequel une transmission sera supprimée de la base de données (0 = jamais)', '365');

-- Mise à jour n° de version
UPDATE `system` SET `value`='v4.2.0' WHERE `name`='base' and `groupe`='module';
