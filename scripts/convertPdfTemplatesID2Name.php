<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
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
 * Conversion des templates d'impression : remplace tag.IdNumérique en tag.NomDeVariable
 * PARAMETRAGE DU DIRECTORY INDISPENSABE => $directory ci-dessous 
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */


ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");
session_start();

$homepath=getenv("MEDSHAKEEHRPATH");
$homepath.=$homepath[strlen($homepath)-1]=='/'?'':'/';

/////////// Composer class auto-upload
require $homepath.'vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
     include $homepath.'class/' . $class . '.php';
});


/////////// Config loader
require $homepath.'config/config.php';

/////////// SQL connexion
$mysqli=msSQL::sqlConnect();


$directory = $p['config']['templatesPdfFolder'];

$scanned_directory = array_diff(scandir($directory), array('..', '.'));

$masque='# tag\.(val|pct)*([0-9]+)#i';

foreach ($scanned_directory as $file) {
    $file=$directory.$file;
    $contenu=file_get_contents($file);

    if (preg_match_all($masque, $contenu, $m)) {
        echo $file."\n";
        print_r($m[2]);

        foreach ($m[2] as $k=>$v) {
            $name=new msData();
            if ($n=$name->getDataType($v, ['name'])) {

              $contenu=str_replace(" tag.".$v." ", ' tag.'.$n['name'].' ', $contenu, $c1);
              $contenu=str_replace(" tag.val".$v." ", ' tag.val_'.$n['name'].' ', $contenu, $c2);
              $contenu=str_replace(" tag.pct".$v." ", ' tag.pct_'.$n['name'].' ', $contenu, $c3);
              $contenu=str_replace(" tag.".$v."|", ' tag.'.$n['name'].'|', $contenu, $c1);
              $contenu=str_replace(" tag.val".$v."|", ' tag.val_'.$n['name'].'|', $contenu, $c2);
              $contenu=str_replace(" tag.pct".$v."|", ' tag.pct_'.$n['name'].'|', $contenu, $c3);

            }
            unset($n);
        }
    }

    file_put_contents($file, $contenu);
}
