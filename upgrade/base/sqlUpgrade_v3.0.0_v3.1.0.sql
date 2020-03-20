
-- people
ALTER TABLE `people` ADD `name` varchar(30) DEFAULT NULL after `id`;
ALTER TABLE `people` ADD UNIQUE KEY `name` (`name`);
ALTER TABLE `people` CHANGE `type` `type` enum('patient','pro','externe','service', 'deleted') NOT NULL DEFAULT 'patient';

UPDATE `people` SET `name`=`id` WHERE `name` is null and `pass`!='';
INSERT IGNORE INTO `people` (`name`, `type`, `rank`, `module`, `pass`, `registerDate`, `fromID`, `lastLogIP`, `lastLogDate`, `lastLogFingerprint`) VALUES
('medshake', 'service', '', 'base', '', '2018-01-01 00:00:00', '1', '', '2018-01-01 00:00:00', ''),
('clicRDV', 'service', '', 'base', '', '2018-01-01 00:00:00', '1', '', '2018-01-01 00:00:00', '');
SET @medshakeid=(SELECT `id` from `people` WHERE `name`='medshake');

-- agenda
ALTER TABLE `agenda` ADD `externid` int UNSIGNED DEFAULT NULL AFTER `id`;
ALTER TABLE `agenda` ADD `lastModified` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `dateAdd`;
ALTER TABLE `agenda` ADD KEY `externid` (`externid`);
ALTER TABLE `agenda` ADD KEY `typeEtUserid` (`type`,`userid`);

-- data_cat
INSERT IGNORE INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('user', 'clicRDV', 'clicRDV', 'Paramètres pour clicRDV', 'base', 1, '2018-01-01 00:00:00'),
('ordo', 'OrdoItems', 'Ordo', 'items d\'une ordonnance', 'base', 1, '2018-01-01 00:00:00');

UPDATE `data_cat` SET `fromID`=@medshakeid WHERE `fromID` in ('0','1');
UPDATE `data_cat` SET `name`='porteursOrdo' WHERE `name`='poteursOrdo';

-- data_types
UPDATE `data_types` set formType='text' where formType not in ('','date','email','number','select','submit','tel','text','textarea','password','checkbox','hidden','range','radio','reset');
ALTER TABLE `data_types` CHANGE  `formType` `formType` enum('','date','email','number','select','submit','tel','text','textarea','password','checkbox','hidden','range','radio','reset') NOT NULL DEFAULT '';
UPDATE `data_types` SET `fromID`='1' WHERE `fromID`='0';
UPDATE `data_types` SET `module`='base' WHERE `name`in ('baseSynthese', 'csBaseGroup');
UPDATE `data_types` SET `label` = 'agendaForPatientsOfTheDay', `description` = 'permet d\'indiquer l\'agenda à utiliser pour la liste patients du jour pour cet utilisateur', `formType` = 'select', `formValues` = '' WHERE `name` = 'agendaNumberForPatientsOfTheDay';
UPDATE `data_types` SET formValues='baseReglement' WHERE name='reglePorteur';

set @cat=(SELECT `id` FROM `data_cat` WHERE `name`='ordoItems');
UPDATE `data_types` SET cat=@cat WHERE name in ('ordoTypeImpression', 'ordoLigneOrdo', 'ordoLigneOrdoALDouPas');

UPDATE `data_types` SET `placeholder`='nom reçu à la naissance', `label`='Nom de naissance', `description`='Nom reçu à la naissance', `validationErrorMsg`='Le nom de naissance est indispensable et ne doit pas contenir de caractères interdits' WHERE name='birthname';

UPDATE `data_types` SET `placeholder`='Nom marital ou utilisé au quotidien', `label`='Nom d\'usage', `description`='Nom marital ou utilisé au quotidien', `validationErrorMsg`='Le nom d\'usage ne doit pas contenir de caractères interdits' WHERE name='lastname';

SET @cat=(SELECT `id` FROM `data_cat` WHERE `name`='catParamsUsersAdmin');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('user', 'agendaService', 'Service d\'agenda externe', 'Service d\'agenda externe', 'nom du service', '', '', 'text', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 2);

