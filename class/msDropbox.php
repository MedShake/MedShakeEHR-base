<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2020
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
 * Dropbox
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msDropbox
{
  private $_allBoxesParameters=[];
  private $_currentBoxId;
  private $_currentFilename;
  private $_currentFilenameData;
  private $_boxesContents=[];
  private $_totalFilesInBoxes;

/**
 * Définir la boite courante
 * @param string $currentBoxId ID de la boite
 */
  public function setCurrentBoxId($currentBoxId) {
    if(empty($this->_allBoxesParameters)) {
      $this->getAllBoxesParametersCurrentUser();
    }
    if(array_key_exists($currentBoxId, $this->_allBoxesParameters)) {
      $this->_currentBoxId=$currentBoxId;
    } else {
      throw new Exception("Cette boite n'existe pas");
    }
  }

/**
 * Définir le fichier courant
 * @param string $filename nom complet du fichier (sans chemin)
 */
  public function setCurrentFilename($filename) {
    $this->_currentFilename=$filename;
  }

/**
 * Obtenir le nombre total de doc dans toutes les boites de l'utilisteur courant
 * @return int nombre total
 */
  public function getTotalFilesInBoxes() {
    if(empty($this->_allBoxesParameters)) {
      $this->getAllBoxesParametersCurrentUser();
    }
    if(empty($this->_boxesContents)) {
      $this->getAllAllowedFilesInBoxes();
    }
    return (int) $this->_totalFilesInBoxes;
  }

/**
 * Obtenir les paramétrages de toutes les boites
 * @return array tableau des paramètres
 */
  public function getAllBoxesParametersCurrentUser() {
    global $p;
    return $this->_allBoxesParameters = Spyc::YAMLLoad($p['config']['dropboxOptions']);
  }

/**
 * Obtenir toutes les datas sur tous les fichiers de toutes les box
 * @return array data sur tous les docs, par box
 */
  public function getAllAllowedFilesInBoxes() {
    if(empty($this->_allBoxesParameters)) {
      $this->getAllBoxesParametersCurrentUser();
    }

    $this->_totalFilesInBoxes = 0;

    foreach($this->_allBoxesParameters as $box=>$params) {

      $boxParameters = $this->_allBoxesParameters[$box];

      if(is_dir($boxParameters['path'])) {
        foreach(new DirectoryIterator($boxParameters['path']) as $item) {
           if (!$item->isDot() and $item->isFile() and in_array($item->getExtension(), $boxParameters['filesAllowedTypes']) ) {
             $this->_totalFilesInBoxes++;
             $this->_boxesContents[$box][$item->getFilename()] = array(
               'filename'=>$item->getFilename(),
               'date'=>$item->getATime(),
               'size'=>msTools::readabledSize($item->getSize(), 1),
               'ext'=>$item->getExtension(),
             );
           }
        }
      }
    }
    if(isset($this->_boxesContents)) {
      return $this->_boxesContents;
    } else {
      return [];
    }
  }

/**
 * Vérifier si un fichier est bien dans la box courante
 * @param  string $filename nom complet du fichier
 * @return bool          true/false
 */
  public function checkFileIsInCurrentBox($filename) {
    if(!isset($this->_currentBoxId)) {
      throw new Exception("Current box is not defined");
    }
    return is_file($this->_allBoxesParameters[$this->_currentBoxId]['path'].$filename);
  }

/**
 * Obtenir les infos sur le fichier courant
 * @return array array d'infos
 */
  public function getCurrentFileData() {
    global $p;
    if(empty($this->_allBoxesParameters)) {
      $this->getAllBoxesParametersCurrentUser();
    }
    $filepath = $this->_allBoxesParameters[$this->_currentBoxId]['path'].$this->_currentFilename;
    if(is_file($filepath)) {
      $fileinfos =  array(
        'fullpath'=>$filepath,
        'webpath'=>'dropbox/'.$this->_currentBoxId.'/'.$this->_currentFilename.'/',
        'ext'=>pathinfo($filepath,  PATHINFO_EXTENSION),
        'mimetype'=>msTools::getmimetype($filepath),
      );
      $objetPreview = new msModBaseObjetPreview;
      $objPre = $objetPreview->getFilePreviewParams($fileinfos['mimetype'], $fileinfos['fullpath']);
      $fileinfos = array_merge($fileinfos, $objPre);
      return $fileinfos;
    } else {
      return [];
    }

  }

/**
 * Obtenir les patients possibles à pour le fichier courant
 * @return array array info admin des patients
 */
  public function getPossiblePatients()
  {
    $data = $this->getDataFromFilename();
    $ps = new msPeopleSearch;
    $ps->setCriteresRecherche($data);
    $ps->setColonnesRetour(array_merge(array_keys($data) , ['streetNumber', 'street', 'postalCodePerso', 'city', 'birthname', 'lastname']));
    $ps->setLimitStart=0;
    $ps->setLimitNumber=5;
    $ps->setPeopleType(['patient', 'pro']);
    return $ps->getSimpleSearchPeople();
  }

/**
 * Obtenir les informations contenu dans le nom du fichier patient
 * @return array infos
 */
  public function getDataFromFilename() {
    if(isset($this->_currentFilenameData)) return $this->_currentFilenameData;

    if(!isset($this->_currentFilename)) {
      throw new Exception("Current filename is not defined");
    }
    $boxParams=$this->_getCurrentBoxParams();
    if(msTools::isRegularExpression($boxParams['filesNameEreg'])) {
      preg_match($boxParams['filesNameEreg'], $this->_currentFilename, $m);
      $data = (array)$boxParams['filesNameEregMatches'];
      $mrep = msTools::getPrefixKeyArray($m, '$');
      foreach($data as $k=>$v) {
        $data[$k]=strtr($v, $mrep);
        if(strpos($data[$k],'$') !== false) $data[$k]='';
      }
      return $this->_currentFilenameData = $data;
    } else {
      return $this->_currentFilenameData = [];
    }
  }

/**
 * Obtenir les paramètres de la boite courante
 * @return array paramètres de la box
 */
  private function _getCurrentBoxParams() {
    return $this->_allBoxesParameters[$this->_currentBoxId];
  }

}
