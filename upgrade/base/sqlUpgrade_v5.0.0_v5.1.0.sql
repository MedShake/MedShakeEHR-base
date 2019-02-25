-- Click2call

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES
('ovhApplicationKey', 'default', 0, 'base', 'Click2call', 'string', 'OVH Application Key', NULL),
('ovhApplicationSecret', 'default', 0, 'base', 'Click2call', 'string', 'OVH Application Secret', NULL),
('ovhConsumerKey', 'default', 0, 'base', 'Click2call', 'string', 'OVH Consumer Key', NULL),
('ovhTelecomBillingAccount', 'default', 0, 'base', 'Click2call', 'string', 'Informations sur la ligne > Nom du groupe', NULL),
('ovhTelecomServiceName', 'default', 0, 'base', 'Click2call', 'string', 'Numéro de la ligne au format international 0033xxxxxxxxxx', NULL),
('ovhTelecomCallingNumber', 'default', 0, 'base', 'Click2call', 'string', 'Numéro de l\'appelant au format international 0033xxxxxxxxxx', NULL),
('ovhTelecomIntercom', 'default', 0, 'base', 'Click2call', 'true/false', 'Activer le mode intercom', NULL),
('click2callService', 'default', 0, 'base', 'Click2call', 'string', 'nom du service Click2call à activer (OVH)', NULL);

UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- mobilePhone', '- mobilePhone,click2call') where internalName='baseListingPatients';
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- homePhone', '- homePhone,click2call') where internalName='baseListingPatients';
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- telPro', '- telPro,click2call') where internalName='baseListingPro';

-- formulaire fax (on passe à 2 lignes vs 2 cols)
UPDATE `forms` SET `yamlStructure` = 'structure:\r\n row1:\r\n  col1: \r\n    size: col\r\n    bloc: \r\n      - mailToEcofaxName,required  \n row2:\r\n   col1: \r\n    size: col\r\n    bloc: \r\n      - mailToEcofaxNumber,required   ' where internalName='baseFax';

-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.1.0' WHERE `name`='base' and `groupe`='module';
