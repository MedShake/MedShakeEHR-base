ALTER TABLE `inbox` CHANGE `txtNumOrdre` `txtNumOrdre` INT(9) UNSIGNED NOT NULL; 


INSERT INTO `configuration` (`id`, `name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES (NULL, 'apicrypt2CertName', 'default', '0', '', 'Apicrypt', 'texte', 'nom du certificat à utiliser', '');
INSERT INTO `configuration` (`id`, `name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES (NULL, 'apicrypt2CertPassword', 'default', '0', '', 'Apicrypt', 'texte', 'mot de passe du certificat', '');
INSERT INTO `configuration` (`id`, `name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES (NULL, 'apicryptVersion', 'default', '0', '', 'Apicrypt', 'texte', 'version d\'Apicrypt à mettre en œuvre ', '1');