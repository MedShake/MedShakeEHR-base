UPDATE `data_types` SET `description`='Ordonnance simple' WHERE `name`='ordoPorteur';
UPDATE `data_types` SET `description`='Règlement secteurs 1 et 2' WHERE `name`='reglePorteur';

UPDATE `system` SET `value`='v3.1.2' WHERE `groupe`='module' and `name`='base';
