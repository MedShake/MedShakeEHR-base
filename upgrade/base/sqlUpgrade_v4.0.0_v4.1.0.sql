
-- Retrait des placeholders
update data_types set placeholder="", description="poids du patient en kg" where name="poids";
update data_types set placeholder="", description="taille du patient en cm" where name="taillePatient";
update data_types set placeholder="", description="clairance de la créatinine en mL/min" where name="clairanceCreatinine";
update data_types set placeholder="" where name="imc";

-- Ajout du secteur plaine / montagne pour tarification IK
INSERT IGNORE INTO `configuration`(`name`, `cat`, `level`, `type`, `description`, `value`) VALUES ('administratifSecteurIK', 'Options', 'default', 'texte', 'tarification des IK : indiquer plaine ou montagne', 'plaine');

-- Modification de la table actes_base pour détails sur actes ccam
ALTER TABLE `actes_base` ADD `C` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `E` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `D` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `R` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `M` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `S` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `P` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `F` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
ALTER TABLE `actes_base` ADD `U` ENUM('false','true') NOT NULL DEFAULT 'false' AFTER `tarifs2`;
