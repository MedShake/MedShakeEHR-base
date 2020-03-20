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
 * Inbox > action : incorporer au dossier choisi le message comme nouveu document
 * et archiver original dans le dossier prévu en config.
 * On redirige vers dossier patient.
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if(!is_numeric($_POST['patientID'])) die;
if(!is_numeric($_POST['mailID'])) die;

//sortir data hprim
if ($data=msSQL::sqlUnique("select txtFileName,  pjSerializeName, hprimExpediteur  from inbox where id='".msSQL::cleanVar($_POST['mailID'])."' ")) {
    $pj['pjSerializeName']=unserialize($data['pjSerializeName']);

    $corps=msInbox::getMessageBody($p['config']['apicryptCheminInbox'].'/'.$data['txtFileName']);
    if (!mb_detect_encoding($corps, 'utf-8', true)) {
        $corps = utf8_encode($corps);
    }
    $sourceFolder = str_replace('.txt', '.f', $data['txtFileName']);

    if (count($pj['pjSerializeName'])>0) {
        foreach ($pj['pjSerializeName'] as $file) {
            $patient = new msObjet();
            $patient->setFromID($p['user']['id']);
            $patient->setToID($_POST['patientID']);

            //source
            $source=$p['config']['apicryptCheminInbox'].$sourceFolder.'/'.$file;

            if (is_file($source)) {

                //support
                $supportID=$patient->createNewObjetByTypeName('docPorteur', $corps);

                //ajout d'un titre
                if (!empty($_POST['titre'])) {
                    msObjet::setTitleObjet($supportID, $_POST['titre']);
                } elseif (isset($data['hprimExpediteur'])) {
                    msObjet::setTitleObjet($supportID, $data['hprimExpediteur']);
                }

                //extension
                $mimetype=msTools::getmimetype($source);
                if ($mimetype=='application/pdf') {
                    $ext='pdf';
                } elseif ($mimetype=='text/plain') {
                    $ext='txt';
                } elseif ($mimetype=='image/jpeg') {
                    $ext='jpg';
                } else {
                    $ext= pathinfo($source,PATHINFO_EXTENSION);
                }

                //nom original
                $patient->createNewObjetByTypeName('docOriginalName', $file, $supportID);
                //type
                $patient->createNewObjetByTypeName('docType', $ext, $supportID);

                ////////////////////////////
                // stockage actif
                //folder
                $folder=msStockage::getFolder($supportID);

                //creation folder si besoin
                msTools::checkAndBuildTargetDir($p['config']['stockageLocation']. $folder.'/');

                $destination = $p['config']['stockageLocation']. $folder.'/'.$supportID.'.'.$ext;
                if($ext=='txt') {
                  msTools::convertPlainTextFileToUtf8($source, $destination);
                } else {
                  copy($source, $destination);
                }

                ////////////////////////////
                // stockage archives
                $finaldir=$p['config']['apicryptCheminArchivesInbox'].$p['user']['id'].'/'.date('Y').'/'.date('m').'/'.date('d').'/'.$sourceFolder.'/';
                msTools::checkAndBuildTargetDir($finaldir);
                copy($source, $finaldir.$file);

                //unlink($source);
            }
        }

        //rmdir($p['config']['apicryptCheminInbox'].$sourceFolder.'/');
    } else {

      //source
      $source=$p['config']['apicryptCheminInbox'].$data['txtFileName'];

        if (is_file($source)) {
            $patient = new msObjet();
            $patient->setFromID($p['user']['id']);
            $patient->setToID($_POST['patientID']);

            //support
            $supportID=$patient->createNewObjetByTypeName('docPorteur', $corps);

            //ajout d'un titre
            if (!empty($_POST['titre'])) {
                msObjet::setTitleObjet($supportID, $_POST['titre']);
            } elseif (isset($data['hprimExpediteur'])) {
                msObjet::setTitleObjet($supportID, $data['hprimExpediteur']);
            }

            //nom original
            $patient->createNewObjetByTypeName('docOriginalName', $data['txtFileName'], $supportID);

            //extension
            $mimetype=msTools::getmimetype($source);
            if ($mimetype=='application/pdf') {
                $ext='pdf';
            } elseif ($mimetype=='text/plain') {
                $ext='txt';
            } else {
                $ext='txt';
            }

            //type
            $patient->createNewObjetByTypeName('docType', $ext, $supportID);

            ////////////////////////////
            // stockage actif
            //folder
            $folder=msStockage::getFolder($supportID);
            //creation folder si besoin
            msTools::checkAndBuildTargetDir($p['config']['stockageLocation']. $folder.'/');

            $destination = $p['config']['stockageLocation']. $folder.'/'.$supportID.'.'.$ext;
            if($ext=='txt') {
              msTools::convertPlainTextFileToUtf8($source, $destination);
            } else {
              copy($source, $destination);
            }
        }
    }

    ////////////////////////////
    // stockage des résultats HPRIM
    if ($bio=msHprim::parseSourceHprim($corps)) {
        $hprimHeader=msHprim::getHprimHeaderData($p['config']['apicryptCheminInbox'].$data['txtFileName']);
        $hprimHeader['dateDossier']= trim($hprimHeader['dateDossier']);
        if (strlen($hprimHeader['dateDossier']) == 10) {
            if($date = DateTime::createFromFormat('d/m/Y', $hprimHeader['dateDossier'])) {
              $date = $date->format('Y-m-d');
            } else {
              $date=date("Y-m-d");
            }
        } else {
            $date=date("Y-m-d");
        }
        msHprim::saveHprim2bdd($bio, $p['user']['id'], $_POST['patientID'], $date, $supportID);
    }


    ////////////////////////////
    // stockage archives du txt
    $ddir=$p['config']['apicryptCheminArchivesInbox'].$p['user']['id'].'/'.date('Y').'/'.date('m').'/'.date('d').'/';
    msTools::checkAndBuildTargetDir($ddir);
    copy($p['config']['apicryptCheminInbox'].$data['txtFileName'], $ddir.$data['txtFileName']);


    //unlink($p['config']['apicryptCheminInbox'].$data['txtFileName']);
    msSQL::sqlQuery("update inbox set archived='c', assoToID='".$_POST['patientID']."' where id='".$_POST['mailID']."' limit 1");
}

msTools::redirection('/patient/'.$_POST['patientID'].'/');
