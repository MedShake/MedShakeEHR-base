-- !!! prévoir aussi update formulaire atcd


INSERT INTO `forms` ( `module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
('base', 'aldDeclaration', 'Déclaration d\'ALD', 'formulaire d\'enregistrement d\'une ALD', 'data_types', 'medical', 'post', '/patient/actions/saveCsForm/', 4, 'public', 'structure:\r\n  row1:\r\n    head: Enregistrement d\'une prise en charge en ALD\r\n    col1:\r\n     size: 12\r\n     bloc:\r\n       - aldNumber                                 		#878  ALD\n  row2:\r\n    col1:\r\n     size: 4\r\n     bloc:\r\n       - aldDateDebutPriseEnCharge                 		#879  Début de prise en charge\n    col2:\r\n      size: 4\r\n      bloc:\r\n       - aldDateFinPriseEnCharge                   		#880  Fin de prise en charge\n  row3:\r\n    col1:\r\n     size: 2\r\n     bloc:\r\n       - aldCIM10,plus={<i class="glyphicon glyphicon-search"></i>} 		#881  Code CIM10 associé\n    col2:\r\n     size: 10\r\n     bloc:\r\n       - aldCIM10label,readonly                    		#883  Label CIM10 associé', NULL, ''),
('base', 'atcdStrucDeclaration', 'Déclaration d\'atcd structuré', 'ajout d\'antécédents structuré et codé CIM 10', 'data_types', 'medical', 'post', '/patient/actions/saveCsForm/', 4, 'public', 'structure: \r\n  row1:\r\n   head : Ajout d\'un antécédent à partir de la classification CIM 10\r\n   col1: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucCIM10,plus={<i class="glyphicon glyphicon-search"></i>} 		#884  Code CIM 10\n   col2: \r\n     size: 10\r\n     bloc:\r\n       - atcdStrucCIM10Label,readonly              		#885  Label CIM 10\n  row2:\r\n    head: "Début"                  		\r\n    col1: \r\n     size: 1\r\n     bloc:\r\n       - atcdStrucDateDebutJour                    		#886  Jour\n    col2: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateDebutMois                    		#888  Mois\n    col3: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateDebutAnnee,min=1910,step=1   		#890  Année\n  row3:\r\n    head: "Fin"\r\n    col1: \r\n     size: 1\r\n     bloc:\r\n       - atcdStrucDateFinJour                      		#887  Jour\n    col2: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateFinMois                      		#889  Mois\n    col3: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateFinAnnee,min=1910,step=1     		#891  Année\n  row4:\r\n    head: "Notes"\r\n    col1: \r\n     size: 12\r\n     bloc:\r\n       - atcdStrucNotes,nolabel                    		#893  Notes', NULL, '');

INSERT INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('medical', 'aldCat', 'ALD', 'paramètres pour la gestion des ALD', 'base', 1, '2018-01-19 10:29:09'),
('medical', 'catAtcdStruc', 'ATCD structurés', 'données pour antécédents structurés', 'base', 1, '2018-01-22 12:45:18'),
('typecs', 'catTypeCsATCD', 'Antécédents et allergies', 'antécédents et allergies', 'base', 1, '2018-01-22 20:31:57'),
('relation', 'catAllergiesStruc', 'Allergies structurées', 'données pour allergies structurées', 'base', 1, '2018-01-23 10:21:09'),
('user', 'lapUserParamCat', 'LAP', 'paramètres pour les réglages utilisateur dans le LAP', 'base', 1, '2018-03-07 21:03:15');

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='dataBio');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'clairanceCreatinine', 'ml/min', 'Clairance créatinine', 'clairance de la créatinine', '', '', 'text', '', 'base', 31, 1, '2018-03-08 12:42:12', 3600, 1);


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='atcd');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'allaitementActuel', '', 'Allaitement', 'allaitement actuel', '', '', 'text', '', 'base', @catID, 1, '2018-03-08 10:46:16', 3600, 1),
('medical', 'insuffisanceHepatique', '', 'Insuffisance hépatique', 'degré d\'insuffisance hépatique', '', '', 'select', '\'z\': "?"\n\'n\': "Pas d\'insuffisance hépatique connue"\n\'1\': \'Légère\'\n\'2\': \'Modérée\'\n\'3\': \'Sévère\'', 'base', 29, 3, '2018-03-08 13:19:28', 3600, 1);


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapUserParamCat');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('user', 'lapAlertPatientTermeGrossesseSup46', '', 'lapAlertPatientTermeGrossesseSup46', 'alerte pour terme de grossesse supérieur à 46SA', '', '', 'checkbox', '', 'base', @catID, 1, '2018-03-07 21:04:34', 3600, 1),
('user', 'lapAlertPatientAllaitementSup3Ans', '', 'lapAlertPatientAllaitementSup3Ans', 'alerte pour allaitement supérieur à 3 ans', '', '', 'checkbox', '', 'base', @catID, 1, '2018-03-07 21:05:06', 3600, 1);


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='aldCat');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'aldNumber', '', 'ALD', 'ALD choisie', '', '', 'select', '1: "Accident vasculaire cérébral invalidant"\n2: "Insuffisances médullaires et autres cytopénies chroniques"\n3: "Artériopathies chroniques avec manifestations ischémiques"\n4: "Bilharziose compliquée"\n5: "Insuffisance cardiaque grave, troubles du rythme graves, cardiopathies valvulaires graves, cardiopathies  congénitales graves"\n6: "Maladies chroniques actives du foie et cirrhoses"\n7: "Déficit immunitaire primitif grave nécessitant un traitement prolongé, infection par le virus de 9: l\'immuno-déficience humaine (VIH)"\n8: "Diabète de type 1 et diabète de type 2"\n9: "Formes graves des affections neurologiques et musculaires (dont myopathie), épilepsie grave"\n10: "Hémoglobinopathies, hémolyses, chroniques constitutionnelles et acquises sévères"\n11: "Hémophilies et affections constitutionnelles de l\'hémostase graves"\n12: "Maladie coronaire"\n13: "Insuffisance respiratoire chronique grave"\n14: "Maladie d\'Alzheimer et autres démences"\n15: "Maladie de Parkinson"\n16: "Maladies métaboliques héréditaires nécessitant un traitement prolongé spécialisé"\n17: "Mucoviscidose"\n18: "Néphropathie chronique grave et syndrome néphrotique primitif"\n19: "Paraplégie"\n20: "Vascularites, lupus érythémateux systémique, sclérodermie systémique"\n21: "Polyarthrite rhumatoïde évolutive"\n22: "Affections psychiatriques de longue durée"\n23: "Rectocolite hémorragique et maladie de Crohn évolutives"\n24: "Sclérose en plaques"\n25: "Scoliose idiopathique structurale évolutive (dont l\'angle est égal ou supérieur à 25 degrés) jusqu\'à maturation rachidienne"\n26: "Spondylarthrite grave"\n27: "Suites de transplantation d\'organe"\n28: "Tuberculose active, lèpre"\n29: "Tumeur maligne, affection maligne du tissu lymphatique ou hématopoïétique"\n31: "Affection hors liste"\n32: "Etat polypathologique"', 'base', @catID, 1, '2018-01-19 15:45:35', 3600, 0),
('medical', 'aldDateDebutPriseEnCharge', '', 'Début de prise en charge', 'date de début de prise en charge', '', '', 'date', '', 'base', @catID, 1, '2018-01-19 10:35:18', 3600, 0),
('medical', 'aldDateFinPriseEnCharge', '', 'Fin de prise en charge', 'date de fin de prise en charge', '', '', 'date', '', 'base', @catID, 1, '2018-01-19 10:35:57', 3600, 0),
('medical', 'aldCIM10', '', 'Code CIM10 associé', 'Code CIM10 attaché à l\'ALD', '', '', 'text', '', 'base', @catID, 1, '2018-01-19 15:54:50', 3600, 0),
('medical', 'aldCIM10label', '', 'Label CIM10 associé', 'Label CIM10 attaché à l\'ALD', '', '', 'text', '', 'base', @catID, 1, '2018-01-19 16:48:49', 3600, 0);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catAtcdStruc');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('medical', 'atcdStrucCIM10', '', 'Code CIM 10', 'code CIM 10 de l\'atcd', '', '', 'text', '', 'base', @catID, 1, '2018-01-22 13:32:53', 3600, 0),
('medical', 'atcdStrucCIM10Label', '', 'Label CIM 10', 'label CIM 10 de l\'atcd', '', '', 'text', '', 'base', @catID, 1, '2018-01-22 13:33:11', 3600, 0),
('medical', 'atcdStrucDateDebutJour', '', 'Jour', 'jour de début de l\'atcd', '', '', 'number', '0', 'base', @catID, 1, '2018-01-22 13:32:59', 3600, 0),
('medical', 'atcdStrucDateFinJour', '', 'Jour', 'jour de fin de l\'atcd', '', '', 'number', '0', 'base', @catID, 1, '2018-01-22 13:33:05', 3600, 0),
('medical', 'atcdStrucDateDebutMois', '', 'Mois', 'mois de début de l\'atcd', '', '', 'select', '\'0\' : \'non précisé\'\n\'1\' : \'janvier\'\n\'2\' : \'février\'\n\'3\' : \'mars\'\n\'4\' : \'avril\'\n\'5\' : \'mai\'\n\'6\' : \'juin\'\n\'7\' : \'juillet\'\n\'8\' : \'août\'\n\'9\' : \'septembre\'\n\'10\' : \'octobre\'\n\'11\' : \'novembre\'\n\'12\' : \'décembre\'', 'base', 80, 1, '2018-01-22 13:33:16', 3600, 0),
('medical', 'atcdStrucDateFinMois', '', 'Mois', 'mois de fin de l\'atcd', '', '', 'select', '\'0\' : \'non précisé\'\n\'1\' : \'janvier\'\n\'2\' : \'février\'\n\'3\' : \'mars\'\n\'4\' : \'avril\'\n\'5\' : \'mai\'\n\'6\' : \'juin\'\n\'7\' : \'juillet\'\n\'8\' : \'août\'\n\'9\' : \'septembre\'\n\'10\' : \'octobre\'\n\'11\' : \'novembre\'\n\'12\' : \'décembre\'', 'base', @catID, 1, '2018-01-22 13:33:22', 3600, 0),
('medical', 'atcdStrucDateDebutAnnee', '', 'Année', 'année de début de l\'atcd', '', '', 'number', '', 'base', @catID, 1, '2018-01-22 13:32:41', 3600, 0),
('medical', 'atcdStrucDateFinAnnee', '', 'Année', 'année de fin de l\'atcd', '', '', 'number', '', 'base', @catID, 1, '2018-01-22 13:32:47', 3600, 0),
('medical', 'atcdStrucNotes', 'notes concernant cet antécédents', 'Notes', 'notes concernant l\'atcd', '', '', 'textarea', '', 'base', @catID, 1, '2018-01-22 13:33:28', 3600, 0);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catTypeCsATCD');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('typecs', 'csAtcdStrucDeclaration', NULL, 'Ajout d\'antécédent', 'support parent pour déclaration d\'antécédent structuré', NULL, NULL, '', 'atcdStrucDeclaration', 'base', @catID, 1, '2018-01-22 20:32:12', 84600, 1),
('typecs', 'csAldDeclaration', NULL, 'Déclaration ALD', 'support parent pour déclaration ALD', NULL, NULL, '', 'aldDeclaration', 'base', @catID, 1, '2018-01-22 20:32:22', 84600, 1);

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='catAllergiesStruc');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('relation', 'allergieLibelleTheriaque', '', 'Libelle Thériaque de l\'allergie', 'libelle Thériaque de l\'allergie', '', '', 'text', '', 'base', @catID, 1, '2018-01-23 10:21:58', 3600, 0),
('relation', 'allergieCodeTheriaque', '', 'Code Thériaque de l\'allergie', 'codee Thériaque de l\'allergie', '', '', 'text', '', 'base', @catID, 1, '2018-01-23 10:22:21', 3600, 0);

INSERT INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('ordo', 'lapCatPorteurs', 'LAP porteurs', 'data pour les porteurs LAP', 'base', 1, '2018-02-13 20:57:19'),
('ordo', 'lapCatLignePrescription', 'LAP ligne de prescription', 'data des lignes de prescription', 'base', 1, '2018-02-13 20:58:15'),
('ordo', 'lapCatMedicament', 'LAP médicament', 'data pour les médicaments', 'base', 1, '2018-02-13 20:59:11'),


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapCatMedicament');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
( 'ordo', 'lapMedicamentEstPrescriptibleEnDC', '', 'Médicament prescriptible en DC', 'médicament prescriptible en DC', '', '', '', '', 'base', @catID, 1, '2018-02-14 12:45:47', 3600, 1),
( 'ordo', 'lapMedicamentDC', '', 'DC du médicament', 'DC du médicament', '', '', '', '', 'base', @catID, 1, '2018-02-13 21:34:53', 3600, 1),
( 'ordo', 'lapMedicamentCodeATC', '', 'Code ATC du médicament', 'code ATC du médicament', '', '', '', '', 'base', 86, 1, '2018-02-13 21:33:48', 3600, 1),
( 'ordo', 'lapMedicamentPresentationCodeTheriaque', '', 'Code Thériaque de la présentation', 'code Thériaque de la présentation (a priori le CIP7)', '', '', '', '', 'base', @catID, 1, '2018-02-13 21:31:52', 3600, 1),
( 'ordo', 'lapMedicamentSpecialiteCodeTheriaque', '', 'Code Thériaque de la spécialité', 'code Thériaque de la spécialité', '', '', '', '', 'base', @catID, 1, '2018-02-13 21:28:46', 3600, 1),
( 'ordo', 'lapMedicamentSpecialiteNom', '', 'Nom de la spécialité', 'nom de la spécialité', '', '', '', '', 'base', @catID, 1, '2018-02-13 21:28:01', 3600, 1),

SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapCatLignePrescription');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
( 'ordo', 'lapLignePrescriptionDureeJours', '', 'Durée de la prescription en jours', 'durée de la prescription en jours', '', '', '', '', 'base', @catID, 1, '2018-02-14 14:08:21', 3600, 1),
( 'ordo', 'lapLignePrescriptionDatePriseFinEffective', '', 'Date effective de fin de prise', 'date effective de fin de prise', '', '', '', '', 'base', @catID, 1, '2018-02-13 21:17:40', 3600, 1),
( 'ordo', 'lapLignePrescriptionDatePriseFin', '', 'Date de fin de prise', 'date de fin de prise', '', '', '', '', 'base', @catID, 1, '2018-02-13 21:17:12', 3600, 1),
( 'ordo', 'lapLignePrescriptionDatePriseDebut', '', 'Date de début de prise', 'date de début de prise', '', '', '', '', 'base', @catID, 1, '2018-02-13 21:16:43', 3600, 1),
( 'ordo', 'lapLignePrescriptionIsChronique', '', 'isChronique', 'ligne TT chronique ou non', '', '', '', '', 'base', @catID, 1, '2018-02-13 21:04:02', 3600, 1),
( 'ordo', 'lapLignePrescriptionIsALD', '', 'isALD', 'ligne ALD ou non', '', '', '', '', 'base', @catID, 1, '2018-02-13 21:01:13', 3600, 1);
('ordo', 'lapLignePrescriptionDatePriseFinAvecRenouv', '', 'Date de fin de prise renouvellements inclus', 'date de fin de prise renouvellements inclus', '', '', '', '', 'base', @catID, 1, '2018-03-09 12:21:46', 3600, 1);


SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='lapCatPorteurs');
INSERT INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
( 'ordo', 'lapLigneMedicament', '', 'Médicament', 'médicament LAP', '', '', '', '', 'base', @catID, 1, '2018-02-13 20:56:17', 3600, 1),
( 'ordo', 'lapLignePrescription', '', 'Ligne de prescription', 'ligne de prescription LAP', '', '', '', '', 'base', @catID, 1, '2018-02-13 20:55:32', 3600, 1),
( 'ordo', 'lapOrdonnance', '', 'Ordonnance', 'ordonnance LAP', '', '', '', '', 'base', @catID, 1, '2018-02-13 20:54:31', 3600, 1);
