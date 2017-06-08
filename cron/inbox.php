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
 * Cron : relève pop3 de la boite apicrypt (ou autre !)
 * A mettre en cron avec la fréquence voulue.
 * 1) relève la boite
 * 2) insère le résultat en base
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");
session_start();

/////////// Composer class auto-upload
require '../vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
    include '../class/' . $class . '.php';
});


/////////// Config loader
$p['config']=Spyc::YAMLLoad('../config/config.yml');

/////////// SQL connexion
$mysqli=msSQL::sqlConnect();

/////////// Relever le compte pop3

$pop = new msPop3();
if ($connection = $pop->pop3_login($p['config']['apicryptPopHost'], $p['config']['apicryptPopPort'], $p['config']['apicryptPopUser'], $p['config']['apicryptPopPass'], 'INBOX', false)) {
    $liste=$pop->pop3_list($connection);

    if (count($liste)>0) {
        foreach ($liste as $msgID=>$msgV) {
            $message=$pop->mail_mime_to_array($connection, $msgID, false);

            if (is_array($message)) {
                foreach ($message as $kpart=>$part) {
                    $filename=date("Ymd-His", $msgV['udate']).'-'.$msgV['uid'];
                    $dirname=date("Ymd-His", $msgV['udate']).'-'.$msgV['uid'].'.f';

                    if ($kpart > 0 and isset($part['is_attachment'])) {
                        if (strlen($part['data'])>10) {
                            msTools::checkAndBuildTargetDir($p['config']['apicryptCheminInbox'].$dirname);
                            $filec=$p['config']['apicryptCheminInbox'].$dirname.'/'.$part['filename'];
                            $filenc=$p['config']['apicryptCheminInbox'].$dirname.'/';
                            file_put_contents($filec, $part['data']);
                            msApicrypt::decrypterPJ($filec, $filenc);
                            unlink($filec);
                        }
                    } elseif ($kpart > 0) {
                        if (strlen($part['data'])>10) {
                            $filec=$p['config']['apicryptCheminInbox'].$filename;
                            $filenc=$p['config']['apicryptCheminInbox'].$filename.'.txt';

                            file_put_contents($filec, $part['data']);
                            msApicrypt::decrypterCorps($filec, $filenc);

                            if (is_file($filenc)) {
                                unlink($filec);
                            } else {
                                rename($filec, $filenc);
                            }
                        }
                    }
                }
                // supprimer le message
                $pop->pop3_dele($connection, $msgID);
            }
        }
        // supprimer les messages
        $pop->pop3_expunge($connection);
    }
}


/////////// Rentrer en base
$scanned_directory = array_diff(scandir($p['config']['apicryptCheminInbox']), array('..', '.'));

//print_r($scanned_directory);

foreach ($scanned_directory as $file) {



  //si c'est un txt
  if (substr($file, -4) == '.txt') {
    $hprim = msHprim::getHprimHeaderData($p['config']['apicryptCheminInbox'].'/'.$file);

    $hprim=msTools::utf8_converter($hprim);

    $filedata = msInbox::getFileDataFromName($file);

    //pj
    $dir=$p['config']['apicryptCheminInbox'].'/'.str_ireplace('.txt', '.f', $file);
    if (is_dir($dir)) {
      $pj = array_diff(scandir($dir), array('..', '.'));
    } else {
      $pj=null;
    }


    $data=array(
    'txtFileName'=>$file,
    'txtDatetime'=>$filedata['datetime'],
    'txtNumOrdre'=>$filedata['numOrdre'],
    'hprimIdentite'=>$hprim['prenom'].' '.$hprim['nom'],
    'hprimExpediteur'=>$hprim['expediteur'],
    'hprimCodePatient'=>$hprim['codePatient'],
    'hprimDateDossier'=>$hprim['dateDossier'],
    'hprimAllSerialize'=>serialize($hprim),
    'pjNombre'=>count($pj),
    'pjSerializeName'=>serialize($pj)
    );

    msSQL::sqlInsert('inbox', $data);
    echo mysqli_error($mysqli);
  }
}
