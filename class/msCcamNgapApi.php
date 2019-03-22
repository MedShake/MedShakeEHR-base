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
 * Récupération des data CCAM / NGAP depuis un serveur d'API MedShake
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msCcamNgapApi
{


/**
* Code CCAM
* @var string
*/
  private $_acteCode;
  private $_acteType='CCAM';
  private $_phaseCode=0;
  private $_activiteCode=1;
  private $_acteCodeProf='mspe';
  private $_acteData;

/**
* Définir le code acte
* @param string $acte code acte CCAM
*/
  public function setActeCode($acte)
  {
      return $this->_acteCode=$acte;
  }

/**
* Définir le code activite
* @param string $activiteCode code activite CCAM
*/
  public function setActiviteCode($activiteCode)
  {
      return $this->_activiteCode=$activiteCode;
  }

/**
* Définir le code phase
* @param string $acte code phase CCAM
*/
  public function setPhaseCode($phaseCode)
  {
      return $this->_phaseCode=$phaseCode;
  }

/**
* Définir le type de l'acte
* @param string $acte code acte CCAM
*/
  public function setActeType($acteType)
  {
      return $this->_acteType=$acteType;
  }

/**
 * Définir le code profession (secteur honoraires NGAP)
 * @param string $acteCodeProf code profession pour sélection grile tarifaire NGAP
 */
  public function setActeCodeProf($acteCodeProf)
  {
      return $this->_acteCodeProf=$acteCodeProf;
  }

/**
* Obtenir les data sur l'acte
* @return array data de l'acte
*/
  public function getActeData()
  {
    if($this->_acteType == 'Libre') {
      return $data2return=array(
        'acteCode'=>$this->_acteCode,
        'acteLabel'=>$this->_acteCode,
        'activiteCode'=>$this->_activiteCode,
        'phaseCode'=>$this->_phaseCode,
        'yaml'=>'tarifBase: ',
        'tarifUnite'=>'euro'
      );
    }

    $scrap = $this->_loadCcamFromServer();
    if(is_string($scrap)) return $scrap;
    $data2return=[];

    if($this->_acteType == 'CCAM') {
      if(!empty($scrap['tarifParGrilleTarifaire'])) {
        foreach($scrap['tarifParGrilleTarifaire'] as $k=>$v) {
          $newData['CodeGrilleT'.$k]=(float)str_replace(',', '.', $v);
        }
        $scrap['tarifParGrilleTarifaire']=$newData;
      }
      if(!empty($scrap['modificateursApplicables'])) {
        foreach($scrap['modificateursApplicables'] as $k=>$v) {
          $newDataMa['CodeGrilleT'.$k]= $v;
        }
        $scrap['modificateursApplicables']=$newDataMa;
      }

      $data=Spyc::YAMLDump(array(
        'tarifParGrilleTarifaire'=>$newData,
        'modificateursParGrilleTarifaire'=>$scrap['modificateursApplicables'],
        'majorationsDom'=>$scrap['majorationsDom'],
      ), false, 0, TRUE);
      $data=preg_replace("#: '([0-9]+),([0-9]+)'#", ": $1.$2", $data);

      $data2return=array(
        'acteCode'=>$this->_acteCode,
        'acteLabel'=>$scrap['nom_long'],
        'activiteCode'=>$this->_activiteCode,
        'phaseCode'=>$this->_phaseCode,
        'yaml'=>$data,
        'tarifUnite'=>'euro'
      );

    } elseif($this->_acteType == 'NGAP') {

      $data=Spyc::YAMLDump(array(
              'tarifParZone'=>array(
                'metro'=>$scrap['tarifMetro'],
                '971'=>$scrap['tarif971'],
                '972'=>$scrap['tarif972'],
                '973'=>$scrap['tarif973'],
                '974'=>$scrap['tarif974'],
                '976'=>$scrap['tarif976']
              )
            ), false, 0, TRUE);

      $data=preg_replace("#: '([0-9]+),([0-9]+)'#", ": $1.$2", $data);

      $data2return=array(
        'acteCode'=>$this->_acteCode,
        'acteLabel'=>$scrap['label'],
        'activiteCode'=>$this->_activiteCode,
        'phaseCode'=>$this->_phaseCode,
        'codeProf'=>$this->_acteCodeProf,
        'yaml'=>$data,
        'tarifUnite'=>'euro'

      );

    } elseif($this->_acteType == 'mCCAM') {

      $labels=array_column($scrap,'libelle');
      $labels=array_unique($labels);
      $label = implode(' / ', $labels);

      foreach($scrap as $k=>$v) {
        if($v['coef'] > 1) {
          $coef=($v['coef']-1)*100;
          $tarifUnite='pourcent';
        } else {
          $coef=0;
          $tarifUnite='euro';
        }
        $dataRet['CodeGrilleT'.$k]=array(
          'label'=>$v['libelle'],
          'coef'=>$coef,
          'forfait'=>$v['forfait']
        );
      }

      $data=Spyc::YAMLDump(array('tarifParGrilleTarifaire'=>$dataRet), false, 0, TRUE);
      $data=preg_replace("#: '([0-9]+),([0-9]+)'#", ": $1.$2", $data);

      $data2return=array(
        'acteCode'=>$this->_acteCode,
        'acteLabel'=>$label,
        'activiteCode'=>$this->_activiteCode,
        'phaseCode'=>$this->_phaseCode,
        'yaml'=>$data,
        'tarifUnite'=>$tarifUnite
      );
    }

    return $data2return;

  }

/**
 * Mettre à jour tous les actes de la base via data de l'API
 * @return array tableau rapport sur la mise à jour, par acte
 */
  public function getAllAndUpdate() {
    $tabr=[];
    if($codes=msSQL::sql2tab("select code, type, phase, activite, codeProf from actes_base where type in ('NGAP', 'CCAM', 'mCCAM') order by type")); {
      foreach($codes as $k=>$code) {
        $this->setActeCode($code['code']);
        $this->setActiviteCode($code['activite']);
        $this->setPhaseCode($code['phase']);
        $this->setActeType($code['type']);
        $this->setActeCodeProf($code['codeProf']);
        $data=$this->getActeData();
        $tabr[$k]=array(
          'type'=>$code['type'],
          'code'=>$code['code'],
          'activite'=>$code['activite'],
          'phase'=>$code['phase'],
        );
        if(is_array($data)) {
          msSQL::sqlInsert('actes_base', array(
            'code'=>$data['acteCode'],
            'activite'=>$data['activiteCode'],
            'phase'=>$data['phaseCode'],
            'codeProf'=>$data['codeProf'],
            'type'=>$code['type'],
            'label'=>$data['acteLabel'],
            'dataYaml'=>$data['yaml'],
            'tarifUnit'=>$data['tarifUnite'],
          ));
          $tabr[$k]['statut']='ok';
          $tabr[$k]['label']=$data['acteLabel'];
        } else {
          $tabr[$k]['statut']='ko';
        }
      }
    }
    return $tabr;
  }

/**
* Extraire data CCAM de l'acte concerné
* @return string  data JSON.
*/
  private function _loadCcamFromServer()
  {
      global $p;
      if($this->_acteType == 'CCAM') {
        $url=$p['config']['apiCcamNgapUrl']."/ccam/actes/".$this->_acteCode."/".$this->_activiteCode."/".$this->_phaseCode."/?key=".$p['config']['apiCcamNgapKey'];
      } elseif($this->_acteType == 'NGAP') {
        $url=$p['config']['apiCcamNgapUrl']."/ngap/actes/".$this->_acteCode."/".$this->_acteCodeProf."/?key=".$p['config']['apiCcamNgapKey'];
      } elseif($this->_acteType == 'mCCAM') {
        $url=$p['config']['apiCcamNgapUrl']."/ccam/modificateur/".$this->_acteCode."/?key=".$p['config']['apiCcamNgapKey'];
      } else {
        return;
      }

      $agent= 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:58.0) Gecko/20100101 Firefox/58.0';

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, $agent);
      curl_setopt($ch, CURLOPT_URL, $url);
      $result=json_decode(curl_exec($ch), TRUE);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      if($httpCode=='200' and !empty($result['data'])) {
        return  $this->_acteData=$result['data'];
      } elseif($httpCode=='200' and empty($result['data'])) {
        return "Code non reconnu";
      } else {
        return implode('; ', $result['erreurs']). ' ('.$httpCode.')';
      }
    }
}
