-- Mise à jour n° de version
UPDATE `system` SET `value`='v7.0.0' WHERE `name`='base' and `groupe`='module';

-- people

ALTER TABLE `people` CHANGE `type` `type` ENUM('patient','pro','externe','service','deleted','groupe','destroyed','registre') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'patient';

-- configuration : groupes

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('formFormulaireListingGroupes', 'default', '0', '', 'Options', 'texte', 'nom du formulaire à utiliser pour le listing groupes', 'baseListingGroupes');

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitGroupePeutCreerGroupe', 'default', '0', '', 'Droits', 'true/false', 'si true, peut créer des groupes', 'false');

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('formFormulaireNouveauGroupe', 'default', '0', '', 'Options', 'texte', 'nom du formulaire à utiliser pour la création d\'un nouveau groupe', 'baseNewGroupe');

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeActiverGroupes', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de la gestion des groupes praticiens', 'false');

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('groupesNbMaxGroupesParPro', 'default', '0', '', 'Groupes', 'nombre', 'nombre maximal de groupes qu\'un praticien peut intégrer (0 = non limité)', '0');

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutVoirUniquementPatientsGroupes', 'default', '0', '', 'Droits', 'true/false', 'si true, peut voir tous les dossiers créés par les autres praticiens des groupes ', 'true');

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutVoirUniquementPraticiensGroupes', 'default', '0', '', 'Droits', 'true/false', 'si true, peut voir uniquement les praticiens appartenant aux mêmes groupes', 'true');

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES
('droitGroupePeutVoirTousGroupes', 'default', 0, '', 'Droits', 'true/false', 'si true, peut voir tous les groupes ', 'false');


-- configuration : changer de catégorie variables d'activation

UPDATE `configuration` set cat = 'Activation services' WHERE `name` in ('utiliserLap', 'utiliserLapExterne', 'dropboxActiver','mailRappelActiver','smsRappelActiver','vitaleActiver');

-- configuration : changement de nom de variable

update `configuration` set value = 'true' WHERE `name` LIKE 'droitDossierPeutVoirTousPatients' and value='false';
update `configuration` set name = 'droitDossierPeutVoirUniquementPatientsPropres' WHERE `name` LIKE 'droitDossierPeutVoirTousPatients' and value = 'true';
update `configuration` set value = 'false' WHERE `name` LIKE 'droitDossierPeutVoirTousPatients' and value='true';
update `configuration` set name = 'droitDossierPeutVoirUniquementPatientsPropres' WHERE `name` LIKE 'droitDossierPeutVoirTousPatients' and value = 'false';

update `configuration` set name = 'optionGeActiverDropbox' WHERE `name` LIKE 'dropboxActiver';
update `configuration` set name = 'optionGeActiverRappelsRdvMail' WHERE `name` LIKE 'mailRappelActiver';
update `configuration` set name = 'optionGeActiverRappelsRdvSMS' WHERE `name` LIKE 'smsRappelActiver';
update `configuration` set name = 'optionGeActiverVitaleLecture' WHERE `name` LIKE 'vitaleActiver';
update `configuration` set name = 'optionGeActiverLapExterne' WHERE `name` LIKE 'utiliserLapExterne';
update `configuration` set name = 'optionGeActiverLapInterne' WHERE `name` LIKE 'utiliserLap';

-- configuration : registres

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitRegistrePeutCreerRegistre', 'default', '0', '', 'Droits', 'true/false', 'si true, peut créer des registres', 'false');

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitRegistrePeutGererAdministrateurs', 'default', '0', '', 'Droits', 'true/false', 'si true, peut gérer les administrateurs registres', 'false');

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitRegistrePeutGererGroupes', 'default', '0', '', 'Droits', 'true/false', 'si true, peut gérer les groupes participant à un registre', 'false');

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeActiverRegistres', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de la gestion de registres', 'false');

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('formFormulaireListingRegistres', 'default', '0', '', 'Options', 'texte', 'nom du formulaire à utiliser pour le listing registres', 'baseListingRegistres');

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('formFormulaireNouveauRegistre', 'default', '0', '', 'Options', 'texte', 'nom du formulaire à utiliser pour la création d\'un nouveau registre', 'baseNewRegistre');

-- configuration : navbar découpée

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ( 'designTopMenuSections', 'default', '0', '', 'Ergonomie et design', 'textarea', 'éléments et ordre de la barre de navigation du menu supérieur (yaml : commenter avec #)', 'agenda\r\npatients\r\npraticiens\r\ngroupes\r\nregistres\r\ncompta\r\ninbox\r\ndropbox\r\ntransmissions\r\noutils');

