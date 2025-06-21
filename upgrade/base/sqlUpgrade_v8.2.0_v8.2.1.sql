-- Mise à jour n° de version
UPDATE `system` SET `value`='v8.2.1' WHERE `name`='base' and `groupe`='module';

-- Fix l'icône patient of the day
UPDATE  `configuration` SET `value` = '- agenda\n- potd\n- patients\n- praticiens\n- groupes\n- registres\n- compta\n- inbox\n- dropbox\n- transmissions\n- outils' WHERE `name` = 'designTopMenuSections';

-- Ajout de la configuration pour les modèles de factures
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`)
VALUES ('templateFacture', 'default', 0, '', 'Modèles de documents', 'fichier', 'template pour factures', 'facture.html.twig');