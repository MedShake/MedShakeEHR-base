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
 * Règlement
 *
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

class msReglement
{

/**
 * @var int  $_factureTypeID facture type concernée
 */
    private $_factureTypeID;

/**
 * @var int  $_secteurTarifaire secteur tarifaire
 */
    private $_secteurTarifaire;

/**
 * @var array  $_factureTypeData data d'une facture type
 */
    private $_factureTypeData;

/**
 * @var array  $_tarifsNgapCcamForOneSecteur data d'une facture type
 */
    private $_tarifsNgapCcamForOneSecteur;

    private $_modifsCCAM;

/**
 * Set factureTypeID
 * @param int $_factureTypeID ID d'une facture type
 */
    public function set_factureTypeID($_factureTypeID)
    {
        $this->_factureTypeID = $_factureTypeID;
        return $this;
    }

/**
 * Set secteur tarifaire (vide, 1 ou 2)
 * @param int $_secteurTarifaire secteur identifié par un int
 */
    public function set_secteurTarifaire($_secteurTarifaire)
    {
      $this->_secteurTarifaire = $_secteurTarifaire !=''?:2;
      return $this;
    }

/**
 * Set facture type pre calculated data
 * @param array $_factureTypeData tableau brut des données d'une facture type
 */
    public function set_factureTypeData($_factureTypeData)
    {
      if(!is_array($_factureTypeData['details'])) {
        $_factureTypeData['details']=Spyc::YAMLLoad($_factureTypeData['details']);
      }
      $this->_factureTypeData = $_factureTypeData;
      return $this;
    }

/**
 * Obtenir les data d'une facture type
 * @return array data extraites de la bdd avec yaml décodé
 */
    public function getFactureTypeData()
    {
        if (!isset($this->_factureTypeID)) {
            throw new Exception('FactureTypeID is not set');
        }
        $data = msSQL::sqlUnique("select id, label, details, flagCmu from actes where id='".$this->_factureTypeID."' limit 1");
        $data['details']=Spyc::YAMLLoad($data['details']);
        $this->_factureTypeData = $data;
        return $data;
    }

/**
 * Obtenir les data calculées sur une facture type
 * @return array array avec les datas de la facture type
 */
public function getCalculateFactureTypeData() {

  if (!isset($this->_factureTypeID)) {
      throw new Exception('FactureTypeID is not set');
  }

  if (!isset($this->_factureTypeData)) {
    $data = $this->getFactureTypeData();
  } else {
    $data = $this->_factureTypeData;
  }

  if (!isset($this->_tarifsNgapCcamForOneSecteur)) {
    $dataTarifs = $this->getAllTarifsNgapCcamForOneSecteur();
  } else {
    $dataTarifs = $this->_tarifsNgapCcamForOneSecteur;
  }

  $data['tarif']=0;
  $data['depassement']=0;
  $data['majoModifCCAM']=0;

  foreach($data['details'] as $key=>$val) {
    if (!is_array($val)) {
        $data['details'][$key]=array('tarif'=>'0', 'depassement'=>'0', 'total'=>'0');
    }
    //sur l'acte
    $data['details'][$key]['base']=$dataTarifs[$key]['tarif'];
    if(isset($val['codeAsso'])) {
      $data['details'][$key]['codeAsso']=$val['codeAsso'];
    } else {
      $data['details'][$key]['codeAsso']='';
    }

    if(isset($val['modifsCCAM'])) {
      $data['details'][$key]['modifsCCAM']=$val['modifsCCAM'];
    } else {
      $data['details'][$key]['modifsCCAM']='';
    }

    if(isset($val['pourcents'])) {
        $data['details'][$key]['tarif'] = round(($dataTarifs[$key]['tarif']*$val['pourcents']/100), 2);
    } else {
        $data['details'][$key]['tarif'] = $dataTarifs[$key]['tarif'];
    }
    if(isset($val['depassement'])) {
        $data['details'][$key]['total'] = $data['details'][$key]['tarif'] + $val['depassement'];
    } else {
        $data['details'][$key]['total'] = $data['details'][$key]['tarif'];
    }

    if(isset($val['modifsCCAM'])) {
        $data['details'][$key]['majoModifCCAM'] = $this->_getMontantModifsCCAM($dataTarifs[$key]['tarif'], $val['modifsCCAM']);
        $data['details'][$key]['total'] = $data['details'][$key]['total'] + $data['details'][$key]['majoModifCCAM'];
    }

    $data['details'][$key]['type'] = $dataTarifs[$key]['type'];
    $data['details'][$key]['tarif'] = number_format($data['details'][$key]['tarif'], 2,'.','');
    $data['details'][$key]['total'] = number_format($data['details'][$key]['total'], 2,'.','');

    //sur la facturation totale
    if(isset($data['details'][$key]['tarif'])) {
        $data['tarif']=$data['tarif']+$data['details'][$key]['tarif'];
    }
    if(isset($val['depassement'])) {
        $data['depassement']=$data['depassement']+$val['depassement'];
    }
    if(isset($val['modifsCCAM'])) {
        $data['majoModifCCAM']=$data['majoModifCCAM'] + $data['details'][$key]['majoModifCCAM'];
    }

  }

  $data['total']=$data['tarif'];
  if(isset($data['depassement'])) {
      $data['total']=$data['total'] + $data['depassement'];
  }
  if(isset($data['majoModifCCAM'])) {
      $data['total']=$data['total'] + $data['majoModifCCAM'];
  }

  $data['total']=number_format($data['total'],2,'.','');
  $data['tarif']=number_format($data['tarif'],2,'.','');
  $data['depassement']=number_format($data['depassement'],2,'.','');

  return $data;
}

/**
 * Obtenir les tarifs des actes NGAP / CCAM pour un secteur tarifaire
 * @return array Tableau code => tarif
 */
      public function getAllTarifsNgapCcamForOneSecteur() {
        if (!isset($this->_secteurTarifaire)) {
            throw new Exception('SecteurTarifaire is not set');
        }
        if($this->_secteurTarifaire !=1 and $this->_secteurTarifaire !=2)  {
          throw new Exception('SecteurTarifaire is not correctly set');
        }
        $this->_tarifsNgapCcamForOneSecteur = msSQL::sql2tabKey("select code, tarifs".$this->_secteurTarifaire." as tarif, type from actes_base", "code");

        return $this->_tarifsNgapCcamForOneSecteur;
      }

/**
 * Obtenir les data sur un acte NGAP / CCAM
 * @param  string $codeActe code acte
 * @return array           tableau des data
 */
      public function getActeData($codeActe) {
        return msSQL::sqlUnique("select code, label, type, tarifs1, tarifs2, tarifUnit, F, P, S, M, R, D, E, C , U from actes_base where code = '".msSQL::cleanVar($codeActe)."' limit 1");
      }

/**
 * Obtenir les data sur les actes NGAP / CCAM trouvés par une recherche
 * @param  string $search chaine de recherche
 * @return array           tableau des data
 */
      public function getActeDataFromTerm($search) {
        $searcho=$search;
        $search=str_replace(' ', '%', $search).'%';
        return msSQL::sql2tab("select code, label, type, tarifs1, tarifs2, tarifUnit, F, P, S, M, R, D, E, C , U from actes_base where code like '".msSQL::cleanVar($search)."' or label like '%".msSQL::cleanVar($search)."' order by code = '".msSQL::cleanVar($searcho)."' desc, code like '".msSQL::cleanVar($search)."' desc limit 25");
      }

/**
 * Retourner les modificateur CCAM
 * @return array tableau code => data
 */
      public function getModificateursCcam() {
        return $this->_modifsCCAM = msSQL::sql2tabKey("select * from actes_base where type = 'mCCAM' ", 'code');
      }

/**
 * Obtenir le montant de surtarification apporté par des modificateurs CCAM
 * @param  float $tarifBase    tarif de base
 * @param  string $modifsString chaine de modificateurs
 * @return float               valeur à appliquer en plus du tarif de base
 */
      private function _getMontantModifsCCAM($tarifBase, $modifsString) {
        $modifsCcamSum=0;
        if(strlen(trim($modifsString)) < 1) return $modifsCcamSum;
        if(!isset($this->_modifsCCAM)) {
          $this->getModificateursCcam();
        }
        $modifs = str_split($modifsString);
        foreach ($modifs as $modif) {
          if ($this->_modifsCCAM[$modif]['tarifUnit'] == 'euro') {
            $modifsCcamSum = $modifsCcamSum + $this->_modifsCCAM[$modif]['tarifs1'];
          } else if ($this->_modifsCCAM[$modif]['tarifUnit'] == 'pourcent') {
            $modifsCcamSum = $modifsCcamSum + ($tarifBase * $this->_modifsCCAM[$modif]['tarifs1'] / 100);
          }
        }
        return $modifsCcamSum;
      }

}
