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
 */

ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");


$template='';

if ($_SERVER['REQUEST_METHOD']=='GET') {
    $template='bienvenue';
} elseif ($_SERVER['REQUEST_METHOD']=='POST') {
     $dossierweb=getcwd();
    if (!is_writable($dossierweb)) {
      $template='erreur-droits';
      $dossier=$dossierweb;
    } else {
        $dossier=$_POST['destination'];
        if (!is_dir($_POST['destination'])) {
            if (mkdir($_POST['destination'], 0770, true)===false) {
                $template='erreur-droits';
            }
        } elseif (!is_writable($_POST['destination'])) {
            $template='erreur-droits';
        } else {
            $dossier.=(strlen($dossier)-1)!='/' ? '/' : '';
            //récupération de la dernière version release
            $ch = curl_init("https://api.github.com/repos/medshake/MedShakeEHR-base/releases/latest");
            curl_setopt($ch, CURLOPT_USERAGENT, "linux");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res=json_decode(curl_exec($ch), true);
            curl_close($ch);
            //téléchargement de la dernière release
            file_put_contents("/tmp/medshake.zip", fopen('https://github.com/medshake/MedShakeEHR-base/archive/'.$res['tag_name'].'.zip', 'r'));
            $zip = new ZipArchive;
            if ($zip->open("/tmp/medshake.zip"))  {
                $zip->extractTo('/tmp/');     
                unlink("/tmp/medshake.zip");
                //deplacement du contenu de public_html
                foreach (scandir('/tmp/MedShakeEHR-base-'.$res['tag_name'].'/public_html') as $f) {
                    if ($f !='.' and $f !='..') {
                        rename('/tmp/MedShakeEHR-base-'.$res['tag_name'].'/public_html/'.$f, $dossierweb.'/'.$f);
                    }
                }
                rmdir('/tmp/MedShakeEHR-base-'.$res['tag_name'].'/public_html');
                //deplacement du reste vers la destination
                $dossier.='MedShakeEHR';
                rename('/tmp/'.'MedShakeEHR-base-'.$res['tag_name'], $dossier);
                chdir($dossier);
                //telechargement de composer
                file_put_contents("composer.phar", fopen("https://getcomposer.org/download/1.6.3/composer.phar", 'r'));
                chmod("composer.phar", 0776);
                exec('COMPOSER_HOME="/tmp/" php composer.phar install 2>&1', $ret);
                json_encode($ret);
                //exécution de composer pour la partie JS
                chdir($dossierweb);
                exec('COMPOSER_HOME="/tmp/" php '.$dossier.'/composer.phar install 2>&1', $ret);
                if(strpos(strtolower($ret), 'error')===false) {
                    unlink($dossierweb.'/self-installer.php');
                    //lancement de la partie configuration
                    header('Location: '.str_replace('base-', '',$_SERVER['REQUEST_URI']));
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

    <title>
      MedShakeEHR : Pre-installation</title>
    <meta name="Description" content=""/>
  </head>
  <style>
    .btn {color:#fff;background-color:#286090;border-color:#204d74;padding:6px 12px;cursor:pointer}
  </style>
  <body>

<?php
if ($template=='bienvenue') :
?>
      <h1>Installateur de MedShakeEHR</h1>
      <p style="margin-top:50px">Nous allons commencer la procédure d'installation. Celà peut prendre plusieurs minutes. <strong>Ne fermez pas cette page, et ne la rechargez pas non plus!</strong></p>
      <p>Définissez ci dessous le dossier parent où MedShakeEHR doit être installé.<br>
        <strong> - Cet emplacement ne doit pas être accessible au réseau (ex:/home/user)</strong><br>
        <strong> - L'utilisateur www-data doit avoir les droits d'écriture sur cet emplacement.</strong></p>
      <form	action="<?=$_SERVER['REQUEST_URI']?>" method="post" style="margin-top:50px;">
        <input name="bienvenue" type="hidden"/>
        <input name="destination" type="text" style="border:solid 1px #ccc"/>
        <button type="submit" class="btn" onclick="this.style.visibility='hidden'">Suivant</button>
        </div>
      </form>
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
      <a href=<?=$_SERVER['REQUEST_URI']?>><button class="btn">Suivant</button></a>
<?php
endif;
?>
    </div>
  </body>
</html>
<?php
endif;

?>
