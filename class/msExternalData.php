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
 * Outils de manipulation de data externes
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */
class msExternalData
{

/**
 * Retourne un fichier json externe en array php
 * @param  string $file fichier disant à lire
 * @return array       array php
 */
  public static function jsonFileToPhpArray($file) {
    if(is_file($file)) {
      $data=file_get_contents($file);
      $data=json_decode($data, true);
      return $data;
    } else {
      return null;
    }
  }

/**
 * Sauvegarder un fichier distant localement
 * @param  string $distantFile fichier distant et chemin complet
 * @param  string $localFile   fichier local de destination
 * @return void
 */
  public static function fileSaveLocal($distantFile, $localFile) {
      if($data=file_get_contents($distantFile)) {
          file_put_contents($localFile, $data);
      }
  }

/**
 * Obtenir un tableau à partir du fichier xml d'un JDV
 * @param  string $jdv nom du fichier de JDV
 * @return array      tableau code=>[attr1=>'', ...]
 */
  public static function getJdvDataFromXml($jdv) {
    global $p;
    $file=$p['homepath'].'ressources/JDV/'.$jdv;
    if(!is_file($file)) {
      throw new Exception('Le fichier porteur du jeu de valeurs n\'existe pas');
    }
    $tab=[];
    $document_xml = new DomDocument();
    @$document_xml->load($p['homepath'].'ressources/JDV/'.$jdv);
    $vals = $document_xml->getElementsByTagName('Concept');
    if($vals->length > 0) {
      foreach($vals as $val) {
        foreach($val->attributes as $attr) {
          $tab[$val->getAttribute('code')][$attr->nodeName]=$attr->nodeValue;
        }
      }
    }
    return $tab;
  }

}
