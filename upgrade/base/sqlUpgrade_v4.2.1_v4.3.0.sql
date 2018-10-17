
-- Ajustements pour bon fonctionnement autocomplete
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'data-acTypeID=2:1', 'data-acTypeID=lastname:birthname');
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'data-acTypeID=3:22:230:235:241', 'data-acTypeID=firstname:othersfirstname');
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'data-acTypeID=11:55', 'data-acTypeID=street:rueAdressePro');
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'data-acTypeID=12:56', 'data-acTypeID=city:villeAdressePro');
UPDATE `forms` SET `yamlStructureDefaut` = yamlStructure;

-- Support pour les documents à signer
INSERT INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('courrier', 'catModelesDocASigner', 'Documents à signer', 'documents à envoyer à la signature numérique', 'base', 1, '2018-01-01 00:00:00');

-- Mise à jour n° de version
UPDATE `system` SET `value`='v4.3.0' WHERE `name`='base' and `groupe`='module';
