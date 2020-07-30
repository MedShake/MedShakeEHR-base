-- Mise à jour n° de version
UPDATE `system` SET `value`='v6.6.1' WHERE `name`='base' and `groupe`='module';

-- Ajout d'un option pour n'evoyer des rappel sms que pour certains types de consultation
-- Ajout pour activer ou non la génération des codes bare rpps et adeli
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES
('smsTypeRdvPourRappel', 'default', '0', '', 'Rappels SMS', 'vide/text', 'N\'envoyer les rappels SMS qui pour les types de rendez-vous listé (placer les types de RDV entre "[]" et séparé par des virgules, ex : "[C1],[C2]"), laisser vide pour envoyer des rappel pour tout type de rendez-vous.', ''),
('activGenBarreCode', 'default', '0', '', 'Options', 'true/false', 'Activer ou non la fonctionalité permettant de générer les codes barres RPPS et ADELI.', 'true');