-- configuration : nouveaux switch activation services

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeActiverAgenda', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de la gestion agenda', 'true');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeActiverCompta', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de la gestion compta', 'true');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeActiverInboxApicrypt', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de la inbox Apicrypt', 'true');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeActiverApiRest', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de l\'API REST', 'true');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeActiverTransmissions', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation des transmissions', 'true');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeActiverPhonecapture', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation de phonecapture (nécessite DICOM)', 'true');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeActiverDicom', 'default', '0', '', 'Activation services', 'true/false', 'si true, activation des fonctions liées au DICOM (nécessite Orthanc)', 'true');

-- configuration : redirection post créa praticien

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGePraticienOuvrirApresCreation', 'default', '0', '', 'Options', 'text', 'où rediriger après création d\'un nouveau praticien (fichepro/crealogin/creagrouplink)', 'fichepro');

-- configuration : catégorie pour les formulaires systèmes

UPDATE `configuration` set cat = 'Formulaires système' WHERE `name` like 'formFormulaire%';

-- configuration : catégorie pour les variables autour du login et nouvelles variables

UPDATE `configuration` set cat = 'Login' WHERE `name` like 'optionGeLogin%';

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeLoginCreationDefaultModule', 'default', '0', '', 'Login', 'texte', 'module par défaut pour création nouvel utilisateur', 'base');
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeLoginCreationDefaultTemplate', 'default', '0', '', 'Login', 'texte', 'template par défaut pour création nouvel utilisateur', '');

-- configuration : montrer patients connus sur fiche praticien

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGePraticienMontrerPatientsLies', 'default', '0', '', 'Options', 'true/false', 'si true, montrer les patients liés au praticien sur la fiche pro', 'true');

-- configuration : proposer création login en 1 clic sur fiche praticien

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutTransformerPraticienEnUtilisateur', 'default', '0', '', 'Droits', 'true/false', 'si true, peut rendre utilisateur un praticien', 'false');

-- configuration : peut assigner en 1 clic ses propres groupes à un praticien fils

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('droitDossierPeutAssignerPropresGroupesPraticienFils', 'default', '0', '', 'Droits', 'true/false', 'si true, peut assigner ses propres appartenances aux groupes à un praticien créé par lui-même', 'false');

-- configuration : activer la création automatique d'un peopleExportID

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeCreationAutoPeopleExportID', 'default', '0', '', 'Options', 'true/false', 'si true, création automatique d\'un peopleExportID', 'true');

-- configuration : choix ou auto détermination des praticiens à exporter pour l'export data

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeExportPratListSelection', 'default', '0', '', 'Options', 'true/false', 'si true, sélection possible des datas à exporter par liste praticiens, sinon auto déterminée par droits utilisateur courant', 'true');

-- configuration droitExportPeutExporterAutresData -> droitExportPeutExporterToutesData

update `configuration` set name = 'droitExportPeutExporterToutesData' WHERE `name` LIKE 'droitExportPeutExporterAutresData';

-- data_cat

INSERT IGNORE INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('admin', 'catDataAdminGroupe', 'Datas groupe', 'datas relatives à l\'identification d\'un groupe', 'base', '1', '2019-01-01 00:00:00');

INSERT IGNORE INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('admin', 'catDataAdminRegistre', 'Datas registre', 'datas relatives à l\'identification d\'un registre', 'base', '1', '2019-01-01 00:00:00');

-- data types

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catTypesUsageSystem');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('system', 'identite', '', 'Identité', 'LASTNAME Firstname (BIRTHNAME) (formulaire d\'affichage)', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='relationRelations');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('relation', 'relationPraticienGroupe', '', 'Relation praticien groupe', 'relation praticien groupe', '', '', 'select', '\'membre\': \'Membre\'\n\'admin\': \'Administrateur\'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('relation', 'relationGroupeRegistre', '', 'Relation groupe registre', 'relation groupe registre', '', '', 'select', '\'membre\': \'Membre\'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1'),
('relation', 'relationRegistrePraticien', '', 'Relation praticien registre', 'relation praticien registre', '', '', 'select', '\'admin\': \'Administrateur\'', 'base', @catID, '1', '2019-01-01 00:00:00', '1576800000', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catDataAdminGroupe');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'groupname', 'nom du groupe', 'Nom du groupe', 'nom du groupe', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catDataAdminRegistre');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'registryAuthorisationDate', '', 'Date d\'autorisation du registre', 'date d\'autorisation du registre', '', '', 'date', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'registryAuthorisationEndDate', '', 'Date de fin d\'autorisation du registre', 'date de fin d\'autorisation du registre', '', '', 'date', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1'),
('admin', 'registryname', 'nom du registre', 'Nom du registre', 'nom du registre', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catMarqueursAdminDossiers');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'peopleExportID', '', 'Id aléatoire export', 'id aléatoire export', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');

