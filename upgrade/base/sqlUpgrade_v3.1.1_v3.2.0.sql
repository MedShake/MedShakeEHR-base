
ALTER TABLE `system` DROP KEY `name`, ADD UNIQUE KEY `nameGroupe` (`name`, `groupe`);

ALTER TABLE `actes_base` CHANGE `type` `type` enum('NGAP','CCAM', 'Libre') NOT NULL DEFAULT 'CCAM';

ALTER TABLE `objets_data` ADD `byID` INT(11) UNSIGNED;

ALTER TABLE `printed` CHANGE `type` `type` ENUM('cr','ordo','courrier','ordoLAP') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'cr';

UPDATE `prescriptions_cat` set `type`='user';
ALTER TABLE `prescriptions_cat` CHANGE `type` `type` ENUM('nonlap','lap','user') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'nonlap';
UPDATE `prescriptions_cat` set `type`='nonlap';
ALTER TABLE `prescriptions_cat` CHANGE `type` `type` ENUM('nonlap','lap') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'nonlap';

INSERT IGNORE INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('medical', 'aldCat', 'ALD', 'paramètres pour la gestion des ALD', 'base', 1, '2018-01-01 00:00:00'),
('medical', 'catAtcdStruc', 'ATCD structurés', 'données pour antécédents structurés', 'base', 1, '2018-01-01 00:00:00'),
('typecs', 'catTypeCsATCD', 'Antécédents et allergies', 'antécédents et allergies', 'base', 1, '2018-01-01 00:00:00'),
('relation', 'catAllergiesStruc', 'Allergies structurées', 'données pour allergies structurées', 'base', 1, '2018-01-01 00:00:00'),
('user', 'lapUserParamCat', 'LAP', 'paramètres pour les réglages utilisateur dans le LAP', 'base', 1, '2018-01-01 00:00:00'),
('ordo', 'lapCatPorteurs', 'LAP porteurs', 'data pour les porteurs LAP', 'base', 1, '2018-01-01 00:00:00'),
('ordo', 'lapCatLignePrescription', 'LAP ligne de prescription', 'data des lignes de prescription', 'base', 1, '2018-01-01 00:00:00'),
('ordo', 'lapCatMedicament', 'LAP médicament', 'data pour les médicaments', 'base', 1, '2018-01-01 00:00:00');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='porteursReglement');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`,`cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
 ('reglement', 'reglePorteurS1', '', 'Règlement conventionné S1', 'Règlement conventionné S1', '', '', '', 'baseReglementS1', 'base', @catID, 1, '2018-01-01 00:00:00', 1576800000, 1),
 ('reglement', 'reglePorteurLibre', '', 'Règlement non conventionné', 'Règlement non conventionné', '', '', '', 'baseReglementLibre', 'base', @catID, 1, '2018-01-01 00:00:00', 1576800000, 1);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='reglementItems');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('reglement', 'regleTarifLibreCejour', '', 'Tarif', 'tarif appliqué ce jour', '', '', 'text', '', 'base', @catID, 1, '2018-01-01 00:00:00', 1576800000, 1),
('reglement', 'regleModulCejour', '', 'Modulation', 'modulation appliquée ce jour', '', '', 'text', '', 'base', @catID, 1, '2018-01-01 00:00:00', 1576800000, 1);

