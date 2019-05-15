-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.4.0' WHERE `name`='base' and `groupe`='module';

-- Droits
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitExportPeutExporterPropresData', 'default', '0', '', 'Droits', 'true/false', 'si true, peut exporter ses propres datas', 'true');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitExportPeutExporterAutresData', 'default', '0', '', 'Droits', 'true/false', 'si true, peut exporter les datas générées par les autres praticiens', 'false');
INSERT IGNORE INTO `configuration` ( `name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitStatsPeutVoirStatsGenerales', 'default', '0', '', 'Droits', 'true/false', 'si true, peut voir les statistiques générales', 'true');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutCreerPraticien', 'default', '0', '', 'Droits', 'true/false', 'si true, peut créer des dossiers praticiens', 'true');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutVoirTousPatients', 'default', '0', '', 'Droits', 'true/false', 'si true, peut voir tous les dossiers créés par les autres praticiens', 'true');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutSupPraticien', 'default', '0', '', 'Droits', 'true/false', 'si true, peut supprimer des dossiers praticiens (non définitivement)', 'true');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutSupPatient', 'default', '0', '', 'Droits', 'true/false', 'si true, peut supprimer des dossiers patients (non définitivement)', 'true');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutRetirerPraticien', 'default', '0', '', 'Droits', 'true/false', 'si true, peut retirer le statut praticien à un dossier (retour à patient, réciproque de droitDossierPeutCreerPraticien)', 'true');

-- Nouveaux params configuration
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('statsExclusionPatients', 'default', '0', '', 'Statistiques', 'liste', 'liste des ID des dossiers tests à exclure des statistiques ', '');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('statsExclusionCats', 'default', '0', '', 'Statistiques', 'liste', 'liste des noms des catégories de formulaires à exclure des statistiques ', 'catTypeCsATCD,csAutres,declencheur');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeAdminActiverLiensRendreUtilisateur', 'default', '0', '', 'Options', 'true/false', 'si true, l\'administrateur peut transformer des patients ou praticiens en utilisateur via les listings publiques', 'false');

-- corrections data types manquants par duplication d'ID dans SQL install
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('relation', 'allergieCodeTheriaque', '', 'Code Thériaque de l\'allergie', 'codee Thériaque de l\'allergie', '', '', 'text', '', 'base', 71, 1, '2018-01-01 00:00:00', 3600, 0),
('ordo', 'lapMedicamentEstPrescriptibleEnDC', '', 'Médicament prescriptible en DC', 'médicament prescriptible en DC', '', '', '', '', 'base', 75, 1, '2018-01-01 00:00:00', 3600, 1),
('medical', 'freqCardiaque', '', 'FC', 'fréquence cardiaque en bpm', '', '', 'text', '', 'base', 28, 1, '2018-05-14 13:41:42', 60, 1),
('medical', 'spO2', '', 'SpO2', 'saturation en oxygène', '', '', 'text', '', 'base', 28, 1, '2018-05-15 10:08:20', 60, 1);
