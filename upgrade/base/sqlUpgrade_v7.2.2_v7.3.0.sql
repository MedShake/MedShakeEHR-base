-- création de la table bdpm_compositions
CREATE TABLE IF NOT EXISTS `bdpm_compositions` (
  `codeCIS` int(10) unsigned NOT NULL,
  `elementPharmaceutique` varchar(500) NOT NULL,
  `codeSubstance` int(10) unsigned NOT NULL,
  `denomination` varchar(500) DEFAULT NULL,
  `dosage` varchar(250) DEFAULT NULL,
  `dosageRef` varchar(250) DEFAULT NULL,
  `nature` enum('SA','FT') NOT NULL,
  `numLiaison` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`codeCIS`,`elementPharmaceutique`,`codeSubstance`,`numLiaison`),
  KEY `codeCIS` (`codeCIS`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- création de la table bdpm_conditions
CREATE TABLE IF NOT EXISTS `bdpm_conditions` (
  `codeCIS` int(11) NOT NULL,
  `condition` varchar(255) NOT NULL,
  PRIMARY KEY (`codeCIS`,`condition`),
  KEY `codeCIS` (`codeCIS`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- création de la table bdpm_groupesGeneriques
CREATE TABLE IF NOT EXISTS `bdpm_groupesGeneriques` (
  `idGroupe` int(10) unsigned NOT NULL,
  `libelle` text NOT NULL,
  `codeCIS` int(10) unsigned DEFAULT NULL,
  `typeGene` smallint(5) unsigned DEFAULT NULL,
  `numOrdre` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`idGroupe`,`numOrdre`),
  KEY `idGroupe` (`idGroupe`),
  KEY `codeCIS` (`codeCIS`),
  FULLTEXT KEY `libelle` (`libelle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- création de la table bdpm_presentations
CREATE TABLE IF NOT EXISTS `bdpm_presentations` (
  `codeCIS` int(10) unsigned DEFAULT NULL,
  `codeCIP7` int(10) unsigned DEFAULT NULL,
  `libelle` varchar(600) DEFAULT NULL,
  `statutAdministratif` varchar(60) DEFAULT NULL,
  `etatCommercialisation` varchar(200) DEFAULT NULL,
  `dateCommercialisation` varchar(10) DEFAULT NULL,
  `codeCIP13` varchar(13) NOT NULL,
  `agrementCol` enum('oui','non','inconnu') DEFAULT NULL,
  `txRembouSS` varchar(10) DEFAULT NULL,
  `prix1` varchar(9) DEFAULT NULL,
  `prix2` varchar(9) DEFAULT NULL,
  `prix3` varchar(9) DEFAULT NULL,
  `indicRembour` text DEFAULT NULL,
  PRIMARY KEY (`codeCIP13`),
  KEY `codeCIS` (`codeCIS`),
  FULLTEXT KEY `libelle` (`libelle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- création de la table bdpm_specialites
CREATE TABLE IF NOT EXISTS `bdpm_specialites` (
  `codeCIS` int(10) unsigned NOT NULL,
  `denomination` varchar(500) DEFAULT NULL,
  `formePharma` varchar(255) DEFAULT NULL,
  `voiesAdmin` varchar(255) DEFAULT NULL,
  `statutAdminAMM` varchar(255) DEFAULT NULL,
  `typeProcedAMM` varchar(255) DEFAULT NULL,
  `etatCommercialisation` varchar(255) DEFAULT NULL,
  `dateAMM` varchar(10) DEFAULT NULL,
  `statutBdm` varchar(50) DEFAULT NULL,
  `numAutoEU` varchar(50) DEFAULT NULL,
  `tituAMM` varchar(500) DEFAULT NULL,
  `surveillanceRenforcee` enum('Oui','Non') DEFAULT NULL,
  PRIMARY KEY (`codeCIS`),
  FULLTEXT KEY `denomination` (`denomination`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- création de la table bdpm_updates
CREATE TABLE IF NOT EXISTS `bdpm_updates` (
  `fileName` varchar(50) NOT NULL DEFAULT '',
  `fileLastParse` datetime DEFAULT NULL,
  PRIMARY KEY (`fileName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

DROP TABLE IF EXISTS `bdpm_presentationsVirtuelles`;
CREATE ALGORITHM=TEMPTABLE SQL SECURITY DEFINER VIEW `bdpm_presentationsVirtuelles` AS (select `p`.`codeCIS` AS `codeSPE`,`g`.`idGroupe` AS `idGroupe`,`p`.`codeCIS` AS `codeCIS`,`p`.`codeCIP7` AS `codeCIP7`,`p`.`libelle` AS `libelle`,`p`.`statutAdministratif` AS `statutAdministratif`,`p`.`etatCommercialisation` AS `etatCommercialisation`,`p`.`dateCommercialisation` AS `dateCommercialisation`,`p`.`codeCIP13` AS `codeCIP13`,`p`.`agrementCol` AS `agrementCol`,`p`.`txRembouSS` AS `txRembouSS`,`p`.`prix1` AS `prix1`,`p`.`prix2` AS `prix2`,`p`.`prix3` AS `prix3`,`p`.`indicRembour` AS `indicRembour`,case when `con`.`condition` = 'réservé à l\'usage HOSPITALIER' then 'OUI' else 'NON' end AS `reservhop` from ((`bdpm_presentations` `p` left join `bdpm_groupesGeneriques` `g` on(`g`.`codeCIS` = `p`.`codeCIS` and `g`.`numOrdre` = '1')) left join `bdpm_conditions` `con` on(`con`.`codeCIS` = `p`.`codeCIS` and `con`.`condition` = 'réservé à l\'usage HOSPITALIER'))) union (select `g`.`idGroupe` AS `codeSPE`,`g`.`idGroupe` AS `idGroupe`,`p`.`codeCIS` AS `codeCIS`,`p`.`codeCIP7` AS `codeCIP7`,`p`.`libelle` AS `libelle`,`p`.`statutAdministratif` AS `statutAdministratif`,`p`.`etatCommercialisation` AS `etatCommercialisation`,`p`.`dateCommercialisation` AS `dateCommercialisation`,`p`.`codeCIP13` AS `codeCIP13`,`p`.`agrementCol` AS `agrementCol`,`p`.`txRembouSS` AS `txRembouSS`,`p`.`prix1` AS `prix1`,`p`.`prix2` AS `prix2`,`p`.`prix3` AS `prix3`,`p`.`indicRembour` AS `indicRembour`,case when `con`.`condition` = 'réservé à l\'usage HOSPITALIER' then 'OUI' else 'NON' end AS `reservhop` from ((`bdpm_presentations` `p` join `bdpm_groupesGeneriques` `g` on(`g`.`codeCIS` = `p`.`codeCIS` and `g`.`numOrdre` = '1')) left join `bdpm_conditions` `con` on(`con`.`codeCIS` = `p`.`codeCIS` and `con`.`condition` = 'réservé à l\'usage HOSPITALIER')));

DROP TABLE IF EXISTS `bdpm_specialitesVirtuelles`;
CREATE ALGORITHM=TEMPTABLE SQL SECURITY DEFINER VIEW `bdpm_specialitesVirtuelles` AS (select `g`.`idGroupe` AS `codeSPE`,`g`.`codeCIS` AS `codeCIS`,concat(substring_index(`g`.`libelle`,' - ',1),', ',substring_index(`g`.`libelle`,', ',-1)) AS `denomination`,`s`.`formePharma` AS `formePharma`,`s`.`voiesAdmin` AS `voiesAdmin`,'' AS `statutAdminAMM`,'' AS `typeProcedAMM`,'' AS `etatCommercialisation`,'' AS `dateAMM`,'' AS `statutBDM`,'' AS `numAutoEU`,'' AS `tituAMM`,'' AS `surveillanceRenforcee`,'1' AS `monovir` from (`bdpm_groupesGeneriques` `g` left join `bdpm_specialites` `s` on(`s`.`codeCIS` = `g`.`codeCIS`)) where `g`.`typeGene` = '0' and `g`.`numOrdre` = '1') union (select `s1`.`codeCIS` AS `codeSPE`,`s1`.`codeCIS` AS `codeCIS`,`s1`.`denomination` AS `denomination`,`s1`.`formePharma` AS `formePharma`,`s1`.`voiesAdmin` AS `voiesAdmin`,`s1`.`statutAdminAMM` AS `statutAdminAMM`,`s1`.`typeProcedAMM` AS `typeProcedAMM`,`s1`.`etatCommercialisation` AS `etatCommercialisation`,`s1`.`dateAMM` AS `dateAMM`,`s1`.`statutBdm` AS `statutBdm`,`s1`.`numAutoEU` AS `numAutoEU`,`s1`.`tituAMM` AS `tituAMM`,`s1`.`surveillanceRenforcee` AS `surveillanceRenforcee`,'0' AS `monovir` from `bdpm_specialites` `s1`);

-- Mise à jour n° de version
UPDATE `system` SET `value`='v7.3.0' WHERE `name`='base' and `groupe`='module';