SET @cat=(SELECT `id` FROM `data_cat` WHERE `name`='identity');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'deathdate', 'dd/mm/YYYY', 'Date de décès', 'Date de décès au format dd/mm/YYYY', 'validedate,\'d/m/Y\'', 'La date de décès indiquée n\'est pas valide', 'date', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 1);

SET @cat=(SELECT `id` FROM `data_cat` WHERE `name`='clicRDV');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('user', 'clicRdvUserId', 'identifiant', 'identifiant', 'email@address.com', '', '', 'text', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 1),
('user', 'clicRdvPassword', 'Mot de passe', 'Mot de passe', 'Mot de passe (chiffré)', '', '', 'password', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 2),
('user', 'clicRdvGroupId', 'Groupe', 'Groupe', 'Groupe Sélectionné', '', '', 'select', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 3),
('user', 'clicRdvCalId', 'Agenda', 'Agenda', 'Agenda sélectionné', '', '', 'select', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 4),
('user', 'clicRdvConsultId', 'Consultations', 'Consultations', 'Correspondance entre consultations', '', '', 'select', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 5);

SET @cat=(SELECT `id` FROM `data_cat` WHERE `name`='divers');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('admin', 'clicRdvPatientId', 'ID patient', 'ID patient', 'ID patient', '', '', 'text', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 1);

SET @cat=(SELECT `id` FROM `data_cat` WHERE `name`='relationRelations');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('relation', 'relationExternePatient', '', 'Relation externe patient', 'relation externe patient', '', '', 'number', '', 'base', @cat, 1, '2018-01-01 00:00:00', 1576800000, 1);

SET @cat=(SELECT `id` FROM `data_cat` WHERE `name`='catParamsUsersAdmin');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('user', 'administratifComptaPeutVoirRecettesDe', '', 'administratifComptaPeutVoirRecettesDe', 'permet à l\'utilisateur sélectionné de voir les recettes des praticiens choisis', '', '', 'text', '', 'base', @cat, 1, '2018-01-01 00:00:00', 3600, 1);

UPDATE `data_types` SET `fromID`=@medshakeid WHERE `fromID` in ('0','1');

-- forms_cat
UPDATE `forms_cat` SET `fromID`=@medshakeid WHERE `fromID` in ('0','1');

-- forms
ALTER TABLE `forms` ADD UNIQUE(`internalName`);
DELETE FROM `forms` WHERE `internalName`='basePasswordChange';
DELETE FROM `forms` WHERE internalName='baseReglementSimple';

