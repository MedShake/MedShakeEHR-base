-- Mise à jour n° de version
UPDATE `system` SET `value`='v6.4.0' WHERE `name`='base' and `groupe`='module';

-- update de la table people pour gestion du password recovery
ALTER TABLE `people` ADD `lastLostPassDate` DATETIME NULL AFTER `lastLogFingerprint`, ADD `lastLostPassRandStr` VARCHAR(25) NULL AFTER `lastLostPassDate`;

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeLoginPassAttribution', 'default', '0', '', 'Options', 'texte', 'méthode d\'attribution des mots de passe utilisateur : admin / random', 'admin');

INSERT IGNORE INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeLoginPassOnlineRecovery', 'default', '0', '', 'Options', 'true/false', 'possibilité de réinitialiser son mot de passe perdu via email ', 'false');

UPDATE data_types SET validationRules = '' WHERE name = 'password' and groupe = 'system';
UPDATE forms set yamlStructure=REPLACE(yamlStructure, '- password,required', '- password') where internalName in ('baseNewUserFromPeople', 'baseNewUser');

-- nouveau formulaire pour procédure password recovery
SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='systemForm');
INSERT IGNORE INTO `forms` (`module`, `internalName`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `options`, `printModel`, `cda`, `javascript`) VALUES
('base', 'baseUserPasswordRecovery', 'Nouveau password après perte', 'saisie d\'un nouveau password en zone publique après perte', 'data_types', 'admin', 'post', '/patient/ajax/saveCsForm/', @catID, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: col-12\r\n    bloc:\r\n      - password,required                          		#1789 Mot de passe\n      - verifPassword                              		#1791 Confirmation du mot de passe', '', '', '', '$(document).ready(function() {\r\n  $(\"#treatNewPass\").on(\"click\", function(e) {\r\n    e.preventDefault();\r\n    password = $(\'#id_password_id\').val();\r\n    verifPassword = $(\'#id_verifPassword_id\').val();\r\n	randStringControl = $(\'input[name=\"randStringControl\"]\').val();\r\n\r\n    $.ajax({\r\n      url: urlBase + \'/public/ajax/publicLostPasswordNewPassTreat/\',\r\n      type: \'post\',\r\n      data: {\r\n        p_password: password,\r\n        p_verifPassword: verifPassword,\r\n        randStringControl: randStringControl,\r\n      },\r\n      dataType: \"json\",\r\n      success: function(data) {\r\n        \r\n       if (data.status == \'ok\') {\r\n         $(\'i.fa-lock\').addClass(\'text-success fa-unlock\').removeClass(\'text-warning fa-lock\');\r\n         $(\'#newPassAskForm\').addClass(\'d-none\');\r\n         $(\'#newPassTreatConfirmation\').removeClass(\'d-none\');\r\n       } else {\r\n         $(\'#newPassAskForm div.alert.cleanAndHideOnModalHide\').removeClass(\'d-none\');\r\n         $(\'#newPassAskForm div.alert.cleanAndHideOnModalHide ul\').html(\'\');\r\n         $.each(data.msg, function(index, value) {\r\n           $(\'#newPassAskForm div.alert.cleanAndHideOnModalHide ul\').append(\'<li>\' + value + \'</li>\');\r\n         });\r\n         $(\'#newPassAskForm .is-invalid\').removeClass(\'is-invalid\');\r\n         $.each(data.code, function(index, value) {\r\n           $(\'#newPassAskForm *[name=\"\' + value + \'\"]\').addClass(\'is-invalid\');\r\n         });\r\n       }        \r\n        \r\n\r\n      },\r\n      error: function() {\r\n        alert(\'Problème, rechargez la page !\');\r\n      }\r\n    });\r\n\r\n  });\r\n});');


-- update form nouvel utilisateur
UPDATE forms set yamlStructure = 'structure:\r\n  row1:\r\n   class: \'mb-4\'\r\n   col1: \r\n     size: col-3\r\n     bloc:\r\n       - administrativeGenderCode,tabindex=1       		#14   Sexe\n       - personalEmail,tabindex=4                  		#4    Email personnelle\n   col2: \r\n     size: col-3\r\n     bloc:\r\n       - birthname,tabindex=1                      		#1    Nom de naissance\n       - profesionnalEmail,tabindex=5              		#5    Email professionnelle\n   col3: \r\n     size: col-3\r\n     bloc:\r\n       - lastname,tabindex=2                       		#2    Nom d usage\n   col4: \r\n     size: col-3\r\n     bloc:\r\n       - firstname,required,tabindex=3             		#3    Prénom\n       \r\n  row2:\r\n   col1: \r\n     size: col-3\r\n     bloc:\r\n       - username,required,tabindex=6              		#1788 Nom d utilisateur\n   col2: \r\n     size: col-3\r\n     bloc:\r\n       - password,tabindex=7                       		#1789 Mot de passe\n   col3: \r\n     size: col-3\r\n     bloc:\r\n       - module,tabindex=8                         		#1795 Module\n   col4: \r\n     size: col-3\r\n     bloc:\r\n       - template,tabindex=9                       		#1796 Template' where
internalName = 'baseNewUser';

-- corrections nom de type pour plus de cohérence
UPDATE forms set yamlStructure=REPLACE(yamlStructure, '- modules,', '- module,') where module = 'base';
UPDATE data_types SET name = 'module', label = 'Module', description = 'module' WHERE `name` = 'modules' and groupe = 'system';

UPDATE forms set yamlStructure=REPLACE(yamlStructure, '- templates,', '- template,') where module = 'base';
UPDATE data_types SET name = 'template', label = 'Template', description = 'template' WHERE `name` = 'templates' and groupe = 'system';
