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
      'trace' => 1,
      'encoding' => 'UTF8'
    ));

    return $this->_client=$client;
  }

  /// infos administratives
  public function get_the_infos() {
    return $this->_client->get_the_infos();
  }

  /// specialités
  public function get_the_spe_txt($libprod, $monovir) {
    return $this->_client->get_the_spe_txt($libprod, $monovir);
  }

  public function get_the_specialite($codeid,$vartyp,$monovir) {
      return $this->_client->get_the_specialite($codeid,$vartyp,$monovir);
  }

  public function get_the_specialite_multi_codeid($codeid,$vartyp,$monovir) {
      return $this->_client->get_the_specialite_multi_codeid($codeid,$vartyp,$monovir);
  }

  public function get_the_secabilite($codeid) {
    return $this->_client->get_the_secabilite($codeid);
  }

  public function get_the_voie_spe($codeid) {
    return $this->_client->get_the_voie_spe($codeid);
  }

  public function get_the_gen_spe($codeid, $vartyp) {
    return $this->_client->get_the_gen_spe($codeid, $vartyp);
  }

  public function get_the_unite($codeid, $typid) {
    return $this->_client->get_the_unite($codeid, $typid);
  }

  /// Présentations
  public function get_the_presentation_v2($codeid,$typid) {
    return $this->_client->get_the_presentation_v2($codeid,$typid);
  }

  public function get_the_pre_rbt($codecip,$vartype) {
    return $this->_client->get_the_pre_rbt($codecip,$vartype);
  }

  public function get_the_desc_pres($codeid,$typid) {
    return $this->_client->get_the_desc_pres($codeid,$typid);
  }

  /// Produits
  public function get_the_pdt_txt($libtxt,$monovir) {
    return $this->_client->get_the_pdt_txt($libtxt,$monovir);
  }

  /// DCI
  public function get_the_denomination_commune($typid, $var, $dc) {
    return $this->_client->get_the_denomination_commune($typid, $var, $dc);
  }

  /// Substance
  public function get_the_sub_txt($libtxt,$vartype) {
    return $this->_client->get_the_sub_txt($libtxt,$vartype);
  }

  /// CIM 10
  public function get_the_cim_10($typ,$search) {
    return $this->_client->get_the_cim_10($typ,$search);
  }

  public function get_the_cdf_to_cim10($cc_cs,$argu, $typ) {
      return $this->_client->get_the_cdf_to_cim10($cc_cs,$argu, $typ);
  }

  /// Allergies
  public function get_the_allergie($typ,$libcod) {
      return $this->_client->get_the_allergie($typ,$libcod);
  }

  /// Terrains
  public function get_the_terrain($libtxt,$typ) {
    return $this->_client->get_the_terrain($libtxt,$typ);
  }

  //classe ATC
  public function get_the_atc_id($codeid) {
    return $this->_client->get_the_atc_id($codeid);
  }

  //medic virtuel père
  public function get_the_med_vir_pere($type, $var, $statut) {
    return $this->_client->get_the_med_vir_pere($type, $var, $statut);
  }

  // Prix
  public function get_the_prix_unit_est($list_code, $typid) {
    return $this->_client->get_the_prix_unit_est($list_code, $typid);
  }

  // Sécurité sociale prestations
  public function get_the_prestation($codeid, $typid) {
    return $this->_client->get_the_prestation($codeid, $typid);
  }
}
