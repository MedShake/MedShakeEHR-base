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
 * Inbox mail
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


class msInbox
{

/**
 * Nombre de messages non lus dans la inbox utilisateur
 * @return int nombre de messages non lus
 */
  public static function getInboxUnreadMessages() {
    global $p;
    if(!empty($p['config']['apicryptInboxMailForUserID'])) {
      $apicryptInboxMailForUserID=explode(',', $p['config']['apicryptInboxMailForUserID']);
      $apicryptInboxMailForUserID[]=$p['user']['id'];
      $apicryptInboxMailForUserID=implode("','", $apicryptInboxMailForUserID);
    } else {
      $apicryptInboxMailForUserID=$p['user']['id'];
    }
    return (int) msSQL::sqlUniqueChamp("select count(txtFileName) from inbox where archived='n' and mailForUserID in ('".$apicryptInboxMailForUserID."') ");
  }


/**
 * Parser le nom d'un fichier txt de la inbox pour extraire datetime et num ordre
 * Pas de rapport réel avec HPRIM
 * @param  string $filename Nom du fichier
 * @return array           Array avec datetime=> et numOrdre=>
 */
    public static function getFileDataFromName($filename)
    {
        $data=array();
        $Y=substr($filename, 0, 4);
        $m=substr($filename, 4, 2);
        $d=substr($filename, 6, 2);
        $H=substr($filename, 9, 2);
        $i=substr($filename, 11, 2);
        $s=substr($filename, 13, 2);

        $data['numOrdre']=explode('-', $filename);
        $data['numOrdre']=explode('.',$data['numOrdre'][2]);
        $data['numOrdre']=$data['numOrdre'][0];

        $data['datetime']=$Y.'-'.$m.'-'.$d.' '.$H.':'.$m.':'.$s;

        return $data;
    }

/**
 * Obtenir le contenu d'un fichier txt
 * @param  string $file fichier et chemn complet
 * @return string       txt (utf8)
 */
    public static function getMessageBody($file)
    {
        $content=file_get_contents($file);
        if (!mb_detect_encoding($content, 'utf-8', true)) {
            $content = utf8_encode($content);
        }
        $content=str_replace("\n\n\n", "\n\n", $content);
        $content=preg_replace("#((\n\s*){3,10})+#i", "\n\n", $content);
        return trim($content);
    }
}
