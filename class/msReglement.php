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
  private $_patientID;
  private $_userID;
  private $_asUserID;
  private $_module;
  private $_objetID=null;
  private $_porteur;
  private $_reglementForm;

/**
 * @var int  $_factureTypeID facture type concernée
 */
    private $_factureTypeID;

/**
 * @var int  $_secteurTarifaire secteur tarifaire
 */
    protected $_secteurTarifaire;

/**
 * @var string  $_secteurTarifaireGeo secteur tarifaire géographique
 */
    protected $_secteurTarifaireGeo='metro';

/**
 * @var string  $_secteurIK secteur tarifaire IK
 */
    protected $_secteurIK;

/**
 * @var array  $_factureTypeData data d'une facture type
 */
    private $_factureTypeData;

/**
 * @var array  $_tarifsNgapCcamForOneSecteur data d'une facture type
 */
    private $_tarifsNgapCcamForOneSecteur;

/**
 * @var array  $_modifsCCAM modificateur CCAM
 */
    private $_modifsCCAM;

/**
 * @var array  $_prevalues valeurs de pré remplissage à l'édition d'un formulaire de règlement
 */
    private $_prevalues;

/**
 * Définir le patientID
 * @param int $patientID patientID
 */
    public function setPatientID($patientID) {
      return $this->_patientID=$patientID;
    }

