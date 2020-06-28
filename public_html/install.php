<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://www.github.com/fr33z00>
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
 * Installateur sur base préinstallée
 *
 * @author fr33z00 <https://www.github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

$webpath=str_replace('/install.php','',$_SERVER['REQUEST_URI']);
$webdir=getcwd();
ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");

if (($homepath=getenv("MEDSHAKEEHRPATH"))===false) {
    if (!is_file("MEDSHAKEEHRPATH") or ($homepath=file_get_contents("MEDSHAKEEHRPATH"))===false) {
        die("La variable d'environnement MEDSHAKEEHRPATH n'a pas été fixée.<br>Veuillez insérer <code>SetEnv MEDSHAKEEHRPATH /chemin/vers/MedShakeEHR</code> dans votre .htaccess ou la configuration du serveur.<br>Alternativement, vous pouvez créer un fichier 'MEDSHAKEEHRPATH' contenant <code>/chemin/vers/MedShakeEHR</code> et le placer dans le dossier web de MedShakeEHR");
    }
    $homepath=trim(str_replace("\n", '', $homepath));
}
$homepath.=$homepath[strlen($homepath)-1]=='/'?'':'/';

/////////// Petites vérifications de l'installation
if (!is_dir($homepath."vendor")) {
    die("L'installation de MedShakeEHR ne semble pas complète, veuillez installer COMPOSER (<a href='https://getcomposer.org'>https://getcomposer.org</a>)<br>Tapez ensuite <code>composer update</code> en ligne de commande dans le répertoire d'installation de MedShakeEHR.");
}
if (!is_dir("thirdparty")) {
    die("L'installation de MedShakeEHR ne semble pas complète, veuillez lancer <code>composer.phar install</code> dans le dossier ".$webdir);
}
if (!is_writable($homepath."config")) {
    die("Le répertoire ".$homepath."config n'est pas accessible en écriture pour le script d'installation. Corrigez ce problème avant de continuer.");
}

/////////// Composer class auto-upload
require $homepath.'vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
  global $homepath;
  if (is_file($homepath.'/class/' . $class . '.php')) {
    include $homepath.'/class/' . $class . '.php';
  }
});

/////////// Récupération des paramètres d'installation

// Init default value for install param
$conf = array (
  'sqlRootId'=>'',
  'sqlRootPwd'=>'',
  'sqlNotCreatDb'=>false,
  'protocol'=>'http://',
  'host'=>'localhost',
  'port'=>'',
  'urlHostSuffixe'=>'',
  'webDirectory'=>$webdir,
  'stockageLocation'=>$homepath.'stockage/',
  'backupLocation'=>$homepath.'backup/',
  'workingDirectory'=>$webdir.'workingDirectory/',
  'cookieDomain'=>'localhost',
  'cookieDuration'=>31104000,
  'fingerprint'=>preg_replace('#[=/|+]#', '', base64_encode(random_bytes(8))),
  'sqlServeur'=>'localhost',
  'sqlBase'=>'medshakeehr',
  'sqlUser'=>'',
  'sqlPass'=>'',
  'sqlVarPassword'=>preg_replace('#[=/|+]#', '', base64_encode(random_bytes(8))),
  'templatesFolder'=>$homepath.'templates/',
  'twigEnvironnementCache'=>false,
  'twigEnvironnementAutoescape'=>false,
);

