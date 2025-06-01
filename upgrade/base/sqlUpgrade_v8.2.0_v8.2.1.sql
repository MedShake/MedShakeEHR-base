-- Mise à jour n° de version
UPDATE `system` SET `value`='v8.2.1' WHERE `name`='base' and `groupe`='module';
-- Ajout de la configuration pour les modèles de factures
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`)
VALUES ('templateFacture', 'default', 0, '', 'Modèles de documents', 'fichier', 'template pour factures', 'facture.html.twig');