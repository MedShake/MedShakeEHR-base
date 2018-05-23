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

$template='';
if (!is_file($homepath.'config/config.yml')) {
    if ($_SERVER['REQUEST_METHOD']=='GET') {
        $template="bienvenue";
    } elseif ($_SERVER['REQUEST_METHOD']=='POST' and isset($_POST['bienvenue'])) {
        $template="configForm";
    } elseif ($_SERVER['REQUEST_METHOD']=='POST' and isset($_POST['configForm'])) {
        $mysqli = new mysqli($_POST['sqlServeur'], $_POST['sqlRootId'], $_POST['sqlRootPwd']);
        $mysqli->set_charset("utf8");
        if (mysqli_connect_errno()) {
            die("Echec de connexion à la base de données.\nVérifiez l'utilisateur et le mot de passe root");
        }
        if ($mysqli->query("CREATE USER IF NOT EXISTS '".$_POST['sqlUser']."'@'localhost' IDENTIFIED BY '".$_POST['sqlPass']."'")===false) {
            die("Echec lors de la création de l'utilisateur MySQL");
        }
        if ($mysqli->query("CREATE DATABASE IF NOT EXISTS ".$_POST['sqlBase']." CHARACTER SET = 'utf8'")===false) {
            die('Echec lors de la création de la base de données MySQL');
        }
        if ($mysqli->query("GRANT ALL PRIVILEGES ON ".$_POST['sqlBase'].".* TO '".$_POST['sqlUser']."'@'localhost'")===false) {
            die("Echec lors de l'attribution des droits sur la base de données MySQL");
        }
        if (!is_dir($_POST['backupLocation'])) {
            if (mkdir($_POST['backupLocation'], 0770, true)===false) {
                die("Echec lors de la création du dossier ".$_POST['backupLocation']."<br>Vérifiez que www-data a les droits d'écriture vers ce chemin.");
            }
        }
        if (!is_dir($_POST['stockageLocation'])) {
            if (mkdir($_POST['stockageLocation'], 0770, true)===false) {
                die("Echec lors de la création du dossier ".$_POST['stockageLocation']."<br>Vérifiez que www-data a les droits d'écriture vers ce chemin.");
            }
        }

        $conf=array(
          'protocol'=>'http'.($_SERVER['HTTPS']?'s':'').'://',
          'host'=>$_SERVER['SERVER_NAME'].(in_array($_SERVER['SERVER_PORT'],['80','443'])?'':':'.$_SERVER['SERVER_PORT']),
          'urlHostSuffixe'=>substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'],'/install.php')),
          'webDirectory'=>$webdir.'/',
          'stockageLocation'=>$_POST['stockageLocation'],
          'backupLocation'=>$_POST['backupLocation'],
          'workingDirectory'=>$webdir.'/workingDirectory/',
          'cookieDomain'=>$_SERVER['SERVER_NAME'],
          'cookieDuration'=>31104000,
          'fingerprint'=>$_POST['fingerprint'],
          'sqlServeur'=>$_POST['sqlServeur'],
          'sqlBase'=>$_POST['sqlBase'],
          'sqlUser'=>$_POST['sqlUser'],
          'sqlPass'=>$_POST['sqlPass'],
          'sqlVarPassword'=>$_POST['sqlVarPassword'],
          'templatesFolder'=>$homepath.'templates/',
          'twigEnvironnementCache'=>false,
          'twigEnvironnementAutoescape'=>false
        );
        if (file_put_contents($homepath.'config/config.yml', Spyc::YAMLDump($conf, false, 0, true))===false) {
            die("Echec lors de l'écriture du fichier de configuration.\n Vérifiez que www-data a les droits d'écriture sur le dossier ".$homepath."config/");
        }

        header('Location: '.$_SERVER['REQUEST_URI']);
        die();
    }
} else {
    if ($_SERVER['REQUEST_METHOD']=='GET') {
        $template='baseInstall';
    } elseif ($_SERVER['REQUEST_METHOD']=='POST' and isset($_POST['baseInstall'])) {

        /////////// Config loader
        $p['config']=Spyc::YAMLLoad($homepath.'config/config.yml');

        /////////// SQL connexion
        $mysqli=msSQL::sqlConnect();

        /////////// Validators loader
        require $homepath.'fonctions/validators.php';

        /////////// Router
        $router = new AltoRouter();
        $routes=Spyc::YAMLLoad($homepath.'config/routes.yml');
        $router->addRoutes($routes);
        $router->setBasePath($p['config']['urlHostSuffixe']);
        $match = $router->match();

        if (!count(msSQL::sql2tabSimple("SHOW TABLES"))) {
            exec('mysql -u '.$p['config']['sqlUser'].' -p'.$p['config']['sqlPass'].' --default-character-set=utf8 '.$p['config']['sqlBase'].' < '.$homepath.'upgrade/base/sqlInstall.sql');
            msSQL::sqlQuery("INSERT INTO configuration (name, level, value) VALUES
            ('mailRappelLogCampaignDirectory', 'default', '".$webdir."'/mailsRappelRdvArchives/'),
            ('smsLogCampaignDirectory', 'default', '".$webdir."/smsArchives/'),
            ('apicryptCheminInbox', 'default', '".$webdir."/inbox/'),
            ('apicryptCheminArchivesInbox', 'default', '".$webdir."/inboxArchives/'),
            ('apicryptCheminFichierNC', 'default', '".$webdir."/workingDirectory/NC/'),
            ('apicryptCheminFichierC', 'default', '".$webdir."/workingDirectory/C/'),
            ('apicryptCheminVersClefs', 'default', '".$homepath."apicrypt/'),
            ('apicryptCheminVersBinaires', 'default', '".$homepath."apicrypt/bin/'),
            ('dicomWorkListDirectory', 'default', '".$webdir."/workingDirectory/'),
            ('dicomWorkingDirectory', 'default', '".$webdir."/workingDirectory/'),
            ('templatesPdfFolder', 'default', '".$homepath."templates/models4print/')
            ON DUPLICATE KEY UPDATE value=VALUES(value)");

            $modules=scandir($homepath.'upgrade/');
            foreach ($modules as $module) {
                if ($module!='.' and $module!='..') {
                    exec('mysql -u '.$p['config']['sqlUser'].' -p'.$p['config']['sqlPass'].' --default-character-set=utf8 '.$p['config']['sqlBase'].' < '.$homepath.'upgrade/'.$module.'/sqlInstall.sql');
                }
            }

            msTools::redirRoute('firstLogin');
        }
    }
}

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

    <link type="text/css" href="<?=$webpath?>/thirdparty/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link type="text/css" href="<?=$webpath?>/thirdparty/eonasdan/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet"/>
    <link type="text/css" href="<?=$webpath?>/js/jquery-ui-1.12.1.custom/jquery-ui.min.css" rel="stylesheet"/>
    <link type="text/css" href="<?=$webpath?>/css/general.css" rel="stylesheet"/>

    <script type="text/javascript" src="<?=$webpath?>/thirdparty/jquery/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="<?=$webpath?>/thirdparty/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
    <script defer src="<?=$webpath?>/thirdparty/moment/moment/min/moment.min.js"></script>
    <script defer src="<?=$webpath?>/thirdparty/eonasdan/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
    <script defer src="<?=$webpath?>/js/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
    <script defer src="<?=$webpath?>/js/general.js"></script>
    <script defer src="<?=$webpath?>/thirdparty/dennyferra/TypeWatch/jquery.typewatch.js"></script>
    <script defer src="<?=$webpath?>/thirdparty/danielm/uploader/dist/js/jquery.dm-uploader.min.js"></script>
    <script defer="defer" src="<?=$webpath?>/thirdparty/lrsjng/kjua/dist/kjua.min.js"></script>
  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#top-navbar">
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <i class="fa fa-bars" aria-hidden="true"></i>
            </button>
            <a href="" class="navbar-brand">MedShakeEHR</a>
        </div>

        <div class="collapse navbar-collapse" id="top-navbar">
          <ul class="nav navbar-nav">
          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid" role="main" style="padding-top:60px; padding-bottom : 50px;">
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
      <p>Nous allons créer le fichier de configuration nécéssaire au démarrage.</p>
      <form	action="<?=$_SERVER['REQUEST_URI']?>" 		method="post">
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
              <label class="control-label">Nom de la base à créer</label>
              <input name="sqlBase" type="text" class="form-control" autocomplete="off" required="required"
              value="medshakeehr"/>
            </div>
            <div class="form-group">
              <label class="control-label">Nom d'utilisateur de la base à créer</label>
              <input name="sqlUser" type="text" class="form-control" autocomplete="off" required="required"
              value=""/>
            </div>
            <div class="form-group">
              <label class="control-label">Mot de passe de l'utilisateur à créer</label>
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
      <p style="margin-top:50px;">Le fichier de configuration a été créé avec succès.<br>Nous allons Maintenant installer la base de données.</p>
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
