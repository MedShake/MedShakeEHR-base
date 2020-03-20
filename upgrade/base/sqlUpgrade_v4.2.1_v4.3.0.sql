
-- Ajustements pour bon fonctionnement autocomplete
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'data-acTypeID=2:1', 'data-acTypeID=lastname:birthname');
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'data-acTypeID=3:22:230:235:241', 'data-acTypeID=firstname:othersfirstname');
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'data-acTypeID=11:55', 'data-acTypeID=street:rueAdressePro');
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'data-acTypeID=12:56', 'data-acTypeID=city:villeAdressePro');
UPDATE `forms` SET `yamlStructureDefaut` = yamlStructure;

-- fix: orthographe
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'médico-chirugicaux', 'médico-chirurgicaux');
UPDATE `data_types` SET `label` = replace(label, 'médico-chirugicaux', 'médico-chirurgicaux');
UPDATE `data_types` SET `placeholder` = replace(placeholder, 'médico-chirugicaux', 'médico-chirurgicaux');
UPDATE `data_types` SET `description` = replace(description, 'médico-chirugicaux', 'médico-chirurgicaux');

-- Support pour les documents à signer
INSERT INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('courrier', 'catModelesDocASigner', 'Documents à signer', 'documents à envoyer à la signature numérique', 'base', 1, '2018-01-01 00:00:00');

-- Data Types : support pour retour data FSE par service tiers
SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='reglementItems');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('reglement', 'regleFseData', '', 'FSE data', 'data de la FSE générée par service tiers', '', '', 'text', '', 'base', @catID, 1, '2018-01-01 01:00:00', 1576800000, 1);

-- Data Types : précisions sur le praticien santé pour CDA
SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='numAdmin');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'PSIdNat', '', 'Identifiant national praticien santé', 'identifiant national praticien santé', '', '', 'text', '', 'base', @catID, 1, '2018-11-23 11:49:03', 3600, 1),
('admin', 'PSCodeProSpe', '', 'Code normé de la profession/spécialité du praticien', 'code normé de la profession/spécialité du praticien', '', '', 'select', '\'Z\' : \'Jeu de valeurs normées absent\'', 'base', @catID, 1, '2018-11-26 11:39:13', 3600, 1),
('admin', 'PSCodeStructureExercice', '', 'Code normé de la structure d\'exercice du praticien', 'code normé de la structure d\'exercice du praticien', '', '', 'select', '\'Z\' : \'Jeu de valeurs normées absent\'', 'base', @catID, 1, '2018-11-26 11:39:24', 3600, 1);

-- Ajout de la relation Patient <-> Médecin traitant déclaré
update `data_types` set formValues = concat("'MTD': 'Médecin traitant déclaré'\n", formValues) where name='relationPatientPraticien';

-- Paramètres de configuration : changement catégorie
UPDATE `configuration` set cat = 'Règlements' where name in ('administratifSecteurHonoraires', 'administratifPeutAvoirFacturesTypes', 'administratifPeutAvoirRecettes', 'administratifComptaPeutVoirRecettesDe', 'administratifSecteurIK');

-- Paramètres de configuration : vitale
INSERT IGNORE INTO `configuration`(`name`, `cat`, `level`, `type`, `description`, `value`) VALUES
('vitaleActiver', 'Vitale', 'default', 'true/false', 'activer / désactiver les services liés à la carte vitale', 'false'),
('vitaleService', 'Vitale', 'default', 'texte', 'service tiers de gestion vitale', ''),
('vitaleHoteLecteurIP', 'Vitale', 'default', 'texte', 'IP sur le réseau interne de la machine supportant le lecteur', ''),
('vitaleNomRessourcePS', 'Vitale', 'default', 'texte', 'nomRessourcePS', ''),
('vitaleNomRessourceLecteur', 'Vitale', 'default', 'texte', 'nomRessourceLecteur', '');

-- Paramètres de configuration : design du menu sup de navigation
INSERT IGNORE INTO `configuration`(`name`, `cat`, `level`, `type`, `description`, `value`) VALUES
('designTopMenuStyle', 'Ergonomie et design', 'default', 'icones / textes', 'aspect du menu de navigation du haut de page', 'textes'),
('designTopMenuInboxCountDisplay', 'Ergonomie et design', 'default', 'true/false', 'afficher dans le menu de navigation du haut de page le nombre de nouveaux messages dans la boite de réception', 'true'),
('designTopMenuTransmissionsCountDisplay', 'Ergonomie et design', 'default', 'true/false', 'afficher dans le menu de navigation du haut de page le nombre de transmissions non lues', 'true'),
('designTopMenuTransmissionsColorIconeImportant', 'Ergonomie et design', 'default', 'true/false', 'colore l\'icône transmission si transmission importante non lue', 'true'),
('designTopMenuTransmissionsColorIconeUrgent', 'Ergonomie et design', 'default', 'true/false', 'colore l\'icône transmission si transmission urgente non lue', 'true');

