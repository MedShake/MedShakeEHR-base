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
 * Set secteur tarifaire (1 ou 2)
 * @param int $_secteurTarifaire secteur identifié par un int
 */
    public function set_secteurTarifaire($_secteurTarifaire)
    {
      $this->_secteurTarifaire = $_secteurTarifaire;
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
  foreach($data['details'] as $key=>$val) {

    //sur l'acte
    if(isset($val['pourcents'])) $data['details'][$key]['tarif'] = round(($dataTarifs[$key]*$val['pourcents']/100), 2);
    if(isset($val['depassement'])) $data['details'][$key]['total'] = $data['details'][$key]['tarif'] + $val['depassement']; else $data['details'][$key]['total'] = $data['details'][$key]['tarif'];

    $data['details'][$key]['tarif'] = number_format($data['details'][$key]['tarif'], 2,'.','');
    $data['details'][$key]['total'] = number_format($data['details'][$key]['total'], 2,'.','');

    //sur la facturation totale
    if(isset($data['details'][$key]['tarif'])) $data['tarif']=$data['tarif']+$data['details'][$key]['tarif'];
    if(isset($val['depassement']))  $data['depassement']=$data['depassement']+$val['depassement'];


  }
  if(isset($data['depassement'])) $data['total']=$data['tarif'] + $data['depassement']; else $data['total']=$data['tarif'];

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
        $this->_tarifsNgapCcamForOneSecteur = msSQL::sql2tabKey("select code, tarifs".$this->_secteurTarifaire." from actes_base", "code", "tarifs".$this->_secteurTarifaire);

        return $this->_tarifsNgapCcamForOneSecteur;
      }

}
