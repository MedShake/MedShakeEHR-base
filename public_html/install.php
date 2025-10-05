<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://www.github.com/fr33z00>
 * http://www.medshake.net
 * DEMAREST Maxime (Indelog) <maxime@indelog.fr>
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
 * Installateur sur base préinstallée
 *
 * @author fr33z00 <https://www.github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib Michaël Val
 */

ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");

// Check si le script est lancé via la ligne de commande ou via le navigateur web
if (PHP_SAPI === 'cli') {
  $iscli = true;

  // Si lancé en cli, ne doit pas être root
  if (posix_getuid() < 1) {
    die("Ce script d'installation ne doit pas être executé avec l'utilisateur root.\n");
  }

  $webdir = str_replace('/install.php', '', array_shift($argv) . '/');
} else {
  $iscli = false;
  $webdir = getcwd() . '/';
  $webpath = str_replace('/install.php', '', $_SERVER['REQUEST_URI']);
}

if (($homepath = getenv("MEDSHAKEEHRPATH")) === false) {
  if (!is_file("MEDSHAKEEHRPATH") or ($homepath = file_get_contents("MEDSHAKEEHRPATH")) === false) {
    if ($iscli) {
      die("La variable d'environnement MEDSHAKEEHRPATH n'a pas été fixée.\nFaites un 'export MEDSHAKEEHRPATH=/chemin/vers/MedShakeEHR\nAlternativement, vous pouvez créer un fichier 'MEDSHAKEEHRPATH' contenant '/chemin/vers/MedShakeEHR' et le placer dans le dossier web de MedShakeEHR.\n");
    } else {
      die("La variable d'environnement MEDSHAKEEHRPATH n'a pas été fixée.<br>Veuillez insérer <code>SetEnv MEDSHAKEEHRPATH /chemin/vers/MedShakeEHR</code> dans votre .htaccess ou la configuration du serveur.<br>Alternativement, vous pouvez créer un fichier 'MEDSHAKEEHRPATH' contenant <code>/chemin/vers/MedShakeEHR</code> et le placer dans le dossier web de MedShakeEHR.\n");
    }
  }
}
$homepath = trim(str_replace("\n", '', $homepath));
$homepath .= $homepath[strlen($homepath) - 1] == '/' ? '' : '/';

/////////// Composer class auto-upload
require $homepath . 'vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
  global $homepath;
  if (is_file($homepath . '/class/' . $class . '.php')) {
    include $homepath . '/class/' . $class . '.php';
  }
});

/////////// Vérifications simples du bon déroulement des étapes antérieures à ce script
if (!is_dir($homepath . "vendor")) {
  die("L'installation de MedShakeEHR ne semble pas complète, veuillez installer COMPOSER (<a href='https://getcomposer.org'>https://getcomposer.org</a>)\nTapez ensuite <code>composer update</code> en ligne de commande dans le répertoire d'installation de MedShakeEHR.\n");
}
if (!is_dir($webdir . "thirdparty")) {
  die("L'installation de MedShakeEHR ne semble pas complète, veuillez lancer <code>composer.phar install</code> dans le dossier " . $webdir);
}
if (!is_writable($homepath . "config")) {
  die("Le répertoire " . $homepath . "config n'est pas accessible en écriture pour le script d'installation. Corrigez ce problème avant de continuer.");
}


/////////// Récupération des paramètres d'installation

// Init default value for install param
$conf = array(
  'sqlRootId' => '',
  'sqlRootPwd' => '',
  'sqlNotCreatDb' => false,
  'protocol' => 'http://',
  'host' => 'localhost',
  'port' => '',
  'urlHostSuffixe' => '',
  'webDirectory' => $webdir,
  'stockageLocation' => $homepath . 'stockage/',
  'backupLocation' => $homepath . 'backup/',
  'workingDirectory' => $webdir . 'workingDirectory/',
  'cookieDomain' => 'localhost',
  'cookieDuration' => 31104000,
  'fingerprint' => msTools::getRandomStr(8),
  'sqlServeur' => 'localhost',
  'sqlBase' => 'medshakeehr',
  'sqlUser' => '',
  'sqlPass' => '',
  'sqlVarPassword' => msTools::getRandomStr(8),
  'templatesFolder' => $homepath . 'templates/',
  'twigEnvironnementCache' => false,
  'twigEnvironnementAutoescape' => false,
  'twigDebug' => false
);

if ($iscli || ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['configForm']))) {
  if ($iscli) {
    $argParams = msInstall::read_args();
  } else {
    $argParams = msInstall::get_post();
    $argParams['host'] = $_SERVER['SERVER_NAME'];
    if (isset($_SERVER['HTTPS']) and !empty($_SERVER['HTTPS'])) {
      $argParams['protocol'] = 'https://';
    } else {
      $argParams['protocol'] = 'http://';
    }
    if (!in_array($_SERVER['SERVER_PORT'], ['80', '443'])) $argParams['port'] = $_SERVER['SERVER_PORT'];
    $argParams['urlHostSuffixe'] = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '/install.php'));
    if (!empty($_POST['stockageLocation'])) $argParams['stockageLocation'] = msTools::setDirectoryLastSlash($_POST['stockageLocation']);
    if (!empty($_POST['backupLocation'])) $argParams['backupLocation'] = msTools::setDirectoryLastSlash($_POST['backupLocation']);
  }
  $argParams['cookieDomain'] = $argParams['host'];
  if (!empty($argParams['port'])) $argParams['host'] = $argParams['host'] . ':' . $argParams['port'];

  $errorsParam = msInstall::check_config_param($argParams);
  if (count($errorsParam) > 0) {
    foreach ($errorsParam as $error) {
      echo $error . "\n";
    }
    if ($iscli) {
      exit(1);
    } else {
      die();
    }
  }

  // Surcharge les parametres par defaut
  foreach ($argParams as $k => $v) {
    // Ne pas enregister le paramettre 'port' (il est enregister dans 'host')
    if ($k == 'port') $break;
    $conf[$k] = $v;
  }
}