-- Paramètre de configuration : précision d'intitulé
UPDATE `configuration` set description = 'ID ou IDs numériques des comptes utilisateurs (séparés par des virgules) pour lesquels l\'utilisateur courant peut voir les mails Apicrypt relevés en inbox' where name = 'apicryptInboxMailForUserID' and level='default';

-- Paramètre de configuration : templates CDA
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ( 'templatesCdaFolder', 'default', '0', '', 'Modèles de documents', 'dossier', 'répertoire des fichiers de template pour la génération de XML CDA', '');

-- Formulaire nouveau patient (+ num de sécu)
update `forms` set `yamlStructure`= 'structure:\r\n  row1:                              \r\n    col1:                              \r\n      head: \'Etat civil\'             \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - birthname,required,autocomplete,data-acTypeID=lastname:birthname 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=lastname:birthname 		#2    Nom d usage\n        - firstname,required,autocomplete,data-acTypeID=firstname:othersfirstname:igPrenomFA:igPrenomFB:igPrenomFC 		#3    Prénom\n        - birthdate,class=pick-year                		#8    Date de naissance\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - personalEmail                            		#4    Email personnelle\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\n        - nss                                      		#180  Numéro de sécu\n    col3:\r\n      head: \'Adresse personnelle\'\r\n      size: 4\r\n      bloc: \r\n        - streetNumber                             		#9    n°\n        - street,autocomplete,data-acTypeID=street:rueAdressePro 		#11   Voie\n        - postalCodePerso                          		#13   Code postal\n        - city,autocomplete,data-acTypeID=city:villeAdressePro 		#12   Ville\n        - deathdate                                		#516  Date de décès\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,rows=5                             		#21   Notes' where internalName = "baseNewPatient";


-- Ajout d'une colonne cda pour les data de génération du xml cda
ALTER TABLE `forms` ADD `cda` TEXT NULL DEFAULT NULL AFTER `printModel`;

-- Mise à jour n° de version
UPDATE `system` SET `value`='v4.3.0' WHERE `name`='base' and `groupe`='module';

-- Mise à jour formulaire nouveau pro
update `forms` set `yamlStructure` = 'structure:\r\n  row1:                              \r\n    col1:                            \r\n      head: \'Etat civil\'            \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - job,autocomplete                         		#19   Activité professionnelle\n        - titre,autocomplete                       		#51   Titre\n        - birthname,autocomplete,data-acTypeID=firstname:othersfirstname:igPrenomFA:igPrenomFB:igPrenomFC 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=lastname:birthname 		#2    Nom d usage\n        - firstname,autocomplete,data-acTypeID=firstname:othersfirstname:igPrenomFA:igPrenomFB:igPrenomFC 		#3    Prénom\n\r\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - profesionnalEmail                        		#5    Email professionnelle\n        - personalEmail                            		#4    Email personnelle\n        - telPro                                   		#57   Téléphone professionnel\n        - telPro2                                  		#248  Téléphone professionnel 2\n        - mobilePhonePro                           		#247  Téléphone mobile pro.\n        - faxPro                                   		#58   Fax professionnel\n    col3:\r\n      head: \'Adresse professionnelle\'\r\n      size: 4\r\n      bloc: \r\n        - numAdressePro                            		#54   Numéro\n        - rueAdressePro,autocomplete,data-acTypeID=street:rueAdressePro 		#55   Rue\n        - codePostalPro                            		#53   Code postal\n        - villeAdressePro,autocomplete,data-acTypeID=city:villeAdressePro 		#56   Ville\n        - serviceAdressePro,autocomplete           		#249  Service\n        - etablissementAdressePro,autocomplete     		#250  Établissement\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notesPro,rows=5                          		#443  Notes pros\n\r\n  row3:\r\n    col1:\r\n      size: 4\r\n      bloc:\r\n        - rpps                                     		#103  RPPS\n        - PSIdNat                                  		#1602 Identifiant national praticien santé\n    col2:\r\n      size: 4\r\n      bloc:\r\n        - adeli                                    		#104  Adeli\n        - PSCodeProSpe,plus={<i class=\"fa fa-pen\"></i>} 		#1603 Code normé de la profession/spécialité du praticien\n    col3:\r\n      size: 4\r\n      bloc:\r\n        - nReseau                                  		#477  Numéro de réseau\n        - PSCodeStructureExercice,plus={<i class=\"fa fa-pen\"></i>} 		#1604 Code normé de la structure d exercice du praticien' where internalName = 'baseNewPro';
