-- Mise à jour n° de version
UPDATE `system` SET `value`='v7.0.1' WHERE `name`='base' and `groupe`='module';

-- data_cat
INSERT IGNORE INTO `data_cat` (`groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
('user', 'clicRDV', 'clicRDV', 'Paramètres pour clicRDV', 'base', '1', '2019-01-01 00:00:00')
ON DUPLICATE KEY UPDATE groupe=values(groupe), name=values(name), label=values(label), description=values(description), type=values(type), fromID=values(fromID), creationDate=values(creationDate);

-- data_types
SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='clicRDV');
INSERT IGNORE INTO `data_types` (`groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `module`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
('user', 'clicRdvCalId', 'Agenda', 'Agenda', 'Agenda sélectionné', '', '', 'select', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '4'),
('user', 'clicRdvConsultId', 'Consultations', 'Consultations', 'Correspondance entre consultations', '', '', 'select', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '5'),
('user', 'clicRdvGroupId', 'Groupe', 'Groupe', 'Groupe Sélectionné', '', '', 'select', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '3'),
('user', 'clicRdvPassword', 'Mot de passe', 'Mot de passe', 'Mot de passe (chiffré)', '', '', 'password', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '2'),
('user', 'clicRdvUserId', 'identifiant', 'identifiant', 'email@address.com', '', '', 'text', '', 'base', @catID, '1', '2019-01-01 00:00:00', '3600', '1');