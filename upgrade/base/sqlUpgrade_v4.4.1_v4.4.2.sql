-- Mise à jour n° de version
UPDATE `system` SET `value`='v4.4.2' WHERE `name`='base' and `groupe`='module';

UPDATE `data_types` set `formValues` =  '\'G\' : \'Tout venant\'\n\'CMU\' : \'CMU\'\n\'TP\' : \'Tiers payant AMO\'\n\'TP ALD DEP\' : \'ALD : tiers payant AVEC dépassement \'\n\'TP ALD\' : \'ALD : tiers payant SANS dépassement \'' where name= 'regleSituationPatient' limit 1;