-- forms

SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='patientforms');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseNewGroupe', 'Formulaire de création d\'un groupe', 'formulaire de création d\'un nouveau groupe', 'data_types', 'admin', 'post', '/groupe/register/', @catID, 'public', 'structure:\r\n  row1:                              \r\n    col1:                            \r\n      size: 4\r\n      bloc:                          \r\n        - groupname,required                       		#16412 Nom du groupe\n    col2:                            \r\n      size: 4\r\n      bloc:                          \r\n        - country                                  		#1656 Pays\n    col3:                            \r\n      size: 4\r\n      bloc:                          \r\n        - city                                     		#12   Ville', '', '', '', ''),
('base', 'baseNewRegistre', 'Formulaire nouveau registre', 'formulaire nouveau registre', 'data_types', 'admin', 'post', '/registre/register/', @catID, 'public', 'structure:\r\n  row1:                              \r\n    col1:                            \r\n      size: col-12\r\n      bloc:                          \r\n        - registryname,required                    		#16415 Nom du registre\n  row2:                              \r\n    col1:                            \r\n      size: col-12 col-md-3\r\n      bloc:                          \r\n        - registryAuthorisationDate,required       		#16416 Date d autorisation du registre\n    col2:                            \r\n      size: col-12 col-md-3\r\n      bloc:                          \r\n        - registryAuthorisationEndDate             		#16417 Date de fin d autorisation du registre', '', '', '', '');

SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='displayforms');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseListingGroupes', 'Listing des groupes', 'description des colonnes affichées en résultat d\'une recherche groupes', 'data_types', 'medical', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'col1:\r\n    head: \"Nom du centre\" \r\n    bloc: \r\n        - groupname                                		#16412 Nom du groupe\ncol2:\r\n    head: \"Pays\"\r\n    bloc:\r\n        - country,text-uppercase                   		#1656 Pays\ncol3:\r\n    head: \"Ville\"\r\n    bloc:\r\n        - city,text-uppercase                      		#12   Ville', '', '', '', ''),
('base', 'baseListingRegistres', 'Listing des registres', 'description des colonnes affichées en résultat d\'une recherche registres', 'data_types', 'medical', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'col1:\r\n    head: \"Nom du registre\" \r\n    bloc: \r\n        - registryname                             		#16415 Nom du registre', '', '', '', '');

UPDATE `forms` SET `yamlStructure` = 'col1:\r\n    head: \"Identité\" \r\n    blocseparator: \" - \"\r\n    bloc: \r\n        - identite                                 		#16413 Identité\ncol2:\r\n    head: \"Date de naissance\" \r\n    blocseparator: \" - \"\r\n    bloc: \r\n        - birthdate                                		#8    Date de naissance\n        - ageCalcule                               		#1799 Age calculé\ncol3:\r\n    head: \"Tel\" \r\n    blocseparator: \" - \"\r\n    bloc: \r\n        - mobilePhone,click2call                   		#7    Téléphone mobile\n        - homePhone,click2call                     		#10   Téléphone domicile\ncol4:\r\n    head: \"Email\"\r\n    bloc:\r\n        - personalEmail                            		#4    Email personnelle\ncol5:\r\n    head: \"Ville\"\r\n    bloc:\r\n        - city,text-uppercase                      		#12   Ville' WHERE `internalName` = 'baseListingPatients' limit 1;

UPDATE `forms` SET `yamlStructure` = 'col1:\r\n    head: \"Identité\" \r\n    bloc: \r\n        - identite                                 		#16413 Identité\ncol2:\r\n    head: \"Activité pro\" \r\n    bloc: \r\n        - job                                      		#19   Activité professionnelle\ncol3:\r\n    head: \"Tel\" \r\n    bloc: \r\n        - telPro,click2call                        		#57   Téléphone professionnel\ncol4:\r\n    head: \"Fax\" \r\n    bloc: \r\n        - faxPro                                   		#58   Fax professionnel\ncol5:\r\n    head: \"Email\"\r\n    blocseparator: \" - \"\r\n    bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - personalEmail                            		#4    Email personnelle\n\r\ncol6:\r\n    head: \"Pays\"\r\n    bloc:\r\n        - paysAdressePro,text-uppercase            		#1657 Pays\n\r\ncol7:\r\n    head: \"Ville\"\r\n    bloc:\r\n        - villeAdressePro,text-uppercase           		#56   Ville' WHERE `internalName` = 'baseListingPro' limit 1;
