-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Ven 16 Juin 2017 à 10:07
-- Version du serveur :  5.7.18-0ubuntu0.16.04.1
-- Version de PHP :  7.0.18-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `ehr`
--

-- --------------------------------------------------------

--
-- Structure de la table `actes`
--

CREATE TABLE `actes` (
  `id` smallint(4) UNSIGNED NOT NULL,
  `cat` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `label` varchar(250) NOT NULL,
  `tarif` float NOT NULL DEFAULT '0',
  `depassement` float NOT NULL DEFAULT '0',
  `code` varchar(250) NOT NULL DEFAULT '',
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL DEFAULT '1000-01-01 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `actes_cat`
--

CREATE TABLE `actes_cat` (
  `id` smallint(5) NOT NULL,
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL,
  `displayOrder` smallint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `data_cat`
--

CREATE TABLE `data_cat` (
  `id` smallint(5) NOT NULL,
  `groupe` enum('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user') NOT NULL DEFAULT 'admin',
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `data_cat`
--

INSERT INTO `data_cat` (`id`, `groupe`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
(1, 'admin', 'identity', 'Etat civil', 'Datas relatives à l\'identité d\'une personne', 'base', 1, '2016-12-15 12:22:35'),
(2, 'admin', 'addressPerso', 'Adresse personnelle', 'datas de l\'adresse personnelle', 'base', 1, '2017-03-26 15:12:14'),
(3, 'admin', 'internet', 'Internet', 'Datas liées aux services internet', 'base', 0, '1000-01-01 00:00:00'),
(24, 'admin', 'contact', 'Contact', 'Moyens de contact', 'base', 1, '2016-12-13 19:18:57'),
(25, 'admin', 'activity', 'Activités', 'Activités professionnelles et de loisir', 'base', 1, '2016-12-15 12:04:59'),
(26, 'admin', 'divers', 'Divers', 'Divers', 'base', 1, '2016-12-16 13:35:52'),
(36, 'admin', 'numAdmin', 'Numéros administratifs', 'RPPS et compagnie', 'base', 1, '2017-03-14 14:43:16'),
(37, 'courrier', 'catModelesCertificats', 'Certificats', 'certificats divers', 'base', 1, '2017-06-15 11:13:49'),
(38, 'courrier', 'catModelesCourriers', 'Courriers', 'modèles de courrier libres', 'base', 1, '2017-06-15 11:14:52'),
(39, 'mail', 'mailForm', 'Data mail', 'data pour les mails expédiés', 'base', 1, '2017-04-07 15:44:51'),
(41, 'mail', 'porteursTech', 'Porteurs', 'porteurs pour les données enfants', 'base', 1, '2017-03-20 12:52:07'),
(42, 'doc', 'docForm', 'Data documents importés / créés', 'données pour le formulaire documents importés ou créés', 'base', 1, '2017-05-15 13:37:29'),
(43, 'doc', 'docPorteur', 'Porteur', 'porteur pour doc importés', 'base', 1, '2017-03-21 10:24:51'),
(44, 'ordo', 'poteursOrdo', 'Porteurs', 'porteurs ordonnance', 'base', 1, '2017-03-22 14:06:30'),
(45, 'reglement', 'porteursReglement', 'Porteurs', 'porteur d\'un règlement', 'base', 1, '2017-03-24 14:41:40'),
(46, 'reglement', 'reglementItems', 'Règlement', 'items d\'un réglement', 'base', 1, '2017-03-24 14:42:21'),
(47, 'admin', 'adressPro', 'Adresse professionnelle', 'Data de l\'adresse professionnelle', 'base', 1, '2017-03-26 15:13:00'),
(55, 'dicom', 'idDicom', 'ID Dicom', 'ID du dicom', 'base', 1, '2017-04-13 14:24:10'),
(56, 'user', 'dicom', 'Dicom', 'Paramètres DICOM', 'base', 1, '2017-04-25 20:26:19'),
(58, 'courrier', 'catModelesMailsToPatient', 'Mails aux patients', 'modèles de mail', 'base', 1, '2017-06-15 11:13:58'),
(59, 'courrier', 'catModelesMailsToApicrypt', 'Mails aux praticiens', 'modèles de mails pour les praticien (apicrypt)', 'base', 1, '2017-06-15 11:14:05'),
(63, 'relation', 'relationRelations', 'Relations', 'types permettant de définir une relation', 'user', 1, '2017-06-29 09:22:33');
-- --------------------------------------------------------

--
-- Structure de la table `data_types`
--

CREATE TABLE `data_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `groupe` enum('admin','medical','typecs','mail','doc','courrier','ordo','reglement','dicom','user') NOT NULL DEFAULT 'admin',
  `name` varchar(60) NOT NULL,
  `placeholder` varchar(255) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `validationRules` varchar(255) NOT NULL,
  `validationErrorMsg` varchar(255) NOT NULL,
  `formType` enum('','date','email','lcc','number','select','submit','tel','text','textarea') NOT NULL,
  `formValues` text NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
  `cat` smallint(5) UNSIGNED NOT NULL,
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL,
  `durationLife` int(9) UNSIGNED NOT NULL DEFAULT '86400',
  `displayOrder` tinyint(3) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `data_types`
--

INSERT INTO `data_types` (`id`, `groupe`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `type`, `cat`, `fromID`, `creationDate`, `durationLife`, `displayOrder`) VALUES
(0, 'admin', 'submit', '', '', '', '', '', 'submit', '', 'base', 0, 0, '1000-01-01 00:00:00', 3600, 1),
(1, 'admin', 'birthname', 'nom de jeune-fille', 'Nom de jeune-fille', 'Nom porté avant mariage éventuel', 'identite', 'Le nom de jeune-fille est indispensable et ne doit pas contenir de caractères interdits', 'text', '', 'base', 1, 1, '2016-12-17 16:04:17', 3600, 1),
(2, 'admin', 'lastname', 'nom', 'Nom', 'Nom utilisé au quotidien', 'identite', 'Le nom ne doit pas contenir de caractères interdits', 'text', '', 'base', 1, 1, '2016-12-17 16:00:48', 3600, 1),
(3, 'admin', 'given', 'prénom', 'Prénom', 'Prénom figurant sur la pièce d\'identité', 'identite', 'Le prénom est indispensable et ne doit pas contenir de caractères interdits', 'text', '', 'base', 1, 1, '2016-12-18 15:17:58', 3600, 1),
(4, 'admin', 'personalEmail', 'email@domain.ext', 'Email personnelle', 'Adresse email personnelle', 'valid_email', 'L\'adresse email n\'est pas correcte. Elle doit être de la forme email@domain.net', 'email', '', 'base', 24, 1, '2017-03-10 12:02:13', 3600, 1),
(5, 'admin', 'profesionnalEmail', 'email@domain.ext', 'Email professionnelle', 'Adresse email professionnelle', 'valid_email', 'L\'adresse email n\'est pas correcte. Elle doit être de la forme email@domain.net', 'email', '', 'base', 24, 1, '2016-12-14 16:21:28', 3600, 1),
(6, 'admin', 'twitterAccount', '', 'Twitter', 'Compte twitter', 'twitterAccount', '', 'text', '', 'base', 3, 1, '2016-12-13 19:17:36', 3600, 1),
(7, 'admin', 'mobilePhone', '06 xx xx xx xx', 'Téléphone mobile', 'Numéro de téléphone commençant par 06 ou 07', 'mobilphone', 'Le numéro de téléphone mobile est incorrect', 'tel', '', 'base', 24, 1, '2016-12-16 15:34:13', 3600, 1),
(8, 'admin', 'birthdate', 'dd/mm/YYYY', 'Date de naissance', 'Date de naissance au format dd/mm/YYYY', 'validedate,\'d/m/Y\'', 'La date de naissance indiquée n\'est pas valide', 'date', '', 'base', 1, 1, '2017-03-10 20:37:23', 3600, 1),
(9, 'admin', 'streetNumber', 'numéro dans la rue', 'Numéro', 'Adresse perso : numéro dans la rue', 'alpha_space', 'Le numéro de rue est incorrect', 'text', '', 'base', 2, 1, '2017-03-09 15:58:39', 3600, 1),
(10, 'admin', 'homePhone', '0x xx xx xx xx', 'Téléphone domicile', 'Téléphone du domicile de la forme 0x xx xx xx xx', 'phone', 'Le numéro de téléphone du domicile n\'est pas correct', 'tel', '', 'base', 24, 1, '2016-12-31 11:33:02', 3600, 1),
(11, 'admin', 'street', 'rue', 'Rue', 'Adresse perso : rue', '', '', 'text', '', 'base', 2, 1, '2017-03-09 16:14:08', 3600, 1),
(12, 'admin', 'city', 'ville', 'Ville', 'Adresse perso : ville', '', '', 'text', '', 'base', 2, 1, '2017-03-09 15:59:04', 3600, 1),
(13, 'admin', 'postalCodePerso', 'code postal', 'Code postal', 'Adresse perso : code postal', '', 'Le code postal n\'est pas correct', 'text', '', 'base', 2, 1, '2017-03-31 15:35:04', 3600, 1),
(14, 'admin', 'administrativeGenderCode', '', 'Sexe', 'Sexe', '', '', 'select', 'F: \'Femme\'\nM: \'Homme\'\nU: \'Inconnu\'', 'base', 1, 1, '2016-12-18 15:17:45', 3600, 1),
(15, 'admin', 'website', '', 'Site web', 'Site web', 'url', '', 'text', '', 'base', 3, 1, '2016-12-13 20:00:15', 3600, 1),
(19, 'admin', 'job', 'activité professionnelle', 'Activité professionnelle', 'Activité professionnelle', '', 'L\'activité professionnelle n\'est pas correcte', 'text', '', 'base', 25, 1, '2017-03-10 12:42:48', 3600, 1),
(20, 'admin', 'sport', 'sport exercé', 'Sport', 'Sport exercé', '', 'Le sport indiqué n\'est pas correct', 'text', '', 'base', 25, 1, '2016-12-15 12:07:34', 3600, 1),
(21, 'admin', 'notes', 'notes', 'Notes', 'Zone de notes', '', '', 'textarea', '', 'base', 26, 1, '2016-12-16 13:36:46', 3600, 1),
(22, 'admin', 'othersfirstname', 'liste des prénoms secondaires', 'Autres prénoms', 'Les autres prénoms d\'une personne', '', '', 'text', '', 'base', 1, 1, '2016-12-16 18:01:37', 3600, 1),
(51, 'admin', 'titre', 'Dr, Pr ...', 'Titre', 'Titre du pro de santé', '', '', 'text', '', 'base', 1, 1, '2017-03-12 21:21:47', 3600, 1),
(53, 'admin', 'codePostalPro', 'code postal', 'Code postal', 'Adresse pro : code postal', 'alpha_space', 'Le code postal n\'est pas conforme', 'text', '', 'base', 47, 1, '2017-03-26 15:13:34', 3600, 1),
(54, 'admin', 'numAdressePro', 'n°', 'Numéro', 'Adresse pro : numéro dans la rue', 'alpha_space', 'Le numero n\'est pas conforme', 'text', '', 'base', 47, 1, '2017-03-26 15:13:46', 3600, 1),
(55, 'admin', 'rueAdressePro', 'rue', 'Rue', 'Adresse pro : rue', '', '', 'text', '', 'base', 47, 1, '2017-03-26 15:13:53', 3600, 1),
(56, 'admin', 'villeAdressePro', 'ville', 'Ville', 'Adresse pro : ville', '', '', 'text', '', 'base', 47, 1, '2017-03-26 15:14:01', 3600, 1),
(57, 'admin', 'telPro', 'téléphone professionnel', 'Téléphone professionnel', 'Téléphone pro.', 'phone', '', 'tel', '', 'base', 24, 1, '2017-03-12 21:33:56', 3600, 1),
(58, 'admin', 'faxPro', 'fax professionel', 'Fax professionnel', 'FAx pro', 'phone', '', 'tel', '', 'base', 24, 1, '2017-03-12 21:34:44', 3600, 1),
(59, 'admin', 'emailApicrypt', 'adresse mail apicript', 'Email apicrypt', 'Email apicrypt', 'valid_email', '', 'email', '', 'base', 24, 1, '2017-03-12 23:01:07', 3600, 1),
(103, 'admin', 'rpps', 'rpps', 'RPPS', 'rpps', 'numeric', '', 'number', '', 'base', 36, 1, '2017-03-14 14:46:29', 3600, 1),
(104, 'admin', 'adeli', 'adeli', 'Adeli', 'n° adeli', '', '', 'text', '', 'base', 36, 1, '2017-03-14 14:48:26', 3600, 1),
(107, 'courrier', 'modeleCourrierVierge', '', 'Courrier', 'modèle de courrier vierge', '', '', '', 'courrier-courrierVierge', 'base', 38, 1, '2017-04-10 16:14:47', 3600, 0),
(108, 'courrier', 'modeleCertifVierge', '', 'Certificat', 'modèle de certificat vierge', '', '', '', 'certif-certificatVierge', 'base', 37, 1, '2017-04-10 16:13:45', 3600, 0),
(109, 'mail', 'mailFrom', 'email@domain.net', 'De', 'mail from', '', '', 'email', '', 'base', 39, 1, '2017-03-21 10:20:08', 1576800000, 1),
(110, 'mail', 'mailTo', '', 'A', 'mail to', '', '', 'email', '', 'base', 39, 1, '2017-03-21 10:20:21', 1576800000, 1),
(111, 'mail', 'mailBody', 'texte du message', 'Message', 'texte du message', '', '', 'textarea', '', 'base', 39, 1, '2017-03-16 19:51:28', 1576800000, 1),
(112, 'mail', 'mailSujet', 'sujet du mail', 'Sujet', 'sujet du mail', '', '', 'text', '', 'base', 39, 1, '2017-03-16 19:52:04', 1576800000, 1),
(177, 'mail', 'mailPorteur', '', 'Mail', 'porteur pour les mails', '', '', '', '', 'base', 41, 1, '2017-06-09 10:24:05', 1576800000, 1),
(178, 'mail', 'mailPJ1', '', 'ID pièce jointe', 'id de la pièce jointe au mail', '', '', '', '', 'base', 39, 1, '2017-06-09 10:22:50', 1576800000, 1),
(179, 'mail', 'mailToApicrypt', '', 'A (correspondant apicrypt)', 'Champ pour les correspondants apicrypt', '', '', 'email', '', 'base', 39, 1, '2017-06-09 10:22:19', 1576800000, 1),
(180, 'admin', 'nss', '', 'Numéro de sécu', 'numéro de sécurité sociale', '', '', 'text', '', 'base', 36, 1, '2017-03-20 15:05:17', 3600, 1),
(181, 'doc', 'docTitle', '', 'Titre', 'titre du document', '', '', '', '', 'base', 42, 1, '2017-03-21 11:57:32', 3600, 1),
(182, 'doc', 'docOrigine', '', 'Origine du document', 'origine du document : interne ou externe(null)', '', '', 'text', '', 'base', 42, 1, '2017-05-15 13:36:57', 3600, 1),
(183, 'doc', 'docType', '', 'Type du document', 'type du document importé', '', '', 'text', '', 'base', 42, 1, '2017-03-21 10:30:16', 3600, 1),
(184, 'doc', 'docPorteur', '', 'Document', 'porteur pour nouveau document importé', '', '', '', '', 'base', 43, 1, '2017-03-21 21:08:37', 1576800000, 1),
(185, 'doc', 'docOriginalName', '', 'Nom original', 'nom original du document', '', '', '', '', 'base', 42, 1, '2017-03-21 11:57:32', 3600, 1),
(186, 'ordo', 'ordoPorteur', '', 'Ordonnance', 'porteur ordonnance', '', '', '', '', 'base', 44, 1, '2017-06-09 14:19:14', 3600, 1),
(189, 'ordo', 'ordoTypeImpression', '', 'Type ordonnance impression', 'type d\'ordonnance pour impression', '', '', '', '', 'base', 44, 1, '2017-06-09 15:26:40', 3600, 1),
(190, 'ordo', 'ordoLigneOrdo', '', 'Ligne d\'ordonnance', 'porteur pour une ligne d\'ordo', '', '', '', '', 'base', 44, 1, '2017-06-09 15:25:25', 3600, 1),
(191, 'ordo', 'ordoLigneOrdoALDouPas', '', 'Ligne d\'ordonnance : ald', '1 si ald', '', '', '', '', 'base', 44, 1, '2017-06-09 15:25:35', 3600, 1),
(192, 'reglement', 'reglePorteur', '', 'Règlement', '', '', '', '', '', 'base', 45, 1, '2017-06-09 21:12:32', 1576800000, 1),
(193, 'reglement', 'regleCheque', '', 'Chèque', 'montant versé en chèque', '', '', 'text', '', 'base', 46, 1, '2017-04-27 13:38:26', 1576800000, 1),
(194, 'reglement', 'regleCB', '', 'CB', 'montant versé en CB', '', '', 'text', '', 'base', 46, 1, '2017-04-27 13:38:21', 1576800000, 1),
(195, 'reglement', 'regleEspeces', '', 'Espèces', 'montant versé en espèce', '', '', 'text', '', 'base', 46, 1, '2017-04-27 13:38:47', 1576800000, 1),
(196, 'reglement', 'regleFacture', '', 'Facturé', 'facturé ce jour', '', '', 'text', '0', 'base', 46, 1, '2017-06-09 21:12:56', 1576800000, 1),
(197, 'reglement', 'regleSituationPatient', '', 'Situation du patient', 'situation du patient : cmu / tp / tout venant', '', '', 'select', '\'G\' : \'Tout venant\'\n\'CMU\' : \'CMU\'\n\'TP\' : \'Tiers payant\'', 'base', 46, 1, '2017-06-09 21:14:35', 1576800000, 1),
(198, 'reglement', 'regleTarifCejour', '', 'Tarif SS', 'tarif SS appliqué ce jour', '', '', 'text', '', 'base', 46, 1, '2017-04-27 13:39:12', 1576800000, 1),
(199, 'reglement', 'regleDepaCejour', '', 'Dépassement', 'dépassement pratiqué ce jour', '', '', 'text', '', 'base', 46, 1, '2017-04-27 13:38:32', 1576800000, 1),
(200, 'reglement', 'regleTiersPayeur', '', 'Tiers', 'part du tiers', '', '', 'text', '', 'base', 46, 1, '2017-04-27 13:39:23', 1576800000, 1),
(205, 'reglement', 'regleIdentiteCheque', 'si différent patient', 'Identité payeur', 'identité du payeur si différente', '', '', 'text', '', 'base', 46, 1, '2017-06-09 21:13:30', 1576800000, 1),
(247, 'admin', 'mobilePhonePro', '06 xx xx xx xx', 'Téléphone mobile pro.', 'Numéro de téléphone commençant par 06 ou 07', 'mobilphone', 'Le numéro de téléphone mobile pro est incorrect', 'tel', '', 'base', 24, 1, '2017-04-04 09:09:52', 3600, 1),
(248, 'admin', 'telPro2', 'téléphone professionnel 2', 'Téléphone professionnel 2', 'Téléphone pro. 2', 'phone', '', 'tel', '', 'base', 24, 1, '2017-04-04 09:16:41', 3600, 1),
(249, 'admin', 'serviceAdressePro', 'service', 'Service', 'Adresse pro : service', '', '', 'text', '', 'base', 47, 1, '2017-04-04 09:35:37', 3600, 1),
(250, 'admin', 'etablissementAdressePro', 'établissement', 'Établissement', 'Adresse pro : établissement', '', '', 'text', '', 'base', 47, 1, '2017-04-04 09:36:21', 3600, 1),
(433, 'dicom', 'dicomStudyID', '', 'StudyID', '', '', '', 'text', '', 'base', 55, 1, '2017-04-13 14:25:00', 3600, 1),
(434, 'dicom', 'dicomSerieID', '', 'SerieID', '', '', '', 'text', '', 'base', 55, 1, '2017-04-13 16:25:25', 3600, 1),
(435, 'dicom', 'dicomInstanceID', '', 'InstanceID', '', '', '', 'text', '', 'base', 55, 1, '2017-04-13 16:25:49', 3600, 1),
(436, 'user', 'dicomAutoSendPatient2Echo', '', 'dicomAutoSendPatient2Echo', 'Pousser le dossier patient à l\'ouverture dans le serveur DICOM', '', '', 'text', 'false', 'base', 56, 1, '2017-04-25 22:06:41', 3600, 1),
(443, 'admin', 'notesPro', 'notes pros', 'Notes pros', 'Zone de notes pros', '', '', 'textarea', '', 'base', 26, 1, '2017-05-04 10:53:11', 3600, 1),
(444, 'courrier', 'consentementEcho', '', 'Consentement échographie foetale', 'Consentement échographie foetale', '', '', '', 'consentementEcho', 'base', 37, 1, '2017-05-09 17:47:38', 3600, 1),
(446, 'mail', 'mailModeles', '', 'Modèle', 'liste des modèles', '', '', 'select', '', 'base', 39, 1, '2017-06-09 10:23:17', 1576800000, 1),
(477, 'admin', 'nReseau', '', 'Numéro de réseau', 'numéro de réseau (dépistage)', '', '', 'text', '', 'base', 36, 1, '2017-05-16 22:21:00', 3600, 1),
(479, 'courrier', 'mmDefautApi', '', 'Défaut', 'modèle mail par défaut', '', '', '', 'Cher confrère,\n\nVeuillez trouver en pièce jointe un document concernant notre patient commun.\nVous souhaitant bonne réception.\n\nBien confraternellement', 'base', 59, 1, '2017-05-29 10:41:14', 3600, 0),
(481, 'mail', 'mailToEcofaxNumber', '', 'Numéro de fax du destinataire', 'Numéro du destinataire du fax (ecofax OVH)', '', '', 'text', '', 'base', 39, 1, '2017-06-09 21:48:01', 1576800000, 1),
(484, 'mail', 'mailToEcofaxName', '', 'Destinataire du fax', 'Destinataire du fax (ecofax OVH)', '', '', 'text', '', 'base', 39, 1, '2017-06-09 21:49:09', 1576800000, 1),
(488, 'relation', 'relationID', '', 'Porteur de relation', 'porteur de relation entre patients ou entre patients et praticiens', '', '', 'number', '', 'base', 63, 1, '2017-06-29 15:28:56', 1576800000, 1),
(489, 'relation', 'relationPatientPatient', '', 'Relation patient patient', 'relation patient patient', '', '', 'select', '\'conjoint\': \'conjoint\'\n\'enfant\': \'parent\'\n\'parent\': \'enfant\'\n\'grand parent\': \'petit enfant\'\n\'petit enfant\': \'grand parent\'\n\'sœur / frère\': \'sœur / frère\' \n\'tante / oncle\': \'nièce / neveu\' \n\'cousin\': \'cousin\'', 'base', 63, 1, '2017-06-30 10:36:59', 1576800000, 1),
(490, 'relation', 'relationPatientPraticien', '', 'Relation patient praticien', 'relation patient  praticien', '', '', 'select', '\'MT\': \'Médecin traitant\'\n\'MS\': \'Médecin spécialiste\'\n\'Autre\': \'Autre correspondant\'', 'base', 63, 1, '2017-06-29 15:29:16', 1576800000, 1);


-- --------------------------------------------------------

--
-- Structure de la table `dicomTags`
--

CREATE TABLE `dicomTags` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `dicomTag` varchar(25) NOT NULL,
  `typeID` mediumint(5) UNSIGNED NOT NULL DEFAULT '0',
  `dicomCodeMeaning` varchar(255) DEFAULT NULL,
  `dicomUnits` varchar(255) DEFAULT NULL,
  `returnValue` enum('min','max','avg') NOT NULL DEFAULT 'avg',
  `roundDecimal` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `forms`
--

CREATE TABLE `forms` (
  `id` smallint(4) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` varchar(250) NOT NULL,
  `dataset` varchar(60) NOT NULL DEFAULT 'data_types',
  `groupe` enum('admin','medical','mail','doc','courrier','ordo','reglement') NOT NULL DEFAULT 'medical',
  `formMethod` enum('post','get') NOT NULL DEFAULT 'post',
  `formAction` varchar(255) DEFAULT '/patient/actions/saveCsForm/',
  `cat` smallint(4) DEFAULT NULL,
  `type` enum('public','private') NOT NULL DEFAULT 'public',
  `yamlStructure` text,
  `yamlStructureDefaut` text,
  `printModel` varchar(25) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `forms`
--

INSERT INTO `forms` (`id`, `name`, `description`, `dataset`, `groupe`, `formMethod`, `formAction`, `cat`, `type`, `yamlStructure`, `yamlStructureDefaut`, `printModel`) VALUES
(1, 'Formulaire nouveau patient', 'formulaire d\'enregistrement d\'un nouveau patient', 'data_types', 'admin', 'post', '/patient/register/', 1, 'public', 'structure:\r\n  row1:                              \r\n    col1:                              \r\n      head: \'Etat civil\'             \r\n      size: 3\r\n      bloc:                          \r\n        - 14                                                 # Sexe\r\n        - 2,required,autocomplete,data-acTypeID=2:1          # Nom d\'usage (requis)\r\n        - 1,autocomplete,data-acTypeID=2:1                   # Nom de jeune fille \r\n        - 3,required,autocomplete,data-acTypeID=3:22:230:235:241    # Prénom (requis)\r\n        - 8	                     # Date de naissance\r\n    col2:\r\n      head: \'Contact\'\r\n      size: 3\r\n      bloc:\r\n        - 4\r\n        - 7\r\n        - 10\r\n    col3:\r\n      head: \'Adresse personnelle\'\r\n      size: 3\r\n      bloc: \r\n        - 9\r\n        - 11,autocomplete,data-acTypeID=11:55\r\n        - 13\r\n        - 12,autocomplete,data-acTypeID=12:56\r\n  row2:\r\n    col1:\r\n      size: 9\r\n      bloc:\r\n        - 21,rows=5', 'structure:\r\n  row1:                              # 1re rangée\r\n    col1:                            # 1re colonne  \r\n      head: \'Etat civil\'             # Titre colonne 1\r\n      size: 3\r\n      bloc:                          # Types utilisés\r\n        - 14                         # Sexe\r\n        - 2,required,autocomplete,data-acTypeID=2:1     # Nom d\'usage (requis)\r\n        - 1,autocomplete,data-acTypeID=2:1             # Nom de jeune fille \r\n        - 3,required,autocomplete,data-acTypeID=3:22:230:235:241    # Prénom (requis)\r\n        - 8	                     # Date de naissance\r\n    col2:\r\n      head: \'Contact\'\r\n      size: 3\r\n      bloc:\r\n        - 4\r\n        - 7\r\n        - 10\r\n    col3:\r\n      head: \'Adresse personnelle\'\r\n      size: 3\r\n      bloc: \r\n        - 9\r\n        - 11,autocomplete,data-acTypeID=11:55\r\n        - 13\r\n        - 12,autocomplete,data-acTypeID=12:56\r\n  row2:\r\n    col1:\r\n      size: 9\r\n      bloc:\r\n        - 21,rows=5', ''),
(2, 'Listing des patients', 'description des colonnes affichées en résultat d\'une recherche patient', 'data_types', 'admin', 'post', '', 2, 'public', 'col1:\r\n    head: "Identité"\r\n    blocseparator: " "\r\n    bloc:\r\n        - 2,text-uppercase,gras\r\n        - 3,text-capitalize,gras\r\ncol2:\r\n    head: "Nom de jeune fille"\r\n    blocseparator: " "\r\n    bloc:\r\n        - 1,text-uppercase,gras \r\ncol3:\r\n    head: "Date de naissance" \r\n    bloc: \r\n        - 8\r\ncol4:\r\n    head: "Tel" \r\n    blocseparator: " - "\r\n    bloc: \r\n        - 7\r\n        - 10\r\ncol5:\r\n    head: "Email"\r\n    bloc:\r\n        - 4\r\ncol6:\r\n    head: "Ville"\r\n    bloc:\r\n        - 12,text-uppercase', 'col1:\r\n    head: "Identité"\r\n    blocseparator: " "\r\n    bloc:\r\n        - 2,text-uppercase,gras\r\n        - 3,text-capitalize,gras\r\ncol2:\r\n    head: "Nom de jeune fille"\r\n    blocseparator: " "\r\n    bloc:\r\n        - 1,text-uppercase,gras \r\ncol3:\r\n    head: "Date de naissance" \r\n    bloc: \r\n        - 8\r\ncol4:\r\n    head: "Tel" \r\n    blocseparator: " - "\r\n    bloc: \r\n        - 7\r\n        - 10\r\ncol5:\r\n    head: "Email"\r\n    bloc:\r\n        - 4\r\ncol6:\r\n    head: "Ville"\r\n    bloc:\r\n        - 12,text-uppercase', ''),
(3, 'Login', 'formulaire login utilisateur', 'form_basic_types', 'admin', 'post', '/login/logInDo/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Identification utilisateur"\r\n    size: 3\r\n    bloc: \r\n      - 1,required\r\n      - 2,required\r\n      - 3', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Identification utilisateur"\r\n    size: 3\r\n    bloc: \r\n      - 1,required\r\n      - 2,required\r\n      - 3', ''),
(7, 'Formulaire nouveau pro', 'formulaire d\'enregistrement d\'un nouveau pro', 'data_types', 'admin', 'post', '/pro/register/', 1, 'public', 'structure:\r\n  row1:                              \r\n    col1:                            \r\n      head: \'Etat civil\'            \r\n      size: 3\r\n      bloc:                          \r\n        - 14                         # Sexe\r\n        - 19,autocomplete            # Profession\r\n        - 51,autocomplete            # titre\r\n        - 2,required,autocomplete,data-acTypeID=2:1    # Nom d\'usage (requis)\r\n        - 3,autocomplete,data-acTypeID=3:22:230:235:241    # Prénom (requis)\r\n    col2:\r\n      head: \'Contact\'\r\n      size: 3\r\n      bloc:\r\n        - 59              # email apicrypt\r\n        - 5              # email pro\r\n        - 4              # email perso\r\n        - 57             # tel pro \r\n        - 248            # tel pro 2\r\n        - 247            # mobile pro \r\n        - 58             # fax pro \r\n    col3:\r\n      head: \'Adresse professionnelle\'\r\n      size: 3\r\n      bloc: \r\n        - 54\r\n        - 55,autocomplete,data-acTypeID=11:55\r\n        - 53\r\n        - 56,autocomplete,data-acTypeID=12:56\r\n        - 249,autocomplete\r\n        - 250,autocomplete\r\n  row2:\r\n    col1:\r\n      size: 9\r\n      bloc:\r\n        - 443,rows=5\r\n\r\n  row3:\r\n    col1:\r\n      size: 3\r\n      bloc:\r\n        - 103         # RPPS\r\n    col2:\r\n      size: 3\r\n      bloc:\r\n        - 104         # ADELI       \r\n    col3:\r\n      size: 3\r\n      bloc:\r\n        - 477         # N° réseau', 'structure:\r\n  row1:                              # 1re rangée\r\n    col1:                            # 1re colonne  \r\n      head: \'Etat civil\'             # Titre colonne 1\r\n      size: 3\r\n      bloc:                          # Types utilisés\r\n        - 14                         # Sexe\r\n        - 19,autocomplete            # Profession\r\n        - 51,autocomplete            # titre\r\n        - 2,required,autocomplete,data-acTypeID=2:1    # Nom d\'usage (requis)\r\n        - 3,autocomplete,data-acTypeID=3:22:230:235:241    # Prénom (requis)\r\n    col2:\r\n      head: \'Contact\'\r\n      size: 3\r\n      bloc:\r\n        - 59              # email apicrypt\r\n        - 5              # email pro\r\n        - 4              # email perso\r\n        - 57             # tel pro \r\n        - 248            # tel pro 2\r\n        - 247            # mobile pro \r\n        - 58             # fax pro \r\n    col3:\r\n      head: \'Adresse professionnelle\'\r\n      size: 3\r\n      bloc: \r\n        - 54\r\n        - 55,autocomplete,data-acTypeID=11:55\r\n        - 53\r\n        - 56,autocomplete,data-acTypeID=12:56\r\n        - 249,autocomplete\r\n        - 250,autocomplete\r\n  row2:\r\n    col1:\r\n      size: 9\r\n      bloc:\r\n        - 443,rows=5\r\n\r\n  row3:\r\n    col1:\r\n      size: 3\r\n      bloc:\r\n        - 103         # RPPS\r\n    col2:\r\n      size: 3\r\n      bloc:\r\n        - 104         # ADELI       \r\n    col3:\r\n      size: 3\r\n      bloc:\r\n        - 477         # N° réseau', ''),
(8, 'Listing des praticiens', 'description des colonnes affichées en résultat d\'une recherche praticien', 'data_types', 'admin', 'post', '', 2, 'public', 'col1:\r\n    head: "Identité"\r\n    blocseparator: " "\r\n    bloc:\r\n        - 51,gras\r\n        - 2,text-uppercase,gras\r\n        - 3,text-capitalize,gras\r\ncol2:\r\n    head: "Activité pro" \r\n    bloc: \r\n        - 19\r\ncol3:\r\n    head: "Tel" \r\n    bloc: \r\n        - 57\r\ncol4:\r\n    head: "Fax" \r\n    bloc: \r\n        - 58\r\ncol5:\r\n    head: "Email"\r\n    bloc-separator: " - "\r\n    bloc:\r\n        - 59\r\n        - 4\r\ncol6:\r\n    head: "Ville"\r\n    bloc:\r\n        - 56,text-uppercase', 'col1:\r\n    head: "Identité"\r\n    blocseparator: " "\r\n    bloc:\r\n        - 51,gras\r\n        - 2,text-uppercase,gras\r\n        - 3,text-capitalize,gras\r\ncol2:\r\n    head: "Activité pro" \r\n    bloc: \r\n        - 19\r\ncol3:\r\n    head: "Tel" \r\n    bloc: \r\n        - 57\r\ncol4:\r\n    head: "Fax" \r\n    bloc: \r\n        - 58\r\ncol5:\r\n    head: "Email"\r\n    bloc-separator: " - "\r\n    bloc:\r\n        - 59\r\n        - 4\r\ncol6:\r\n    head: "Ville"\r\n    bloc:\r\n        - 56,text-uppercase', ''),
(11, 'Formulaire mail', 'formulaire pour mail', 'data_types', 'mail', 'post', '/patient/actions/sendMail/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - 109,required\r\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - 110,required\r\n row2:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 112,required\r\n row3:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 446\r\n row4:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 111,rows=10', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - 109,required\r\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - 110,required\r\n row2:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 112,required\r\n row3:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 446\r\n row4:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 111,rows=10', ''),
(14, 'Formulaire mail Apicrypt', 'formulaire pour expédier un mail vers un correspondant apicrypt', 'data_types', 'mail', 'post', '/patient/actions/sendMail/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - 109,required\r\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - 179,required\r\n row2:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 112,required\r\n row3:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 446\r\n row4:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 111,rows=10', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - 109,required\r\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - 179,required\r\n row2:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 112,required\r\n row3:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 446\r\n row4:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 111,rows=10', ''),
(15, 'Fomulaire d\'import de document externe', 'fomulaire d\'import de document externe', 'data_types', 'doc', 'post', NULL, 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 181', 'structure:\r\n row1:\r\n  col1: \r\n    size: 12\r\n    bloc: \r\n      - 181', ''),
(16, 'Formulaire ordonnance', 'formualire ordonnance', 'data_types', 'ordo', 'post', NULL, 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - 187\r\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - 188', 'structure:\r\n row1:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - 187\r\n  col2: \r\n    size: 6\r\n    bloc: \r\n      - 188', ''),
(17, 'Formulaire règlement', 'formulaire pour le règlement', 'data_types', 'reglement', 'post', '/patient/actions/saveReglementForm/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - 197\r\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - 198,readonly,plus={€}\r\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - 199,plus={€}\r\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - 196,readonly,plus={€}\r\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - 194,plus={€}\r\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - 193,plus={€}\r\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - 195,plus={€}\r\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - 200,plus={€}\r\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - 205', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - 197\r\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - 198,plus={€}\r\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - 199,plus={€}\r\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - 196,readonly,plus={€}\r\n row2:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - 194,plus={€}\r\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - 193,plus={€}\r\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - 195,plus={€}\r\n  col4: \r\n    size: 3\r\n    bloc: \r\n      - 200,plus={€}\r\n row3:\r\n  col1: \r\n    size: 6\r\n    bloc: \r\n      - 205', ''),
(18, 'Formulaire simplifié règlement (page compta)', 'formulaire simplifié pour le règlement', 'data_types', 'reglement', 'post', '/compta/actions/saveReglementForm/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - 193,plus={€}\r\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - 194,plus={€}\r\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - 195,plus={€}\r\n row2:\r\n  col1: \r\n    size: 9\r\n    bloc: \r\n      - 205', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - 193,plus={€}\r\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - 194,plus={€}\r\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - 195,plus={€}\r\n row2:\r\n  col1: \r\n    size: 9\r\n    bloc: \r\n      - 205', ''),
(19, 'Recherche règlements', 'formulaire recherche règlement', 'form_basic_types', 'admin', 'post', '', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - 4\r\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - 4\r\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - 3', 'structure:\r\n row1:\r\n  col1: \r\n    size: 3\r\n    bloc: \r\n      - 4\r\n  col2: \r\n    size: 3\r\n    bloc: \r\n      - 4\r\n  col3: \r\n    size: 3\r\n    bloc: \r\n      - 3', ''),
(22, 'Import', 'formulaire pour consultation importée d\'une source externe', 'data_types', 'medical', 'post', '', 5, 'public', 'global:\r\n  formClass: \'newCS\' \r\nstructure:\r\n####### INTRODUCTION ######\r\n  row1:                              \r\n    head: \'Consultation importée\'\r\n    col1:                              \r\n      size: 12\r\n      bloc:                          \r\n        - 252,rows=10', 'global:\r\n  formClass: \'newCS\' \r\nstructure:\r\n####### INTRODUCTION ######\r\n  row1:                              # 1re rangée\r\n    head: \'Consultation importée\'\r\n    col1:                            # 1re colonne  \r\n      size: 12\r\n      bloc:                          # Types utilisés\r\n        - 252,rows=10', 'csImportee'),
(25, 'Assigner un mot de passe', 'formulaire assigner un password à un utilisateur', 'form_basic_types', 'admin', 'post', '/configuration/actions/configUpdatePassword/', 5, 'public', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Assigner le mot de passe à l\'identifiant"\r\n    size: 3\r\n    bloc: \r\n      - 1,required\r\n      - 2,required\r\n      - 3', 'structure:\r\n row1:\r\n  col1: \r\n    head: "Assigner le mot de passe à l\'identifiant"\r\n    size: 3\r\n    bloc: \r\n      - 1,required\r\n      - 2,required\r\n      - 3', '');

-- --------------------------------------------------------

--
-- Structure de la table `forms_cat`
--

CREATE TABLE `forms_cat` (
  `id` smallint(5) NOT NULL,
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `forms_cat`
--

INSERT INTO `forms_cat` (`id`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`) VALUES
(1, 'patientforms', 'Formulaires de saisie', 'Formulaire liés à la saisie de données', 'user', 1, '2016-12-16 16:13:38'),
(2, 'displayforms', 'Formulaires d\'affichage', 'Formulaires liés à l\'affichage d\'informations', 'user', 1, '2016-12-16 16:19:35'),
(4, 'formCS', 'Formulaires de consultation', 'Formulaires pour construire les consultations', 'user', 1, '2017-03-13 12:08:35'),
(5, 'systemForm', 'Formulaires système', 'formulaires système', 'user', 1, '2017-03-16 19:53:03');

-- --------------------------------------------------------

--
-- Structure de la table `form_basic_types`
--

CREATE TABLE `form_basic_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(60) NOT NULL,
  `placeholder` varchar(255) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `validationRules` varchar(255) NOT NULL,
  `validationErrorMsg` varchar(255) NOT NULL,
  `formType` varchar(255) NOT NULL,
  `formValues` text NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
  `cat` smallint(5) UNSIGNED NOT NULL,
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL,
  `deleteByID` smallint(5) UNSIGNED NOT NULL,
  `deleteDate` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `form_basic_types`
--

INSERT INTO `form_basic_types` (`id`, `name`, `placeholder`, `label`, `description`, `validationRules`, `validationErrorMsg`, `formType`, `formValues`, `type`, `cat`, `fromID`, `creationDate`, `deleteByID`, `deleteDate`) VALUES
(1, 'userid', 'identifiant', 'Identifiant', 'identifiant numérique d\'utilisateur', 'required|numeric', 'L\'identifiant utilisateur n\'est pas correct', 'text', '', 'base', 0, 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(2, 'password', 'mot de passe', 'Mot de passe', 'mot de passe utilisateur', 'required', 'Le mot de passe est manquant', 'password', '', 'base', 0, 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(3, 'submit', '', 'Valider', 'bouton submit de validation', '', '', 'submit', '', 'base', 0, 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00'),
(4, 'date', '', 'Début de période', '', '', '', 'date', '', 'base', 0, 0, '2017-03-27 00:00:00', 0, '2017-03-27 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `hprim`
--

CREATE TABLE `hprim` (
  `id` int(11) NOT NULL,
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `toID` smallint(5) UNSIGNED NOT NULL,
  `date` date DEFAULT '1000-01-01',
  `objetID` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `labelStandard` varchar(255) NOT NULL,
  `typeResultat` varchar(2) NOT NULL,
  `resultat` varchar(255) NOT NULL,
  `unite` varchar(20) NOT NULL,
  `normaleInf` varchar(20) NOT NULL,
  `normaleSup` varchar(20) NOT NULL,
  `indicateurAnormal` varchar(5) NOT NULL,
  `statutRes` varchar(1) NOT NULL,
  `resAutreU` varchar(50) NOT NULL,
  `normaleInfAutreU` varchar(20) NOT NULL,
  `normalSupAutreU` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `inbox`
--

CREATE TABLE `inbox` (
  `id` int(7) UNSIGNED NOT NULL,
  `txtFileName` varchar(30) NOT NULL,
  `txtDatetime` datetime NOT NULL,
  `txtNumOrdre` smallint(4) UNSIGNED NOT NULL,
  `hprimIdentite` varchar(250) NOT NULL,
  `hprimExpediteur` varchar(250) NOT NULL,
  `hprimCodePatient` varchar(250) NOT NULL,
  `hprimDateDossier` varchar(30) NOT NULL,
  `hprimAllSerialize` blob NOT NULL,
  `pjNombre` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `pjSerializeName` blob NOT NULL,
  `archived` enum('y','c','n') NOT NULL DEFAULT 'n',
  `assoToID` mediumint(7) UNSIGNED DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `objets_data`
--

CREATE TABLE `objets_data` (
  `id` int(11) UNSIGNED NOT NULL,
  `fromID` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `toID` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `typeID` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `parentTypeID` int(11) UNSIGNED DEFAULT '0',
  `instance` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `creationDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `updateDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `value` text,
  `outdated` enum('','y') NOT NULL DEFAULT '',
  `important` enum('n','y') DEFAULT 'n',
  `titre` varchar(255) NOT NULL DEFAULT '',
  `deleted` enum('','y') NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `people`
--

CREATE TABLE `people` (
  `id` int(11) UNSIGNED NOT NULL,
  `type` enum('patient','pro') NOT NULL DEFAULT 'patient',
  `rank` enum('','admin') DEFAULT NULL,
  `pass` varbinary(60) DEFAULT NULL,
  `registerDate` datetime DEFAULT NULL,
  `fromID` smallint(5) DEFAULT NULL,
  `lastLogIP` varchar(50) DEFAULT NULL,
  `lastLogDate` datetime DEFAULT NULL,
  `lastLogFingerprint` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `people`
--

INSERT INTO `people` (`id`, `type`, `rank`, `pass`, `registerDate`, `fromID`, `lastLogIP`, `lastLogDate`, `lastLogFingerprint`) VALUES
(1, 'pro', 'admin', 0x75a548542f3c5d2917b7ecb03112ddcc, NULL, NULL, '127.0.0.1', '2017-05-23 20:15:30', '4b7425f9d6cc059e94bb32f6627bda5acd049a84');

-- --------------------------------------------------------

--
-- Structure de la table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` smallint(5) NOT NULL,
  `cat` smallint(3) UNSIGNED NOT NULL DEFAULT '0',
  `label` varchar(250) NOT NULL,
  `description` text NOT NULL,
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `prescriptions_cat`
--

CREATE TABLE `prescriptions_cat` (
  `id` smallint(5) NOT NULL,
  `name` varchar(60) NOT NULL,
  `label` varchar(60) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('base','user') NOT NULL DEFAULT 'user',
  `fromID` smallint(5) UNSIGNED NOT NULL,
  `creationDate` datetime NOT NULL,
  `displayOrder` tinyint(2) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Contenu de la table `prescriptions_cat`
--

INSERT INTO `prescriptions_cat` (`id`, `name`, `label`, `description`, `type`, `fromID`, `creationDate`, `displayOrder`) VALUES
(2, 'prescripMedic', 'Prescriptions médicamenteuses', 'prescriptions médicamenteuses', 'user', 1, '2017-03-22 12:36:12', 1),
(4, 'prescriNonMedic', 'Prescriptions non médicamenteuses', 'prescriptions non médicamenteuses', 'user', 1, '2017-05-09 14:02:12', 1);

-- --------------------------------------------------------

--
-- Structure de la table `printed`
--

CREATE TABLE `printed` (
  `id` int(11) UNSIGNED NOT NULL,
  `fromID` int(11) UNSIGNED NOT NULL,
  `toID` int(11) UNSIGNED NOT NULL,
  `type` enum('cr','ordo','courrier') NOT NULL DEFAULT 'cr',
  `objetID` int(11) UNSIGNED DEFAULT NULL,
  `creationDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `serializedTags` longblob,
  `outdated` enum('','y') NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `actes`
--
ALTER TABLE `actes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `actes_cat`
--
ALTER TABLE `actes_cat`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `data_cat`
--
ALTER TABLE `data_cat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `data_types`
--
ALTER TABLE `data_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `groupe` (`groupe`),
  ADD KEY `cat` (`cat`),
  ADD KEY `groupe_2` (`groupe`,`id`);

--
-- Index pour la table `dicomTags`
--
ALTER TABLE `dicomTags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dicomTag` (`dicomTag`,`typeID`),
  ADD KEY `dicomTag_2` (`dicomTag`),
  ADD KEY `typeID` (`typeID`);

--
-- Index pour la table `forms`
--
ALTER TABLE `forms`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `forms_cat`
--
ALTER TABLE `forms_cat`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `form_basic_types`
--
ALTER TABLE `form_basic_types`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `hprim`
--
ALTER TABLE `hprim`
  ADD UNIQUE KEY `id` (`id`);

--
-- Index pour la table `inbox`
--
ALTER TABLE `inbox`
  ADD PRIMARY KEY (`txtFileName`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `archived` (`archived`);

--
-- Index pour la table `objets_data`
--
ALTER TABLE `objets_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `toID_typeID` (`toID`,`typeID`),
  ADD KEY `typeID` (`typeID`),
  ADD KEY `instance` (`instance`),
  ADD KEY `parentTypeID` (`parentTypeID`),
  ADD KEY `toID` (`toID`),
  ADD KEY `toID_2` (`toID`,`outdated`,`deleted`),
  ADD KEY `typeIDetValue` (`typeID`,`value`(15));

--
-- Index pour la table `people`
--
ALTER TABLE `people`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`);

--
-- Index pour la table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `prescriptions_cat`
--
ALTER TABLE `prescriptions_cat`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `printed`
--
ALTER TABLE `printed`
  ADD PRIMARY KEY (`id`),
  ADD KEY `examenID` (`objetID`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `actes`
--
ALTER TABLE `actes`
  MODIFY `id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `actes_cat`
--
ALTER TABLE `actes_cat`
  MODIFY `id` smallint(5) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `data_cat`
--
ALTER TABLE `data_cat`
  MODIFY `id` smallint(5) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `data_types`
--
ALTER TABLE `data_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `dicomTags`
--
ALTER TABLE `dicomTags`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `forms`
--
ALTER TABLE `forms`
  MODIFY `id` smallint(4) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `forms_cat`
--
ALTER TABLE `forms_cat`
  MODIFY `id` smallint(5) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `form_basic_types`
--
ALTER TABLE `form_basic_types`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `hprim`
--
ALTER TABLE `hprim`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `inbox`
--
ALTER TABLE `inbox`
  MODIFY `id` int(7) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `objets_data`
--
ALTER TABLE `objets_data`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `people`
--
ALTER TABLE `people`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` smallint(5) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `prescriptions_cat`
--
ALTER TABLE `prescriptions_cat`
  MODIFY `id` smallint(5) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `printed`
--
ALTER TABLE `printed`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