if ($iscli || ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['configForm']))) {
  if ($iscli) {
    $argParams = read_args();
  }
  else {
    $argParams = get_post();
    $argParams['host'] = $_SERVER['SERVER_NAME'];
    $argParams['protocol'] = 'http'.($_SERVER['HTTPS']?'s':'').'://';
    if (! in_array($_SERVER['SERVER_PORT'],['80','443'])) $argParams['port'] = $_SERVER['SERVER_PORT'];
    $argParams['urlHostSuffixe'] = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'],'/install.php'));
    if (! empty($_POST['stockageLocation'])) $argParams['stockageLocation'] = msTools::setDirectoryLastSlash($_POST['stockageLocation']);
    if (! empty($_POST['backupLocation'])) $argParams['backupLocation'] = msTools::setDirectoryLastSlash($_POST['backupLocation']);
  }
  $argParams['cookieDomain'] = $argParams['host'];
  if (! empty($argParams['port'])) $argParams['host'] = $argParams['host'].':'.$argParams['port'];

  $errorsParam = check_config_param($argParams);
  if (count($errorsParam) > 0) {
    foreach ($errorsParam as $error) {
      echo $error."\n";
    }
    if ($iscli) exit(1);
    else die();
  }

  // Surcharge les paramettre par defaut
  foreach($argParams as $k=>$v) {
    // Ne pas enrgister le paramettre 'port' (il est enregister dans 'host')
    if ($k == 'port') $break;
    $conf[$k] = $v;
  }

}
$template='';
// Si le fichier de configuration est absent le crée
if (!is_file($homepath.'config/config.yml')) {
  if (!$iscli && $_SERVER['REQUEST_METHOD']=='GET') {
      $template="bienvenue";
  } elseif (!$iscli && $_SERVER['REQUEST_METHOD']=='POST' and isset($_POST['bienvenue'])) {
      $template="configForm";
  } elseif ($iscli || ($_SERVER['REQUEST_METHOD']=='POST' and isset($_POST['configForm']))) {
    check_and_create_base_config();
    if (!check_and_create_base_config()) {
      die();
    } elseif (! $iscli) {
      header('Location: '.$_SERVER['REQUEST_URI']);
      exit();
    }
  }
}

