-- 2.1.0 to 2.2.0

ALTER TABLE data_types CHANGE `formType` `formType` ENUM('','date','email','lcc','number','select','submit','tel','text','textarea','checkbox','hidden','range','radio','reset') NOT NULL DEFAULT '';

ALTER TABLE `objets_data` ADD `registerDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `instance`;
update objets_data set registerDate=creationDate;

update forms set yamlStructure=REPLACE(yamlStructure, 'size: 3', 'size: 4'), yamlStructureDefaut=REPLACE(yamlStructureDefaut, 'size: 3', 'size: 4') where id in ('1','7');
update forms set yamlStructure=REPLACE(yamlStructure, 'size: 9', 'size: 12'), yamlStructureDefaut=REPLACE(yamlStructureDefaut, 'size: 9', 'size: 12') where id in ('1','7');

