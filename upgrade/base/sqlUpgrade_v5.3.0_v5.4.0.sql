-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.4.0' WHERE `name`='base' and `groupe`='module';

-- Droits
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ( 'droitExportPeutExporterPropresData', 'default', '0', '', 'Droits', 'true/false', 'si true, peut exporter ses propres datas', 'true');
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ( 'droitExportPeutExporterAutresData', 'default', '0', '', 'Droits', 'true/false', 'si true, peut exporter les datas générées par les autres praticiens', 'false');
