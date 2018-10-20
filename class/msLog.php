<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Utilisation du log apache
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msLog
{
  private $_file;
  private $_userID;
  private $_userIP;
  private $_dateStart;
  private $_dateStartOperator;
  private $_dateEnd;
  private $_dateEndOperator;
  private $_heureStart;
  private $_heureEnd;
  private $_urlPattern;
  private $_nbLignes=2000;
  private $_awk_beforeif='';
  private $_corresIdToIdentite=[];

  function __construct() {
      $this->_getUsersList();
  }

/**
 * Définir le fichier de log
 * @param string $file /chemin/fichier.log
 */
  public function setFile($file) {
    if(!is_file($file)) throw new Exception("File n'est pas un fichier existant");
    return $this->_file = $file;
  }

/**
 * Définir le userID spécifique à rechercher
 * @param int $userID userID
 */
  public function setUserID($userID) {
    if(!is_numeric($userID)) throw new Exception('UserID is not numeric');
    return $this->_userID = $userID;
  }

/**
 * Définir l'IP utilisateur à rechercher
 * @param string $userIP IP
 */
  public function setUserIP($userIP) {
    if(!preg_match("#[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}#",$userIP)) throw new Exception("UserIP n'est pas valide");
    return $this->_userIP = $userIP;
  }

/**
 * Définir le nombre maximal de lignes à retourner
 * @param int $nbLignes nb max de lignes
 */
  public function setNbLignes($nbLignes) {
    if(!is_numeric($nbLignes)) throw new Exception('NbLignes is not numeric');
    return $this->_nbLignes = $nbLignes;
  }

/**
 * Définir la date (de début) de recherche
 * @param string $dateStart date au format yyyy-mm-dd
 */
  public function setDateStart($dateStart) {
    if(!msTools::validateDate($dateStart, 'Y-m-d')) throw new Exception("DateStart n'est pas valide");
    return $this->_dateStart = $dateStart;
  }

/**
 * Définir l'opérateur de comparaison à appliquer à la date de début
 * @param string $dateStartOperator < <= == >= >
 */
  public function setDateStartOperator($dateStartOperator) {
    if(!in_array($dateStartOperator, ['<', '<=', '==', '>=', '>'])) throw new Exception('DateStartOperator is not good');
    return $this->_dateStartOperator = $dateStartOperator;
  }

/**
 * Définir la date de fin
 * @param string $dateEnd date de fin yyyy-mm-dd
 */
  public function setDateEnd($dateEnd) {
    if(!msTools::validateDate($dateEnd, 'Y-m-d')) throw new Exception("DateEnd n'est pas valide");
    return $this->_dateEnd = $dateEnd;
  }

/**
 * Défnir l'heure qui complète la date de début
 * @param string $heureStart heure qui complète la date de début HH:ii:ss
 */
  public function setHeureStart($heureStart) {
    if(!msTools::validateDate($heureStart, 'H:i:s')) throw new Exception("HeureStart n'est pas valide");
    return $this->_heureStart = $heureStart;
  }

/**
 * Définir l'heure qui complète la date de fin
 * @param string $heureEnd heure qui complète la date de fin
 */
  public function setHeureEnd($heureEnd) {
    if(!msTools::validateDate($heureEnd, 'H:i:s')) throw new Exception("HeureEnd n'est pas valide");
    return $this->_heureEnd = $heureEnd;
  }

/**
 * Définir l'opérateur de comparaison à appliquer à la date de fin
 * @param string $dateEndOperator < <= == >= >
 */
  public function setDateEndOperator($dateEndOperator) {
    if(!in_array($dateEndOperator, ['<', '<=', '==', '>=', '>'])) throw new Exception('DateEndOperator is not good');
    return $this->_dateEndOperator = $dateEndOperator;
  }

/**
 * Définir le pattern à appliquer à l'ereg de recherche url
 * @param string $urlPattern expression régulière
 */
  public function setUrlPattern($urlPattern) {
    if(!msTools::isRegularExpression($urlPattern)) throw new Exception("UrlPattern n'est pas valide");
    return $this->_urlPattern = $urlPattern;
  }

/**
 * Obtenir les données extraites du fichier log
 * @return array array de chaque ligne complété avec data extraites secondairement
 */
  public function getDataWithAwk() {
    $conditions[] = $this->_getDateConditions();
    $conditions[] = $this->_getUrlPatternConditions();
    $conditions[] = $this->_getUserIDConditions();
    $conditions[] = $this->_getUserIPConditions();
    $conditions = array_filter($conditions);
    if(!empty($conditions)) {
      $chaineConditions =   $this->_awk_beforeif.'if ('.implode(' && ', $conditions).' )';
    } else {
      $chaineConditions='';
    }
    $out=null;
    $commande = "cat ".$this->_file." | awk '{".$chaineConditions."{print}}'| tail -n".$this->_nbLignes;
    exec($commande , $out);

    if(!empty($out)) {
      foreach($out as $k=>$v) {
        $out[$k]=explode(' ', $v);
        if(key_exists($out[$k][6],$this->_corresIdToIdentite)) {
          $out[$k]['userIdentite']=$this->_corresIdToIdentite[$out[$k][6]];
        } else {
          $out[$k]['userIdentite']='?';
        }
      }
    }

    $data=array(
      'commande'=> $commande,
      'output'=>$out,
    );
    return $data;
  }

/**
 * Obtenir la liste des utilisateur de l'EHR
 * @return void
 */
  private function _getUsersList() {
    if($users=msPeopleSearch::getUsersList()) {
      foreach($users as $v) {
        $this->_corresIdToIdentite[$v['id']]=$v['prenom'].' '.$v['nom'];
      }
    }
  }

/**
 * Obtenir la règle d'extraction awk basée sur le pattern url
 * @return string règle pour l'url
 */
  private function _getUrlPatternConditions() {
    if(isset($this->_urlPattern)) {
      $chaine = '$5 ~ '.$this->_urlPattern;
    } else {
      $chaine = '';
    }
    return $chaine;
  }

/**
 * Obtenir la règle d'axtraction awk basée sur le userID
 * @return string règle userID
 */
  private function _getUserIDConditions() {
    if(isset($this->_userID)) {
      $chaine = '$7 == '.$this->_userID;
    } else {
      $chaine = '';
    }
    return $chaine;
  }

/**
 * Obtenir la règle d'extraction awk basée sur l'IP
 * @return string règle IP
 */
  private function _getUserIPConditions() {
    if(isset($this->_userIP)) {
      $chaine = '$3 == "'.$this->_userIP.'"';
    } else {
      $chaine = '';
    }
    return $chaine;
  }

/**
 * Obtenir la règle d'extraction awk basée sur les dates et heures
 * @return string règle dates et heures
 */
  private function _getDateConditions() {
    $compareStart=' "'.$this->_dateStart.'"';
    if(isset($this->_dateStart, $this->_heureStart)) {
      $this->_awk_beforeif.='start=$1" "$2;';
      $compareStart=' "'.$this->_dateStart.' '.$this->_heureStart.'"';
    } elseif(isset($this->_dateStart)) {
      $this->_awk_beforeif.='start=$1;';
    }

    $compareEnd=' "'.$this->_dateEnd.'"';
    if(isset($this->_dateEnd, $this->_heureEnd)) {
      $this->_awk_beforeif.='end=$1" "$2;';
      $compareEnd=' "'.$this->_dateEnd.' '.$this->_heureEnd.'"';
    } elseif(isset($this->_dateEnd)) {
      $this->_awk_beforeif.='end=$;1';
    }

    if(isset($this->_dateStart, $this->_dateEnd)) {
      $chaine = 'start '.$this->_dateStartOperator.$compareStart;
      $chaine .= ' && end '.$this->_dateEndOperator.$compareEnd;
    } elseif (isset($this->_dateStart)) {
      $chaine = 'start '.$this->_dateStartOperator.$compareStart;
    } else {
      $chaine='';
    }
    return $chaine;
  }


}
