-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.5.0' WHERE `name`='base' and `groupe`='module';

-- table DICOM Tags : retrait typeID et ajout TypeName
ALTER TABLE `dicomTags` ADD `typeName` VARCHAR(60) DEFAULT NULL AFTER `typeID`;
ALTER TABLE `dicomTags` DROP INDEX `dicomTag`;
ALTER TABLE `dicomTags` DROP INDEX `typeID`;
update `dicomTags` as dc left join data_types as d on dc.typeID=d.id set dc.typeName = d.name where dc.typeID > 0;
CREATE INDEX `typeName` ON `dicomTags` (`typeName`);
CREATE UNIQUE INDEX `dicomTag` ON `dicomTags` (`dicomTag`, `typeName`);
ALTER TABLE `dicomTags` DROP `typeID`;

-- modification table data_cat
ALTER TABLE `data_cat` CHANGE `type` `type` ENUM('base','module', 'user') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'base';

-- Mode vitale
INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('vitaleMode', 'default', '0', '', 'Vitale', 'texte', 'simple / complet', 'simple');

-- Modification types pour autosize
UPDATE `data_types` SET `formType` = 'textarea' WHERE `name` = 'job';
UPDATE `data_types` SET `formType` = 'textarea' WHERE `name` = 'toxiques';

-- Modification formulaires pour autosize
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'baseSynthese,rows=8', 'baseSynthese,rows=2') where internalName = 'baseSynthese';

UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- job,autocomplete', '- job,autocomplete,rows=1') where internalName = 'baseNewPro';
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'notesPro,rows=5', 'notesPro,rows=3') where internalName = 'baseNewPro';
UPDATE `forms` set `javascript`='$(document).ready(function() {\r\n\r\n   // modal edit data admin patient\r\n  $(\'#newPro\').on(\'shown.bs.modal\', function (e) {\r\n    autosize.update($(\'#newPro textarea\'));\r\n  });\r\n  \r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseNewPro textarea\')); \r\n\r\n});' where internalName = 'baseNewPro';

UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'notes,rows=5', 'notes,rows=3') where internalName = 'baseNewPatient';
UPDATE `forms` SET `javascript`='$(document).ready(function() {\r\n\r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseNewPatient textarea\')); \r\n\r\n  // modal edit data admin patient\r\n  $(\'#editAdmin\').on(\'shown.bs.modal\', function (e) {\r\n    autosize.update($(\'#editAdmin textarea\'));\r\n  });\r\n  \r\n});' where internalName = 'baseNewPatient';

UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'atcdMedicChir,rows=6', 'atcdMedicChir,rows=2') where internalName = 'baseATCD';
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'atcdFamiliaux,rows=6', 'atcdFamiliaux,rows=2') where internalName = 'baseATCD';
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'allergies,rows=2', 'allergies,rows=1') where internalName = 'baseATCD';
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- job', '- job,rows=1') where internalName = 'baseATCD';
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- toxiques', '- toxiques,rows=1') where internalName = 'baseATCD';

update `forms` set `javascript`='$(document).ready(function() {\r\n\r\n  //calcul IMC\r\n  if ($(\'#id_imc_id\').length > 0) {\r\n\r\n    imc = imcCalc($(\'#id_poids_id\').val(), $(\'#id_taillePatient_id\').val());\r\n    if (imc > 0) {\r\n      $(\'#id_imc_id\').val(imc);\r\n    }\r\n\r\n    $(\"#patientLatCol\").on(\"keyup\", \"#id_poids_id , #id_taillePatient_id\", function() {\r\n      poids = $(\'#id_poids_id\').val();\r\n      taille = $(\'#id_taillePatient_id\').val();\r\n      imc = imcCalc(poids, taille);\r\n      $(\'#id_imc_id\').val(imc);\r\n      patientID = $(\'#identitePatient\').attr(\"data-patientID\");\r\n      setPeopleDataByTypeName(imc, patientID, \'imc\', \'#id_imc_id\', \'0\');\r\n\r\n    });\r\n  }\r\n\r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseATCD textarea\')); \r\n  \r\n});' where internalName = 'baseATCD';
update `forms` set `javascript`='$(document).ready(function() {  \r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseSynthese textarea\'));\r\n });' where internalName = 'baseSynthese';

-- coquille
UPDATE `data_types` SET `placeholder` = 'notes concernant cet antécédent' WHERE name = 'atcdStrucNotes';

