-- corrections nom de type pour plus de cohérence
UPDATE forms set yamlStructure=REPLACE(yamlStructure, '- modules,', '- module,') where module = 'base';
UPDATE data_types SET name = 'module', label = 'Module', description = 'module' WHERE `name` = 'modules' and groupe = 'system';

UPDATE forms set yamlStructure=REPLACE(yamlStructure, '- templates,', '- template,') where module = 'base';
UPDATE data_types SET name = 'template', label = 'Template', description = 'template' WHERE `name` = 'templates' and groupe = 'system';
