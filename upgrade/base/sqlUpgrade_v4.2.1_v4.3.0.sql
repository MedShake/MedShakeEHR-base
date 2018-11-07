
-- Ajustements pour bon fonctionnement autocomplete
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'data-acTypeID=2:1', 'data-acTypeID=lastname:birthname');
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'data-acTypeID=3:22:230:235:241', 'data-acTypeID=firstname:othersfirstname');
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'data-acTypeID=11:55', 'data-acTypeID=street:rueAdressePro');
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'data-acTypeID=12:56', 'data-acTypeID=city:villeAdressePro');
UPDATE `forms` SET `yamlStructureDefaut` = yamlStructure;

-- Support pour les documents à signer
INSERT INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('courrier', 'catModelesDocASigner', 'Documents à signer', 'documents à envoyer à la signature numérique', 'base', 1, '2018-01-01 00:00:00');

-- Paramètres de configuration : changement catégorie
UPDATE `configuration` set cat = 'Règlements' where name in ('administratifSecteurHonoraires', 'administratifPeutAvoirFacturesTypes', 'administratifPeutAvoirRecettes', 'administratifComptaPeutVoirRecettesDe', 'administratifSecteurIK');

-- Paramètres pour vitale
INSERT IGNORE INTO `configuration`(`name`, `cat`, `level`, `type`, `description`, `value`) VALUES
('vitaleActiver', 'Vitale', 'default', 'true/false', 'activer / désactiver les services liés à la carte vitale', 'false'),
('vitaleService', 'Vitale', 'default', 'texte', 'service tiers de gestion vitale', ''),
('vitaleHoteLecteurIP', 'Vitale', 'default', 'texte', 'IP sur le réseau interne de la machine supportant le lecteur', ''),
('vitaleNomRessourcePS', 'Vitale', 'default', 'texte', 'nomRessourcePS', ''),
('vitaleNomRessourceLecteur', 'Vitale', 'default', 'texte', 'nomRessourceLecteur', '');

-- Formulaire nouveau patient (+ num de sécu)
update `forms` set `yamlStructure`= 'structure:\r\n  row1:                              \r\n    col1:                              \r\n      head: \'Etat civil\'             \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - birthname,required,autocomplete,data-acTypeID=lastname:birthname 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=lastname:birthname 		#2    Nom d usage\n        - firstname,required,autocomplete,data-acTypeID=firstname:othersfirstname:igPrenomFA:igPrenomFB:igPrenomFC 		#3    Prénom\n        - birthdate,class=pick-year                		#8    Date de naissance\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - personalEmail                            		#4    Email personnelle\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\n        - nss                                      		#180  Numéro de sécu\n    col3:\r\n      head: \'Adresse personnelle\'\r\n      size: 4\r\n      bloc: \r\n        - streetNumber                             		#9    n°\n        - street,autocomplete,data-acTypeID=street:rueAdressePro 		#11   Voie\n        - postalCodePerso                          		#13   Code postal\n        - city,autocomplete,data-acTypeID=city:villeAdressePro 		#12   Ville\n        - deathdate                                		#516  Date de décès\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,rows=5                             		#21   Notes' where internalName = "baseNewPatient";

-- Paramètres de configuration : design du menu sup de navigation
INSERT IGNORE INTO `configuration`(`name`, `cat`, `level`, `type`, `description`, `value`) VALUES
('designTopMenuStyle', 'Ergonomie & design', 'default', 'icones / textes', 'aspect du menu de navigation du haut de page', 'textes');

-- Mise à jour n° de version
UPDATE `system` SET `value`='v4.3.0' WHERE `name`='base' and `groupe`='module';