update `forms` set `javascript`= '$(document).ready(function() {\r\n  $(\"#nouvelleCs\").on(\"click\",\"#id_atcdStrucCIM10_idAddOn\", function() {\r\n    $(\'#searchCIM10\').modal(\'show\');\r\n  });\r\n\r\n  $(\'#searchCIM10\').on(\'shown.bs.modal\', function() {\r\n    $(\'#searchCIM10 #texteRechercheCIM10\').focus();\r\n  });\r\n\r\n  $(\"#texteRechercheCIM10\").typeWatch({\r\n    wait: 1000,\r\n    highlight: false,\r\n    allowSubmit: false,\r\n    captureLength: 3,\r\n    callback: function(value) {\r\n      $.ajax({\r\n        url: urlBase+\'/lap/ajax/cim10search/\',\r\n        type: \'post\',\r\n        data: {\r\n          term: value\r\n        },\r\n        dataType: \"html\",\r\n        beforeSend: function() {\r\n          $(\'#codeCIM10trouves\').html(\'<div class=\"col-md-12\">Attente des résultats de la recherche ...</div>\');\r\n        },\r\n        success: function(data) {\r\n          $(\'#codeCIM10trouves\').html(data);\r\n        },\r\n        error: function() {\r\n          alert(\'Problème, rechargez la page !\');\r\n        }\r\n      });\r\n    }\r\n  });\r\n\r\n  $(\'#searchCIM10\').on(\"click\", \"button.catchCIM10\", function() {\r\n    code = $(this).attr(\'data-code\');\r\n    label = $(this).attr(\'data-label\');\r\n    $(\"#id_atcdStrucCIM10_id\").val(code);\r\n    $(\"#id_atcdStrucCIM10Label_id\").val(label);\r\n    $(\'#searchCIM10\').modal(\'toggle\');\r\n    $(\'#codeCIM10trouves\').html(\'\');\r\n    $(\"#texteRechercheCIM10\").val(\'\');\r\n\r\n  });\r\n  \r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#id_atcdStrucNotes_id\'));\r\n  \r\n});', yamlStructure = 'global:\r\n  formClass: \'ignoreReturn\'\r\nstructure: \r\n  row1:\r\n   head : Ajout d\'un antécédent à partir de la classification CIM 10\r\n   col1: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucCIM10,plus={<i class=\"fa fa-search\"></i>} 		#891  Code CIM 10\n   col2: \r\n     size: 7\r\n     bloc:\r\n       - atcdStrucCIM10Label,readonly              		#892  Label CIM 10\n   col3:\r\n     size: 3\r\n     bloc:\r\n       - atcdStrucCIM10InLap                       		#925  A prendre en compte dans le LAP\n\r\n  row2:\r\n    col1: \r\n     size: 5\r\n     head: \'Début\'\r\n    col2: \r\n     size: 2\r\n     head: \'\'\r\n    col3:\r\n     size: 5\r\n     head: \'Fin\'\r\n  \r\n   \r\n  row3:\r\n    col1: \r\n     size: 1\r\n     bloc:\r\n       - atcdStrucDateDebutJour                    		#893  Jour\n    col2: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateDebutMois                    		#895  Mois\n    col3: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateDebutAnnee,min=1910,step=1   		#897  Année\n    col4: \r\n     size: 2\r\n    col5: \r\n     size: 1\r\n     bloc:\r\n       - atcdStrucDateFinJour                      		#894  Jour\n    col6: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateFinMois                      		#896  Mois\n    col7: \r\n     size: 2\r\n     bloc:\r\n       - atcdStrucDateFinAnnee,min=1910,step=1     		#898  Année\n  row4:\r\n    col1: \r\n      head: \"Notes\"\r\n      size: 12\r\n      bloc:\r\n        - atcdStrucNotes,nolabel                   		#899  Notes' WHERE internalName = 'atcdStrucDeclaration';

-- corrections
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('relation', 'allergieCodeTheriaque', '', 'Code Thériaque de l\'allergie', 'codee Thériaque de l\'allergie', '', '', 'text', '', 'base', 71, 1, '2018-01-01 00:00:00', 3600, 0),
('ordo', 'lapMedicamentEstPrescriptibleEnDC', '', 'Médicament prescriptible en DC', 'médicament prescriptible en DC', '', '', '', '', 'base', 75, 1, '2018-01-01 00:00:00', 3600, 1),
('medical', 'freqCardiaque', '', 'FC', 'fréquence cardiaque en bpm', '', '', 'text', '', 'base', 28, 1, '2018-05-14 13:41:42', 60, 1),
('medical', 'spO2', '', 'SpO2', 'saturation en oxygène', '', '', 'text', '', 'base', 28, 1, '2018-05-15 10:08:20', 60, 1);