INSERT INTO `forms` (`module`,`internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
('base','baseFirstLogin', 'Premier utilisateur', 'Création premier utilisateur', 'form_basic_types', 'admin', 'post', '/login/logInFirstDo/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Mot de passe de l\'utilisateur 1"\r\n    size: 3\r\n    bloc:\r\n      - userid,readonly \r\n      - password,required\r\n      - verifPassword,required\r\n      - submit', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Mot de passe de l\'utilisateur 1"\r\n    size: 3\r\n    bloc: \r\n      - userid,readonly \r\n      - password,required\r\n      - verifPassword,required\r\n      - submit', NULL),
('base', 'baseUserParametersPassword', 'Paramètres utilisateur MedShakeEHR', 'Paramètres utilisateur MedShakeEHR', 'form_basic_types', 'admin', 'post', '/user/actions/userParametersPassword/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Paramètres MedShakeEHR"\r\n    size: 3\r\n    bloc:\r\n      - currentPassword,required                            		#6    Mot de passe actuel\n\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Paramètres MedShakeEHR"\r\n    size: 3\r\n    bloc:\r\n      - currentPassword,required                            		#6    Mot de passe actuel\n\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe', NULL);

INSERT IGNORE INTO `forms_cat` (`name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('formATCD', 'Formulaires d\'antécédents', 'Formulaires pour construire les antécédents', 'user', 1, '2018-01-01 00:00:00'),
('formSynthese', 'Formulaires de synthèse', 'Formulaires pour construire les synthèses', 'user', 1, '2018-01-01 00:00:00');

ALTER TABLE `forms` CHANGE `formAction` `formAction` varchar(255) DEFAULT '/patient/ajax/saveCsForm/';

SET @catID=(SELECT `id` FROM `forms_cat` WHERE `name`='formSynthese');
UPDATE `forms` SET `cat`=@catID WHERE `internalName`='baseSynthese';

UPDATE `forms` SET `formAction`='/patient/ajax/saveReglementForm/' WHERE `internalName`='baseReglement';

UPDATE `forms` SET `formAction`='/patient/ajax/saveCsForm/' WHERE `internalName`='baseConsult';

UPDATE `forms` SET `yamlStructureDefaut` = 'col1:\r\n    head: "Nom de naissance"\r\n    bloc:\r\n        - birthname,text-uppercase,gras            		#1    Nom de naissance\ncol2:\r\n    head: "Nom d\'usage"\r\n    bloc:\r\n        - lastname,text-uppercase,gras             		#2    Nom d usage\n\r\ncol3:\r\n    head: "Prénom"\r\n    bloc:\r\n        - firstname,text-capitalize,gras           		#3    Prénom\ncol4:\r\n    head: "Date de naissance" \r\n    bloc: \r\n        - birthdate                                		#8    Date de naissance\ncol5:\r\n    head: "Tel" \r\n    blocseparator: " - "\r\n    bloc: \r\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\ncol6:\r\n    head: "Email"\r\n    bloc:\r\n        - personalEmail                            		#4    Email personnelle\ncol7:\r\n    head: "Ville"\r\n    bloc:\r\n        - city,text-uppercase                      		#12   Ville'
WHERE `internalName`='baseListingPatients';


UPDATE `forms` SET `yamlStructure`='structure:\r\n row1:\r\n  col1: \r\n    head: "Premier utilisateur"\r\n    size: 3\r\n    bloc:\r\n      - username,required                            		#1    Identifiant\n      - moduleSelect                               		#7    Module\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe\n      - submit                                     		#3    Valider', `yamlStructureDefaut`='structure:\r\n row1:\r\n  col1: \r\n    head: "Premier utilisateur 1"\r\n    size: 3\r\n    bloc:\r\n      - username,required                            		#1    Identifiant\n      - moduleSelect                               		#7    Module\n      - password,required                          		#2    Mot de passe\n      - verifPassword,required                     		#5    Confirmation du mot de passe\n      - submit                                     		#3    Valider' WHERE `internalName`='baseFirstLogin';

SET @catID=(SELECT `id` FROM `forms_cat` WHERE `name`='formATCD');
UPDATE `forms` SET `cat`=@catID, `yamlStructure`='structure:\r\n  row1: \r\n    col1: \r\n      size: 4\r\n      bloc: \r\n        - poids,plus={<i class="glyphicon glyphicon glyphicon-duplicate duplicate"></i>} #34   Poids\r\n    col2: \r\n      size: 4\r\n      bloc: \r\n       - taillePatient,plus={<i class="glyphicon glyphicon glyphicon-duplicate duplicate"></i>} #35   Taille\r\n    col3: \r\n      size: 4\r\n      bloc: \r\n       - imc,readonly,plus={<i class="glyphicon glyphicon glyphicon-stats graph"></i>} #43   IMC\r\n  row2: \r\n   col1: \r\n     size: 12\r\n     bloc: \r\n       - job                                       		#19   Activité professionnelle\r\n       - allergies,rows=2                          		#66   Allergies\r\n       - toxiques                                  		#42   Toxiques\r\n  row3: \r\n    col1: \r\n     size: 12\r\n     bloc: \r\n       - atcdMedicChir,rows=6                      		#41   Antécédents médico-chirurgicaux\r\n       - atcdFamiliaux,rows=6                      		#38   Antécédents familiaux', `yamlStructureDefaut`='structure:\r\n  row1: \r\n    col1: \r\n      size: 4\r\n      bloc: \r\n        - poids,plus={<i class="glyphicon glyphicon glyphicon-duplicate duplicate"></i>} #34   Poids\r\n    col2: \r\n      size: 4\r\n      bloc: \r\n       - taillePatient,plus={<i class="glyphicon glyphicon glyphicon-duplicate duplicate"></i>} #35   Taille\r\n    col3: \r\n      size: 4\r\n      bloc: \r\n       - imc,readonly                              		#43   IMC\r\n  row2: \r\n   col1: \r\n     size: 12\r\n     bloc: \r\n       - job                                       		#19   Activité professionnelle\r\n       - allergies,rows=2                          		#66   Allergies\r\n       - toxiques                                  		#42   Toxiques\r\n  row3: \r\n    col1: \r\n     size: 12\r\n     bloc: \r\n       - atcdMedicChir,rows=6                      		#41   Antécédents médico-chirurgicaux\r\n       - atcdFamiliaux,rows=6                      		#38   Antécédents familiaux' WHERE `internalName`='baseATCD';

UPDATE `forms` SET `yamlStructure`='global:\r\n  formClass: \'form-signin\' \r\nstructure:\r\n row1:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - username,required,nolabel                            		#1    Identifiant\n      - password,required,nolabel                          		#2    Mot de passe\n      - submit,Connection,class=btn-primary,class=btn-block                                     		#3    Valider', `yamlStructureDefaut`='global:\r\n  globalClass: \'form-signin\' \r\nstructure:\r\n row1:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - username,required,nolabel                            		#1    Identifiant\n      - password,required,nolabel                          		#2    Mot de passe\n      - submit,Connection,class=btn-primary,class=btn-block                                     		#3    Valider' WHERE `internalName`='baseLogin';

UPDATE `forms` SET `yamlStructure`='structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient,class=regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifCejour,readonly,plus={€},class=regleTarifCejour       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€},class=regleDepaCejour                 		#199  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€},class=regleTiersPayeur                		#200  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur', `yamlStructureDefaut`='structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient,class=regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifCejour,readonly,plus={€},class=regleTarifCejour       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€},class=regleDepaCejour                 		#199  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€},class=regleTiersPayeur                		#200  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur' WHERE `internalName`='baseReglement';

UPDATE `forms` SET `yamlStructure`='structure:\r\n  row1:                              \r\n    col1:                              \r\n      head: \'Etat civil\'             \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - birthname,required,autocomplete,data-acTypeID=2:1 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=2:1  		#2    Nom d usage\n        - firstname,required,autocomplete,data-acTypeID=3:22:230:235:241 		#3    Prénom\n        - birthdate                                		#8    Date de naissance\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - personalEmail                            		#4    Email personnelle\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\n    col3:\r\n      head: \'Adresse personnelle\'\r\n      size: 4\r\n      bloc: \r\n        - streetNumber                             		#9    Numéro\n        - street,autocomplete,data-acTypeID=11:55  		#11   Rue\n        - postalCodePerso                          		#13   Code postal\n        - city,autocomplete,data-acTypeID=12:56    		#12   Ville\n        - deathdate                                		#508  Date de décès\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,rows=5                             		#21   Notes', `yamlStructureDefaut`='structure:\r\n  row1:                              \r\n    col1:                              \r\n      head: \'Etat civil\'             \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\r\n        - birthname,required,autocomplete,data-acTypeID=2:1 		#1    Nom de naissance\r\n        - lastname,autocomplete,data-acTypeID=2:1  		#2    Nom d usage\r\n        - firstname,required,autocomplete,data-acTypeID=3:22:230:235:241 		#3    Prénom\r\n        - birthdate                                		#8    Date de naissance\r\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - personalEmail                            		#4    Email personnelle\r\n        - mobilePhone                              		#7    Téléphone mobile\r\n        - homePhone                                		#10   Téléphone domicile\r\n    col3:\r\n      head: \'Adresse personnelle\'\r\n      size: 4\r\n      bloc: \r\n        - streetNumber                             		#9    Numéro\r\n        - street,autocomplete,data-acTypeID=11:55  		#11   Rue\r\n        - postalCodePerso                          		#13   Code postal\r\n        - city,autocomplete,data-acTypeID=12:56    		#12   Ville\r\n        - deathdate                                		#508  Date de décès\r\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,rows=5                             		#21   Notes' WHERE `internalName`='baseNewPatient';

SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='systemForm');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
('base', 'baseUserParametersClicRdv', 'Paramètres utilisateur clicRDV', 'Paramètres utilisateur clicRDV', 'data_types', 'admin', 'post', '/user/actions/userParametersClicRdv/', @catID, 'public', 'structure:\n row1:\n  col1: \n    head: "Compte clicRDV"\n    size: 3\n    bloc:\n      - clicRdvUserId\n      - clicRdvPassword\n      - clicRdvGroupId\n      - clicRdvCalId\n      - clicRdvConsultId,nolabel', 'structure:\n row1:\n  col1: \n    head: "Compte clicRDV"\n    size: 3\n    bloc:\n      - clicRdvUserId\n      - clicRdvPassword\n      - clicRdvGroupId\n      - clicRdvCalId\n      - clicRdvConsultId,nolabel', NULL);

