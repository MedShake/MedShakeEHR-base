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
 * Utilisation des webservices Thériaque <http://www.theriaque.org>
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msTheriaqueWS {

  private $_client;

  /**
   * Constructeur : connexion
   */
  public function __construct()
  {
    global $p;
    ini_set('soap.wsdl_cache_enabled', 0);
    $client = new SoapClient($p['config']['theriaqueWsURL'], array(
      'encoding' => 'UTF8',
    ));

    return $this->_client=$client;
  }

  /////////////////////////////////////////////////////
  ////// 1. fonctions administratives

  public function get_the_infos() {
    return $this->_client->get_the_infos();
  }

  /////////////////////////////////////////////////////
  ////// 2. les spécialités

  public function get_the_spe_txt($libprod, $monovir) {
    return $this->_client->get_the_spe_txt($libprod, $monovir);
  }

  public function get_the_specialite($codeid,$vartyp,$monovir) {
    return $this->_client->get_the_specialite($codeid,$vartyp,$monovir);
  }

  public function get_the_specialite_multi_codeid($codeid,$vartyp,$monovir) {
    return $this->_client->get_the_specialite_multi_codeid($codeid,$vartyp,$monovir);
  }

  public function get_the_voie_spe($codeid) {
    return $this->_client->get_the_voie_spe($codeid);
  }

  public function get_the_forme_spe($codeid) {
    return $this->_client->get_the_forme_spe($codeid);
  }

  public function get_the_forme_comp_spe($codeid, $typeid) {
    return $this->_client->get_the_forme_comp_spe($codeid, $typeid);
  }

  public function get_the_ref_forme($codeid) {
    return $this->_client->get_the_ref_forme($codeid);
  }

  public function get_the_forme_txt_spe($codeid) {
    return $this->_client->get_the_forme_txt_spe($codeid);
  }

  /////////////////////////////////////////////////////
  ////// 3. les laboratoires

  public function get_the_lab_spe($codeid, $typid) {
    return $this->_client->get_the_lab_spe($codeid, $typid);
  }

  /////////////////////////////////////////////////////
  ////// 4. les produits

  public function get_the_pdt_txt($libtxt,$monovir) {
    return $this->_client->get_the_pdt_txt($libtxt,$monovir);
  }

  public function get_the_pdt_id($codeid) {
    return $this->_client->get_the_pdt_id($codeid);
  }

  /////////////////////////////////////////////////////
  ////// 5. les substances

  public function get_the_sub_txt($libtxt,$vartype) {
    return $this->_client->get_the_sub_txt($libtxt,$vartype);
  }

  public function get_the_sub_id($codeid,$vartype) {
    return $this->_client->get_the_sub_id($codeid,$vartype);
  }

  public function get_the_exp_id($codeid) {
    return $this->_client->get_the_exp_id($codeid);
  }

  public function get_the_sub_spe($codeid,$typeid) {
    return $this->_client->get_the_sub_spe($codeid,$typeid);
  }

  public function get_the_sub_preccomp_spe($codeid) {
    return $this->_client->get_the_sub_preccomp_spe($codeid);
  }

  public function get_the_sub_teneur_spe($codeid) {
    return $this->_client->get_the_sub_teneur_spe($codeid);
  }

  public function get_the_det_exp($codeid, $typid) {
    return $this->_client->get_the_det_exp($codeid, $typid);
  }

  public function get_the_det_subact($codeid, $typid) {
    return $this->_client->get_the_det_subact($codeid, $typid);
  }

  /////////////////////////////////////////////////////
  ////// 6. les indications

  public function get_the_ind_txt($libtxt) {
    return $this->_client->get_the_ind_txt($libtxt);
  }

  public function get_the_ind_spe($codeid) {
    return $this->_client->get_the_ind_spe($codeid);
  }

  public function get_the_det_ind($codeid, $typeid) {
    return $this->_client->get_the_det_ind($codeid, $typeid);
  }

  public function get_the_ref_ind($codeid, $codespe) {
    return $this->_client->get_the_ref_ind($codeid, $codespe);
  }

  public function get_the_smr_spe($codeid, $codespe) {
    return $this->_client->get_the_smr_spe($codeid, $codespe);
  }

  public function get_the_atr_spe($codefic) {
    return $this->_client->get_the_atr_spe($codefic);
  }

  /////////////////////////////////////////////////////
  ////// 7. les contre-indications

  public function get_the_det_cipemg($idcipemg, $codeter, $nature, $idseq, $typid) {
    return $this->_client->get_the_det_cipemg($idcipemg, $codeter, $nature, $idseq, $typid);
  }

  public function get_the_cipemg_spe($codeid, $typid) {
    return $this->_client->get_the_cipemg_spe($codeid, $typid);
  }

  public function get_the_ref_cipemg($codeid, $idcipemg) {
    return $this->_client->get_the_ref_cipemg($codeid, $idcipemg);
  }

  /////////////////////////////////////////////////////
  ////// 8 classes ATC

  public function get_the_atc_id($codeid) {
    return $this->_client->get_the_atc_id($codeid);
  }

  public function get_the_atc_ddd($codeid, $typid) {
    return $this->_client->get_the_atc_ddd($codeid, $typid);
  }

  /////////////////////////////////////////////////////
  ////// 10 classes pharmaco-thérapeutiques

  public function get_the_cph_spe($codeid) {
    return $this->_client->get_the_cph_spe($codeid);
  }

  /////////////////////////////////////////////////////
  ////// 12. critères de choix

  public function get_the_choix($codeid, $typ) {
    return $this->_client->get_the_choix($codeid, $typ);
  }

  public function get_the_atr_compl($codeav) {
    return $this->_client->get_the_atr_compl($codeav);
  }

  public function get_the_doc_spe($codefic, $vartyp) {
    return $this->_client->get_the_doc_spe($codefic, $vartyp);
  }

  /////////////////////////////////////////////////////
  ////// 13. les spécialités génériques

  public function get_the_gen_spe($codeid, $vartyp) {
    return $this->_client->get_the_gen_spe($codeid, $vartyp);
  }

  /////////////////////////////////////////////////////
  ////// 14. psosologies

  public function get_the_poso($idspe, $lstter) {
    return $this->_client->get_the_poso($idspe, $lstter);
  }

  public function get_the_poso_com_uti($idspe, $typ) {
    return $this->_client->get_the_poso_com_uti($idspe, $typ);
  }

  public function get_the_poso_text($lstidpos) {
    return $this->_client->get_the_poso_text($lstidpos);
  }

  public function get_the_det_poso_spe($code, $typ) {
    return $this->_client->get_the_det_poso_spe($code, $typ);
  }

  /////////////////////////////////////////////////////
  ////// 15. interactions

  public function get_the_inter_spe($codeid) {
    return $this->_client->get_the_inter_spe($codeid);
  }

  /////////////////////////////////////////////////////
  ////// 17. CIM10
  public function get_the_cim_10($typ,$search) {
    return $this->_client->get_the_cim_10($typ,$search);
  }

  /////////////////////////////////////////////////////
  ////// 18 terrain

  public function get_the_terrain($libtxt,$typ) {
    return $this->_client->get_the_terrain($libtxt,$typ);
  }

  /////////////////////////////////////////////////////
  ////// 19. présentations

  public function get_the_presentation_v2($codeid,$typid) {
    return $this->_client->get_the_presentation_v2($codeid,$typid);
  }

  public function get_the_pre_cdt($codecip,$vartype) {
    return $this->_client->get_the_pre_cdt($codecip,$vartype);
  }

  public function get_the_pre_statut($codecip, $vartyp) {
    return $this->_client->get_the_pre_statut($codecip, $vartyp);
  }

  public function get_the_pre_pri($codecip, $vartyp) {
    return $this->_client->get_the_pre_pri($codecip, $vartyp);
  }

  public function get_the_spe_statut($idspe) {
    return $this->_client->get_the_spe_statut($idspe);
  }

  public function get_the_pre_dsp($codecip, $vartyp) {
    return $this->_client->get_the_pre_dsp($codecip, $vartyp);
  }

  public function get_the_pre_csv($codecip, $vartyp) {
    return $this->_client->get_the_pre_csv($codecip, $vartyp);
  }

  public function get_the_pre_rbt($codecip,$vartype) {
    return $this->_client->get_the_pre_rbt($codecip,$vartype);
  }

  /////////////////////////////////////////////////////
  ////// 20. grossesse allaitement

  public function get_the_gr_fic_spe($codeid) {
    return $this->_client->get_the_gr_fic_spe($codeid);
  }

  public function get_the_al_fic_spe($codeid) {
    return $this->_client->get_the_al_fic_spe($codeid);
  }

  public function get_the_gr_spe($codeid, $typid, $codefic) {
    // NB : ordre des paramètres différents entre WS et PG.
    return $this->_client->get_the_gr_spe($codeid, $codefic, $typid);
  }

  public function get_the_al_spe($codeid, $typid, $codefic) {
    // NB : ordre des paramètres différents entre WS et PG.
    return $this->_client->get_the_al_spe($codeid, $codefic, $typid);
  }

  public function get_the_fpro_spe($codeid, $typid, $codefic) {
    // NB : ordre des paramètres différents entre WS et PG.
    return $this->_client->get_the_fpro_spe($codeid, $codefic, $typid);
  }

  /////////////////////////////////////////////////////
  ////// 21. pharmacocinétique

  public function get_the_cinetique_spe($codeid) {
    return $this->_client->get_the_cinetique_spe($codeid);
  }

  public function get_the_secupreclinique_spe($codeid) {
    return $this->_client->get_the_secupreclinique_spe($codeid);
  }

  public function get_the_ref_secupreclinique($codeid, $codefic) {
    return $this->_client->get_the_ref_secupreclinique($codeid, $codefic);
  }

  /////////////////////////////////////////////////////
  ////// 23. effets indésirables

  public function get_the_effind_spe($codeid, $typid) {
    return $this->_client->get_the_effind_spe($codeid, $typid);
  }

  public function get_the_det_effind($codeid, $typid) {
    return $this->_client->get_the_det_effind($codeid, $typid);
  }

  public function get_the_det_effind_sd($codeid, $typid) {
    return $this->_client->get_the_det_effind_sd($codeid, $typid);
  }

  public function get_the_ref_effind($codeind, $codespe, $typ) {
    return $this->_client->get_the_ref_effind($codeind, $codespe, $typ);
  }

  public function get_the_effind_id($codeid) {
    return $this->_client->get_the_effind_id($codeid);
  }

  /////////////////////////////////////////////////////
  ////// 24. propriétés pharmacodynamie

  public function get_the_det_phdyna($codeid, $typid) {
    return $this->_client->get_the_det_phdyna($codeid, $typid);
  }

  public function get_the_det_etio($codefic, $typeid, $codeid) {
    // NB : ordre des paramètres différents entre WS et PG.
    return $this->_client->get_the_det_etio($codeid, $codefic, $typeid);
  }

  public function get_the_etio_spe($codeid) {
    return $this->_client->get_the_etio_spe($codeid);
  }

  /////////////////////////////////////////////////////
  ////// 25. conducteur / utilisation machines

  public function get_the_fco_id_by_spe($codeid) {
    return $this->_client->get_the_fco_id_by_spe($codeid);
  }

  public function get_the_det_fco($idfco) {
    return $this->_client->get_the_det_fco($idfco);
  }

  /////////////////////////////////////////////////////
  ////// 27. utilitaires

  public function get_the_cdf_to_cim10($cc_cs,$argu, $typ) {
      return $this->_client->get_the_cdf_to_cim10($cc_cs,$argu, $typ);
  }

  /////////////////////////////////////////////////////
  ////// 29. analyse d'ordonnance complète

  public function get_analyse_ordonnance($patient, $prescription, $posologie, $typeAlerteSortie, $natureAlerteCipemg, $niveauGraviteInteraction) {
    $brut = $this->_client->get_analyse_ordonnance($patient, $prescription, $posologie, $typeAlerteSortie, $natureAlerteCipemg, $niveauGraviteInteraction);

    // on formate le retour
    $brut = msTools::objectToArray($brut);
    foreach($brut as $k=>$v) {
      foreach($v as $kk=>$d) {
        if(isset($d['indiceligneprescription']) or isset($d['indiceligneprescription_1'])) {
            $tab[$k][0]=$d;
        } else {
            $tab[$k]=$d;
        }
      }
    }
    // retourner les data d'analyse
    return array(
      'brut'=>$brut,
      'formate'=>$tab
    );
  }

  public function get_the_conducteur($codeid, $typid) {
    return $this->_client->get_the_conducteur($codeid, $typid);
  }

  public function get_the_dopage($codeid, $typid) {
    return $this->_client->get_the_dopage($codeid, $typid);
  }

  /////////////////////////////////////////////////////
  ////// 30 classe ATC d'une spécialité

  public function get_the_atc($codeid) {
    return $this->_client->get_the_atc($codeid);
  }

  /////////////////////////////////////////////////////
  ////// 31 classe EphMra d'une spécialité

  public function get_the_ephmra($codeid) {
    return $this->_client->get_the_ephmra($codeid);
  }

  /////////////////////////////////////////////////////
  ////// 32 statut de prescription / délivrance d'un spécialité

  public function get_the_presdel($codeid, $typid) {
    return $this->_client->get_the_presdel($codeid, $typid);
  }

  /////////////////////////////////////////////////////
  ////// 37 code nature prestation sécurité sociale d'une spécialité

  public function get_the_prestation($codeid, $typid) {
    return $this->_client->get_the_prestation($codeid, $typid);
  }

  /////////////////////////////////////////////////////
  ////// 39. unités possibles pour une spécialité

  public function get_the_unite($codeid, $typid) {
    return $this->_client->get_the_unite($codeid, $typid);
  }

  /////////////////////////////////////////////////////
  ////// 40. sécabilité

  public function get_the_secabilite($codeid) {
    return $this->_client->get_the_secabilite($codeid);
  }

  /////////////////////////////////////////////////////
  ////// 41. description des présentations

  public function get_the_desc_pres($codeid,$typid) {
    return $this->_client->get_the_desc_pres($codeid,$typid);
  }

  /////////////////////////////////////////////////////
  ////// 42. recherche d'hypersensibilité

  public function get_the_allergie($typ,$libcod) {
      return $this->_client->get_the_allergie($typ,$libcod);
  }

  /////////////////////////////////////////////////////
  ////// 44. recherche de DC

  public function get_the_denomination_commune($typid, $var, $dc) {
    return $this->_client->get_the_denomination_commune($typid, $var, $dc);
  }

  /////////////////////////////////////////////////////
  ////// 45. recherche de medicament virtuel père

  public function get_the_med_vir_pere($type, $var, $statut) {
    return $this->_client->get_the_med_vir_pere($type, $var, $statut);
  }

  /////////////////////////////////////////////////////
  ////// 46. recherche de substance active / excipient

  public function get_the_composant($type, $var, $typ_composant) {
    return $this->_client->get_the_composant($type, $var, $typ_composant);
  }

  /////////////////////////////////////////////////////
  ////// 47. prix unitaire estimatif

  public function get_the_prix_unit_est($list_code, $typid) {
    return $this->_client->get_the_prix_unit_est($list_code, $typid);
  }


}
