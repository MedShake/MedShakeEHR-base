-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.6.0' WHERE `name`='base' and `groupe`='module';

-- modification du label datatype pour label long
ALTER TABLE `data_types` CHANGE `label` `label` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

-- options formulaires
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('formFormulaireListingPatients', 'default', '0', '', 'Options', 'texte', 'nom du formulaire à utiliser pour le listing patients', 'baseListingPatients');
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('formFormulaireListingPraticiens', 'default', '0', '', 'Options', 'texte', 'nom du formulaire à utiliser pour le listing praticiens', 'baseListingPro');
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('formFormulaireNouveauPatient', 'default', '0', '', 'Options', 'texte', 'nom du formulaire à utiliser pour la création d\'un nouveau patient', 'baseNewPatient');
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('formFormulaireNouveauPraticien', 'default', '0', '', 'Options', 'texte', 'nom du formulaire à utiliser pour la création d\'un nouveau praticien', 'baseNewPro');
