-- Mise à jour n° de version
UPDATE `system` SET `value`='v5.5.0' WHERE `name`='base' and `groupe`='module';

-- Mode vitale
INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('vitaleMode', 'default', '0', '', 'Vitale', 'texte', 'simple / complet', 'simple');

-- Modification types pour autosize
UPDATE `data_types` SET `formType` = 'textarea' WHERE `name` = 'job';
UPDATE `data_types` SET `formType` = 'textarea' WHERE `name` = 'toxiques';

-- Modification formulaires pour autosize
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'baseSynthese,rows=8', 'baseSynthese,rows=2') where internalName = 'baseSynthese';

UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'atcdMedicChir,rows=6', 'atcdMedicChir,rows=2') where internalName = 'baseATCD';
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'atcdFamiliaux,rows=6', 'atcdFamiliaux,rows=2') where internalName = 'baseATCD';
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, 'allergies,rows=2', 'allergies,rows=1') where internalName = 'baseATCD';
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- job', '- job,rows=1') where internalName = 'baseATCD';
UPDATE `forms` SET `yamlStructure` = replace(yamlStructure, '- toxiques', '- toxiques,rows=1') where internalName = 'baseATCD';

update `forms` set `javascript`='$(document).ready(function() {\r\n\r\n  //calcul IMC\r\n  if ($(\'#id_imc_id\').length > 0) {\r\n\r\n    imc = imcCalc($(\'#id_poids_id\').val(), $(\'#id_taillePatient_id\').val());\r\n    if (imc > 0) {\r\n      $(\'#id_imc_id\').val(imc);\r\n    }\r\n\r\n    $(\"#patientLatCol\").on(\"keyup\", \"#id_poids_id , #id_taillePatient_id\", function() {\r\n      poids = $(\'#id_poids_id\').val();\r\n      taille = $(\'#id_taillePatient_id\').val();\r\n      imc = imcCalc(poids, taille);\r\n      $(\'#id_imc_id\').val(imc);\r\n      patientID = $(\'#identitePatient\').attr(\"data-patientID\");\r\n      setPeopleDataByTypeName(imc, patientID, \'imc\', \'#id_imc_id\', \'0\');\r\n\r\n    });\r\n  }\r\n\r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseATCD textarea\')); \r\n  \r\n});' where internalName = 'baseATCD';
update `forms` set `javascript`='$(document).ready(function() {  \r\n  //ajutement auto des textarea en hauteur\r\n  autosize($(\'#formName_baseSynthese textarea\'));\r\n });' where internalName = 'baseSynthese';
