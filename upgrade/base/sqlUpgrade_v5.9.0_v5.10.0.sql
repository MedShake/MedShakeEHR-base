ALTER TABLE `agenda` ADD `attente` ENUM('non','oui') NOT NULL DEFAULT 'non' AFTER `absente`;
ALTER TABLE `agenda_changelog` CHANGE `operation` `operation` ENUM('edit','move','delete','missing','waiting') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
