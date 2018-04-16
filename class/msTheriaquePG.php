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
    $this->_client = pg_pconnect("dbname=theriaque user=theriaque password=theriaque");
    pg_query($this->_client, "set search_path to theriaque, public;");
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

  /// infos administratives
  public function get_the_infos() {
    return $this->get_data_from_pg("get_the_infos('')");
  }

  /// specialités
  public function get_the_spe_txt($libprod, $monovir) {
    return $this->get_data_from_pg('get_the_spe_txt(\''.$libprod.'\', '.$monovir.')');
  }

  public function get_the_specialite($codeid,$vartyp,$monovir) {
      return $this->get_data_from_pg('get_the_specialite(\''.$codeid.'\','.$vartyp.','.$monovir.')');
  }

  public function get_the_specialite_multi_codeid($codeid,$vartyp,$monovir) {
    $codes=explode(',',$codeid);
    foreach($codes as $code) {
      $arr=$this->get_data_from_pg('get_the_specialite(\''.$code.'\','.$vartyp.','.$monovir.')');
    }
    return $arr;
  }

  public function get_the_unite($codeid, $typid) {
    $codeid=(string) $codeid;
    return $this->get_data_from_pg("get_the_unite('$codeid',$typid)");
  }

  public function get_the_secabilite($codeid) {
    return $this->get_data_from_pg("get_the_secabilite($codeid)");
  }

  public function get_the_voie_spe($codeid) {
    return $this->get_data_from_pg("get_the_voie_spe($codeid)");
  }

  public function get_the_gen_spe($codeid, $vartyp) {
    return $this->get_data_from_pg("get_the_gen_spe($codeid, $vartyp)");
  }

  //Posologie
  public function get_the_poso($idspe, $lstter) {
    if(empty($lstter)) $lstter='NULL';
    return $this->get_data_from_pg("get_the_poso($idspe, $lstter)");
  }
  public function get_the_det_poso_spe($code, $typ) {
    return $this->get_data_from_pg("get_the_det_poso_spe($code, '$typ')");
  }
  public function get_the_poso_text($lstidpos) {
    return $this->get_data_from_pg("get_the_poso_text('$lstidpos')");
  }

  /// Présentations
  public function get_the_presentation_v2($codeid,$typid) {
    return $this->get_data_from_pg("get_the_presentation_v2('$codeid',$typid)");
  }

  public function get_the_pre_rbt($codecip,$vartype) {
    return $this->get_data_from_pg("get_the_pre_rbt('$codecip',$vartype)");
  }

  public function get_the_desc_pres($codeid,$typid) {
    return $this->get_data_from_pg("get_the_desc_pres($codeid,$typid)");
  }

  /// Produits
  public function get_the_pdt_txt($libtxt,$monovir) {
    return $this->get_data_from_pg("get_the_pdt_txt('$libtxt',$monovir)");
  }

  /// DCI
  public function get_the_denomination_commune($typid, $var, $dc) {
    return $this->get_data_from_pg("get_the_denomination_commune($typid, '$var', $dc)");
  }

  /// Substance
  public function get_the_sub_txt($libtxt,$vartype) {
    return $this->get_data_from_pg("get_the_sub_txt('$libtxt',$vartype)");
  }

  /// Substance par code spé
  public function get_the_sub_spe($codeid,$typeid) {
    return $this->get_data_from_pg("get_the_sub_spe($codeid,$typeid)");
  }

  /// CIM 10
  public function get_the_cim_10($typ,$search) {
    return $this->get_data_from_pg("get_the_cim_10($typ,'$search')");
  }

  public function get_the_cdf_to_cim10($cc_cs,$argu, $typ) {
      return $this->get_data_from_pg("get_the_cdf_to_cim10($cc_cs,$argu, $typ)");
  }

  /// Allergies
  public function get_the_allergie($typ,$libcod) {
      return $this->get_data_from_pg("get_the_allergie($typ,'$libcod')");
  }

  /// Terrains
  public function get_the_terrain($libtxt,$typ) {
    return $this->get_data_from_pg("get_the_terrain($libtxt,$typ");
  }

  //classe ATC
  public function get_the_atc_id($codeid) {
    return $this->get_data_from_pg("get_the_atc_id('$codeid')");
  }

  //medic virtuel père
  public function get_the_med_vir_pere($type, $var, $statut) {
    return $this->get_data_from_pg("get_the_med_vir_pere($type, '$var', $statut)");
  }

  // Prix
  public function get_the_prix_unit_est($list_code, $typid) {
    return $this->get_data_from_pg("get_the_prix_unit_est('$list_code', $typid)");
  }

  // Sécurité sociale prestations
  public function get_the_prestation($codeid, $typid) {
    return $this->get_data_from_pg("get_the_prestation('$codeid', $typid)");
  }

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
    pg_query($this->_client, "delete from patient where id_analyse='".$id_analyse."'");
    pg_query($this->_client, "delete from prescriptions where id_analyse='".$id_analyse."'");
    pg_query($this->_client, "delete from posologies where id_analyse='".$id_analyse."'");

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

  // informations dopage
  public function get_the_dopage($codeid, $typid) {
    return $this->get_data_from_pg("get_the_dopage('$codeid', $typid)");
  }

  // informations conducteur
  public function get_the_conducteur($codeid, $typid) {
    return $this->get_data_from_pg("get_the_conducteur('$codeid', $typid)");
  }

  //effets indésirables (fiches)
  public function get_the_effind_spe($codeid, $typid) {
    return $this->get_data_from_pg("get_the_effind_spe($codeid, $typid)");
  }

  //effets indésirables (infos gen dont fqc codée)
  public function get_the_effind_id($codeind) {
    return $this->get_data_from_pg("get_the_effind_id($codeind)");
  }

  //effets indésirables (détails)
  public function get_the_det_effind($codeid, $typid) {
    return $this->get_data_from_pg("get_the_det_effind($codeid, $typid)");
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
