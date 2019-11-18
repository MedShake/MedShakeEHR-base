-- Ajustement taille pour label de l'acte 
ALTER TABLE `actes_base` CHANGE `label` `label` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
