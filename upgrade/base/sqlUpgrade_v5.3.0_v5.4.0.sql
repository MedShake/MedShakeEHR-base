-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.4.0' WHERE `name`='base' and `groupe`='module';

-- Droits
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitExportPeutExporterPropresData', 'default', '0', '', 'Droits', 'true/false', 'si true, peut exporter ses propres datas', 'true');
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitExportPeutExporterAutresData', 'default', '0', '', 'Droits', 'true/false', 'si true, peut exporter les datas générées par les autres praticiens', 'false');
INSERT INTO `configuration` ( `name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitStatsPeutVoirStatsGenerales', 'default', '0', '', 'Droits', 'true/false', 'si true, peut voir les statistiques générales', 'true');
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutCreerPraticien', 'default', '0', '', 'Droits', 'true/false', 'si true, peut créer des dossiers praticiens', 'true');
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutVoirTousPatients', 'default', '0', '', 'Droits', 'true/false', 'si true, peut voir tous les dossiers créés par les autres praticiens', 'true');
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutSupPraticien', 'default', '0', '', 'Droits', 'true/false', 'si true, peut supprimer des dossiers praticiens (non définitivement)', 'true');
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutSupPatient', 'default', '0', '', 'Droits', 'true/false', 'si true, peut supprimer des dossiers patients (non définitivement)', 'true');
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutRetirerPraticien', 'default', '0', '', 'Droits', 'true/false', 'si true, peut retirer le statut praticien à un dossier (retour à patient, réciproque de droitDossierPeutCreerPraticien)', 'true');

-- Nouveaux params configuration
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('statsExclusionPatients', 'default', '0', '', 'Statistiques', 'liste', 'liste des ID des dossiers tests à exclure des statistiques ', '');
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('statsExclusionCats', 'default', '0', '', 'Statistiques', 'liste', 'liste des noms des catégories de formulaires à exclure des statistiques ', 'catTypeCsATCD,csAutres,declencheur');
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeAdminActiverLiensRendreUtilisateur', 'default', '0', '', 'Options', 'true/false', 'si true, l\'administrateur peut transformer des patients ou praticiens en utilisateur via les listings publiques', 'false');