/**
 * Définir l'objetID et ce qui en découle automatiquement
 * @param int $objetID objetID
 */
    public function setObjetID($objetID) {
      if(!is_numeric($objetID)) throw new Exception('ObjetID is not numeric');
      $this->_objetID=$objetID;
      if($res=msSQL::sqlunique("SELECT dt.module AS module, dt.formValues AS form, dt.id as porteur, dt.fromID AS userID
        FROM data_types as dt
        LEFT JOIN objets_data as od ON dt.id=od.typeID
        WHERE od.id='".$objetID."' limit 1")) {
          $this->_reglementForm=$res['form'];
          $this->_porteur=$res['porteur'];
          $this->_userID=$res['userID'];
          $this->_module=$res['module'];
      } else {
        throw new Exception('ObjetID n\'est pas valide');
      }

      return $this->_objetID;
    }

/**
 * Définir le module
 * @param string $module module
 */
    public function setModule($module) {
      return $this->_module=$module;
    }

/**
 * Obtenir la valeur courante pour le module
 * @return string valeur courante pour module
 */
    public function getModule() {
      return $this->_module;
    }

/**
 * Définir le userID
 * @param int $userID userID
 */
    public function setUserID($userID) {
      return $this->_userID=$userID;
    }

/**
 * Définir le asUserID
 * @param int $asUserID asUserID
 */
    public function setAsUserID($asUserID) {
      return $this->_asUserID=$asUserID;
    }

/**
 * Définir le porteur
 * @param int $porteur porteur du règlement (ID du dataType porteur)
 */
    public function setPorteur($porteur) {
      return $this->_porteur=$porteur;
    }

/**
 * Définir le nom du formulaire de réglement
 * @param string $reglementForm nom du formaulaire de réglèment
 */
    public function setReglementForm($reglementForm) {
      return $this->_reglementForm=$reglementForm;
    }

/**
 * Obtneir le nom courant du formulaire de règlement
 * @return string nom courant formulaire de règlement
 */
    public function getReglementForm() {
      return $this->_reglementForm;
    }

/**
 * Set factureTypeID
 * @param int $_factureTypeID ID d'une facture type
 */
    public function setFactureTypeID($_factureTypeID)
    {
        $this->_factureTypeID = $_factureTypeID;
        return $this;
    }

/**
 * Set secteur tarifaire
 * @param int $_secteurTarifaire secteur identifié par un int
 */
    public function setSecteurTarifaire($_secteurTarifaire)
    {
      $this->_secteurTarifaire = $_secteurTarifaire;
      return $this;
    }

/**
 * Set secteur tarifaire géographique
 * @param int $_secteurTarifaireGeo secteur tarifaire géographique
 */
    public function setSecteurTarifaireGeo($_secteurTarifaireGeo)
    {
      $this->_secteurTarifaireGeo = $_secteurTarifaireGeo;
      return $this;
    }

/**
 * Définir le secteur des IK
 * @param string $_secteurK sexteur IK
 */
    public function setSecteurIK($_secteurK)
    {
      $this->_secteurIK = $_secteurK;
      return $this;
    }

/**
 * Set facture type pre calculated data
 * @param array $_factureTypeData tableau brut des données d'une facture type
 */
    public function setFactureTypeData($_factureTypeData)
    {
      if(!is_array($_factureTypeData['details'])) {
        $_factureTypeData['details']=Spyc::YAMLLoad($_factureTypeData['details']);
      }
      $this->_factureTypeData = $_factureTypeData;
      return $this;
    }

/**
 * Obtenir les array pour la constructions des menus de factures types
 * @return array array pour construction menu factures types
 */
    public function getFacturesTypesMenus() {
      $tab=[];
      if ($tabTypes=msSQL::sql2tab("select a.* , c.label as catLabel
        from actes as a
        left join actes_cat as c on c.id=a.cat
        where a.toID in ('0','".$this->_userID."') and c.module='".$this->_module."'
        group by a.id
        order by c.displayOrder, c.label asc, a.label asc")) {
          foreach ($tabTypes as $k=>$v) {

              //n° de facture correspondant
              $v['numIndexFSE']=$k+1;

              //on récupère détails
              $v['details']=Spyc::YAMLLoad($v['details']);

              $tab[$v['catLabel']][]=$v;
          }
      }
      return $tab;
    }

/**
 * Définir l'ID de la facture type en fonction de l'objetID courant
 * @return int ID de la facture type
 */
    public function getFactureTypeIDFromObjetID() {
      if($factureTypeID=msSQL::sqlUniqueChamp("select parentTypeID from objets_data where id='".$this->_objetID."' limit 1 ")) {
        return $this->_factureTypeID = $factureTypeID;
      } else {
        return $this->_factureTypeID = null;
      }
    }

/**
 * Obtenir les prevalues pour le formulaire de règlement à partir de l'objetID courant
 * @return array typeID=>value
 */
    public function getPreValuesForReglementForm() {
      return $this->_prevalues = msSQL::sql2tabKey("select typeID, value from objets_data where id='".$this->_objetID."' or instance='".$this->_objetID."'", 'typeID', 'value');
    }

/**
 * Définir les secteurs tarifaires en fonction du contexte de règlement
 */
    public function setSecteursTarifaires() {
      global $p;

      $data=new msData();
      $name2typeID=$data->getTypeIDsFromName(['regleSecteurGeoTarifaire', 'regleSecteurHonoraires', 'regleSecteurIK']);
      if(isset($this->prevalues[$name2typeID['regleSecteurHonoraires']])) {
        $this->setSecteurTarifaire($this->prevalues[$name2typeID['regleSecteurHonoraires']]);
      } else {
        $this->setSecteurTarifaire($p['config']['administratifSecteurHonoraires']);
      }
      if(isset($this->prevalues[$name2typeID['regleSecteurGeoTarifaire']])) {
        $this->setSecteurTarifaireGeo($this->prevalues[$name2typeID['regleSecteurGeoTarifaire']]);
      } else {
        $this->setSecteurTarifaireGeo($p['config']['administratifSecteurGeoTarifaire']);
      }
      if(isset($this->prevalues[$name2typeID['regleSecteurIK']])) {
        $this->setSecteurIK($this->prevalues[$name2typeID['regleSecteurIK']]);
      } else {
        $this->setSecteurIK($p['config']['administratifSecteurIK']);
      }
    }

/**
 * Définir les champs cachés utiles au formulaire de règlement
 * @param array $f formualire de règlement sous forme d'aray PHP
 */
    public function setHiddenInputToReglementForm(&$f) {
      $add=array(
        'porteur'=>$this->_porteur,
        'reglementForm'=>$this->_reglementForm,
        'module'=>$this->_module,
        'asUserID'=>$this->_asUserID,
        'patientID'=>$this->_patientID,
        'acteID'=>$this->_factureTypeID,
        'regleDetailsActes'=>'',
        'regleSecteurGeoTarifaire'=>$this->_secteurTarifaireGeo,
        'regleSecteurHonoraires'=>$this->_secteurTarifaire,
        'regleSecteurIK'=>$this->_secteurIK,
      );
      if ($this->_objetID > 0) {
        $add['objetID']=$this->_objetID;
        $add['regleDetailsActes']=$this->_prevalues[msData::getTypeIDFromName('regleDetailsActes')];
      }
      msForm::addHiddenInput($f , $add);
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

        if($data = msSQL::sql2tabKey("select code, dataYaml, type, tarifUnit from actes_base where type!='mCCAM'", "code")) {
          foreach($data as $k=>$v) {
            $tarif=$this->_getTarifFromYamlData($v)['tarif'];
            $tab[$k]=array(
              'code'=>$k,
              'tarif'=>$tarif,
              'type'=>$v['type']
            );
          }
        }
        return $this->_tarifsNgapCcamForOneSecteur=$tab;
      }


/**
 * Obtenir le tarif d'un acte de base à partir des datas yaml en fonctions des secteurs tarifaires
 * @param  array $v données de l'acte
 * @return array    données de l'acte avec champ tarif
 */
      private function _getTarifFromYamlData(&$v) {
        $dataYaml=Spyc::YAMLLoadString($v['dataYaml']);
        if($v['type']=='CCAM' and !empty($this->_secteurTarifaire)) {
          $v['tarif']=$dataYaml['tarifParGrilleTarifaire']['CodeGrilleT'.$this->_secteurTarifaire];
          if(isset($dataYaml['modificateursParGrilleTarifaire']) and !empty($dataYaml['modificateursParGrilleTarifaire'])) {
            $v['modifsCCAMpossibles']=implode('', $dataYaml['modificateursParGrilleTarifaire']['CodeGrilleT'.$this->_secteurTarifaire]);
          }
          // application coeff majoration DOM
          if(isset($dataYaml['majorationsDom'][$this->_secteurTarifaireGeo])) {
            $v['tarif']=round(($v['tarif']*$dataYaml['majorationsDom'][$this->_secteurTarifaireGeo]),2);
          }
        } elseif($v['type']=='mCCAM' and !empty($this->_secteurTarifaire)) {
          if($v['tarifUnit']=='euro') {
            $v['tarif']=$dataYaml['tarifParGrilleTarifaire']['CodeGrilleT'.$this->_secteurTarifaire]['forfait'];
          } else {
            $v['tarif']=$dataYaml['tarifParGrilleTarifaire']['CodeGrilleT'.$this->_secteurTarifaire]['coef'];
          }
        } elseif($v['type']=='NGAP') {
          $v['tarif']=$dataYaml['tarifParZone'][$this->_secteurTarifaireGeo];
        } elseif($v['type']=='Libre') {
          $v['tarif']=$dataYaml['tarifBase'];
        } else {
          $v['tarif']='';
        }
        return $v;
      }

/**
 * Obtenir les data sur les actes NGAP / CCAM trouvés par une recherche
 * @param  string $search chaine de recherche
 * @return array           tableau des data
 */
      public function getActeDataFromTerm($search) {
        $searcho=$search;
        $search=str_replace(' ', '%', $search).'%';
        $data=[];
        if($data =  msSQL::sql2tab("select * from actes_base where code like '".msSQL::cleanVar($search)."' or label like '%".msSQL::cleanVar($search)."' order by code = '".msSQL::cleanVar($searcho)."' desc, code like '".msSQL::cleanVar($search)."' desc limit 25")) {
          foreach($data as $k=>$v) {
            $data[$k]=$this->_getTarifFromYamlData($v);
          }
        }
        return $data;
      }

/**
 * Retourner les modificateur CCAM
 * @return array tableau code => data
 */
      public function getModificateursCcam() {
        $modifs=[];
          if($modifs=msSQL::sql2tabKey("select * from actes_base where type = 'mCCAM' ", 'code')) {
           foreach($modifs as $k=>$v) {
             $modifs[$k]['dataYaml']=Spyc::YAMLLoad($v['dataYaml']);
             if(isset($modifs[$k]['dataYaml']['tarifParGrilleTarifaire']['CodeGrilleT'.$this->_secteurTarifaire])) {
               if($v['tarifUnit']=='euro') {
                 $modifs[$k]['tarif']=$modifs[$k]['dataYaml']['tarifParGrilleTarifaire']['CodeGrilleT'.$this->_secteurTarifaire]['forfait'];
               } else {
                 $modifs[$k]['tarif']=$modifs[$k]['dataYaml']['tarifParGrilleTarifaire']['CodeGrilleT'.$this->_secteurTarifaire]['coef'];
               }
             } else {
               //unset($modifs[$k]);
             }
           }
          }
        return $this->_modifsCCAM = $modifs;
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
          if ($this->_modifsCCAM[$modif]['dataYaml']['tarifUnit'] == 'euro') {
            $modifsCcamSum = $modifsCcamSum + $this->_modifsCCAM[$modif]['dataYaml']['tarifParGrilleTarifaire']['CodeGrilleT'.$this->_secteurTarifaire]['forfait'];
          } else if ($this->_modifsCCAM[$modif]['dataYaml']['tarifUnit'] == 'pourcent') {
            $modifsCcamSum = $modifsCcamSum + ($tarifBase * $this->_modifsCCAM[$modif]['dataYaml']['tarifParGrilleTarifaire']['CodeGrilleT'.$this->_secteurTarifaire]['coef'] / 100);
          }
        }
        return $modifsCcamSum;
      }

}
