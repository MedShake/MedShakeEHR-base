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
 * Utilisation de Thériaque bdd PostgreSQL <http://www.theriaque.org>
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msTheriaquePG {

  private $_client;

  /**
   * Constructeur : connexion
   */
  public function __construct()
  {
    global $p;
    $this->_client = pg_pconnect("dbname=".pg_escape_string($p['config']['theriaquePgDbName'])." user=".pg_escape_string($p['config']['theriaquePgDbUser'])." password=".pg_escape_string($p['config']['theriaquePgDbPassword']));
    @pg_query($this->_client, "set search_path to theriaque, public;");
    return $this->_client;
  }

  private function get_data_from_pg($fonction) {
    pg_query($this->_client, "set search_path to theriaque, public;");
    if($m=pg_query($this->_client, "BEGIN;SELECT quote_ident(".$fonction."::text);")) {
      $cursorName=pg_fetch_array($m);
      if($m2=pg_query($this->_client, "FETCH ALL FROM ".$cursorName[0].";")) {
        $arr = pg_fetch_all($m2);
        pg_query($this->_client,"END;");
        return msTools::utf8_converter($arr);
      } else return false;
    } else return false;
  }

  /////////////////////////////////////////////////////
  ////// 1. fonctions administratives

  public function get_the_infos() {
    return $this->get_data_from_pg("get_the_infos('')");
  }

  /////////////////////////////////////////////////////
  ////// 2. les spécialités

  public function get_the_spe_txt($libprod, $monovir) {
    return $this->get_data_from_pg('get_the_spe_txt(\''.$libprod.'\', '.$monovir.')');
  }

  public function get_the_specialite($codeid,$vartyp,$monovir) {
      return $this->get_data_from_pg('get_the_specialite(\''.$codeid.'\','.$vartyp.','.$monovir.')');
  }

  public function get_the_specialite_multi_codeid($codeid,$vartyp,$monovir) {
    $codes=explode(',',$codeid);
    $arr=[];
    foreach($codes as $code) {
      $data=$this->get_data_from_pg('get_the_specialite(\''.$code.'\','.$vartyp.','.$monovir.')');
      if(isset($data[0])) $arr=array_merge($arr, $data);
    }
    return $arr;
  }

  public function get_the_voie_spe($codeid) {
    return $this->get_data_from_pg("get_the_voie_spe($codeid)");
  }

  public function get_the_forme_spe($codeid) {
    return $this->get_data_from_pg("get_the_forme_spe($codeid)");
  }

  public function get_the_forme_comp_spe($codeid, $typeid) {
    return $this->get_data_from_pg("get_the_forme_comp_spe($codeid, $typeid)");
  }

  public function get_the_ref_forme($codeid) {
    return $this->get_data_from_pg("get_the_ref_forme($codeid)");
  }

  public function get_the_forme_txt_spe($codeid) {
    return $this->get_data_from_pg("get_the_forme_txt_spe($codeid)");
  }

  /////////////////////////////////////////////////////
  ////// 3. les laboratoires

  public function get_the_lab_spe($codeid, $typid) {
    return $this->get_data_from_pg("get_the_lab_spe($codeid, $typid)");
  }


  /////////////////////////////////////////////////////
  ////// 4. les produits

  public function get_the_pdt_txt($libtxt,$monovir) {
    return $this->get_data_from_pg("get_the_pdt_txt('$libtxt',$monovir)");
  }

  public function get_the_pdt_id($codeid) {
    return $this->get_data_from_pg("get_the_pdt_id($codeid)");
  }

  /////////////////////////////////////////////////////
  ////// 5. les substances

  public function get_the_sub_txt($libtxt,$vartype) {
    return $this->get_data_from_pg("get_the_sub_txt('$libtxt',$vartype)");
  }

  public function get_the_sub_id($codeid,$vartype) {
    return $this->get_data_from_pg("get_the_sub_id($codeid,$vartype)");
  }

  public function get_the_exp_id($codeid) {
    return $this->get_data_from_pg("get_the_exp_id($codeid)");
  }

  public function get_the_sub_spe($codeid,$typeid) {
    return $this->get_data_from_pg("get_the_sub_spe($codeid,$typeid)");
  }

  public function get_the_sub_preccomp_spe($codeid) {
    return $this->get_data_from_pg("get_the_sub_preccomp_spe($codeid)");
  }

  public function get_the_sub_teneur_spe($codeid) {
    return $this->get_data_from_pg("get_the_sub_teneur_spe($codeid)");
  }

  public function get_the_det_exp($codeid, $typid) {
    return $this->get_data_from_pg("get_the_det_exp($codeid, $typid)");
  }

  public function get_the_det_subact($codeid, $typid) {
    return $this->get_data_from_pg("get_the_det_subact($codeid, $typid)");
  }

  /////////////////////////////////////////////////////
  ////// 6. les indications

  public function get_the_ind_txt($libtxt) {
    return $this->get_data_from_pg("get_the_ind_txt('$libtxt')");
  }

  public function get_the_ind_spe($codeid) {
    return $this->get_data_from_pg("get_the_ind_spe($codeid)");
  }

  public function get_the_det_ind($codeid, $typeid) {
    return $this->get_data_from_pg("get_the_det_ind($codeid, $typeid)");
  }

  public function get_the_ref_ind($codeid, $codespe) {
    return $this->get_data_from_pg("get_the_ref_ind($codeid, $codespe)");
  }

  public function get_the_smr_spe($codeid, $codespe) {
    return $this->get_data_from_pg("get_the_smr_spe($codeid, $codespe)");
  }

  public function get_the_atr_spe($codefic) {
    return $this->get_data_from_pg("get_the_atr_spe($codefic)");
  }

  /////////////////////////////////////////////////////
  ////// 7. les contre-indications

  public function get_the_det_cipemg($idcipemg, $codeter, $nature, $idseq, $typid) {
    return $this->get_data_from_pg("get_the_det_cipemg($idcipemg, '$codeter', '$nature', $idseq, $typid)");
  }

  public function get_the_cipemg_spe($codeid, $typid) {
    return $this->get_data_from_pg("get_the_cipemg_spe($codeid, $typid)");
  }

  public function get_the_ref_cipemg($codeid, $idcipemg) {
    return $this->get_data_from_pg("get_the_ref_cipemg($codeid, $idcipemg)");
  }

  /////////////////////////////////////////////////////
  ////// 8 classes ATC

  public function get_the_atc_id($codeid) {
    return $this->get_data_from_pg("get_the_atc_id('$codeid')");
  }

  public function get_the_atc_ddd($codeid, $typid) {
    return $this->get_data_from_pg("get_the_atc_ddd('$codeid', $typid)");
  }

  /////////////////////////////////////////////////////
  ////// 10 classes pharmaco-thérapeutiques

  public function get_the_cph_spe($codeid) {
    return $this->get_data_from_pg("get_the_cph_spe('$codeid')");
  }

  /////////////////////////////////////////////////////
  ////// 12. critères de choix

  public function get_the_choix($codeid, $typ) {
    return $this->get_data_from_pg("get_the_choix('$codeid', $typ)");
  }

  public function get_the_atr_compl($codeav) {
    return $this->get_data_from_pg("get_the_atr_compl($codeav)");
  }

  public function get_the_doc_spe($codefic, $vartyp) {
    return $this->get_data_from_pg("get_the_doc_spe($codefic, $vartyp)");
  }

  /////////////////////////////////////////////////////
  ////// 13. les spécialités génériques

  public function get_the_gen_spe($codeid, $vartyp) {
    return $this->get_data_from_pg("get_the_gen_spe($codeid, $vartyp)");
  }

  /////////////////////////////////////////////////////
  ////// 14. psosologies

  public function get_the_poso($idspe, $lstter) {
    if(empty($lstter)) $lstter='NULL';
    return $this->get_data_from_pg("get_the_poso($idspe, $lstter)");
  }

  public function get_the_poso_com_uti($idspe, $typ) {
    return $this->get_data_from_pg("get_the_poso_com_uti($idspe, $typ)");
  }

  public function get_the_poso_text($lstidpos) {
    return $this->get_data_from_pg("get_the_poso_text('$lstidpos')");
  }

  public function get_the_det_poso_spe($code, $typ) {
    return $this->get_data_from_pg("get_the_det_poso_spe($code, '$typ')");
  }

  /////////////////////////////////////////////////////
  ////// 15. interactions

  public function get_the_inter_spe($codeid) {
    return $this->get_data_from_pg("get_the_inter_spe($codeid)");
  }

  /////////////////////////////////////////////////////
  ////// 17. CIM10

  public function get_the_cim_10($typ,$search) {
    return $this->get_data_from_pg("get_the_cim_10($typ,'$search')");
  }

  /////////////////////////////////////////////////////
  ////// 18 terrain

  public function get_the_terrain($libtxt,$typ) {
    return $this->get_data_from_pg("get_the_terrain($libtxt,$typ");
  }

  /////////////////////////////////////////////////////
  ////// 19. présentations

  public function get_the_presentation_v2($codeid,$typid) {
    return $this->get_data_from_pg("get_the_presentation_v2('$codeid',$typid)");
  }

  public function get_the_pre_cdt($codecip, $vartyp) {
    return $this->get_data_from_pg("get_the_pre_cdt('$codecip', $vartyp)");
  }

  public function get_the_pre_statut($codecip, $vartyp) {
    return $this->get_data_from_pg("get_the_pre_statut('$codecip', $vartyp)");
  }

  public function get_the_pre_pri($codecip, $vartyp) {
    return $this->get_data_from_pg("get_the_pre_pri('$codecip', $vartyp)");
  }

  public function get_the_spe_statut($idspe) {
    return $this->get_data_from_pg("get_the_spe_statut($idspe)");
  }

  public function get_the_pre_dsp($codecip, $vartyp) {
    return $this->get_data_from_pg("get_the_pre_dsp('$codecip', $vartyp)");
  }

  public function get_the_pre_csv($codecip, $vartyp) {
    return $this->get_data_from_pg("get_the_pre_csv('$codecip', $vartyp)");
  }

  public function get_the_pre_rbt($codecip,$vartype) {
    return $this->get_data_from_pg("get_the_pre_rbt('$codecip',$vartype)");
  }

  /////////////////////////////////////////////////////
  ////// 20. grossesse allaitement

  public function get_the_gr_fic_spe($codeid) {
    return $this->get_data_from_pg("get_the_gr_fic_spe($codeid)");
  }

  public function get_the_al_fic_spe($codeid) {
    return $this->get_data_from_pg("get_the_al_fic_spe($codeid)");
  }

  public function get_the_gr_spe($codeid, $typid, $codefic) {
    return $this->get_data_from_pg("get_the_gr_spe($codeid, $typid, $codefic)");
  }

  public function get_the_al_spe($codeid, $typid, $codefic) {
    return $this->get_data_from_pg("get_the_al_spe($codeid, $typid, $codefic)");
  }

  public function get_the_fpro_spe($codeid, $typid, $codefic) {
    return $this->get_data_from_pg("get_the_fpro_spe($codeid, $typid, $codefic)");
  }

  /////////////////////////////////////////////////////
  ////// 21. pharmacocinétique

  public function get_the_cinetique_spe($codeid) {
    return $this->get_data_from_pg("get_the_cinetique_spe($codeid)");
  }

  public function get_the_secupreclinique_spe($codeid) {
    return $this->get_data_from_pg("get_the_secupreclinique_spe($codeid)");
  }

  public function get_the_ref_secupreclinique($codeid, $codefic) {
    return $this->get_data_from_pg("get_the_ref_secupreclinique($codeid, $codefic)");
  }

  /////////////////////////////////////////////////////
  ////// 23. effets indésirables

  public function get_the_effind_spe($codeid, $typid) {
    return $this->get_data_from_pg("get_the_effind_spe($codeid, $typid)");
  }

  public function get_the_det_effind($codeid, $typid) {
    return $this->get_data_from_pg("get_the_det_effind($codeid, $typid)");
  }

  public function get_the_det_effind_sd($codeid, $typid) {
    return $this->get_data_from_pg("get_the_det_effind_sd('$codeid', $typid)");
  }

  public function get_the_ref_effind($codeind, $codespe, $typ) {
    return $this->get_data_from_pg("get_the_ref_effind($codeind, $codespe, '$typ')");
  }

  public function get_the_effind_id($codeid) {
    return $this->get_data_from_pg("get_the_effind_id($codeid)");
  }

  /////////////////////////////////////////////////////
  ////// 24. propriétés pharmacodynamie

  public function get_the_det_phdyna($codeid, $typid) {
    return $this->get_data_from_pg("get_the_det_phdyna($codeid, $typid)");
  }

  public function get_the_det_etio($codefic, $typeid, $codeid) {
    return $this->get_data_from_pg("get_the_det_etio('$codefic', $typeid, '$codeid')");
  }

  public function get_the_etio_spe($codeid) {
    return $this->get_data_from_pg("get_the_etio_spe($codeid)");
  }

  /////////////////////////////////////////////////////
  ////// 25. conducteur / utilisation machines

  public function get_the_fco_id_by_spe($codeid) {
    return $this->get_data_from_pg("get_the_fco_id_by_spe($codeid)");
  }

  public function get_the_det_fco($idfco) {
    return $this->get_data_from_pg("get_the_det_fco('$idfco')");
  }

  /////////////////////////////////////////////////////
  ////// 27. utilitaires

  public function get_the_cdf_to_cim10($cc_cs,$argu, $typ) {
      return $this->get_data_from_pg("get_the_cdf_to_cim10($cc_cs,$argu, $typ)");
  }

  /////////////////////////////////////////////////////
  ////// 29. analyse d'ordonnance complète

  //Analyse ordonnance
  public function get_analyse_ordonnance($patient, $prescription, $posologie, $typeAlerteSortie, $natureAlerteCipemg, $niveauGraviteInteraction) {

    global $p;

    $id_analyse = $p['user']['id'].rand(1000,9999);

    // data patient
    $patient['id_analyse']=$id_analyse;
    $patient=$this->_prepareArrayForQuery($patient);
    $queryPatient = "insert into patient (".implode(", ", array_keys($patient)).") values (".implode(", ", $patient).");";
    pg_query($this->_client, $queryPatient);

    //data prescriptions
    if(!empty($prescription)) {
      foreach($prescription as $pres) {
        $pres['id_analyse']=$id_analyse;
        $pres=$this->_prepareArrayForQuery($pres);
        $queryPres = "insert into prescriptions (".implode(", ", array_keys($pres)).") values (".implode(", ", $pres).");";
        pg_query($this->_client, $queryPres);
      }
    }
    //data poso
    if(!empty($posologie)) {
      foreach($posologie as $poso) {
        $poso['id_analyse']=$id_analyse;
        $poso=$this->_prepareArrayForQuery($poso);
        $queryPoso = "insert into posologies (".implode(", ", array_keys($poso)).") values (".implode(", ", $poso).");";
        pg_query($this->_client, $queryPoso);
      }
    }

    //obtenir les analyses
    $data = $this->get_data_from_pg("get_analyse_ordonnance($id_analyse, '$typeAlerteSortie', '$natureAlerteCipemg', '$niveauGraviteInteraction')");

    // vider les tables
    pg_query($this->_client, "delete from patient where id_analyse='".pg_escape_string($id_analyse)."'");
    pg_query($this->_client, "delete from prescriptions where id_analyse='".pg_escape_string($id_analyse)."'");
    pg_query($this->_client, "delete from posologies where id_analyse='".pg_escape_string($id_analyse)."'");

    // on reformate facon webservice
    if(!empty($data)) {
      foreach($data as $v) {
        if($v['id_type_alerte'] == 'Q') {
            if(!isset($v['indiceligneprescription_1'])) $v['indiceligneprescription_1']=$v['indiceligneprescription'];
            $retour['alertes_incompatibilite'][]=$v;
        } elseif($v['id_type_alerte'] == 'P') {
            $retour['alertes_redondance'][]=$v;
        } elseif($v['id_type_alerte'] == 'O') {
            if(!isset($v['indiceligneprescription_1'])) $v['indiceligneprescription_1']=$v['indiceligneprescription'];
            $retour['alertes_interaction'][]=$v;
        } elseif($v['id_type_alerte'] == 'M' or $v['id_type_alerte'] == 'N') {
            $retour['alertes_posologie'][]=$v;
        } elseif($v['id_type_alerte'] == 'L' or $v['id_type_alerte'] == 'K' or $v['id_type_alerte'] == 'D' or $v['id_type_alerte'] == 'C' or $v['id_type_alerte'] == 'B' or $v['id_type_alerte'] == 'A') {
            $retour['alertes_cipemg'][]=$v;
        } elseif($v['id_type_alerte'] == 'H' or $v['id_type_alerte'] == 'J' or $v['id_type_alerte'] == 'I' or $v['id_type_alerte'] == 'F') {
            $retour['alertes_grossesse'][]=$v;
        }
      }
    }

    // retourner les data d'analyse
    return array(
      'brut'=>$data,
      'formate'=>$retour
    );
  }

  public function get_the_conducteur($codeid, $typid) {
    return $this->get_data_from_pg("get_the_conducteur('$codeid', $typid)");
  }

  public function get_the_dopage($codeid, $typid) {
    return $this->get_data_from_pg("get_the_dopage('$codeid', $typid)");
  }

  /////////////////////////////////////////////////////
  ////// 30 classe ATC d'une spécialité

  public function get_the_ephmra($codeid) {
    return $this->get_data_from_pg("get_the_ephmra($codeid)");
  }

  /////////////////////////////////////////////////////
  ////// 31 classe EphMra d'une spécialité

  public function get_the_atc($codeid) {
    return $this->get_data_from_pg("get_the_atc($codeid)");
  }

  /////////////////////////////////////////////////////
  ////// 32 statut de prescription / délivrance d'un spécialité

  public function get_the_presdel($codeid, $typid) {
      return $this->get_data_from_pg("get_the_presdel('$codeid', $typid)");
  }

  /////////////////////////////////////////////////////
  ////// 37 code nature prestation sécurité sociale d'une spécialité

  public function get_the_prestation($codeid, $typid) {
    return $this->get_data_from_pg("get_the_prestation('$codeid', $typid)");
  }

  /////////////////////////////////////////////////////
  ////// 39. unités possibles pour une spécialité

  public function get_the_unite($codeid, $typid) {
    $codeid=(string) $codeid;
    return $this->get_data_from_pg("get_the_unite('$codeid',$typid)");
  }

  /////////////////////////////////////////////////////
  ////// 40. sécabilité

  public function get_the_secabilite($codeid) {
    return $this->get_data_from_pg("get_the_secabilite($codeid)");
  }

  /////////////////////////////////////////////////////
  ////// 41. description des présentations

  public function get_the_desc_pres($codeid,$typid) {
    return $this->get_data_from_pg("get_the_desc_pres($codeid,$typid)");
  }

  /////////////////////////////////////////////////////
  ////// 42. recherche d'hypersensibilité

  public function get_the_allergie($typ,$libcod) {
      return $this->get_data_from_pg("get_the_allergie($typ,'$libcod')");
  }

  /////////////////////////////////////////////////////
  ////// 44. recherche de DC

  public function get_the_denomination_commune($typid, $var, $dc) {
    return $this->get_data_from_pg("get_the_denomination_commune($typid, '$var', $dc)");
  }

  /////////////////////////////////////////////////////
  ////// 45. recherche de medicament virtuel père

  public function get_the_med_vir_pere($type, $var, $statut) {
    return $this->get_data_from_pg("get_the_med_vir_pere($type, '$var', $statut)");
  }

  /////////////////////////////////////////////////////
  ////// 46. recherche de substance active / excipient

  public function get_the_composant($type, $var, $typ_composant) {
    return $this->get_data_from_pg("get_the_composant($type, '$var', $typ_composant)");
  }


  /////////////////////////////////////////////////////
  ////// 47. prix unitaire estimatif

  public function get_the_prix_unit_est($list_code, $typid) {
    return $this->get_data_from_pg("get_the_prix_unit_est('$list_code', $typid)");
  }



  /**
   * Ajuster les array patients / prescription / poso avant génération d'insert
   * @param  array $tab array
   * @return array      array ajusté
   */
  private function _prepareArrayForQuery($tab) {
    $tab=array_map(function($value) {
      return trim($value) === "" ? 'NULL' : $value;
    }, $tab);
    foreach($tab as $k=>$v) {
      if(@$v[0].@$v[1] == '0,') $v=str_replace(',', '.', $v);
      if(is_numeric($v)) {$tab[$k]=$v;}
      elseif($v == 'NULL') {$tab[$k]=$v;}
      else {$tab[$k]="'".$v."'";}
    }
    return $tab;
  }

}
