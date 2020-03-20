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
 * Inbox > action : retirer de la inbox le message sans l'incorporer à un dossier
 * (mais archiver tout de même)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if(!is_numeric($match['params']['mailID'])) die;

//sortir data hprim
if ($data=msSQL::sqlUnique("select txtFileName, hprimAllSerialize, pjSerializeName  from inbox where id='".$match['params']['mailID']."' ")) {
    $pj['pjSerializeName']=unserialize($data['pjSerializeName']);
    $sourceFolder = str_replace('.txt', '.f', $data['txtFileName']);

    if (count($pj['pjSerializeName'])>0) {
        foreach ($pj['pjSerializeName'] as $file) {

            //source
            $source=$p['config']['apicryptCheminInbox'].$sourceFolder.'/'.$file;

            if (is_file($source)) {

                //extension
                $mimetype=msTools::getmimetype($source);
                if ($mimetype=='application/pdf') {
                    $ext='pdf';
                } elseif ($mimetype=='text/plain') {
                    $ext='txt';
                }


                ////////////////////////////
                // stockage archives
                $finaldir=$p['config']['apicryptCheminArchivesInbox'].$p['user']['id'].'/'.date('Y').'/'.date('m').'/'.date('d').'/'.$sourceFolder.'/';
                msTools::checkAndBuildTargetDir($finaldir);

                copy($source, $finaldir.$file);

                unlink($source);
            }
        }

        rmdir($p['config']['apicryptCheminInbox'].$sourceFolder.'/');
    }

    ////////////////////////////
    // stockage archives du txt
    $ddir=$p['config']['apicryptCheminArchivesInbox'].$p['user']['id'].'/'.date('Y').'/'.date('m').'/'.date('d').'/';
    msTools::checkAndBuildTargetDir($ddir);

    copy($p['config']['apicryptCheminInbox'].$data['txtFileName'], $ddir.$data['txtFileName']);

    unlink($p['config']['apicryptCheminInbox'].$data['txtFileName']);
    msSQL::sqlQuery("update inbox set archived='y' where id='".$match['params']['mailID']."' limit 1");
}

msTools::redirection('/inbox/');