// Si le fichier de configuration est absent le créer
if (!is_file($homepath . 'config/config.yml')) {
  if (!$iscli && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $template = "install-bienvenue";
  } elseif (!$iscli && $_SERVER['REQUEST_METHOD'] == 'POST' and isset($_POST['bienvenue'])) {
    $template = "install-configForm";
  } elseif ($iscli || ($_SERVER['REQUEST_METHOD'] == 'POST' and isset($_POST['configForm']))) {
    if (!msInstall::check_and_create_base_config()) {
      die();
    } elseif (!$iscli) {
      header('Location: ' . $_SERVER['REQUEST_URI']);
      exit();
    }
  }
} else {
  if (!$iscli && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $template = 'install-prebdd';
  } elseif ($iscli || ($_SERVER['REQUEST_METHOD'] == 'POST' and isset($_POST['baseInstall']))) {

    /////////// Config loader
    $p['config'] = msYAML::yamlFileRead($homepath . 'config/config.yml');

    /////////// SQL connexion
    $pdo = msSQL::sqlConnect();

    /////////// Validators loader
    define("PASSWORDLENGTH", 8);
    require $homepath . 'fonctions/validators.php';

    /////////// Router
    if (!$iscli) {
      $router = new AltoRouter();
      $routes = msYAML::yamlFileRead($homepath . 'config/routes/routes-base.yml') + msYAML::yamlFileRead($homepath . 'config/routes/routes-login.yml');
      $router->addRoutes($routes);
      $router->setBasePath($p['config']['urlHostSuffixe']);
      $match = $router->match();
    }

    if (empty(msSQL::sql2tabSimple("SHOW TABLES"))) {
      msSQL::sqlExecuteFile($homepath . 'upgrade/base/sqlInstall.sql');
      msSQL::sqlQuery("INSERT INTO configuration (name, level, value) VALUES
          ('mailRappelLogCampaignDirectory', 'default', '" . $webdir . "/mailsRappelRdvArchives/'),
          ('smsLogCampaignDirectory', 'default', '" . $webdir . "/smsArchives/'),
          ('apicryptCheminInbox', 'default', '" . $webdir . "/inbox/'),
          ('apicryptCheminArchivesInbox', 'default', '" . $homepath . "inboxArchives/'),
          ('apicryptCheminFichierNC', 'default', '" . $webdir . "/workingDirectory/NC/'),
          ('apicryptCheminFichierC', 'default', '" . $webdir . "/workingDirectory/C/'),
          ('apicryptCheminVersClefs', 'default', '" . $homepath . "apicrypt/'),
          ('apicryptCheminVersBinaires', 'default', '" . $homepath . "apicrypt/bin/'),
          ('dicomWorkListDirectory', 'default', '" . $webdir . "/workingDirectory/'),
          ('dicomWorkingDirectory', 'default', '" . $webdir . "/workingDirectory/'),
          ('templatesPdfFolder', 'default', '" . $homepath . "templates/models4print/'),
          ('templatesCdaFolder', 'default', '" . $homepath . "templates/CDA/')
          ON DUPLICATE KEY UPDATE value=VALUES(value)");

      $modules = scandir($homepath . 'upgrade/');
      foreach ($modules as $module) {
        if ($module != '.' and $module != '..') {
          msSQL::sqlExecuteFile($homepath . 'upgrade/' . $module . '/sqlInstall.sql');
        }
      }
    }
    if (!$iscli) {
      $router = new msSystem();
      msTools::redirRoute('userLogInFirst');
    }
  }
}

// Print html if not cli
if (!$iscli) {

  //////// View if defined
  if (isset($template) and !empty($template)) {

    $p['page'] = array(
      'webpath' => $webpath,
      'request_uri'  => $_SERVER['REQUEST_URI'],
      'homepath' => $homepath,
      'fingerprintRandom' => msTools::getRandomStr(8),
      'sqlVarPasswordRandom' => msTools::getRandomStr(8)
    );
    if (!isset($p['config'])) {
      $p['config'] = $conf;
    }

    if (isset($_SESSION)) {
      $p['session'] = $_SESSION;
    }

    header("Expires: 0");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: private, no-store, max-age=0, no-cache, must-revalidate, post-check=0, pre-check=0");
    header("Pragma: no-cache");

    //générer et sortir le html
    $getHtml = new msGetHtml();
    $getHtml->set_template($template);
    echo $getHtml->genererHtml();
  }
}
