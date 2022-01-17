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
 * Installateur de base
 *
 * @author fr33z00 <https://www.github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");

function command_exist($cmd) {
    $return = shell_exec(sprintf("which %s", escapeshellarg($cmd)));
    return !empty($return);
}

$template='';

if ($_SERVER['REQUEST_METHOD']=='GET') {
    $template='bienvenue';
} elseif ($_SERVER['REQUEST_METHOD']=='POST') {
    $dossierweb=getcwd();
    if (!is_writable($dossierweb)) {
      $template='erreur-droits';
      $dossier=$dossierweb;
    } elseif (!command_exist('git')) {
      $template='erreur-git';
      $ret='git (apt-get install git)<br>';
    } else {
        $dossier=$_POST['destination'];
        if (!is_dir($_POST['destination'])) {
            mkdir($_POST['destination'], 0774, true);
        }
        if (!is_dir($_POST['destination']) or !is_writable($_POST['destination'])) {
            $template='erreur-droits';
        } else {
            file_put_contents("MEDSHAKEEHRPATH", $_POST['destination']);
            $dossier.=($dossier[strlen($dossier)-1])!='/' ? '/' : '';

            if(!isset($_POST['v'])) {
              //récupération de la dernière version release
              $ch = curl_init("https://api.github.com/repos/medshake/MedShakeEHR-base/releases/latest");
              curl_setopt($ch, CURLOPT_USERAGENT, "linux");
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              $res=json_decode(curl_exec($ch), true);
              curl_close($ch);
              $releaseTagName = $res['tag_name'];
            } else {
              $releaseTagName = $_POST['v'];
            }
            //téléchargement de la dernière release
            file_put_contents("/tmp/medshake.zip", fopen('https://github.com/medshake/MedShakeEHR-base/archive/'.$releaseTagName.'.zip', 'r'));
            $zip = new ZipArchive;
            if ($zip->open("/tmp/medshake.zip"))  {
                $zip->extractTo('/tmp/');
                unlink("/tmp/medshake.zip");
                //deplacement du contenu de public_html
                $dossierdezip='/tmp/MedShakeEHR-base-'.$releaseTagName;
                if (!is_dir($dossierdezip)) {
                    $dossierdezip='/tmp/MedShakeEHR-base-'.str_replace('v','',$releaseTagName);
                }
                foreach (scandir($dossierdezip.'/public_html') as $f) {
                    if ($f !='.' and $f !='..') {
                        exec('mv '.$dossierdezip.'/public_html/'.$f.' '.$dossierweb.'/'.$f);
                    }
                }
                rmdir($dossierdezip.'/public_html');
                //deplacement du reste vers la destination
                foreach (scandir($dossierdezip) as $f) {
                    if ($f !='.' and $f !='..') {
                        exec('mv '.$dossierdezip.'/'.$f.' '.$dossier.$f);
                    }
                }
                chdir($dossier);
                //telechargement de composer
                file_put_contents("composer.phar", fopen("https://getcomposer.org/download/1.6.3/composer.phar", 'r'));
                chmod("composer.phar", 0774);
                exec('COMPOSER_HOME="/tmp/" php ./composer.phar install 2>&1', $ret);
                json_encode($ret);
                //exécution de composer pour la partie JS
                chdir($dossierweb);
                exec('COMPOSER_HOME="/tmp/" php '.$dossier.'composer.phar install 2>&1', $ret);
                //Vérifie l'absence d'erreur dans le log Composer
                $errormatches = array_filter($ret, function ($haystack){
					if( strpos(strtolower($haystack), 'error') === false) {return false;} else {return true;}
				});
                if(empty($errormatches)) {
                    unlink($dossierweb.'/self-installer.php');
                    $htaccess="SetEnv MEDSHAKEEHRPATH ".$dossier."\n";
                    $htaccess.=file_get_contents($dossierweb."/.htaccess");
                    file_put_contents($dossierweb."/.htaccess", $htaccess);
                    //lancement de la partie configuration
                    header('Location: '.$dossierweb."/install.php");
                    die();
                } else {
                    $ret=explode('<br>', $ret);
                    $template='erreur-inconnue';
                }
            } else {
                $ret="Impossible de dezipper le fichier /tmp/medshake.zip";
                $template='erreur-inconnue';
            }
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">

    <title>
      MedShakeEHR : Pre-installation</title>
    <meta name="Description" content=""/>
  </head>
  <style>
    .btn {color:#fff;background-color:#286090;border-color:#204d74;padding:6px 12px;cursor:pointer}
    .mysvg {width:64px;height:64px}
    .svgcontainer {width:64px;height:64px;display:none;position:absolute;left:170px;top:240px}
    .svganim {display:block !important; animation:shake 10s normal 1s infinite}
    @keyframes shake {
      from {margin-left:0}
      0.2% {margin-left:-2px}
      0.6% {margin-left:4px}
      1% {margin-left:-6px}
      1.4% {margin-left:6px}
      1.8% {margin-left:-4px}
      2.1% {margin-left:2px}
      2.4% {margin-left:0}
      20% {margin-left:0}
      20.2% {margin-left:-2px}
      20.6% {margin-left:4px}
      21% {margin-left:-6px}
      21.4% {margin-left:6px}
      21.8% {margin-left:-4px}
      22.1% {margin-left:2px}
      22.4% {margin-left:0}
      40% {margin-left:0}
      40.2% {margin-left:-2px}
      40.6% {margin-left:4px}
      41% {margin-left:-6px}
      41.4% {margin-left:6px}
      41.8% {margin-left:-4px}
      42.1% {margin-left:2px}
      42.4% {margin-left:0}
      60% {margin-left:0}
      60.2% {margin-left:-2px}
      60.6% {margin-left:4px}
      61% {margin-left:-6px}
      61.4% {margin-left:6px}
      61.8% {margin-left:-4px}
      62.1% {margin-left:2px}
      62.4% {margin-left:0}
      80% {transform:rotate(0deg)}
      85% {transform:rotate(360deg)}
      to {transform:rotate(360deg)}
    }
  </style>
  <script>
    window.addEventListener("beforeclose", function(){
        alert("Si vous quittez cette page, l'installation ne sera pas fonctionnelle!");
    }, false);
  </script>
  <body>
    <div class="container-fluid">


<?php
if ($template=='bienvenue') :
?>
      <h1>Installateur de MedShakeEHR</h1>
      <div id="inst">
        <p>Nous allons commencer la procédure d'installation. Cela peut prendre plusieurs minutes.<br>
          <strong>Ne fermez pas cette page, et ne la rechargez pas non plus!</strong></p>
        <p>Définissez ci-dessous le dossier où MedShakeEHR doit être installé.<br>
          <strong> - Cet emplacement ne doit pas être accessible via le web</strong><br>
          <strong> - L'utilisateur www-data doit avoir les droits d'écriture sur cet emplacement, ainsi que sur le dossier <code><?=getcwd()?></code>.</p>
        <form	action="<?=$_SERVER['REQUEST_URI']?>" method="post" class="form-inline">
          <input name="bienvenue" type="hidden"/>
          <?php if (isset($_GET['v'])) { ?><input name="v" type="hidden" value="<?=$_GET['v']?>"/> <?php } ?>
          <input id="dest" class="form-control mr-2" name="destination" type="text" value="<?=dirname(getcwd())?>" />
          <button type="submit" class="btn btn-light" onclick="document.querySelector('#inst').style.display='none';document.querySelector('.svgcontainer').className+=' svganim';">Suivant</button>
        </form>
      </div>
      <div class="svgcontainer">
        <svg class="mysvg" id="Capa_1" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 385.34484 385.34484" height="612" width="612" version="1.1" y="0px" x="0px" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/"><g><flowRoot id="flowRoot3033" style="word-spacing:0px;letter-spacing:0px" line-height="125%" xml:space="preserve" font-size="40px" transform="translate(0 62.965)" font-family="Sans" fill="#ffffff"><flowRegion id="flowRegion3035"><rect id="rect3037" height="184.41" width="143.19" y="229.97" x="162.71" fill="#fff"/></flowRegion><flowPara id="flowPara3039"/></flowRoot><path id="path3050" d="m124.97 205.15s-11.284-37.253 10.49-59.026c28.553-25.332 47.126 25.029 59.648 13.768 18.471-9.6488 32.343-31.696 53.192-13.768 34.416 41.678-0.80691 92.11-0.80691 92.11s-7.7238 18.106-40.116 0.26897c-13.505-7.4365 19.388-40.477 4.945-45.87-7.3838-2.7571-12.18 11.011-20.062 11.011-7.8817 0-12.298-11.486-19.682-8.7291-14.443 5.3929 14.705 32.686 3.8038 43.587-9.6456 9.6456-37.043 11.592-40.654-1.8828" fill="#337ab7"/><g id="g3895" fill="#337ab7" transform="translate(31.526 31.438)"><g id="g3891"><path id="path7" d="m66.945 51.904c6.903 0 12.5-5.597 12.5-12.5s-5.597-12.5-12.5-12.5c-36.914 0-66.945 30.031-66.945 66.945 0 6.903 5.596 12.5 12.5 12.5 6.903 0 12.5-5.597 12.5-12.5 0-23.129 18.816-41.945 41.945-41.945z"/><path id="path9" d="m93.675 77.907c0-6.903-5.597-12.5-12.5-12.5-23.529 0-42.672 19.142-42.672 42.671 0 6.903 5.597 12.5 12.5 12.5s12.5-5.597 12.5-12.5c0-9.744 7.928-17.671 17.672-17.671 6.903 0 12.5-5.597 12.5-12.5z"/></g><g id="g3875"><path id="path11" d="m309.88 216.03c-6.903 0-12.5 5.597-12.5 12.5 0 23.129-18.816 41.945-41.945 41.945-6.903 0-12.5 5.597-12.5 12.5s5.597 12.5 12.5 12.5c36.914 0 66.945-30.031 66.945-66.945 0-6.904-5.597-12.5-12.5-12.5z"/><path id="path13" d="m283.88 214.3c0-6.903-5.597-12.5-12.5-12.5s-12.5 5.597-12.5 12.5c0 9.744-7.927 17.671-17.671 17.671-6.903 0-12.5 5.597-12.5 12.5s5.597 12.5 12.5 12.5c23.529 0 42.671-19.141 42.671-42.671z"/></g><g id="g3879" transform="matrix(-.088334 -.99609 .99609 -.088334 50.573 368.48)"><path id="path3881" d="m309.88 216.03c-6.903 0-12.5 5.597-12.5 12.5 0 23.129-18.816 41.945-41.945 41.945-6.903 0-12.5 5.597-12.5 12.5s5.597 12.5 12.5 12.5c36.914 0 66.945-30.031 66.945-66.945 0-6.904-5.597-12.5-12.5-12.5z"/><path id="path3883" d="m283.88 214.3c0-6.903-5.597-12.5-12.5-12.5s-12.5 5.597-12.5 12.5c0 9.744-7.927 17.671-17.671 17.671-6.903 0-12.5 5.597-12.5 12.5s5.597 12.5 12.5 12.5c23.529 0 42.671-19.141 42.671-42.671z"/></g><g id="g3885" transform="matrix(.22191 .97507 -.97507 .22191 237.57 -71.256)"><path id="path3887" d="m309.88 216.03c-6.903 0-12.5 5.597-12.5 12.5 0 23.129-18.816 41.945-41.945 41.945-6.903 0-12.5 5.597-12.5 12.5s5.597 12.5 12.5 12.5c36.914 0 66.945-30.031 66.945-66.945 0-6.904-5.597-12.5-12.5-12.5z"/><path id="path3889" d="m283.88 214.3c0-6.903-5.597-12.5-12.5-12.5s-12.5 5.597-12.5 12.5c0 9.744-7.927 17.671-17.671 17.671-6.903 0-12.5 5.597-12.5 12.5s5.597 12.5 12.5 12.5c23.529 0 42.671-19.141 42.671-42.671z"/></g></g></g></svg>
      </div>
<?php
elseif ($template=='erreur-git') :
?>

      <h1>Erreur!</h1>
      <p style="margin-top:50px;">Un programme est manquant pour l'installation :</p>
      <p><?=$ret?></p>

<?php
elseif ($template=='erreur-inconnue') :
?>
      <h1>Erreur!</h1>
      <p style="margin-top:50px;">Une erreur s'est produite durant l'installation. Voici les messages :</p>
      <p><?=$ret?></p>
<?php
else :
?>
      <h1>Erreur!</h1>
      <p style="margin-top:50px;">Le dossier <?=$dossier?> n'est pas accéssible en écriture. Veuillez corriger le problème puis cliquer sur suivant.</p>
      <a href=<?=$_SERVER['REQUEST_URI']?>><button class="btn btn-light">Suivant</button></a>
<?php
endif;
?>
</div>
  </body>
</html>
<?php
endif;

?>
