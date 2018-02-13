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
 * Pivot central des pages loguées
 *
 * @author fr33z00 <https://www.github.com/fr33z00>
 */

ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");

if(($homepath=getenv("MEDSHAKEEHRPATH"))===false) {
    die("La variable d'environnement MEDSHAKEEHRPATH n'a pas été fixée.<br>Veuillez insérer 'SetEnv MEDSHAKEEHRPATH /chemin/vers/MedShakeEHR' dans votre .htaccess ou la configuration du serveur");
}
$homepath.=$homepath[strlen($homepath)-1]=='/'?'':'/';

/////////// Petites vérifications de l'installation
if (!is_dir($homepath."vendor")) {
    die("L'installation de MedShakeEHR ne semble pas complète, veuillez installer COMPOSER (<a href='https://getcomposer.org'>https://getcomposer.org</a>)<br>Tapez ensuite <code>composer update</code> en ligne de commande dans le répertoire d'installation de MedShakeEHR.");
}
if (!is_dir("bower_components")) {
    die("L'installation de MedShakeEHR ne semble pas complète, veuillez installer BOWER (<a href='https://bower.io'>https://bower.io</a>)<br>Tapez ensuite <code>bower update --save</code> en ligne de commande dans le répertoire /public_html de MedShakeEHR.");
}

/////////// Composer class auto-upload
require $homepath.'vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
    if (is_file(getenv("MEDSHAKEEHRPATH").'/class/' . $class . '.php')) {
        include getenv("MEDSHAKEEHRPATH").'/class/' . $class . '.php';
    }
});