if (is_file($homepath.'config/config.yml')) {
  if (!$iscli && $_SERVER['REQUEST_METHOD']=='GET') {
      $template='baseInstall';
  } elseif ($iscli || ($_SERVER['REQUEST_METHOD']=='POST' and isset($_POST['baseInstall']))) {

      /////////// Config loader
      $p['config']=Spyc::YAMLLoad($homepath.'config/config.yml');

      /////////// SQL connexion
      $mysqli=msSQL::sqlConnect();

      /////////// Validators loader
      define("PASSWORDLENGTH", msConfiguration::getDefaultParameterValue('optionGeLoginPassMinLongueur'));
      require $homepath.'fonctions/validators.php';

      /////////// Router
      if (!$iscli) {
        $router = new AltoRouter();
        $routes=Spyc::YAMLLoad($homepath.'config/routes.yml');
        $router->addRoutes($routes);
        $router->setBasePath($p['config']['urlHostSuffixe']);
        $match = $router->match();
      }

      if (empty(msSQL::sql2tabSimple("SHOW TABLES"))) {
          exec('mysql -u '.escapeshellarg($p['config']['sqlUser']).' -p'.escapeshellarg($p['config']['sqlPass']).' -h'.escapeshellarg($p['config']['sqlServeur']).' --default-character-set=utf8 '.escapeshellarg($p['config']['sqlBase']).' < '.$homepath.'upgrade/base/sqlInstall.sql');
          msSQL::sqlQuery("INSERT INTO configuration (name, level, value) VALUES
          ('mailRappelLogCampaignDirectory', 'default', '".$webdir."/mailsRappelRdvArchives/'),
          ('smsLogCampaignDirectory', 'default', '".$webdir."/smsArchives/'),
          ('apicryptCheminInbox', 'default', '".$webdir."/inbox/'),
          ('apicryptCheminArchivesInbox', 'default', '".$homepath."inboxArchives/'),
          ('apicryptCheminFichierNC', 'default', '".$webdir."/workingDirectory/NC/'),
          ('apicryptCheminFichierC', 'default', '".$webdir."/workingDirectory/C/'),
          ('apicryptCheminVersClefs', 'default', '".$homepath."apicrypt/'),
          ('apicryptCheminVersBinaires', 'default', '".$homepath."apicrypt/bin/'),
          ('dicomWorkListDirectory', 'default', '".$webdir."/workingDirectory/'),
          ('dicomWorkingDirectory', 'default', '".$webdir."/workingDirectory/'),
          ('templatesPdfFolder', 'default', '".$homepath."templates/models4print/'),
          ('templatesCdaFolder', 'default', '".$homepath."templates/CDA/')
          ON DUPLICATE KEY UPDATE value=VALUES(value)");

          $modules=scandir($homepath.'upgrade/');
          foreach ($modules as $module) {
              if ($module!='.' and $module!='..') {
                  exec('mysql -u '.escapeshellarg($p['config']['sqlUser']).' -p'.escapeshellarg($p['config']['sqlPass']).' -h'.escapeshellarg($p['config']['sqlServeur']).' --default-character-set=utf8 '.escapeshellarg($p['config']['sqlBase']).' < '.$homepath.'upgrade/'.$module.'/sqlInstall.sql');
              }
          }
      }
      if (!$iscli) msTools::redirRoute('userLogInFirst');
  }
}

// Print html if not cli
if (! $iscli) {
  if ($template!=''): ?>
  <!DOCTYPE html>
  <html lang="fr">
    <head>
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">

      <title>
        MedShakeEHR : Installation</title>
      <meta name="Description" content=""/>

      <link type="text/css" href="<?=$webpath?>/scss/bs_custom.min.css" rel="stylesheet"/>
      <link type="text/css" href="<?=$webpath?>/thirdparty/eonasdan/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet"/>
      <link type="text/css" href="<?=$webpath?>/js/jquery-ui-1.12.1.custom/jquery-ui.min.css" rel="stylesheet"/>

      <script type="text/javascript" src="<?=$webpath?>/thirdparty/jquery/jquery/dist/jquery.min.js"></script>
      <script type="text/javascript" src="<?=$webpath?>/thirdparty/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
      <script defer src="<?=$webpath?>/thirdparty/moment/moment/min/moment.min.js"></script>
      <script defer src="<?=$webpath?>/thirdparty/eonasdan/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
      <script defer src="<?=$webpath?>/js/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
      <script defer src="<?=$webpath?>/js/general.js"></script>
      <script defer src="<?=$webpath?>/thirdparty/dennyferra/TypeWatch/jquery.typewatch.js"></script>
      <script defer src="<?=$webpath?>/thirdparty/danielm/uploader/dist/js/jquery.dm-uploader.min.js"></script>
      <script defer="defer" src="<?=$webpath?>/thirdparty/lrsjng/kjua/dist/kjua.min.js"></script>

      <!-- js spécifique pour l'installeur -->
      <script>
        $(document).ready(function() {

          // Si nous ne voulons pas que l'instalateur crée la base de
          // donnée, désactive les champs adequats
          $('input[name=sqlNotCreatDb]').on("change", function() {
            if(this.checked) {
              $('input[name=sqlRootId]').prop("disabled", true);
              $('input[name=sqlRootId]').prop("required", false);
              $('input[name=sqlRootPwd]').prop("disabled", true);
              $('input[name=sqlRootId]').prop("required", false);
            } else {
              $('input[name=sqlRootId]').prop("disabled", false);
              $('input[name=sqlRootId]').prop("required", true);
              $('input[name=sqlRootPwd]').prop("disabled", false);
              $('input[name=sqlRootPwd]').prop("required", true);
            };
          });
        });
      </script>
    </head>

    <body>

      <nav class="navbar navbar-dark bg-dark mb-3">
        <a class="navbar-brand" href="#">MedShakeEHR</a>
      </nav>

      <div class="container-fluid" role="main">
  <?php
  if ($template=='bienvenue') :
  ?>
        <h1>Bienvenue dans MedShakeEHR!</h1>
        <p style="margin-top:50px">Avant de pouvoir utiliser MedShakeEHR, nous devons procéder à quelques étapes.</p>
        <form	action="<?=$_SERVER['REQUEST_URI']?>" method="post" style="margin-top:50px;">
          <input name="bienvenue" type="hidden"/>
          <div class="row">
            <div class="col-md-4">
              <input type="submit" title="Suivant" value="Suivant" class="btn btn-primary" />
            </div>
          </div>
        </form>
  <?php
  elseif ($template=='configForm') :
  ?>
        <h2>Configuration rapide</h2>
        <p>Nous allons créer le fichier de configuration nécessaire au démarrage.</p>
        <form	action="<?=$_SERVER['REQUEST_URI']?>" class="mb-4" method="post">
          <input name="configForm" type="hidden"/>
          <div class="row">
            <div class="col-md-4">
              <h3>Paramètres généraux</h3>
              <div class="form-group">
                <label class="control-label">Chemin du dossier de stockage</label>
                <input name="stockageLocation" type="text" class="form-control" autocomplete="off" required="required"
                value="<?= $homepath; ?>stockage/"/>
              </div>
              <div class="form-group">
                <label class="control-label">Chemin du dossier de sauvegarde</label>
                <input name="backupLocation" type="text" class="form-control" autocomplete="off" required="required"
                value="<?= $homepath; ?>backups/"/>
              </div>
              <div class="form-group">
                <label class="control-label">Empreinte de sécurité pour les sessions (chaîne aléatoire)</label>
                <input name="fingerprint" type="text" class="form-control" autocomplete="off" required="required"
                value="<?= preg_replace('#[=/|+]#', '', base64_encode(random_bytes(8))) ?>"/>
              </div>
              <h3>Paramètres de la base de données</h3>
              <div class="form-group">
                <label class="control-label">Serveur SQL</label>
                <input name="sqlServeur" type="text" class="form-control" autocomplete="off" required="required"
                value="localhost"/>
              </div>
              <div class="alert alert-dark" role="alert">
                <div class="form-check">
                  <input name="sqlNotCreatDb" type="checkbox" class="form-check-input"/>
                  <label class="form-check-label">Ne pas créer la base de donnée</br>(seulement si vous avez déjà créer la base de donnée et l'utilisateur pour MedShakeEHR)</label>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label">Nom utilisateur root MySQL (ne sera pas enregistré)</label>
                <input name="sqlRootId" type="text" class="form-control" autocomplete="off" required="required"
                value="root"/>
              </div>
              <div class="form-group">
                <label class="control-label">Mot de passe utilisateur root MySQL (ne sera pas enregistré)</label>
                <input name="sqlRootPwd" type="password" class="form-control" autocomplete="off" required="required"
                value=""/>
              </div>
              <div class="form-group">
                <label class="control-label">Nom de la base à utiliser</label>
                <input name="sqlBase" type="text" pattern="[a-zA-Z0-9_]{1,64}" class="form-control" autocomplete="off" required="required" value="medshakeehr"/>
                <small class="form-text text-muted">Caractères alphanumériques et underscore uniquement</small>
              </div>
              <div class="form-group">
                <label class="control-label">Nom d'utilisateur de la base à utiliser</label>
                <input name="sqlUser" type="text" class="form-control" autocomplete="off" required="required"
                value=""/>
              </div>
              <div class="form-group">
                <label class="control-label">Mot de passe de l'utilisateur à utiliser</label>
                <input name="sqlPass" type="text" class="form-control" autocomplete="off" required="required"
                value=""/>
              </div>
              <div class="form-group">
                <label class="control-label">Empreinte de sécurité pour les mots de passe de la base (chaîne aléatoire)</label>
                <input name="sqlVarPassword" type="text" class="form-control" autocomplete="off" required="required"
                value="<?= preg_replace('#[=/|+]#', '', base64_encode(random_bytes(8))) ?>"/>
              </div>
              <input type="submit" title="Valider" value="Valider" class="btn btn-primary" />
            </div>
          </div>
        </form>
  <?php
  else :
  ?>
        <h2>Installation de la base de données</h2>
        <p style="margin-top:50px;">Le fichier de configuration a été créé avec succès.<br>Nous allons maintenant installer la base de données.</p>
        <form	action="<?=$_SERVER['REQUEST_URI']?>" method="post" style="margin-top:50px;">
          <input name="baseInstall" type="hidden"/>
          <div class="row">
            <div class="col-md-4">
              <input type="submit" title="Suivant" value="Suivant" class="btn btn-primary" />
            </div>
          </div>
        </form>
  <?php
  endif;
  ?>
      </div>
    </body>
  </html>
  <?php
  endif;
}
