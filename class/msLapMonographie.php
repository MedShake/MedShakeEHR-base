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
 *
 * LAP : méthodes concernant la construction de la monographie d'une spécialité,
 * dont fiches annexes
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msLapMonographie extends msLap
{

  private $_spe;
  private $_speData;
  private $_voieSpe;



/**
 * Définir la spécialité et sortir ses datas basiques
 * @param string $spe code Thériaque de la spécialité
 */
  public function setSpe($spe)
  {
      if (is_numeric($spe)) {
        $t['spe']=$this->_prepareData($this->_the->get_the_specialite($spe,1,3));
        $this->_speData = $t['spe'][0];
        return $this->_spe = $spe;
      } else {
        throw new Exception('Spe is not numeric');
      }
  }

/**
 * Obtenir les data basiques de la spécialité
 * @return array data de la spécialité
 */
  public function getSpeData() {
    return $this->_speData;
  }

/**
 * Obtenir les data pour la fiche Données administratives
 * @return array données administratives
 */
  public function getMonoAdministratif() {
    $produits=$this->_prepareData($this->_the->get_the_pdt_id($this->_speData['sp_pr_code_fk']));
    foreach($produits as $k=>$v) {
      if($v['sp_code_sq_pk'] == $this->_spe) {
        $t['produit']=$v;
        continue;
      }
    }

    $t['spe']=$this->_speData;
    $t['labSpe1']=$this->_prepareData($this->_the->get_the_lab_spe($this->_spe, 1));
    $t['labSpe2']=$this->_prepareData($this->_the->get_the_lab_spe($this->_spe, 2));
    $t['speStatut']=$this->_prepareData($this->_the->get_the_spe_statut($this->_spe));
    $t['presdel']=@$this->_prepareData($this->_the->get_the_presdel($this->_spe, 0))[0];
    return $t;

  }

/**
 * Obtenir les data pour la fiche Forms pharmaceutiques
 * @return array data formes pharmaceutiques
 */
  public function getMonoFormesPharmaceutiques() {
    if(isset($this->_voieSpe) and !empty($this->_voieSpe)) {
      $t['voieSpe']=$this->_voieSpe;
    } else {
      $t['voieSpe']=$this->_voieSpe=$this->_prepareData($this->_the->get_the_voie_spe($this->_spe));
    }
    $t['formeSpe']=$this->_prepareData($this->_the->get_the_forme_spe($this->_spe));
    $t['formeCompSpe1']=$this->_prepareData($this->_the->get_the_forme_comp_spe($this->_spe, 1));
    $t['formeCompSpe2']=$this->_prepareData($this->_the->get_the_forme_comp_spe($this->_spe, 2));
    //$t['refForme']=$this->_prepareData($this->_the->get_the_ref_forme($this->_spe));
    $t['formeTxtSpe']=$this->_prepareData($this->_the->get_the_forme_txt_spe($this->_spe));
    return $t;
  }

/**
 * Obtenir les data pour la fiche Composition
 * @return array data Composition
 */
  public function getMonoComposition() {
    $t['speData']=$this->getSpeData();
    $t['subSpe3']=$this->_prepareData($this->_the->get_the_sub_spe($this->_spe, 3));
    $t['subSpe4']=$this->_prepareData($this->_the->get_the_sub_spe($this->_spe, 4));

    //nettoyage de subSpe4
    if(!empty($t['subSpe4'])) {
      foreach($t['subSpe4'] as $k=>$v) {
        if(empty($v['libsubst'])) unset($t['subSpe4'][$k]);
      }
    }
    msTools::array_natsort_by('numordre', $t['subSpe4']);
    msTools::array_natsort_by('numordre', $t['subSpe3']);
    $t['subPreCompSpe']=$this->_prepareData($this->_the->get_the_sub_preccomp_spe($this->_spe));
    $t['subTeneurSpe']=$this->_prepareData($this->_the->get_the_sub_teneur_spe($this->_spe));
    return $t;
  }

/**
 * Obtenir les data pour la fiche Classifications
 * @return array data Classifications
 */
  public function getMonoClassifications() {
    $t['cphSpe']=$this->_prepareData($this->_the->get_the_cph_spe($this->_spe));
    $t['atc']=$this->_prepareData($this->_the->get_the_atc($this->_spe));
    $t['ephmra']=$this->_prepareData($this->_the->get_the_ephmra($this->_spe));
    $t['ddd']=$this->_prepareData($this->_the->get_the_atc_ddd($this->_spe, 2));
    return $t;
  }

/**
 * Obtenir les data pour la fiche Pharmacodynamie
 * @return [type] [description]
 */
  public function getMonoPharmacodynamie() {
    $t['detPhdyn1']=$this->_prepareData($this->_the->get_the_det_phdyna($this->_spe, 1));
    $t['detPhdyn2']=$this->_prepareData($this->_the->get_the_det_phdyna($this->_spe, 2));

    $t['etioSpe']=$this->_prepareData($this->_the->get_the_etio_spe($this->_spe));


    if(!empty($t['etioSpe'])) {
      $fichesID = array_column($t['etioSpe'], 'codefic');
      if(!empty($fichesID)) {
        foreach($fichesID as $fiche) {
          for ($i = 1; $i <= 19; $i++) {
            $t['detEtioSpe'][$fiche][$i]=$this->_prepareData($this->_the->get_the_det_etio($fiche, $i, $this->_spe));
          }
        }
      }
    }

    return $t;
  }

/**
 * Obtenir les datas pour la fiche Pharmacocinétique
 * @return array data Pharmacocinétique
 */
  public function getMonoPharmacocinetique() {
    $t['cinetiqueSpe']=$this->_prepareData($this->_the->get_the_cinetique_spe($this->_spe));
    return $t;
  }

/**
 * Obtenir les datas pour la fiche Sécurité clinique
 * @return array data Sécutité clinique
 */
  public function getMonoSecuritePreclinique() {
    $t['secuPrecliniqueSpe']=$this->_prepareData($this->_the->get_the_secupreclinique_spe($this->_spe));
    if(!empty($t['secuPrecliniqueSpe'])) {
      foreach($t['secuPrecliniqueSpe'] as $k=>$v) {
        $t['refSecuPreclinique'][$v['codefic']]=$this->_prepareData($this->_the->get_the_ref_secupreclinique($this->_spe, $v['codefic']));
      }
    }
    return $t;
  }

  public function getMonoMedicamentVirtuelTheriaque() {
    $t['mvPere']=$this->_prepareData($this->_the->get_the_med_vir_pere(1, $this->_spe, 0));
    $t['genThe']=$this->_prepareData($this->_the->get_the_gen_spe($this->_spe, 1));

    //spé ref
    if(is_array($t['genThe'])) {
      $refs=array_unique(array_column($t['genThe'],'gen_ref'));
      foreach($refs as $k=>$v) {
        $t['ref'][$k]=$this->_prepareData($this->_the->get_the_specialite($v,1,3))[0];
      }

      foreach($t['genThe'] as $k=>$v) {
        $det=$this->_prepareData($this->_the->get_the_specialite($v['sp_code'],1,3))[0];
        $t['genThe'][$k]=array_merge($t['genThe'][$k],$det);
      }
      msTools::array_natsort_by('sp_nomlong',$t['genThe']);
    }
    return $t;
  }

  public function getMonoGeneriques() {
    $t['genAfssaps']=$this->_prepareData($this->_the->get_the_gen_spe($this->_spe, 2));
    if(is_array($t['genAfssaps'])) {
      foreach($t['genAfssaps'] as $k=>$v) {
        // correction sortie PG
        if(!isset($v['sp_code'])) $t['genAfssaps'][$k]['sp_code']=$v['sp_code_sq_pk'];

        $det=$this->_prepareData($this->_the->get_the_specialite($t['genAfssaps'][$k]['sp_code'],1,3))[0];
        $t['genAfssaps'][$k]=array_merge($t['genAfssaps'][$k],$det);
      }
      msTools::array_natsort_by('sp_nomlong',$t['genAfssaps']);
    }
    return $t;
  }

/**
 * Obtenir les data pour la fiche Recommandations
 * @return array data Recommandations
 */
  public function getMonoRecommandations() {
    for ($i = 1; $i <= 7; $i++) {
      $t['choix'][$i]=$this->_prepareData($this->_the->get_the_choix($this->_spe, $i));
      if(empty($t['choix'][$i])) unset($t['choix'][$i]);
    }
    if(isset($t['choix'][1]) and is_array($t['choix'][1])) rsort($t['choix'][1]);
    return $t;
  }

/**
 * Obtenir les data pour la fiche Présentations
 * @return array data Présentations
 */
  public function getMonoPresentations() {
    $t['presentations']=$this->_prepareData($this->_the->get_the_presentation_v2($this->_spe, 1));
    if(!empty($t['presentations'])) {
      foreach($t['presentations'] as $v) {
        $t['preCdt'][$v['pre_code_pk']]=$this->_prepareData($this->_the->get_the_pre_cdt($v['pre_code_pk'], 1));
        $t['preStatut'][$v['pre_code_pk']]=$this->_prepareData($this->_the->get_the_pre_statut($v['pre_code_pk'], 1));
        $t['prePri'][$v['pre_code_pk']]=$this->_prepareData($this->_the->get_the_pre_pri($v['pre_code_pk'], 1));
        $t['preDsp'][$v['pre_code_pk']]=$this->_prepareData($this->_the->get_the_pre_dsp($v['pre_code_pk'], 1));
        $t['preCsv'][$v['pre_code_pk']]=$this->_prepareData($this->_the->get_the_pre_csv($v['pre_code_pk'], 1));
        if(is_array($t['preCsv'][$v['pre_code_pk']])) msTools::array_natsort_by('type', $t['preCsv'][$v['pre_code_pk']]);
        $t['preRbt'][$v['pre_code_pk']]=$this->_prepareData($this->_the->get_the_pre_rbt($v['pre_code_pk'], 1));
      }
    }

    return $t;
  }

/**
 * Obtenir les data pour la fiche Indications
 * @return array data Indications
 */
  public function getMonoIndications() {
    $t['indSpe']=$this->_prepareData($this->_the->get_the_ind_spe($this->_spe));
    if(!empty($t['indSpe'])) {
      foreach($t['indSpe'] as $k=>$v) {
        // patch pour sortie PG
        if(isset($v['fin_code_sq_pk']) and !isset($v['codeind'])) {
          $t['indSpe'][$k]['codeind']=$v['fin_code_sq_pk'];
          $v['codeind']=$v['fin_code_sq_pk'];
        }

        for ($i = 1; $i <= 8; $i++) {
          $t['detInd'][$v['codeind']][$i]=$this->_prepareData($this->_the->get_the_det_ind($v['codeind'], $i));
        }
        $t['refInd'][$v['codeind']]=$this->_prepareData($this->_the->get_the_ref_ind($v['codeind'], $this->_spe));
        $t['smrSpe'][$v['codeind']]=$this->_prepareData($this->_the->get_the_smr_spe($v['codeind'],$this->_spe));
        if(is_array($t['smrSpe'][$v['codeind']])) msTools::array_natsort_by('dtsmr',$t['smrSpe'][$v['codeind']]);
      }
      //on prépare un peu indSpe
      foreach($t['indSpe'] as $k=>$v) {
        $t['indSpe'][$k]['titre']=$t['detInd'][$v['codeind']]['7']['0']['libcourt'];
      }
      if(is_array($t['indSpe'])) msTools::array_natsort_by('titre',$t['indSpe']);
    }
    return $t;
  }

/**
 * Obtenir les fiches pour la fiche Mode d'administration
 * @return array data Mode d'administration
 */
  public function getMonoModeAdministration() {
    if(isset($this->_voieSpe) and !empty($this->_voieSpe)) {
      $t['voieSpe']=$this->_voieSpe;
    } else {
      $t['voieSpe']=$this->_voieSpe=$this->_prepareData($this->_the->get_the_voie_spe($this->_spe));
    }
    for ($i = 1; $i <= 3; $i++) {
      $t['posoComUti'][$i]=$this->_prepareData($this->_the->get_the_poso_com_uti($this->_spe, $i));
    }
    if(isset($t['posoComUti'][1]) and is_array($t['posoComUti'][1])) msTools::array_natsort_by('grp', $t['posoComUti'][1]);
    return $t;
  }

/**
 * Obtenir les datas de la fiche Posologies
 * @return array data Posologies
 */
  public function getMonoPosologies() {
    $poso=$this->_prepareData($this->_the->get_the_poso($this->_spe, ''));


    if(!empty($poso)) {
      foreach($poso as $v) {

        // sortir les fiches uniques
        // générer le det_poso de chacune
        if($v['typ']=='0' or empty($v['typ'])) {
          $t['posoFiches'][$v['nofic']]=$v;
          $detPosoSpe[$v['nofic']]=$this->_prepareData($this->_the->get_the_det_poso_spe($v['nofic'],2));
        }
        // sortir les voies
        elseif($v['typ']=='1') {
          $t['posoVoies'][$v['nofic']][]=$v;
        }
        // sortir les datas terrain
        elseif($v['typ']=='2') {
          $posoTerrains[$v['nofic']][]=$v;
        }
        // sortir les datas indications
        elseif($v['typ']=='3') {
          $t['posoIndications'][$v['nofic']][]=$v;
        }
        // sortir les datas indications comp
        elseif($v['typ']=='4') {
          $t['posoIndicationsComp'][$v['nofic']][]=$v;
        }
        // sortir les datas référence off
        elseif($v['typ']=='5') {
          $t['posoRef'][$v['nofic']][]=$v;
        }
      }
      if(!empty($t['posoFiches'])) {
        $posoText=$this->_prepareData($this->_the->get_the_poso_text(implode(',',array_keys($t['posoFiches']))));
        if(!empty($posoText)) {
          foreach($posoText as $k=>$v) {
            $t['posoText'][$v['nofic']]=$v;
          }
        }
      }

      //ordre alphabetique pour les indications
      if(!empty($t['posoIndications'])) {
        foreach($t['posoIndications'] as $k=>$v) {
          msTools::array_natsort_by('info_01', $t['posoIndications'][$k]);
        }
      }

      //regroupement pour terrains
      if(!empty($posoTerrains)) {
        foreach($posoTerrains as $fiche=>$array) {
          foreach($array as $k=>$v) {
            if(isset($t['posoTerrains'][$fiche][$v['valeur_01']])) {
              $t['posoTerrains'][$fiche][$v['valeur_01']]=$t['posoTerrains'][$fiche][$v['valeur_01']].' '.$v['info_01'];
            } else {
              $t['posoTerrains'][$fiche][$v['valeur_01']]=$v['info_01'];
            }
          }
        }
      }

      //remix posologie détaillée
      if(!empty($detPosoSpe)) {
        foreach($detPosoSpe as $fiche=>$array) {
          foreach($array as $k=>$v) {
            $d=explode('.', $v['grp']);

            if($d[0] == '1' and $d[2] == '0') {
              $t['detPosoSpe'][$fiche][$d[1]]['commentaire'][$d[3]]=$v['info_01'];
            }
            elseif($d[0] == '1' and $d[2] == '1' and $d[3] == '0') {
              $t['detPosoSpe'][$fiche][$d[1]]['dose'][]=$v;
            }
            elseif($d[0] == '1' and $d[2] == '2' and $d[3] == '0') {
              $t['detPosoSpe'][$fiche][$d[1]]['frequence'][]=$v;
            }
            elseif($d[0] == '1' and $d[2] == '2' and $d[3] == '1') {
              $t['detPosoSpe'][$fiche][$d[1]]['frequenceCom'][$d[4]]=$v;
            }
            elseif($d[0] == '1' and $d[2] == '2' and $d[3] == '2') {
              $t['detPosoSpe'][$fiche][$d[1]]['duree'][]=$v;
            }
            elseif($d[0] == '1' and $d[2] == '3') {
              $t['detPosoSpe'][$fiche][$d[1]]['dureeCom'][$d[3]]=$v;
            }
            elseif($d[0] == '2' and $d[1] == '0' and $d[2] == '1') {
              $t['detPosoAdapt'][$fiche][$d[3]]=$v;
            }
            elseif($d[0] == '2' and $d[1] == '1' and $d[2] == '1') {
              $t['detPosoSurvei'][$fiche][$d[3]]=$v;
            }
            elseif($d[0] == '2' and $d[1] == '2' and $d[2] == '1') {
              $t['detPosoReco'][$fiche][$d[3]]=$v;
            }
          }
        }
      }
    }
    return $t;
  }

/**
 * Obtenir les data pour les fiches basées sur get_the_cipemg_sp
 * @param  int $cat catégorie pour get_the_cipemg_sp
 * @return array      data cipemg
 */
  public function getMonoCIPEMG($cat) {
    $t['contreIndic']=$this->_prepareData($this->_the->get_the_cipemg_spe($this->_spe, $cat));
    if(!empty($t['contreIndic'])) {
        foreach($t['contreIndic'] as $k=>$v) {

          $t['contreIndic'][$k]['ref']=$this->_prepareData($this->_the->get_the_ref_cipemg($this->_spe,$v['idcipemg']));

          $bouclesur=[1,2,3,5,6];
          foreach ($bouclesur as $i) {
            $t['contreIndic'][$k]['det'.$i]=$this->_prepareData($this->_the->get_the_det_cipemg($v['idcipemg'],$v['codeter'],$v['nature'],$v['no_seq'],$i));
          }
        }

        msTools::array_natsort_by('terrain', $t['contreIndic']);
    }

    return $t;
  }

/**
 * Obtenir les data pour la fiche Interactions médicamenteuses
 * @return array Interactions médicamenteuses
 */
  public function getMonoInteractionsMedicamenteuses() {
    $t['interSpe']=$this->_prepareData($this->_the->get_the_inter_spe($this->_spe));
    return $t;
  }

  public function getMonoGrossesse() {
    $t['grFicSpe']=$this->_prepareData($this->_the->get_the_gr_fic_spe($this->_spe));
    if(!empty($t['grFicSpe'])) {
      foreach($t['grFicSpe'] as $v) {
        for ($i = 1; $i <= 17; $i++) {
          $t['grSpe'][$v['code_fiche']][$i]=$this->_prepareData($this->_the->get_the_gr_spe($this->_spe, $i, $v['code_fiche']));
        }
      }
    }
    return $t;

  }

/**
 * Obtenir les fiches pour Allaitement et Femme en age de procréer
 * @return array data Allaitement et Femme en age de procréer
 */
  public function getMonoAlaitementEtFemmeAgePocreer() {
    $t['alFicSpe']=$this->_prepareData($this->_the->get_the_al_fic_spe($this->_spe));
    if(!empty($t['alFicSpe'])) {
      foreach($t['alFicSpe'] as $v) {
        for ($i = 1; $i <= 8; $i++) {
          $t['alSpe'][$v['code_fiche']][$i]=$this->_prepareData($this->_the->get_the_al_spe($this->_spe, $i,  $v['code_fiche']));
        }
        for ($i = 1; $i <= 5; $i++) {
          $t['fproSpe'][$v['code_fiche']][$i]=$this->_prepareData($this->_the->get_the_fpro_spe($this->_spe, $i, $v['code_fiche']));
        }
      }
    }
    return $t;
  }

/**
 * Obtenir les information pour la fiche Conduite
 * @return array data Conduite
 */
  public function getMonoConduite() {
    $t['fcoIdBySpe']=$this->_prepareData($this->_the->get_the_fco_id_by_spe($this->_spe));
    if(!empty($t['fcoIdBySpe'])) {
        foreach($t['fcoIdBySpe'] as $v) {
          $t['detFco'][$v['idfco']]=$this->_prepareData($this->_the->get_the_det_fco($v['idfco']));
          if(isset($t['detFco'][$v['idfco']])) msTools::array_natsort_by('grp', $t['detFco'][$v['idfco']]);
        }
    }
    return $t;
  }

/**
 * Obtenir les infos pour Effets indésirables
 * @return array data Effets indésirables
 */
  public function getMonoEffetsIndesirables() {
    $effindSpe[1]=$this->_prepareData($this->_the->get_the_effind_spe($this->_spe, 1));
    $effindSpe[2]=$this->_prepareData($this->_the->get_the_effind_spe($this->_spe, 2));
    $effindSpe[3]=$this->_prepareData($this->_the->get_the_effind_spe($this->_spe, 3));
    $effindSpe[4]=$this->_prepareData($this->_the->get_the_effind_spe($this->_spe, 4));

    for ($i = 1; $i <= 2; $i++) {
      if(is_array($effindSpe[$i])) {
        foreach($effindSpe[$i] as $k=>$v) {
          $effind[$v['codeeffind']][$i][]=$v['texteffet'];
          sort($effind[$v['codeeffind']][$i]);
        }
      }
    }

    for ($i = 3; $i <= 4; $i++) {
      if(is_array($effindSpe[$i])) {
        foreach($effindSpe[$i] as $k=>$v) {
          $effindS[$v['codeeffind']][$i][]=$v['texteffet'];
          sort($effindS[$v['codeeffind']][$i]);
        }
      }
    }

    foreach($effind as $k=>$v) {
      $fiche=$this->_prepareData($this->_the->get_the_det_effind($k, 12));

      // correction coquille PG
      if(!isset($fiche[0]['codeind']) and isset($fiche[0]['code']))  {
         $fiche[0]['codeind']=$fiche[0]['code'];
      }

      if(!isset($fiche[0]['codeind'])) {
        $fiche[0]['codeind']='sans';
        $fiche[0]['libind']='';
      }
      $t['effindSpe'][$fiche[0]['codeind']]['title']=$fiche[0]['libind'];
      $t['effindSpe'][$fiche[0]['codeind']]['data']=$fiche[0];
      $t['effindSpe'][$fiche[0]['codeind']]['liste'][$k]=$v;
    }
    msTools::array_natsort_by('title', $t['effindSpe']);


    foreach($effindS as $k=>$v) {
      $fiche=$this->_prepareData($this->_the->get_the_det_effind($k, 12));

      // correction coquille PG
      if(!isset($fiche[0]['codeind']) and isset($fiche[0]['code']))  {
        $fiche[0]['codeind']=$fiche[0]['code'];
      }

      if(!isset($fiche[0]['codeind'])) {
        $fiche[0]['codeind']='sans';
        $fiche[0]['libind']='';
      }
      $t['effindSpeS'][$fiche[0]['codeind']]['title']=$fiche[0]['libind'];
      $t['effindSpeS'][$fiche[0]['codeind']]['data']=$fiche[0];
      $t['effindSpeS'][$fiche[0]['codeind']]['liste'][$k]=$v;
    }
    msTools::array_natsort_by('title', $t['effindSpeS']);


    return $t;

  }

/**
 * Obtenir les infos pour la fiche Effets indésirables hors surdosage
 * @param  int $code code fiche effet indésirable
 * @return  array       data fiche Effet indésirable
 */
  public function getMonoAnnexesEI($code) {
    $t['effindg']=$this->_prepareData($this->_the->get_the_effind_id($code));
    for ($i = 1; $i <= 15; $i++) {
      $t['effind'][$i]=$this->_prepareData($this->_the->get_the_det_effind($code, $i));
    }
    if(is_array($t['effind'][8])) msTools::array_natsort_by('libind', $t['effind'][8]);
    if(is_array($t['effind'][9])) msTools::array_natsort_by('libind', $t['effind'][9]);
    $t['refEffind']=$this->_prepareData($this->_the->get_the_ref_effind($code, $this->_spe, 'T'));
    return $t;
  }

/**
 * Obtenir les infos pour la fiche Effets indésirables en surdosage
 * @param  int $code code fiche effet indésirable
 * @return array       data fiche Effets indésirables en surdosage
 */
  public function getMonoAnnexesEIS($code) {
    $t['effindg']=$this->_prepareData($this->_the->get_the_effind_id($code));
    for ($i = 1; $i <= 3; $i++) {
      $t['effind'][$i]=$this->_prepareData($this->_the->get_the_det_effind_sd($code, $i));
    }
    if(is_array($t['effind'][1])) msTools::array_natsort_by('grp', $t['effind'][1]);
    $t['refEffind']=$this->_prepareData($this->_the->get_the_ref_effind($code, $this->_spe, 'S'));
    return $t;
  }

/**
 * Obtenir un document ou avis
 * @param  int $fiche numéro de la fiche
 * @param  int $type  type
 * @return array        data Document
 */
  public function getMonoAnnexesDoc($fiche,$type) {
    $t['doc']=$this->_prepareData($this->_the->get_the_doc_spe($fiche,$type));
    if($type=='1') {
      $t['docDet']=$this->_prepareData($this->_the->get_the_atr_spe($fiche));
    }
    return $t;
  }

/**
 * Obtenir les data pour la fiche substance active
 * @param  int $code code substance
 * @return array       data Substance active
 */
  public function getMonoAnnexesSubA($code) {
    $t['subId']=$this->_prepareData($this->_the->get_the_sub_id($code,1))[0];
    for ($i = 1; $i <= 3; $i++) {
      $t['detSubact'][$i]=$this->_prepareData($this->_the->get_the_det_subact($code,$i));
    }
    return $t;

  }

/**
 * Obtenir les data pour la fiche excipient
 * @param  int $code code substance
 * @return array       data Excipient
 */
  public function getMonoAnnexesSubE($code) {
    $t['subId2']=$this->_prepareData($this->_the->get_the_sub_id($code,2))[0];
    $t['subId']=$this->_prepareData($this->_the->get_the_exp_id($code))[0];
    for ($i = 1; $i <= 4; $i++) {
      $t['detExp'][$i]=$this->_prepareData($this->_the->get_the_det_exp($code,$i));
    }
    return $t;

  }

}
