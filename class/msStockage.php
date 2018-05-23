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
 * Fonctions de stockage des documents reçus (upload, inbox)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

class msStockage
{

    /**
     * @var int ID de l'objet
     */
    private $_objetID;
    /**
     * @var string extension du fichier (pdf, txt)
     */
    private $_fileExt;
    /**
     * @var string chemin vers le fichier
     */
    private $_pathToDoc;

/**
 * Définir l'ID de l'objet
 * @param [type] $v [description]
 */
    public function setObjetID($v)
    {
        if (is_numeric($v)) {
            return $this->_objetID = $v;
        } else {
            throw new Exception('ObjetID is not numeric');
        }
    }

/**
 * Définir un chemin de sous-dossier de stockage à partir de l'ID objet
 * @param  int $objetID ID de l'objet
 * @return string          chemin de sous dossiers (numérique)
 */
    public static function getFolder($objetID)
    {
        $chaine = strval($objetID);
        if($objetID<10) {
          $first=$chaine{0}.'0';
        } else {
          $first=$chaine{0}.$chaine{1};
        }

        return $first.'/'.floor($objetID/1000);
    }

/**
 * Obtenir le chemin absolu du fichier sur le serveur (fichier inclus) à partir de l'ID objet
 * @return string  Chemin absolu + nom de fichier
 */
    public function getPathToDoc()
    {
        if (!isset($this->_objetID)) {
            throw new Exception('ObjetID is not numeric');
        }

        if(isset($this->_pathToDoc)) return $this->_pathToDoc;

        global $p;
        $ext=$this->getFileExtOfDoc($this->_objetID);
        $folder=msStockage::getFolder($this->_objetID);
        $this->_pathToDoc = $p['config']['stockageLocation'].$folder.'/'.$this->_objetID.'.'.$ext;

        return $this->_pathToDoc;
    }

/**
 * Obtenir le chemin relatif du fichier sur le serveur (fichier inclus) à partir de l'ID objet
 * @return string  Chemin relatif + nom de fichier
 */
    public function getWebPathToDoc()
    {
        if (!isset($this->_objetID)) {
            throw new Exception('ObjetID is not numeric');
        }
        return 'fichier/'.$this->_objetID.'/';
    }

/**
 * Obtenir l'extension d'un fichier (pdf ou txt) à partir de l'ID objet
 * @return string  Chemin relatif + nom de fichier
 */
    public function getFileExtOfDoc()
    {
        if (!isset($this->_objetID)) {
            throw new Exception('ObjetID is not numeric');
        }

        if(isset($this->_fileExt)) return $this->_fileExt;

        $docTypeID = msData::getTypeIDFromName('docType');

        if ($ext=msSQL::sqlUniqueChamp("select value from objets_data where instance='".$this->_objetID."' and typeID='".$docTypeID."' limit 1")) {
            $this->_fileExt=$ext;
            return $ext;
        } else {
            return 'pdf';
        }
    }

/**
 * Obtenir la taille d'un fichier
 * @param  int $decimals nombre de décimales souhaitées
 * @return string           taille du fichier
 */
    public function getFileSize($decimals = 2) {
      $bytes=filesize($this->getPathToDoc());
      $sz = ['o', 'Ko', 'Mo', 'Go', 'To', 'Po'];
      $factor = floor((strlen($bytes) - 1) / 3);
      return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }

/**
 * test si un doc existe à partir de objetID
 * @return bool true or false
 */
    public function testDocExist()
    {
        if (!isset($this->_objetID)) {
            throw new Exception('ObjetID is not numeric');
        }
        if (is_file($this->getPathToDoc())) {
            return true;
        } else {
            return false;
        }
    }

/**
 * Supprime le pdf correspondant à l'objet
 * @return void
 */
    public function deleteDoc()
    {
        if (!isset($this->_objetID)) {
            throw new Exception('ObjetID is not numeric');
        }
        if(is_file($this->getPathToDoc())) {
          unlink($this->getPathToDoc());
        }
    }
}
