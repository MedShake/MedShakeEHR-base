INSERT INTO `configuration` (`name`, `level`, `toID`, `module`, `cat`, `type`, `description`, `value`) VALUES ('optionGeLoginPassAttribution', 'default', '0', '', 'Options', 'texte', 'méthode d\'attribution des mots de passe utilisateur : admin / random', 'admin');

UPDATE data_types SET validationRules = '' WHERE name = 'password' and groupe = 'system';
UPDATE forms set yamlStructure=REPLACE(yamlStructure, '- password,required', '- password') where internalName in ('baseNewUserFromPeople', 'baseNewUser');

-- form nouvel utilisateur 
UPDATE forms set yamlStructure = 'structure:\r\n  row1:\r\n   class: \'mb-4\'\r\n   col1: \r\n     size: col-3\r\n     bloc:\r\n       - administrativeGenderCode,tabindex=1       		#14   Sexe\n       - personalEmail,tabindex=4                  		#4    Email personnelle\n   col2: \r\n     size: col-3\r\n     bloc:\r\n       - birthname,tabindex=1                      		#1    Nom de naissance\n       - profesionnalEmail,tabindex=5              		#5    Email professionnelle\n   col3: \r\n     size: col-3\r\n     bloc:\r\n       - lastname,tabindex=2                       		#2    Nom d usage\n   col4: \r\n     size: col-3\r\n     bloc:\r\n       - firstname,required,tabindex=3             		#3    Prénom\n       \r\n  row2:\r\n   col1: \r\n     size: col-3\r\n     bloc:\r\n       - username,required,tabindex=6              		#1788 Nom d utilisateur\n   col2: \r\n     size: col-3\r\n     bloc:\r\n       - password,tabindex=7                       		#1789 Mot de passe\n   col3: \r\n     size: col-3\r\n     bloc:\r\n       - module,tabindex=8                         		#1795 Module\n   col4: \r\n     size: col-3\r\n     bloc:\r\n       - template,tabindex=9                       		#1796 Template' where
internalName = 'baseNewUser';

-- corrections nom de type pour plus de cohérence
UPDATE forms set yamlStructure=REPLACE(yamlStructure, '- modules,', '- module,') where module = 'base';
UPDATE data_types SET name = 'module', label = 'Module', description = 'module' WHERE `name` = 'modules' and groupe = 'system';

UPDATE forms set yamlStructure=REPLACE(yamlStructure, '- templates,', '- template,') where module = 'base';
UPDATE data_types SET name = 'template', label = 'Template', description = 'template' WHERE `name` = 'templates' and groupe = 'system';
