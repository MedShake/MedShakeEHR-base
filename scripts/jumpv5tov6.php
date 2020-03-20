<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
 * Bertrand Boutillier <b.boutillier@gmail.com>
 * http://www.medshake.net
 *
 * MedShakeEHR is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * MedShakeEHR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MedShakeEHR.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Ajustement SQL pour saut de version majeure 5 à 6.
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

msSQL::sqlQuery("ALTER TABLE `people` ADD `secret2fa` VARBINARY(1000) NULL AFTER `pass`;");

msSQL::sqlQuery("ALTER TABLE `data_cat` CHANGE `groupe` `groupe` ENUM('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user','relation','system') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'admin'");

msSQL::sqlQuery("ALTER TABLE `data_types` CHANGE `groupe` `groupe` ENUM('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user','relation','system') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'admin'");


msSQL::sqlQuery("UPDATE forms set `yamlStructure` = 'global:\r\n  formClass: \'form-signin\' \r\nstructure:\r\n row1:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - username,required,nolabel                  		#1    Identifiant\n      - password,required,nolabel                  		#2    Mot de passe\n      - otpCode,nolabel                            		#7    code otp\n      - submit,Connexion,class=btn-primary,class=btn-block 		#3    Valider' where `internalName`='baseLogin';");

msSQL::sqlQuery("DELETE from `data_types` where name = 'submit'");

msSQL::sqlQuery("UPDATE forms set dataset = 'data_types' where dataset = 'form_basic_types';");

msSQL::sqlQuery("INSERT IGNORE INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('system', 'catTypesUsageSystem', 'Types à usage system', 'types à usage system', 'base', 1, '2019-09-27 21:42:35')");

$catID=msSQL::sqlUniqueChamp("SELECT data_cat.id FROM data_cat WHERE data_cat.name='catTypesUsageSystem'");

msSQL::sqlQuery("INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('system', 'currentPassword', 'Mot de passe actuel', 'Mot de passe actuel', 'Mot de passe actuel de l\'utilisateur', 'required', 'Le mot de passe actuel est manquant', 'password', '', 'base', ".$catID.", '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'date', '', 'Début de période', '', '', '', 'date', '', 'base', ".$catID.", '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'modules', '', 'Modules', 'modules utilisables', '', '', 'select', '', 'base', ".$catID.", '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'otpCode', 'code otp', 'code otp', 'code otp', '', 'Le code otp est manquant', 'text', '', 'base', ".$catID.", '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'password', 'mot de passe', 'Mot de passe', 'mot de passe utilisateur', 'required', 'Le mot de passe est manquant', 'password', '', 'base', ".$catID.", '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'submit', '', 'Valider', 'bouton submit de validation', '', '', 'submit', '', 'base', ".$catID.", '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'templates', '', 'Templates utilisables', 'template utilisables', '', '', 'select', '', 'base', ".$catID.", '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'username', 'nom d\'utilisateur', 'Nom d\'utilisateur', 'nom d\'utilisateur', 'required', '', 'text', '', 'base', ".$catID.", '1', '2019-01-01 00:00:00', '86400', '1'),
('system', 'verifPassword', 'confirmation du mot de passe', 'Confirmation du mot de passe', 'Confirmation du mot de passe utilisateur', 'required', 'La confirmation du mot de passe est manquante', 'password', '', 'base', ".$catID.", '1', '2019-01-01 00:00:00', '86400', '1');");


 ?>
