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
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

// pour le configurateur de cron
if (isset($p)) {
    $p['page']['availableCrons']['inbox']=array(
        'task' => 'Apicrypt',
        'defaults' => array('m'=>'0,5,10,15,20,25,30,35,40,45,50,55','h'=>'8-20','M'=>'*','dom'=>'*','dow'=>'1,2,3,4,5,6'),
        'description' => 'Relève des mails Apicrypt');
    return;
}


ini_set('display_errors', 1);
setlocale(LC_ALL, "fr_FR.UTF-8");
session_start();

$homepath=getcwd().'/';

/////////// Composer class auto-upload
require $homepath.'vendor/autoload.php';

/////////// Class medshakeEHR auto-upload
spl_autoload_register(function ($class) {
    global $homepath;
    include $homepath.'class/' . $class . '.php';
});

/////////// Config loader
$p['configDefault']=$p['config']=yaml_parse_file($homepath.'config/config.yml');
$p['homepath']=$homepath;


/////////// SQL connexion
$mysqli=msSQL::sqlConnect();


$users=msPeople::getUsersWithSpecificParam('apicryptAdresse');


foreach ($users as $userID=>$val) {

    /////////// config pour l'utilisateur concerné
    $p['config']=array_merge($p['configDefault'], msConfiguration::getAllParametersForUser(['id'=>$userID]));

    /////////// Relever le compte pop3
    $pop = new msPop3();
    if ($connection = $pop->pop3_login($p['config']['apicryptPopHost'], $p['config']['apicryptPopPort'], $p['config']['apicryptPopUser'], $p['config']['apicryptPopPassword'], 'INBOX', false)) {
        $liste=$pop->pop3_list($connection);

        if (count($liste)>0) {
            foreach ($liste as $msgID=>$msgV) {
                $message=$pop->mail_mime_to_array($connection, $msgID, false);

                if (is_array($message)) {
                    $filename=date("Ymd-His", $msgV['udate']).'-'.$msgV['uid'];
                    $dirname=date("Ymd-His", $msgV['udate']).'-'.$msgV['uid'].'.f';

                    foreach ($message as $kpart=>$part) {
                        if ($kpart > 0 and isset($part['is_attachment'])) {
                            if (strlen($part['data'])>10) {
                                msTools::checkAndBuildTargetDir($p['config']['apicryptCheminInbox'].$dirname);
                                $filec=$p['config']['apicryptCheminInbox'].$dirname.'/'.msTools::sanitizeFilename($part['filename']);
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

                    // sauvegarder en base
                    $hprim = msHprim::getHprimHeaderData($p['config']['apicryptCheminInbox'].$filename.'.txt');
                    $hprim=msTools::utf8_converter($hprim);

                    // pj
                    $pj=[];
                    $dir=$p['config']['apicryptCheminInbox'].$dirname;
                    if (is_dir($dir)) {
                        msTools::sanitizeDirectoryFiles($dir.'/');
                        $pj = array_diff(scandir($dir), array('..', '.'));
                    }


                    $data=array(
                      'txtFileName'=>$filename.'.txt',
                      'mailForUserID'=>$userID,
                      'mailHeaderInfos'=>serialize(msTools::utf8_converter($msgV)),
                      'txtDatetime'=>date("Y-m-d H:i:s", $msgV['udate']),
                      'txtNumOrdre'=>$msgV['uid'],
                      'hprimIdentite'=>$hprim['prenom'].' '.$hprim['nom'],
                      'hprimExpediteur'=>$hprim['expediteur'],
                      'hprimCodePatient'=>$hprim['codePatient'],
                      'hprimDateDossier'=>$hprim['dateDossier'],
                      'hprimAllSerialize'=>serialize($hprim),
                      'pjNombre'=>count($pj),
                      'pjSerializeName'=>serialize($pj)
                    );

                    msSQL::sqlInsert('inbox', $data);

                    // supprimer le message
                    $pop->pop3_dele($connection, $msgID);
                }
            }
            // supprimer les messages
            $pop->pop3_expunge($connection);
        }
    }
}