UPDATE `data_types` SET `name`='regleTarifSSCejour' WHERE `name`='regleTarifCejour';
UPDATE `data_types` SET `name`='reglePorteurS2', `label`='Règlement conventionné S2', `placeholder`='Règlement conventionné S2', `formValues`='baseReglementS2' WHERE `name`='reglePorteur';
UPDATE `data_types` SET `placeholder`='type et nom de la voie', `label`='Voie', `description`='Adresse perso : voie' WHERE `name`='street';
UPDATE `data_types` SET `placeholder`='n° dans la voie', `label`='n°', `description`='Adresse perso : n° dans la voie' WHERE `name`='streetNumber';
UPDATE `data_types` SET `description`='Adresse pro : n° dans la voie' WHERE `name`='numAdressePro';
UPDATE `data_types` SET `placeholder`='mobile: 0x xx xx xx xx' WHERE  `name`='mobilePhone';
UPDATE `data_types` SET `placeholder`='fixe: 0x xx xx xx xx' WHERE  `name`='homePhone';
UPDATE `data_types` SET `placeholder`='naissance: dd/mm/YYYY' WHERE  `name`='birthdate';
UPDATE `data_types` SET `placeholder`='décès: dd/mm/YYYY' WHERE  `name`='deathdate';
UPDATE `data_types` SET `placeholder`='nom marital ou d\'usage' WHERE  `name`='lastname';
UPDATE `data_types` SET `placeholder`='nom' WHERE  `name`='birthname';
UPDATE `data_types` SET `label`='Informations paiement', `description`='Information complémentaires sur le paiement', `placeholder`='n° de chèque, nom du payeur si différent du patient,...' WHERE  `name`='regleIdentiteCheque';

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='dataBio');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'clairanceCreatinine', 'ml/min', 'Clairance créatinine', 'clairance de la créatinine', '', '', 'text', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='atcd');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'allaitementActuel', '', 'Allaitement', 'allaitement actuel', '', '', 'text', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('medical', 'insuffisanceHepatique', '', 'Insuffisance hépatique', 'degré d\'insuffisance hépatique', '', '', 'select', '\'z\': "?"\n\'n\': "Pas d\'insuffisance hépatique connue"\n\'1\': \'Légère\'\n\'2\': \'Modérée\'\n\'3\': \'Sévère\'', 'base', @catID, 3, '2018-01-01 00:00:00', 3600, 1);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapUserParamCat');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('user', 'lapAlertPatientTermeGrossesseSup46', '', 'lapAlertPatientTermeGrossesseSup46', 'alerte pour terme de grossesse supérieur à 46SA', '', '', 'checkbox', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('user', 'lapAlertPatientAllaitementSup3Ans', '', 'lapAlertPatientAllaitementSup3Ans', 'alerte pour allaitement supérieur à 3 ans', '', '', 'checkbox', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('user', 'theriaqueShowMedicHospi', '', 'theriaqueShowMedicHospi', 'montrer les médicaments hospitaliers', '', '', 'checkbox', '', 'base', @catID, 1, '2018-03-14 16:37:34', 3600, 1),
('user', 'theriaqueShowMedicNonComer', '', 'theriaqueShowMedicNonComer', 'montrer les médicaments non commercialisés', '', '', 'checkbox', '', 'base', @catID, 1, '2018-03-14 16:37:42', 3600, 1);


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='aldCat');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'aldNumber', '', 'ALD', 'ALD choisie', '', '', 'select', '1: "Accident vasculaire cérébral invalidant"\n2: "Insuffisances médullaires et autres cytopénies chroniques"\n3: "Artériopathies chroniques avec manifestations ischémiques"\n4: "Bilharziose compliquée"\n5: "Insuffisance cardiaque grave, troubles du rythme graves, cardiopathies valvulaires graves, cardiopathies  congénitales graves"\n6: "Maladies chroniques actives du foie et cirrhoses"\n7: "Déficit immunitaire primitif grave nécessitant un traitement prolongé, infection par le virus de 9: l\'immuno-déficience humaine (VIH)"\n8: "Diabète de type 1 et diabète de type 2"\n9: "Formes graves des affections neurologiques et musculaires (dont myopathie), épilepsie grave"\n10: "Hémoglobinopathies, hémolyses, chroniques constitutionnelles et acquises sévères"\n11: "Hémophilies et affections constitutionnelles de l\'hémostase graves"\n12: "Maladie coronaire"\n13: "Insuffisance respiratoire chronique grave"\n14: "Maladie d\'Alzheimer et autres démences"\n15: "Maladie de Parkinson"\n16: "Maladies métaboliques héréditaires nécessitant un traitement prolongé spécialisé"\n17: "Mucoviscidose"\n18: "Néphropathie chronique grave et syndrome néphrotique primitif"\n19: "Paraplégie"\n20: "Vascularites, lupus érythémateux systémique, sclérodermie systémique"\n21: "Polyarthrite rhumatoïde évolutive"\n22: "Affections psychiatriques de longue durée"\n23: "Rectocolite hémorragique et maladie de Crohn évolutives"\n24: "Sclérose en plaques"\n25: "Scoliose idiopathique structurale évolutive (dont l\'angle est égal ou supérieur à 25 degrés) jusqu\'à maturation rachidienne"\n26: "Spondylarthrite grave"\n27: "Suites de transplantation d\'organe"\n28: "Tuberculose active, lèpre"\n29: "Tumeur maligne, affection maligne du tissu lymphatique ou hématopoïétique"\n31: "Affection hors liste"\n32: "Etat polypathologique"', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0),
('medical', 'aldDateDebutPriseEnCharge', '', 'Début de prise en charge', 'date de début de prise en charge', '', '', 'date', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0),
('medical', 'aldDateFinPriseEnCharge', '', 'Fin de prise en charge', 'date de fin de prise en charge', '', '', 'date', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0),
('medical', 'aldCIM10', '', 'Code CIM10 associé', 'Code CIM10 attaché à l\'ALD', '', '', 'text', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0),
('medical', 'aldCIM10label', '', 'Label CIM10 associé', 'Label CIM10 attaché à l\'ALD', '', '', 'text', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catAtcdStruc');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'atcdStrucCIM10', '', 'Code CIM 10', 'code CIM 10 de l\'atcd', '', '', 'text', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0),
('medical', 'atcdStrucCIM10Label', '', 'Label CIM 10', 'label CIM 10 de l\'atcd', '', '', 'text', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0),
('medical', 'atcdStrucDateDebutJour', '', 'Jour', 'jour de début de l\'atcd', '', '', 'number', '0', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0),
('medical', 'atcdStrucDateFinJour', '', 'Jour', 'jour de fin de l\'atcd', '', '', 'number', '0', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0),
('medical', 'atcdStrucDateDebutMois', '', 'Mois', 'mois de début de l\'atcd', '', '', 'select', '\'0\' : \'non précisé\'\n\'1\' : \'janvier\'\n\'2\' : \'février\'\n\'3\' : \'mars\'\n\'4\' : \'avril\'\n\'5\' : \'mai\'\n\'6\' : \'juin\'\n\'7\' : \'juillet\'\n\'8\' : \'août\'\n\'9\' : \'septembre\'\n\'10\' : \'octobre\'\n\'11\' : \'novembre\'\n\'12\' : \'décembre\'', 'base', 80, 1, '2018-01-01 00:00:00', 3600, 0),
('medical', 'atcdStrucDateFinMois', '', 'Mois', 'mois de fin de l\'atcd', '', '', 'select', '\'0\' : \'non précisé\'\n\'1\' : \'janvier\'\n\'2\' : \'février\'\n\'3\' : \'mars\'\n\'4\' : \'avril\'\n\'5\' : \'mai\'\n\'6\' : \'juin\'\n\'7\' : \'juillet\'\n\'8\' : \'août\'\n\'9\' : \'septembre\'\n\'10\' : \'octobre\'\n\'11\' : \'novembre\'\n\'12\' : \'décembre\'', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0),
('medical', 'atcdStrucDateDebutAnnee', '', 'Année', 'année de début de l\'atcd', '', '', 'number', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0),
('medical', 'atcdStrucDateFinAnnee', '', 'Année', 'année de fin de l\'atcd', '', '', 'number', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0),
('medical', 'atcdStrucNotes', 'notes concernant cet antécédents', 'Notes', 'notes concernant l\'atcd', '', '', 'textarea', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0),
('medical', 'atcdStrucCIM10InLap', '', 'A prendre en compte dans le LAP', 'prise en compte ou non dans le LAP', '', '', 'select', '\'o\': \'oui\'\n\'n\': \'non\'', 'base',  @catID, 1, '2018-01-01 00:00:00', 3600, 1);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catTypeCsATCD');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('typecs', 'csAtcdStrucDeclaration', NULL, 'Ajout d\'antécédent', 'support parent pour déclaration d\'antécédent structuré', NULL, NULL, '', 'atcdStrucDeclaration', 'base', @catID, 1, '2018-01-01 00:00:00', 84600, 1),
('typecs', 'csAldDeclaration', NULL, 'Déclaration ALD', 'support parent pour déclaration ALD', NULL, NULL, '', 'aldDeclaration', 'base', @catID, 1, '2018-01-01 00:00:00', 84600, 1);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catAllergiesStruc');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('relation', 'allergieLibelleTheriaque', '', 'Libelle Thériaque de l\'allergie', 'libelle Thériaque de l\'allergie', '', '', 'text', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0),
('relation', 'allergieCodeTheriaque', '', 'Code Thériaque de l\'allergie', 'codee Thériaque de l\'allergie', '', '', 'text', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 0);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapCatSams');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'lapSamCommentaire', '', 'Commentaire patient SAM', 'commentaire par patient pour un SAM', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 1576800000, 1),
('ordo', 'lapSamDisabled', '', 'Marqueur de SAM bloqué', 'marqueur de SAM bloqué', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 1576800000, 1);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapCatMedicament');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'lapMedicamentEstPrescriptibleEnDC', '', 'Médicament prescriptible en DC', 'médicament prescriptible en DC', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapMedicamentDC', '', 'DC du médicament', 'DC du médicament', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapMedicamentCodeATC', '', 'Code ATC du médicament', 'code ATC du médicament', '', '', '', '', 'base', 86, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapMedicamentPresentationCodeTheriaque', '', 'Code Thériaque de la présentation', 'code Thériaque de la présentation (a priori le CIP7)', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapMedicamentSpecialiteCodeTheriaque', '', 'Code Thériaque de la spécialité', 'code Thériaque de la spécialité', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapMedicamentSpecialiteNom', '', 'Nom de la spécialité', 'nom de la spécialité', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapMedicamentCodeSubstanceActive', '', 'Code substance active du médicament', 'code substance active du médicament', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapCatLignePrescription');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
( 'ordo', 'lapLignePrescriptionDureeJours', '', 'Durée de la prescription en jours', 'durée de la prescription en jours', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
( 'ordo', 'lapLignePrescriptionDatePriseFinEffective', '', 'Date effective de fin de prise', 'date effective de fin de prise', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapLignePrescriptionDatePriseFin', '', 'Date de fin de prise', 'date de fin de prise', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapLignePrescriptionDatePriseDebut', '', 'Date de début de prise', 'date de début de prise', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapLignePrescriptionIsChronique', '', 'isChronique', 'ligne TT chronique ou non', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapLignePrescriptionIsALD', '', 'isALD', 'ligne ALD ou non', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapLignePrescriptionDatePriseFinAvecRenouv', '', 'Date de fin de prise renouvellements inclus', 'date de fin de prise renouvellements inclus', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapLignePrescriptionRenouvelle', '', 'ID de la ligne qui est renouvelée par cette ligne', 'ID de la ligne qui est renouvelée par cette ligne', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapCatPorteurs');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('ordo', 'lapLigneMedicament', '', 'Médicament', 'médicament LAP', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapLignePrescription', '', 'Ligne de prescription', 'ligne de prescription LAP', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapOrdonnance', '', 'Ordonnance', 'ordonnance LAP', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 1),
('ordo', 'lapSam', '', 'SAM', 'porteur SAM LAP', '', '', '', '', 'base', @catID, 1, '2018-01-01 00:00:00', 1576800000, 1);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catModelesCourriers');
INSERT IGNORE INTO `data_types`( `groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('courrier', 'modeleCourrierTtEnCours', '', 'Traitement en cours', 'modèle de courrier pour l\'impression du traitement en cours', '', '', '', 'courrier-ttEnCours', 'base', @catID, 1, '2018-01-01 00:00:00', 3600, 6);

SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='formCS');
INSERT IGNORE INTO `forms` ( `module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
('base', 'aldDeclaration', 'Déclaration d\'ALD', 'formulaire d\'enregistrement d\'une ALD', 'data_types', 'medical', 'post', '/patient/actions/saveCsForm/', @catID, 'public', 'structure:\r\n  row1:\r\n    head: Enregistrement d\'une prise en charge en ALD\r\n  row2:\r\n    col1:\r\n     size: 12\r\n     bloc:\r\n       - aldNumber                                 		#878  ALD\n  row3:\r\n    col1:\r\n     size: 4\r\n     bloc:\r\n       - aldDateDebutPriseEnCharge                 		#879  Début de prise en charge\n    col2:\r\n      size: 4\r\n      bloc:\r\n       - aldDateFinPriseEnCharge                   		#880  Fin de prise en charge\n  row4:\r\n    col1:\r\n     size: 2\r\n     bloc:\r\n       - aldCIM10,plus={<i class="fa fa-search"></i>} 		#881  Code CIM10 associé\n    col2:\r\n     size: 10\r\n     bloc:\r\n       - aldCIM10label,class=my-1,readonly                    		#883  Label CIM10 associé', NULL, ''),
('base', 'atcdStrucDeclaration', 'Déclaration d\'atcd structuré', 'ajout d\'antécédents structuré et codé CIM 10', 'data_types', 'medical', 'post', '/patient/actions/saveCsForm/', @catID, 'public', 'structure: \r\n  row1:\r\n   head : Ajout d\'un antécédent à partir de la classification CIM 10\r\n  row2:\r\n   col1: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucCIM10,plus={<i class="fa fa-search"></i>} 		#884  Code CIM 10\n   col2: \r\n     size: 10\r\n     bloc:\r\n       - atcdStrucCIM10Label,class=my-1,readonly              		#885  Label CIM 10\n  row3:\r\n    head: "Début"                  		\r\n    col1: \r\n     size: 1\r\n     bloc:\r\n       - atcdStrucDateDebutJour                    		#886  Jour\n    col2: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateDebutMois                    		#888  Mois\n    col3: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateDebutAnnee,min=1910,step=1   		#890  Année\n  row4:\r\n    head: "Fin"\r\n    col1: \r\n     size: 1\r\n     bloc:\r\n       - atcdStrucDateFinJour                      		#887  Jour\n    col2: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateFinMois                      		#889  Mois\n    col3: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateFinAnnee,min=1910,step=1     		#891  Année\n  row5:\r\n    head: "Notes"\r\n    col1: \r\n     size: 12\r\n     bloc:\r\n       - atcdStrucNotes,nolabel                    		#893  Notes', NULL, '');

SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='systemForm');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
('base', 'baseReglementS1', 'Règlement conventionné S1', 'Formulaire pour le règlement d\'honoraires conventionnés secteur 1', 'data_types', 'reglement', 'post', '/patient/ajax/saveReglementForm/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient,class=regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifSSCejour,required,readonly,plus={€},class=regleTarifCejour       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€},class=regleDepaCejour                 		#200  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€},class=regleTiersPayeur                		#202  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient,class=regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifSSCejour,required,readonly,plus={€},class=regleTarifCejour       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€},class=regleDepaCejour                 		#200  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€},class=regleTiersPayeur                		#202  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur', ''),
('base', 'baseReglementLibre', 'Formulaire règlement', 'formulaire pour le règlement d\'honoraires libres', 'data_types', 'reglement', 'post', '/patient/ajax/saveReglementForm/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 4\r\n    bloc: \r\n      - regleTarifLibreCejour,readonly,plus={€},class=regleTarifCejour  		#199  Tarif\n  col2: \r\n    size: 4\r\n    bloc: \r\n      - regleModulCejour,plus={€},class=regleDepaCejour                 		#201  Dépassement\n  col3: \r\n    size: 4\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 4\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 4\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 4\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur', 'structure:\r\n row1:\r\n  col1: \r\n    size: 4\r\n    bloc: \r\n      - regleTarifLibreCejour,readonly,plus={€},class=regleTarifCejour  		#199  Tarif\n  col2: \r\n    size: 4\r\n    bloc: \r\n      - regleModulCejour,plus={€},class=regleDepaCejour                 		#201  Dépassement\n  col3: \r\n    size: 4\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 4\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 4\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 4\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur', '');

UPDATE `forms` SET `internalName`='baseReglementS2', `name`='Règlement conventionné S2', `description`='Formulaire pour le règlement d\'honoraires conventionnés secteur 2', `yamlStructure`='structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient,class=regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifSSCejour,required,readonly,plus={€},class=regleTarifCejour       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€},class=regleDepaCejour                 		#200  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€},class=regleTiersPayeur                		#202  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur', `yamlStructureDefaut`='structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient,class=regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifSSCejour,required,readonly,plus={€},class=regleTarifCejour       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€},class=regleDepaCejour                 		#200  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€},class=regleTiersPayeur                		#202  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur' WHERE `internalName`='baseReglement';

UPDATE `forms` SET `yamlStructure`='structure:\r\n  row1:                              \r\n    col1:                              \r\n      head: \'Etat civil\'             \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - birthname,required,autocomplete,data-acTypeID=2:1 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=2:1  		#2    Nom d usage\n        - firstname,required,autocomplete,data-acTypeID=3:22:230:235:241 		#3    Prénom\n        - birthdate,class=pick-year               		#8    Date de naissance\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - personalEmail                            		#4    Email personnelle\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\n    col3:\r\n      head: \'Adresse personnelle\'\r\n      size: 4\r\n      bloc: \r\n        - streetNumber                             		#9    Numéro\n        - street,autocomplete,data-acTypeID=11:55  		#11   Rue\n        - postalCodePerso                          		#13   Code postal\n        - city,autocomplete,data-acTypeID=12:56    		#12   Ville\n        - deathdate                                		#508  Date de décès\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,rows=5                             		#21   Notes', `yamlStructureDefaut`='structure:\r\n  row1:                              \r\n    col1:                              \r\n      head: \'Etat civil\'             \r\n      size: 4\r\n      bloc:                          \r\n        - administrativeGenderCode                 		#14   Sexe\n        - birthname,required,autocomplete,data-acTypeID=2:1 		#1    Nom de naissance\n        - lastname,autocomplete,data-acTypeID=2:1  		#2    Nom d usage\n        - firstname,required,autocomplete,data-acTypeID=3:22:230:235:241 		#3    Prénom\n        - birthdate,class=pick-year               		#8    Date de naissance\n    col2:\r\n      head: \'Contact\'\r\n      size: 4\r\n      bloc:\r\n        - personalEmail                            		#4    Email personnelle\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\n    col3:\r\n      head: \'Adresse personnelle\'\r\n      size: 4\r\n      bloc: \r\n        - streetNumber                             		#9    Numéro\n        - street,autocomplete,data-acTypeID=11:55  		#11   Rue\n        - postalCodePerso                          		#13   Code postal\n        - city,autocomplete,data-acTypeID=12:56    		#12   Ville\n        - deathdate                                		#508  Date de décès\n  row2:\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,rows=5                             		#21   Notes' WHERE `internalName`='baseNewPatient';

UPDATE `forms` SET `yamlStructure`='col1:\r\n    head: "Date de naissance" \r\n    bloc: \r\n        - birthdate                                		#8    Date de naissance\ncol2:\r\n    head: "Tel" \r\n    blocseparator: " - "\r\n    bloc: \r\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\ncol3:\r\n    head: "Email"\r\n    bloc:\r\n        - personalEmail                            		#4    Email personnelle\ncol4:\r\n    head: "Ville"\r\n    bloc:\r\n        - city,text-uppercase                      		#12   Ville', `yamlStructureDefaut`='col1:\r\n    head: "Date de naissance" \r\n    bloc: \r\n        - birthdate                                		#8    Date de naissance\ncol2:\r\n    head: "Tel" \r\n    blocseparator: " - "\r\n    bloc: \r\n        - mobilePhone                              		#7    Téléphone mobile\n        - homePhone                                		#10   Téléphone domicile\ncol3:\r\n    head: "Email"\r\n    bloc:\r\n        - personalEmail                            		#4    Email personnelle\ncol4:\r\n    head: "Ville"\r\n    bloc:\r\n        - city,text-uppercase                      		#12   Ville' WHERE `internalName`='baseListingPatients';

UPDATE `forms` SET `yamlStructure`='structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient,class=regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifCejour,required,readonly,plus={€},class=regleTarifCejour       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€},class=regleDepaCejour                 		#199  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€},class=regleTiersPayeur                		#200  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur', `yamlStructureDefaut`='structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleSituationPatient,class=regleSituationPatient                      		#197  Situation du patient\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleTarifCejour,required,readonly,plus={€},class=regleTarifCejour       		#198  Tarif SS\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleDepaCejour,plus={€},class=regleDepaCejour                 		#199  Dépassement\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleFacture,readonly,plus={€},class=regleFacture           		#196  Facturé\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - regleCB,plus={€},class=regleCB                         		#194  CB\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - regleCheque,plus={€},class=regleCheque                     		#193  Chèque\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - regleEspeces,plus={€},class=regleEspeces                    		#195  Espèces\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - regleTiersPayeur,plus={€},class=regleTiersPayeur                		#200  Tiers\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - regleIdentiteCheque,class=regleIdentiteCheque                        		#205  Identité payeur' WHERE internalName='baseReglement';

UPDATE `forms` SET `yamlStructure`='col1:\r\n    head: "Activité pro" \r\n    bloc: \r\n        - job                                      		#19   Activité professionnelle\ncol2:\r\n    head: "Tel" \r\n    bloc: \r\n        - telPro                                   		#57   Téléphone professionnel\ncol3:\r\n    head: "Fax" \r\n    bloc: \r\n        - faxPro                                   		#58   Fax professionnel\ncol4:\r\n    head: "Email"\r\n    bloc-separator: " - "\r\n    bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - personalEmail                            		#4    Email personnelle\ncol5:\r\n    head: "Ville"\r\n    bloc:\r\n        - villeAdressePro,text-uppercase           		#56   Ville', `yamlStructureDefaut`='col1:\r\n    head: "Activité pro" \r\n    bloc: \r\n        - job                                      		#19   Activité professionnelle\ncol2:\r\n    head: "Tel" \r\n    bloc: \r\n        - telPro                                   		#57   Téléphone professionnel\ncol3:\r\n    head: "Fax" \r\n    bloc: \r\n        - faxPro                                   		#58   Fax professionnel\ncol4:\r\n    head: "Email"\r\n    bloc-separator: " - "\r\n    bloc:\r\n        - emailApicrypt                            		#59   Email apicrypt\n        - personalEmail                            		#4    Email personnelle\ncol5:\r\n    head: "Ville"\r\n    bloc:\r\n        - villeAdressePro,text-uppercase           		#56   Ville' WHERE `internalName`='baseListingPro';

UPDATE `forms` SET `yamlStructure`='structure:\r\n  row1: \r\n    col1: \r\n      size: 12 col-12 col-sm-4 col-lg-4\r\n      bloc: \r\n        - poids,plus={<i class="fa fa-clone duplicate"></i>} #34   Poids\r\n    col2: \r\n      size: 12 col-12 col-sm-4 col-lg-4\r\n      bloc: \r\n       - taillePatient,plus={<i class="fa fa-clone duplicate"></i>} #35   Taille\r\n    col3: \r\n      size: 12 col-12 col-sm-4 col-lg-4\r\n      bloc: \r\n       - imc,readonly,plus={<i class="fa fa-chart-line graph"></i>} #43   IMC\r\n  row2: \r\n   col1: \r\n     size: 12\r\n     bloc: \r\n       - job                                       		#19   Activité professionnelle\r\n       - allergies,rows=2                          		#66   Allergies\r\n       - toxiques                                  		#42   Toxiques\r\n  row3: \r\n    col1: \r\n     size: 12\r\n     bloc: \r\n       - atcdMedicChir,rows=6                      		#41   Antécédents médico-chirugicaux\r\n       - atcdFamiliaux,rows=6                      		#38   Antécédents familiaux', `yamlStructureDefaut`='structure:\r\n  row1: \r\n    col1: \r\n      size: 12 col-12 col-sm-4 col-lg-4\r\n      bloc: \r\n        - poids,plus={<i class="fa fa-clone duplicate"></i>} #34   Poids\r\n    col2: \r\n      size: 12 col-12 col-sm-4 col-lg-4\r\n      bloc: \r\n       - taillePatient,plus={<i class="fa fa-clone duplicate"></i>} #35   Taille\r\n    col3: \r\n      size: 12 col-12 col-sm-4 col-lg-4\r\n      bloc: \r\n       - imc,readonly,plus={<i class="fa fa-chart-line graph"></i>} #43   IMC\r\n  row2: \r\n   col1: \r\n     size: 12\r\n     bloc: \r\n       - job                                       		#19   Activité professionnelle\r\n       - allergies,rows=2                          		#66   Allergies\r\n       - toxiques                                  		#42   Toxiques\r\n  row3: \r\n    col1: \r\n     size: 12\r\n     bloc: \r\n       - atcdMedicChir,rows=6                      		#41   Antécédents médico-chirugicaux\r\n       - atcdFamiliaux,rows=6                      		#38   Antécédents familiaux' WHERE `internalName`='baseATCD';

UPDATE `forms` SET `yamlStructure`='global:\r\n  formClass: \'form-signin\' \r\nstructure:\r\n row1:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - username,required,nolabel                            		#1    Identifiant\n      - password,required,nolabel                          		#2    Mot de passe\n      - submit,Connexion,class=btn-primary,class=btn-block                                     		#3    Valider', `yamlStructureDefaut`='global:\r\n  formClass: \'form-signin\' \r\nstructure:\r\n row1:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - username,required,nolabel                            		#1    Identifiant\n      - password,required,nolabel                          		#2    Mot de passe\n      - submit,Connexion,class=btn-primary,class=btn-block                                     		#3    Valider' WHERE `internalName`='baseLogin';

UPDATE `forms` SET `formAction`='/user/ajax/userParametersClicRdv/', `yamlStructure`='global:\n  formClass:\'ajaxForm\'\nstructure:\n row1:\n  col1: \n    head: "Compte clicRDV"\n    size: 3\n    bloc:\n      - clicRdvUserId\n      - clicRdvPassword\n      - clicRdvGroupId\n      - clicRdvCalId\n      - clicRdvConsultId,nolabel', `yamlStructureDefaut`='global:\n  formClass:\'ajaxForm\'\nstructure:\n row1:\n  col1: \n    head: "Compte clicRDV"\n    size: 3\n    bloc:\n      - clicRdvUserId\n      - clicRdvPassword\n      - clicRdvGroupId\n      - clicRdvCalId\n      - clicRdvConsultId,nolabel' WHERE `internalName`='baseUserParametersClicRdv';

INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
('base', 'baseModalNewPatient', 'Formulaire nouveau patient pour modal', 'formulaire d\'enregistrement d\'un nouveau patient dans fenêtre modale', 'data_types', 'admin', 'post', '', 1, 'public', 'structure:\r\n  row1:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 12\r\n      bloc:                          \r\n        - administrativeGenderCode,nolabel      		#14   Sexe\r\n  row2:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 6\r\n      bloc:                          \r\n        - birthname,required,nolabel,autocomplete,data-acTypeID=2:1 #1    Nom de naissance\r\n    col2:\r\n      size: 6\r\n      bloc:                          \r\n        - lastname,nolabel,autocomplete,data-acTypeID=2:1  		#2    Nom d usage\r\n  row3:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 12\r\n      bloc:                          \r\n        - firstname,nolabel,required,autocomplete,data-acTypeID=3:22:230:235:241 		#3    Prénom\r\n        - birthdate,nolabel,required,class=pick-years                   		#8    Date de naissance\r\n        - personalEmail,nolabel,class=updatable             		#4    Email personnelle\r\n  row4:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 6\r\n      bloc:                          \r\n        - mobilePhone,nolabel,class=updatable               		#7    Téléphone mobile\r\n    col2:\r\n      size: 6\r\n      bloc:                          \r\n        - homePhone,nolabel,class=updatable                 		#10   Téléphone domicile\r\n\r\n  row5:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 4\r\n      bloc: \r\n        - streetNumber,nolabel,class=updatable              		#9    Numéro\r\n        - postalCodePerso,nolabel,class=updatable           		#13   Code postal\r\n    col2:\r\n      size: 8\r\n      bloc: \r\n        - street,nolabel,autocomplete,data-acTypeID=11:55,class=updatable #11   Rue\r\n        - city,nolabel,autocomplete,data-acTypeID=12:56,class=updatable #12   Ville\r\n  row6:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,nolabel,rows=5,class=updatable             		#21   Notes', 'structure:\r\n  row1:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 12\r\n      bloc:                          \r\n        - administrativeGenderCode,nolabel      		#14   Sexe\r\n  row2:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 6\r\n      bloc:                          \r\n        - birthname,required,nolabel,autocomplete,data-acTypeID=2:1 #1    Nom de naissance\r\n    col2:\r\n      size: 6\r\n      bloc:                          \r\n        - lastname,nolabel,autocomplete,data-acTypeID=2:1  		#2    Nom d usage\r\n  row3:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 12\r\n      bloc:                          \r\n        - firstname,nolabel,required,autocomplete,data-acTypeID=3:22:230:235:241 		#3    Prénom\r\n        - birthdate,nolabel,required,class=pick-years                   		#8    Date de naissance\r\n        - personalEmail,nolabel,class=updatable             		#4    Email personnelle\r\n  row4:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 6\r\n      bloc:                          \r\n        - mobilePhone,nolabel,class=updatable               		#7    Téléphone mobile\r\n    col2:\r\n      size: 6\r\n      bloc:                          \r\n        - homePhone,nolabel,class=updatable                 		#10   Téléphone domicile\r\n\r\n  row5:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 4\r\n      bloc: \r\n        - streetNumber,nolabel,class=updatable              		#9    Numéro\r\n        - postalCodePerso,nolabel,class=updatable           		#13   Code postal\r\n    col2:\r\n      size: 8\r\n      bloc: \r\n        - street,nolabel,autocomplete,data-acTypeID=11:55,class=updatable #11   Rue\r\n        - city,nolabel,autocomplete,data-acTypeID=12:56,class=updatable #12   Ville\r\n  row6:\r\n    class: \'my-0\'\r\n    col1:\r\n      size: 12\r\n      bloc:\r\n        - notes,nolabel,rows=5,class=updatable             		#21   Notes', '');

UPDATE `system` SET `value`='v3.2.0' WHERE `name`='base' and `groupe`='module';
INSERT IGNORE INTO `system` (`name`, `groupe`,`value`) VALUES
('state', 'system', 'normal');

CREATE TABLE IF NOT EXISTS `configuration` (
  `id` smallint(4) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `level` enum('default', 'module', 'user') DEFAULT 'default',
  `toID` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `module` varchar(20) NOT NULL DEFAULT '',
  `cat` varchar(30) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `value` text DEFAULT NULL,
  UNIQUE KEY `nameLevel` (`name`,`level`,`module`,`toID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `configuration`(`name`, `cat`, `level`, `type`, `description`, `value`) VALUES
('PraticienPeutEtrePatient', 'Options', 'default', 'true/false', 'si false, le praticien peut toujours avoir une fiche patient séparée', 'true'),
('VoirRouletteObstetricale', 'Options', 'default', 'true/false', '', 'true'),
('administratifSecteurHonoraires', 'Options', 'default', 'vide/1/2', 'vide pour non conventionné', '1'),
('administratifPeutAvoirFacturesTypes', 'Options', 'default', 'true/false', '', 'false'),
('administratifPeutAvoirPrescriptionsTypes', 'Options', 'default', 'true/false', '', 'false'),
('administratifPeutAvoirAgenda', 'Options', 'default', 'true/false', '', 'true'),
('administratifPeutAvoirRecettes', 'Options', 'default', 'true/false', '', 'true'),
('administratifComptaPeutVoirRecettesDe', 'Options', 'default', 'liste', 'ID des utilisateurs, séparés par des virgules (sans espace)', ''),
('templatesPdfFolder', 'Modèles de documents', 'default', 'dossier', '', ''),
('templateDefautPage', 'Modèles de documents', 'default', 'fichier', '', 'base-page-headAndFoot.html.twig'),
('templateOrdoHeadAndFoot', 'Modèles de documents', 'default', 'fichier', '', 'base-page-headAndFoot.html.twig'),
('templateOrdoBody', 'Modèles de documents', 'default', 'fichier', '', 'ordonnanceBody.html.twig'),
('templateOrdoALD', 'Modèles de documents', 'default', 'fichier', '', 'ordonnanceALD.html.twig'),
('templateCrHeadAndFoot', 'Modèles de documents', 'default', 'fichier', '', 'base-page-headAndNoFoot.html.twig'),
('templateCourrierHeadAndFoot', 'Modèles de documents', 'default', 'fichier', '', 'base-page-headAndNoFoot.html.twig'),
('smtpTracking', 'Mail', 'default', 'texte', '', ''),
('smtpFrom', 'Mail', 'default', 'email', 'ex: user@domain.net', ''),
('smtpFromName', 'Mail', 'default', 'texte', '', ''),
('smtpHost', 'Mail', 'default', 'url/ip', '', ''),
('smtpPort', 'Mail', 'default', 'nombre', '', '587'),
('smtpSecureType', 'Mail', 'default', 'texte', '', 'tls'),
('smtpOptions', 'Mail', 'default', 'texte', '', 'off'),
('smtpUsername', 'Mail', 'default', 'texte', '', ''),
('smtpPassword', 'Mail', 'default', 'texte', '', ''),
('smtpDefautSujet', 'Mail', 'default', 'texte', '', 'Document vous concernant'),
('apicryptCheminInbox', 'Apicrypt', 'default', 'dossier', '', ''),
('apicryptCheminArchivesInbox', 'Apicrypt', 'default', 'dossier', '', ''),
('apicryptCheminFichierNC', 'Apicrypt', 'default', 'dossier', '', ''),
('apicryptCheminFichierC', 'Apicrypt', 'default', 'dossier', '', ''),
('apicryptCheminVersClefs', 'Apicrypt', 'default', 'dossier', '', ''),
('apicryptCheminVersBinaires', 'Apicrypt', 'default', 'dossier', '', ''),
('apicryptInboxMailForUserID', 'Apicrypt', 'default', 'nombre', '', '0'),
('apicryptUtilisateur', 'Apicrypt', 'default', 'texte', 'prenom.NOM', ''),
('apicryptAdresse', 'Apicrypt', 'default', 'texte', 'prenom.NOM@medicalXX.apicrypt.org', ''),
('apicryptSmtpHost', 'Apicrypt', 'default', 'url/ip', '', ''),
('apicryptSmtpPort', 'Apicrypt', 'default', 'nombre', '', '25'),
('apicryptPopHost', 'Apicrypt', 'default', 'url/ip', '', ''),
('apicryptPopPort', 'Apicrypt', 'default', 'nombre', '', '110'),
('apicryptPopUser', 'Apicrypt', 'default', 'texte', 'prenom.NOM', ''),
('apicryptPopPassword', 'Apicrypt', 'default', 'texte', 'passwordapicrypt', ''),
('apicryptDefautSujet', 'Apicrypt', 'default', 'texte', '', 'Document concernant votre patient'),
('faxService', 'Fax', 'default', 'vide/ecofaxOVH', '', ''),
('ecofaxMyNumber', 'Fax', 'default', 'n° fax', 'ex: 0900000000', ''),
('ecofaxPassword', 'Fax', 'default', 'texte', 'password', ''),
('dicomHost', 'DICOM', 'default', 'url/ip', '', ''),
('dicomPrefixIdPatient', 'DICOM', 'default', 'texte', '', '1.100.100'),
('dicomAutoSendPatient', 'DICOM', 'default', 'true/false', 'Envoi automatique du patient à l`imagerie à l\'ouverture du dossier', 'false'),
('dicomDiscoverNewTags', 'DICOM', 'default', 'true/false', '', 'true'),
('dicomWorkListDirectory', 'DICOM', 'default', 'dossier', '', ''),
('dicomWorkingDirectory', 'DICOM', 'default', 'dossier', '', ''),
('phonecaptureFingerprint', 'Phonecapture', 'default', 'texte', '', 'phonecapture'),
('phonecaptureCookieDuration', 'Phonecapture', 'default', 'nombre', '', '31104000'),
('phonecaptureResolutionWidth', 'Phonecapture', 'default', 'nombre', '', '1920'),
('phonecaptureResolutionHeight', 'Phonecapture', 'default', 'nombre', '', '1080'),
('agendaService', 'Agenda', 'default', 'vide/clicRDV', '', ''),
('agendaPremierJour', 'Agenda', 'default', 'vide/nombre', 'vide pour roulant, 0 pour dimanche, 1 pour lundi, etc...', '1'),
('agendaDistantLink', 'Agenda', 'default', 'url', 'si agendaService est configuré, alors agendaDistantLink doit être vide', ''),
('agendaDistantPatientsOfTheDay', 'Agenda', 'default', 'url', '', ''),
('agendaLocalPatientsOfTheDay', 'Agenda', 'default', 'fichier', '', 'patientsOfTheDay.json'),
('agendaNumberForPatientsOfTheDay', 'Agenda', 'default', 'nombre', '', '0'),
('agendaModePanneauLateral', 'Agenda', 'default', 'true/false', 'Utilisation du panneau latéral (true) ou d\'une fenêtre contextuelle (false)', 'true'),
('mailRappelLogCampaignDirectory', 'Rappels mail', 'default', 'dossier', '', ''),
('mailRappelDaysBeforeRDV', 'Rappels mail', 'default', 'nombre', '', '3'),
('smsProvider', 'Rappels SMS', 'default', 'url/ip', '', ''),
('smsLogCampaignDirectory', 'Rappels SMS', 'default', 'dossier', '', ''),
('smsDaysBeforeRDV', 'Rappels SMS', 'default', 'nombre', '', '3'),
('smsCreditsFile', 'Rappels SMS', 'default', 'fichier', '', 'creditsSMS.txt'),
('smsSeuilCreditsAlerte', 'Rappels SMS', 'default', 'nombre', '', '150'),
('smsTpoa', 'Rappels SMS', 'default', 'texte', '', 'Dr ....'),
('clicRdvApiKey', 'clicRDV', 'default', 'texte', '', ''),
('clicRdvUserId', 'clicRDV', 'default', 'texte', '', ''),
('clicRdvPassword', 'clicRDV', 'default', 'texte', '', ''),
('clicRdvGroupId', 'clicRDV', 'default', 'nombre', '', ''),
('clicRdvCalId', 'clicRDV', 'default', 'nombre', '', ''),
('clicRdvConsultId', 'clicRDV', 'default', 'JSON', '', ''),
('utiliserLap','propre','LAP', 'default', 'true/false', '', ''),
('lapActiverAtcdStrucSur', 'LAP', 'default', 'texte', '', ''),
('lapActiverAllergiesStrucSur', 'LAP', 'default', 'texte','', ''),
('lapAtcdStrucPersoPourAnalyse', 'LAP', 'default', 'texte','', ''),
('lapAllergiesStrucPersoPourAnalyse', 'LAP', 'default', 'texte','', ''),
('theriaqueMode', 'LAP', 'default', 'texte','', ''),
('theriaqueWsURL', 'LAP', 'default', 'texte','', ''),
('theriaqueShowMedicHospi', 'LAP', 'default', 'true/false', '', 'true'),
('theriaqueShowMedicNonComer', 'LAP', 'default', 'true/false', '', 'false'),
('lapAlertPatientTermeGrossesseSup46', 'LAP', 'default', 'true/false', '', 'true'),
('lapAlertPatientAllaitementSup3Ans', 'LAP', 'default', 'true/false', '', 'true');

INSERT IGNORE INTO configuration (`name`, `level`, `toID`, `value`)
SELECT dt.name, 'user', od.toID, od.value FROM objets_data AS od
LEFT JOIN data_types AS dt ON dt.id=od.typeID
WHERE od.deleted='' and od.outdated='' and dt.groupe='user';