$template='';
if (!is_file($homepath.'config/config.yml')) {
    if ($_SERVER['REQUEST_METHOD']=='GET') {
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
            if ( mkdir($_POST['backupLocation'], 0660, true)===false) {
                die("Echec lors de la création du dossier ".$_POST['backupLocation']."<br>Vérifiez que www-data a les droits d'écriture vers ce chemin.");
            }
        }
        if (!is_dir($_POST['stockageLocation'])) {
            if ( mkdir($_POST['stockageLocation'], 0660, true)===false) {
                die("Echec lors de la création du dossier ".$_POST['stockageLocation']."<br>Vérifiez que www-data a les droits d'écriture vers ce chemin.");
            }
        }

        $conf="#\n"
             ."# This file is part of MedShakeEHR.\n"
             ."#\n"
             ."# Copyright (c) 2017\n"
             ."# Bertrand Boutillier <b.boutillier@gmail.com>\n"
             ."# http://www.medshake.net\n"
             ."#\n"
             ."# MedShakeEHR is free software: you can redistribute it and/or modify\n"
             ."# it under the terms of the GNU General Public License as published by\n"
             ."# the Free Software Foundation, either version 3 of the License, or\n"
             ."# any later version.\n"
             ."#\n"
             ."# MedShakeEHR is distributed in the hope that it will be useful,\n"
             ."# but WITHOUT ANY WARRANTY; without even the implied warranty of\n"
             ."# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\n"
             ."# GNU General Public License for more details.\n"
             ."#\n"
             ."# You should have received a copy of the GNU General Public License\n"
             ."# along with MedShakeEHR.  If not, see <http://www.gnu.org/licenses/>.\n"
             ."#\n"
             ."#\n"
             ."#######################################\n"
             ."#\n"
             ."# Configuration générale de MedShakeEHR au format yaml\n"
             ."#\n"
             ."# Chaque paramètre peut être surchargé de façon spécifique pour chaque\n"
             ."# utilisateur (voir la zone de configuration)\n"
             ."#\n"
             ."#######################################\n"
             ."\n"
             ."#######################################\n"
             ."##  general config\n"
             ."protocol: 'http".($_SERVER['HTTPS']?"s":"")."://'\n"
             ."host: '".$_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT']!="80"?":".$_SERVER['SERVER_PORT']:"")."'\n"
             ."urlHostSuffixe: '".substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'],"/install.php"))."'\n"
             ."webDirectory: '".getcwd()."/'\n"
             ."stockageLocation: '".$_POST['stockageLocation']."'\n"
             ."backupLocation: '".$_POST['backupLocation']."'\n"
             ."workingDirectory: '".getcwd()."/workingDirectory/'\n"
             ."cookieDomain: '".$_SERVER['SERVER_NAME']."'\n"
             ."cookieDuration: 31104000\n"
             ."fingerprint: '".$_POST['fingerprint']."'\n"
             ."\n"
             ."#######################################\n"
             ."## sql access\n"
             ."sqlServeur: '".$_POST['sqlServeur']."'\n"
             ."sqlBase: '".$_POST['sqlBase']."'\n"
             ."sqlUser: '".$_POST['sqlUser']."'\n"
             ."sqlPass: '".$_POST['sqlPass']."'\n"
             ."sqlVarPassword: '".$_POST['sqlVarPassword']."'\n"
             ."\n"
             ."#######################################\n"
             ."## Permettre aux praticiens d'être patients (si false, il faudra leur créer une fiche patient)\n"
             ."PraticienPeutEtrePatient: true\n"
             ."\n"
             ."#######################################\n"
             ."## administratif\n"
             ."administratifSecteurHonoraires: 1\n"
             ."administratifPeutAvoirFacturesTypes: 'false'\n"
             ."administratifPeutAvoirPrescriptionsTypes: 'false'\n"
             ."administratifPeutAvoirAgenda: 'false'\n"
             ."administratifPeutAvoirRecettes: 'true'\n"
             ."administratifComptaPeutVoirRecettesDe: ''\n"
             ."\n"
             ."#######################################\n"
             ."## templates impression\n"
             ."##\n"
             ."\n"
             ."# répertoire par défaut\n"
             ."templatesPdfFolder: '".$homepath."templates/models4print/'\n"
             ."\n"
             ."# modèle par defaut\n"
             ."templateDefautPage: 'base-page-headAndFoot.html.twig'\n"
             ."# ordonnance\n"
             ."templateOrdoHeadAndFoot: 'base-page-headAndFoot.html.twig'\n"
             ."templateOrdoBody: 'ordonnanceBody.html.twig'\n"
             ."templateOrdoALD: 'ordonnanceALD.html.twig'\n"
             ."# compte rendu\n"
             ."templateCrHeadAndFoot: 'base-page-headAndNoFoot.html.twig'\n"
             ."# courrier et certificat\n"
             ."templateCourrierHeadAndFoot: 'base-page-headAndNoFoot.html.twig'\n"
             ."\n"
             ."\n"
             ."#######################################\n"
             ."## smtp\n"
             ."smtpTracking: ''\n"
             ."smtpFrom: 'user@domain.net'\n"
             ."smtpFromName: ''\n"
             ."smtpHost: 'smtp.net'\n"
             ."smtpPort: 587\n"
             ."smtpSecureType: 'tls'\n"
             ."smtpOptions: 'off'\n"
             ."smtpUsername: 'smtpuserlogin'\n"
             ."smtpPassword: 'smtppassword'\n"
             ."smtpDefautSujet: 'Document vous concernant'\n"
             ."\n"
             ."#######################################\n"
             ."## apicrypt\n"
             ."\n"
             ."apicryptCheminInbox: '".getcwd()."/inbox/'\n"
             ."apicryptCheminArchivesInbox: '".getcwd()."/inboxArchives/'\n"
             ."apicryptInboxMailForUserID: '0'\n"
             ."\n"
             ."apicryptCheminFichierNC: '".getcwd()."/workingDirectory/NC/'\n"
             ."apicryptCheminFichierC: '".getcwd()."/workingDirectory/C/'\n"
             ."apicryptCheminVersClefs: '".$homepath."apicrypt/'\n"
             ."apicryptCheminVersBinaires: '".$homepath."apicrypt/bin/'\n"
             ."apicryptUtilisateur: 'prenom.NOM'\n"
             ."apicryptAdresse: 'prenom.NOM@medicalXX.apicrypt.org'\n"
             ."apicryptSmtpHost: 'smtp.intermedic.org'\n"
             ."apicryptSmtpPort: '25'\n"
             ."apicryptPopHost: 'pop.intermedic.org'\n"
             ."apicryptPopPort: '110'\n"
             ."apicryptPopUser: 'prenom.NOM'\n"
             ."apicryptPopPass: 'passwordapicrypt'\n"
             ."apicryptDefautSujet: 'Document concernant votre patient'\n"
             ."\n"
             ."#######################################\n"
             ."## fax en ligne\n"
             ."faxService: 'ecofaxOVH'\n"
             ."ecofaxMyNumber: '0900000000'\n"
             ."ecofaxPass: 'password'\n"
             ."\n"
             ."#######################################\n"
             ."## dicom\n"
             ."dicomHost: '192.168.xxx.xxx'\n"
             ."dicomPrefixIdPatient: '1.100.100'\n"
             ."dicomWorkListDirectory: '".getcwd()."/workingDirectory/'\n"
             ."dicomWorkingDirectory: '".getcwd()."/workingDirectory/'\n"
             ."dicomAutoSendPatient2Echo: 'false'\n"
             ."dicomDiscoverNewTags: 'true'\n"
             ."\n"
             ."#######################################\n"
             ."## PhoneCapture\n"
             ."phonecaptureFingerprint: 'phonecapture'\n"
             ."phonecaptureCookieDuration: 31104000\n"
             ."phonecaptureResolutionWidth: 1920\n"
             ."phonecaptureResolutionHeight: 1080\n"
             ."\n"
             ."#######################################\n"
             ."## agenda\n"
             ."\n"
             ."agendaService: ''\n"
             ."## si agendaService est actif, alors agendaDistantLink doit être mis à ''\n"
             ."agendaDistantLink: 'http://monagenda.agenda.abc'\n"
             ."agendaDistantPatientsOfTheDay: 'http://monagenda.agenda.abc/patientsOfTheDay.json'\n"
             ."agendaLocalPatientsOfTheDay: 'patientsOfTheDay.json'\n"
             ."agendaNumberForPatientsOfTheDay: 0\n"
             ."\n"
             ."\n"
             ."\n"
             ."#######################################\n"
             ."## Rappels RDV par mail\n"
             ."mailRappelLogCampaignDirectory: '".getcwd()."/mailsRappelRdvArchives/'\n"
             ."mailRappelDaysBeforeRDV: '3'\n"
             ."\n"
             ."#######################################\n"
             ."## SMS (cf /servicesTiers/sms/ )\n"
             ."smsProvider: ''\n"
             ."smsLogCampaignDirectory: '".getcwd()."/smsArchives/'\n"
             ."smsDaysBeforeRDV: '3'\n"
             ."smsCreditsFile: 'creditsSMS.txt'\n"
             ."smsSeuilCreditsAlerte: '150'\n"
             ."smsTpoa: 'Dr ....'\n"
             ."\n"
             ."#######################################\n"
             ."## Templates affichage écran\n"
             ."templatesFolder: '".$homepath."templates/'\n"
             ."\n"
             ."\n"
             ."#######################################\n"
             ."## Twig configuration\n"
             ."twigEnvironnementCache: false #'/tmp/templates_cache/'\n"
             ."twigEnvironnementAutoescape: false";
        if(file_put_contents($homepath.'config/config.yml', $conf)===false) {
            die("Echec lors de l'écriture du fichier de configuration.\n Vérifiez que www-data a les droits d'écriture sur le dossier ".$homepath."config/");
        }

        header('Location: /install.php');
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

if($template!=''): ?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
      MedShakeEHR : Installation</title>
    <meta name="Description" content=""/>

    <link type="text/css" href="/bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link type="text/css" href="/bower_components/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet"/>
    <link type="text/css" href="/js/jquery-ui-1.12.1.custom/jquery-ui.min.css" rel="stylesheet"/>
    <link type="text/css" href="/css/general.css" rel="stylesheet"/>

    <script type="text/javascript" src="/bower_components/jquery/dist/jquery.min.js"></script>
    <script type="text/javascript" src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script defer src="/bower_components/moment/min/moment.min.js"></script>
    <script defer src="/bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>
    <script defer src="/js/jquery-ui-1.12.1.custom/jquery-ui.min.js"></script>
    <script defer src="/js/general.js"></script>
    <script defer src="/bower_components/jquery-typewatch/jquery.typewatch.js"></script>
    <script defer src="/bower_components/uploader/src/dmuploader.min.js"></script>
    <script defer="defer" src="/bower_components/kjua/dist/kjua.min.js"></script>
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
if ($template=='configForm') :
?>
      <h2>Configuration rapide</h2>
      <p>Nous allons créer le fichier de configuration nécéssaire au démarrage.<br>Vous le retrouverez sous le nom <strong><?php $homepath ?>/config/config.yml</strong> et pourrez y configurer plus en détail les préférences globales.</p>
      <form	action="/install.php" 		method="post">
        <input name="configForm" type="hidden"/>
        <div class="row">
          <div class="col-md-4">
            <h2>Paramètres généraux</h2>
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
              value="<?= str_replace('=','',base64_encode(random_bytes(8))) ?>"/>
            </div>
            <h2>Paramètres de la base de données</h2>
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
              value="<?= str_replace('=','',base64_encode(random_bytes(8))) ?>"/>
            </div>
            <input type="submit" title="Valider" value="Valider" class="btn btn-primary" />
	        </div>
        </div>
      </form>
    </div>
  </body>
</html>
<?php
else :
?>
      <h2>Installation de la base de données</h2>
      <p>Le fichier de configuration a été créé avec succès.<br>Nous allons Maintenant installer la base de données.</p>
      <form	action="/install.php" 		method="post">
        <input name="baseInstall" type="hidden"/>
        <div class="row">
          <div class="col-md-4">
            <input type="submit" title="Valider" value="Valider" class="btn btn-primary" />
	        </div>
        </div>
      </form>
      </div>

</body>
</html>
<?php
endif;
endif;