-- forms_basic_types
update `form_basic_types` set creationDate='2018-01-01 00:00:00', deleteDate='2018-01-01 00:00:00' where id in(1,2,3);
ALTER TABLE `form_basic_types` ADD UNIQUE(`name`);
DELETE FROM `form_basic_types` WHERE `name` in ('moduleSelect');
INSERT IGNORE INTO  `form_basic_types` (`name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `type`, `cat`, `fromID`, `creationDate`, `deleteByID`, `deleteDate`) VALUES
('actualPassword', 'Mot de passe de passe actuel', 'Mot de passe actuel', 'Mot de passe actuel', '', '', 'password', '', 'base', 0, 0, '2018-01-01 00:00:00', 0, '1970-01-01 00:00:00'),
('verifPassword', 'confirmation du mot de passe', 'Confirmation du mot de passe', 'Confirmation du mot de passe utilisateur', 'required', 'La confirmation du mot de passe est manquante', 'password', '', 'base', 0, 0, '2018-01-01 00:00:00', 0, '1970-01-01 00:00:00'),
('module', '', 'Module', '', '', '', 'hidden', '', 'base', 0, 0, '2018-01-01 00:00:00', 0, '2018-01-01 00:00:00'),
('currentPassword', 'Mot de passe actuel', 'Mot de passe actuel', 'Mot de passe actuel de l\'utilisateur', 'required', 'Le mot de passe actuel est manquant', 'password', '', 'base', 0, 1, '2018-01-01 00:00:00', 0, '2018-01-01 00:00:00');


UPDATE `form_basic_types` SET `name`='username', `description`='identifiant utilisateur', `validationRules`='required', `validationErrorMsg`='L\'identifiant utilisateur est manquant' WHERE `name`='userid';
UPDATE `form_basic_types` SET `fromID`=@medshakeid WHERE `fromID` in ('0','1');

-- objets_data
ALTER TABLE `objets_data` ADD `deletedByID` int(11) DEFAULT NULL after `deleted`;

UPDATE `objets_data` as od2 left join objets_data as od1 
on od1.toID=od2.toID
set od2.typeID=1
WHERE ((od1.value='M' and od1.typeID=14) or (od1.value>'2000-01-01 00:00:00' and od1.typeID=8)) and od2.typeID=2;

-- system
ALTER TABLE `system` MODIFY `id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `system` CHANGE `module` `name` VARCHAR(30) NOT NULL;
ALTER TABLE `system` ADD UNIQUE KEY `name` (`name`);
ALTER TABLE `system` ADD `groupe` enum('system', 'module', 'cron', 'lock') DEFAULT 'system' after `name`;
ALTER TABLE `system` CHANGE `version` `value` text DEFAULT NULL;

UPDATE `system` SET `groupe`='module' WHERE `name`='base';
UPDATE `system` SET `value`='v3.1.0' WHERE `name`='base';
INSERT IGNORE INTO `system` (`name`, `groupe`,`value`) VALUES
('state', 'system', 'normal');
