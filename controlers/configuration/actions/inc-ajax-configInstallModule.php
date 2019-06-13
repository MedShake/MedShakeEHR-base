<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00
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
 * Config : installer un module
 *
 * @author fr33z00 <https://github.com/fr33z00
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

$fichier=$_FILES['file'];
$mimetype=msTools::getmimetype($fichier['tmp_name']);
if ($mimetype!='application/zip') {
    die("Erreur: Le fichier n'est pas un fichier zip");
}
if (!is_writable($p['config']['webDirectory'])) {
    die("Erreur: www-data n'a pas les droits d'écriture sur le dossier ".$p['config']['webDirectory']);
}
if (!is_writable($p['homepath'])) {
    die("Erreur: www-data n'a pas les droits d'écriture sur le dossier ".$p['homepath']);
}
$zip = new ZipArchive;
if ($zip->open($fichier['tmp_name'])) {
    if ($zip->getFromName(".MedShakeEHR")!==false) {
        $dataMaintenance = array(
          'name'=>'state',
          'value'=>'maintenance',
          'groupe'=>'system'
        );
        msSQL::sqlInsert('system', $dataMaintenance);
        if ($zip->extractTo($p['homepath'])) {
            $zip->close();
            if ($p['config']['webDirectory']!=$p['homepath'].'public_html/') {
                exec('cp -r '.$p['homepath'].'public_html/* '.$p['config']['webDirectory']);
                exec('rm -rf '.$p['homepath'].'public_html/');
            }
            @unlink($p['homepath'].'public_html/install.php');
            die("ok");
        }
        $zip->close();
        die("Erreur: une erreur est survenue durant la décompression du fichier");
    }
    $zip->close();
    die("Erreur: Le fichier n'est pas un fichier MedShakeEHR");
}
die("Erreur: le fichier n'a pas pu être ouvert pour une raison inconnue");
