<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * Règlement : Manipulation des actes de base
 *
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msReglementActe extends msReglement
{

  private $_acteCode;
  private $_acteType;
  private $_acteActivite=1;
  private $_actePhase=0;

/**
 * Définir le code de l'acte
 * @param string $acteCode code de l'acte
 */
  public function setActeCode($acteCode) {
    if(!is_string($acteCode)) throw new Exception('ActeCode is not string');
    $this->_acteCode=$acteCode;
    $this->getActeType();
    return $this->_acteCode;
  }

/**
 * Définir le code activité (acte CCAM)
 * @param int $acteActivite code activité
 */
  public function setActeActivite($acteActivite) {
    if(!is_numeric($acteActivite)) throw new Exception('ActeActivite is not numeric');
    return $this->_acteActivite=$acteActivite;
  }

/**
 * Définir le code phase (acte CCAM)
 * @param int $actePhase code phase
 */
  public function setActePhase($actePhase) {
    if(!is_numeric($actePhase)) throw new Exception('ActePhase is not numeric');
    return $this->_actePhase=$actePhase;
  }

/**
 * Déterminer le type de l'acte
 * @return string type de l'acte
 */
  public function getActeType() {
    if(isset($this->_acteType)) return $this->_acteType;
    if(preg_match('#[A-Z]{4}[0-9]{3}#i',$this->_acteCode)) {
      return $this->_acteType='CCAM';
    } elseif($type = msSQL::sqlUniqueChamp("select `type` from `actes_base` where `code`='".msSQL::cleanVar($this->_acteCode)."' and `type` ='NGAP' limit 1 ")) {
      return $this->_acteType=$type;
    } else {
      return $this->_acteType='Libre';
    }
  }

/**
 * Obtenir le tarif de l'acte dans son contexte
 * @return float tarif de l'acte
 */
  public function getActeTarifBase() {

    if (!isset($this->_acteCode)) {
        throw new Exception('ActeCode is not set');
    }
    if (!isset($this->_acteType)) {
        throw new Exception('ActeType is not set');
    }
    if (!isset($this->_secteurTarifaire)) {
        throw new Exception('SecteurTarifaire is not set');
    }
    if (!isset($this->_secteurTarifaireGeo)) {
        throw new Exception('SecteurTarifaireGeo is not set');
    }
    if (!isset($this->_secteurTarifaireNgap)) {
        throw new Exception('SecteurTarifaireNgap is not set');
    }

    if($this->_acteType=='CCAM' and !empty($this->_secteurTarifaire)) {
      if($d = $this->_getActeCcamData(['dataYaml'])) {
        // application coeff majoration DOM
        if(isset($d['dataYaml']['majorationsDom'][$this->_secteurTarifaireGeo])) {
          $tarif =  round(($d['dataYaml']['tarifParGrilleTarifaire']['CodeGrilleT'.$this->_secteurTarifaire]*$d['dataYaml']['majorationsDom'][$this->_secteurTarifaireGeo]),2);
        } else {
          $tarif =  $d['dataYaml']['tarifParGrilleTarifaire']['CodeGrilleT'.$this->_secteurTarifaire];
        }
      }

    } elseif($this->_acteType=='mCCAM' and !empty($this->_secteurTarifaire)) {
      if($d = $this->_getActeModifCcamData(['dataYaml'])) {
        $tarif =  $d['dataYaml']['tarifParGrilleTarifaire']['CodeGrilleT'.$this->_secteurTarifaire];
      }

    } elseif($this->_acteType=='NGAP' and !empty($this->_secteurTarifaireNgap)) {
      if($d = $this->_getActeNgapData(['dataYaml'])) {
        $tarif =  $d['dataYaml']['tarifParZone'][$this->_secteurTarifaireGeo];
      }

    } elseif($this->_acteType=='Libre') {
      if($d = $this->_getActeLibreData(['dataYaml'])) {
        $tarif =  $d['dataYaml']['tarifBase'];
      }

    }
    if(is_numeric($tarif)) {
      return number_format($tarif, 2, '.', '');
    } else {
      return '';
     }

  }

/**
 * Obtenir la tableau de correspondance entre codePro et labels
 * @param  string $sort trier par label ou pas code
 * @return array       tableau de correspodance trié
 */
  public static function getCodeProfLabel($sort='label') {
    $d = array(
      "mbio"=>"Biologie",
      "mcardio"=>"Cardiologie",
      "mchirortho"=>"Chirurgie orthopédique",
      "mcure"=>"Cure thermale",
      "mdermato"=>"Dermatologie",
      "mendoc"=>"Endocrinologie",
      "mg"=>"Médecine générale",
      "mgo"=>"Gynécologie Obstétrique",
      "mhge"=>"Hépato-gastro-entérologie",
      "minterne"=>"Médecine interne",
      "mmpr"=>"MPR",
      "mnephro"=>"Néphrologie",
      "mophtalmo"=>"Ophtalmologie",
      "mpedia"=>"Pédiatrie",
      "mpneumo"=>"Pneumologie",
      "mpsy"=>"Psychiatrie",
      "mrhumato"=>"Rhumatologie",
      "mspe"=>"Autres spécialités médicales",
      "msto"=>"Stomato & Chirugie maxillo-faciale",
      "mvasc"=>"Médecine vasculaire",
      "sf"=>"Sage-femme",
    );
    if($sort == "label") {
      asort($d);
    } else {
      ksort($d);
    }
    return $d;
  }

/**
 * Obtenir les data en base sur un acte CCAM
 * @param  array  $cols colonnes sql à extraire
 * @return array       data acte
 */
  private function _getActeCcamData($cols=['*']) {
    if($d = msSQL::sqlUnique("select ".implode(', ', msSQL::cleanArray($cols))." from `actes_base` where `code`='".msSQL::cleanVar($this->_acteCode)."' and `type`='CCAM' and `activite`='".$this->_acteActivite."' and `phase`='".$this->_actePhase."' limit 1")) {
      if(isset($d['dataYaml'])) $d['dataYaml']=Spyc::YAMLLoad($d['dataYaml']);
      return $d;
    } else {
      return false;
    }
  }

/**
 * Obtenir les data en base sur un modificateur CCAM
 * @param  array  $cols colonnes sql à extraire
 * @return array       data acte
 */
  private function _getActeModifCcamData($cols=['*']) {
    if($d = msSQL::sqlUnique("select ".implode(', ', msSQL::cleanArray($cols))." from `actes_base` where `code`='".msSQL::cleanVar($this->_acteCode)."' and `type`='mCCAM'")) {
      if(isset($d['dataYaml'])) $d['dataYaml']=Spyc::YAMLLoad($d['dataYaml']);
      return $d;
    } else {
      return false;
    }
  }

/**
 * Obtenir les data en base sur un acte NGAP
 * @param  array  $cols colonnes sql à extraire
 * @return array       data acte
 */
  private function _getActeNgapData($cols=['*'], $strict=FALSE) {
    if($d = msSQL::sqlUnique("select ".implode(', ', msSQL::cleanArray($cols))." from `actes_base` where `code`='".msSQL::cleanVar($this->_acteCode)."' and `type`='NGAP' and `codeProf`='".msSQL::cleanVar($this->_secteurTarifaireNgap)."' limit 1")) {
      if(isset($d['dataYaml'])) $d['dataYaml']=Spyc::YAMLLoad($d['dataYaml']);
      return $d;
    } elseif ($strict == FALSE and $d = msSQL::sqlUnique("select ".implode(', ', msSQL::cleanArray($cols))." from `actes_base` where `code`='".msSQL::cleanVar($this->_acteCode)."' and `type`='NGAP' limit 1")) {
      if(isset($d['dataYaml'])) $d['dataYaml']=Spyc::YAMLLoad($d['dataYaml']);
      return $d;
    } else {
      return false;
    }
  }

/**
 * Obtenir les data en base sur un acte libre
 * @param  array  $cols colonnes sql à extraire
 * @return array       data acte
 */
  private function _getActeLibreData($cols=['*']) {
    if($d = msSQL::sqlUnique("select ".implode(', ', msSQL::cleanArray($cols))." from `actes_base` where `code`='".msSQL::cleanVar($this->_acteCode)."' and `type`='Libre'")) {
      if(isset($d['dataYaml'])) $d['dataYaml']=Spyc::YAMLLoad($d['dataYaml']);
      return $d;
    } else {
      return false;
    }
  }

}
