<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2023
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
 * Utilisation des données de la Base de données publique des médicaments
 * https://base-donnees-publique.medicaments.gouv.fr/
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msBDPM {

  /////////////////////////////////////////////////////
  ////// 1. fonctions administratives

  public function get_the_infos() {
    return $data[0]=array(
		'vers' => 'Base de données publique des médicaments',
		'date_ext' => msSQL::sqlUniqueChamp("SELECT fileLastParse FROM bdpm_updates order by fileLastParse desc limit 1;")
	);
  }

  /////////////////////////////////////////////////////
  ////// 2. les spécialités

  public function get_the_spe_txt($libprod, $monovir) {

	if($monovir == '0') {
		return  msSQL::sql2tab("select
			codeSPE as sp_code_sq_pk,
			etatCommercialisation as statut_lab,
			SUBSTRING_INDEX(denomination, ', ', 1) as sp_nom,
			codeCIS as sp_nl,
			SUBSTRING_INDEX(denomination, ', ', 1) as sp_nomlong,
			monovir as mono_vir,
			codeSPE as sp_gsp_code_fk,
			'' as sp_catc_code_fk,
			'' as sp_cipucd,
			'' as sp_cipucd13
			from bdpm_specialitesVirtuelles
			where monovir = '0' and denomination like '".msSQL::cleanVar($libprod)."'
			order by denomination
			limit 150
			");
	} elseif ($monovir == '1') {
		return  msSQL::sql2tab("select
			codeSPE as sp_code_sq_pk,
			etatCommercialisation as statut_lab,
			SUBSTRING_INDEX(denomination, ', ', 1) as sp_nom,
			codeCIS as sp_nl,
			SUBSTRING_INDEX(denomination, ', ', 1) as sp_nomlong,
			monovir as mono_vir,
			codeSPE as sp_gsp_code_fk,
			'' as sp_catc_code_fk,
			'' as sp_cipucd,
			'' as sp_cipucd13
			from bdpm_specialitesVirtuelles
			where monovir = '1' and denomination like '".msSQL::cleanVar($libprod)."'
			order by denomination
			limit 150
		");
	} elseif ($monovir == '3') {
		return  msSQL::sql2tab("select
			codeSPE as sp_code_sq_pk,
			etatCommercialisation as statut_lab,
			SUBSTRING_INDEX(denomination, ', ', 1) as sp_nom,
			codeCIS as sp_nl,
			SUBSTRING_INDEX(denomination, ', ', 1) as sp_nomlong,
			monovir as mono_vir,
			codeSPE as sp_gsp_code_fk,
			'' as sp_catc_code_fk,
			'' as sp_cipucd,
			'' as sp_cipucd13
			from bdpm_specialitesVirtuelles
			where denomination like '".msSQL::cleanVar($libprod)."'
			order by denomination
			limit 150
		");
	}
  }

  public function get_the_specialite($codeid,$vartyp,$monovir) {
	if($vartyp === 1) {
		return  msSQL::sql2tab("select
			s.codeCIS as sp_code_sq_pk,
			s.etatCommercialisation as statut_lab,
			SUBSTRING_INDEX(s.denomination, ', ', 1) as sp_nom,
			s.codeCIS as sp_nl,
			s.denomination as sp_nomlong,
			s.monovir as mono_vir,
			s.codeSPE as sp_gsp_code_fk,
			'' as sp_catc_code_fk,
			'' as sp_cipucd,
			'' as sp_cipucd13
			from bdpm_specialitesVirtuelles as s
			left join bdpm_groupesGeneriques as g on g.codeCIS = s.codeCIS
			where s.codeSPE = '".msSQL::cleanVar($codeid)."'
			limit 1
		");
	}
  }

  public function get_the_specialite_multi_codeid($codeid,$vartyp,$monovir) {

	if($vartyp === 1) {
		if(!isset($monovir) or $monovir == 0 or $monovir === 0) {
			$monovirSearch = "and monovir = '0'";
		} elseif($monovir == 1 or $monovir === 1) {
			$monovirSearch = "and monovir = '1'";
		} else {
			$monovirSearch = '';
		}
		return  msSQL::sql2tab("select
			s.codeCIS as sp_code_sq_pk,
			s.etatCommercialisation as statut_lab,
			SUBSTRING_INDEX(s.denomination, ', ', 1) as sp_nom,
			s.codeCIS as sp_nl,
			s.denomination as sp_nomlong,
			s.monovir as mono_vir,
			s.codeSPE as sp_gsp_code_fk,
			'' as sp_catc_code_fk,
			'' as sp_cipucd,
			'' as sp_cipucd13
			from bdpm_specialitesVirtuelles as s
			left join bdpm_groupesGeneriques as g on g.codeCIS = s.codeCIS
			where s.codeSPE in (".msSQL::cleanVar($codeid).") ".$monovirSearch."
		");

    } elseif($vartyp == 7) {
		return  msSQL::sql2tab("select
			c.codeCIS as sp_code_sq_pk,
			c.denomination as sp_nom
			from bdpm_compositions as c
			left join bdpm_groupesGeneriques as g on g.codeCIS = c.codeCIS
			where c.codeSubstance in (".msSQL::cleanVar($codeid).")
		");
	}

  }

  public function get_the_voie_spe($codeid) {
	$data = [];
	$data = msSQL::sqlUniqueChamp("select
		voiesAdmin
		from bdpm_specialitesVirtuelles
		where codeSPE = '".msSQL::cleanVar($codeid)."'
	");
	$data = explode(';', $data);
	$retour = [];
	foreach($data as $v) {
		$retour[]=[
			'codevoie' => $v,
			'txtvoie' => $v,
			'voie_abrege' => $v
		];
	}
	return $retour;
  }

  public function get_the_forme_spe($codeid) {

  }

  public function get_the_forme_comp_spe($codeid, $typeid) {

  }

  public function get_the_ref_forme($codeid) {

  }

  public function get_the_forme_txt_spe($codeid) {

  }

  /////////////////////////////////////////////////////
  ////// 3. les laboratoires

  public function get_the_lab_spe($codeid, $typid) {

  }


  /////////////////////////////////////////////////////
  ////// 4. les produits

  public function get_the_pdt_txt($libtxt,$monovir) {

  }

  public function get_the_pdt_id($codeid) {

  }

  /////////////////////////////////////////////////////
  ////// 5. les substances

  public function get_the_sub_txt($libtxt,$vartype) {
	return  msSQL::sql2tab("select
		distinct(c.codeSubstance) as sac_code_sq_pk,
		c.denomination as sac_nom
		from bdpm_compositions as c
		where c.denomination like '".msSQL::cleanVar($libtxt)."'
	");
  }

  public function get_the_sub_id($codeid,$vartype) {

  }

  public function get_the_exp_id($codeid) {

  }

  public function get_the_sub_spe($codeid,$typeid) {
	if($typeid === 2) {
		return  msSQL::sql2tab("select
			'A' as typsubst,
			codeSubstance as codesubst,
			dosage as dosesubst,
			CONCAT(dosage, ' pour ', dosageRef) as udosesubst,
			c.denomination as libsubst,
			CONCAT(denomination, ' ', dosage) as denominationDosage
			from bdpm_compositions as c
			where nature = 'SA' and codeCIS = '".msSQL::cleanVar($codeid)."'
		");
	}
  }

  public function get_the_sub_preccomp_spe($codeid) {

  }

  public function get_the_sub_teneur_spe($codeid) {

  }

  public function get_the_det_exp($codeid, $typid) {

  }

  public function get_the_det_subact($codeid, $typid) {

  }

  /////////////////////////////////////////////////////
  ////// 6. les indications

  public function get_the_ind_txt($libtxt) {

  }

  public function get_the_ind_spe($codeid) {

  }

  public function get_the_det_ind($codeid, $typeid) {

  }

  public function get_the_ref_ind($codeid, $codespe) {

  }

  public function get_the_smr_spe($codeid, $codespe) {

  }

  public function get_the_atr_spe($codefic) {

  }

  /////////////////////////////////////////////////////
  ////// 7. les contre-indications

  public function get_the_det_cipemg($idcipemg, $codeter, $nature, $idseq, $typid) {

  }

  public function get_the_cipemg_spe($codeid, $typid) {

  }

  public function get_the_ref_cipemg($codeid, $idcipemg) {

  }

  /////////////////////////////////////////////////////
  ////// 8 classes ATC

  public function get_the_atc_id($codeid) {

  }

  public function get_the_atc_ddd($codeid, $typid) {

  }

  /////////////////////////////////////////////////////
  ////// 10 classes pharmaco-thérapeutiques

  public function get_the_cph_spe($codeid) {

  }

  /////////////////////////////////////////////////////
  ////// 12. critères de choix

  public function get_the_choix($codeid, $typ) {

  }

  public function get_the_atr_compl($codeav) {

  }

  public function get_the_doc_spe($codefic, $vartyp) {

  }

  /////////////////////////////////////////////////////
  ////// 13. les spécialités génériques

  public function get_the_gen_spe($codeid, $vartyp) {

  }

  /////////////////////////////////////////////////////
  ////// 14. psosologies

  public function get_the_poso($idspe, $lstter) {
    if(empty($lstter)) $lstter='NULL';

  }

  public function get_the_poso_com_uti($idspe, $typ) {

  }

  public function get_the_poso_text($lstidpos) {

  }

  public function get_the_det_poso_spe($code, $typ) {

  }

  /////////////////////////////////////////////////////
  ////// 15. interactions

  public function get_the_inter_spe($codeid) {

  }

  /////////////////////////////////////////////////////
  ////// 17. CIM10

  public function get_the_cim_10($typ,$search) {

  }

  /////////////////////////////////////////////////////
  ////// 18 terrain

  public function get_the_terrain($libtxt,$typ) {

  }

  /////////////////////////////////////////////////////
  ////// 19. présentations

  public function get_the_presentation_v2($codeid,$typid) {

	if($typid == 2 or $typid == 4) {

		return msSQL::sql2tab("select
			p.codeCIP13 as pre_code_pk,
			p.codeCIS as pre_sp_code_fk,
			p.libelle as pre_adm,
			CASE
				WHEN p.statutAdministratif = 'Présentation active' and p.etatCommercialisation = 'Déclaration de commercialisation' THEN 'OUI'
				ELSE 'S'
			END as pre_etat_commer,
			p.dateCommercialisation as pre_datecommer,
			p.codeCIP13 as pre_ean_ref,
			s.formePharma as pre_nat,
			reservhop
			from bdpm_presentationsVirtuelles as p
			left join bdpm_specialites as s on s.codeCIS = p.codeCIS
			where p.codeCIP13 = '".msSQL::cleanVar($codeid)."'
		");
	} else {

		return msSQL::sql2tab("select
			p.codeCIP13 as pre_code_pk,
			p.codeCIS as pre_sp_code_fk,
			p.libelle as pre_adm,
			CASE
				WHEN p.statutAdministratif = 'Présentation active' and p.etatCommercialisation = 'Déclaration de commercialisation' THEN 'OUI'
				ELSE 'S'
			END as pre_etat_commer,
			p.dateCommercialisation as pre_datecommer,
			p.codeCIP13 as pre_ean_ref,
			s.formePharma as pre_nat,
			reservhop
			from bdpm_presentationsVirtuelles as p
			left join bdpm_specialites as s on s.codeCIS = p.codeCIS
			where p.codeSPE = '".msSQL::cleanVar($codeid)."'
		");
	}
  }

  public function get_the_pre_cdt($codecip, $vartyp) {

  }

  public function get_the_pre_statut($codecip, $vartyp) {

  }

  public function get_the_pre_pri($codecip, $vartyp) {

  }

  public function get_the_spe_statut($idspe) {

  }

  public function get_the_pre_dsp($codecip, $vartyp) {

  }

  public function get_the_pre_csv($codecip, $vartyp) {

  }

  public function get_the_pre_rbt($codecip,$vartype) {
	if($vartype == 2) {
		return msSQL::sql2tab("select
			REPLACE(txRembouSS, ' ', '') as info_1,
			indicRembour as texte
			from bdpm_presentationsVirtuelles
			where codeCIP13 = '".msSQL::cleanVar($codecip)."'
		");
	} elseif($vartype == 1) {
		return msSQL::sql2tab("select
			REPLACE(txRembouSS, ' ', '') as info_1,
			indicRembour as texte
			from bdpm_presentationsVirtuelles
			where codeCIP7 = '".msSQL::cleanVar($codecip)."'
		");
	}
  }

  /////////////////////////////////////////////////////
  ////// 20. grossesse allaitement

  public function get_the_gr_fic_spe($codeid) {

  }

  public function get_the_al_fic_spe($codeid) {

  }

  public function get_the_gr_spe($codeid, $typid, $codefic) {

  }

  public function get_the_al_spe($codeid, $typid, $codefic) {

  }

  public function get_the_fpro_spe($codeid, $typid, $codefic) {

  }

  /////////////////////////////////////////////////////
  ////// 21. pharmacocinétique

  public function get_the_cinetique_spe($codeid) {

  }

  public function get_the_secupreclinique_spe($codeid) {

  }

  public function get_the_ref_secupreclinique($codeid, $codefic) {

  }

  /////////////////////////////////////////////////////
  ////// 23. effets indésirables

  public function get_the_effind_spe($codeid, $typid) {

  }

  public function get_the_det_effind($codeid, $typid) {

  }

  public function get_the_det_effind_sd($codeid, $typid) {

  }

  public function get_the_ref_effind($codeind, $codespe, $typ) {

  }

  public function get_the_effind_id($codeid) {

  }

  /////////////////////////////////////////////////////
  ////// 24. propriétés pharmacodynamie

  public function get_the_det_phdyna($codeid, $typid) {

  }

  public function get_the_det_etio($codefic, $typeid, $codeid) {

  }

  public function get_the_etio_spe($codeid) {

  }

  /////////////////////////////////////////////////////
  ////// 25. conducteur / utilisation machines

  public function get_the_fco_id_by_spe($codeid) {

  }

  public function get_the_det_fco($idfco) {

  }

  /////////////////////////////////////////////////////
  ////// 27. utilitaires

  public function get_the_cdf_to_cim10($cc_cs,$argu, $typ) {

  }

  /////////////////////////////////////////////////////
  ////// 29. analyse d'ordonnance complète


  public function get_the_conducteur($codeid, $typid) {
	$dataConducteur = [];
	$dataConducteur[0]['reco']='';
	$dataConducteur[0]['niv']='';
	$dataConducteur[0]['libelle_niv']='';
	return $dataConducteur;
  }

  public function get_the_dopage($codeid, $typid) {
	$dataDopage = [];
	$dataDopage[0]['niveau']='';
    return $dataDopage;
  }

  /////////////////////////////////////////////////////
  ////// 30 classe ATC d'une spécialité

  public function get_the_ephmra($codeid) {

  }

  /////////////////////////////////////////////////////
  ////// 31 classe EphMra d'une spécialité

  public function get_the_atc($codeid) {

  }

  /////////////////////////////////////////////////////
  ////// 32 statut de prescription / délivrance d'un spécialité

  public function get_the_presdel($codeid, $typid) {
	if($typid < 1 ) {
		$tabRetour[0]=array(
			'RH'=>'',
			'PH'=>'',
			'PUH'=>'',
			'SP'=>'',
			'PS'=>''
		);
		if($data = msSQL::sql2tab("select
		`condition`
		from bdpm_conditions
		where codeCIS = '".msSQL::cleanVar($codeid)."';")) {
			foreach($data as $v) {
				if (strpos($v['condition'], 'réservé à l\'usage HOSPITALIER' ) !== false) $tabRetour[0]['RH'] = 'oui';
				if (strpos($v['condition'], 'prescription hospitalière' ) !== false) $tabRetour[0]['PH'] = 'oui';
				if (strpos($v['condition'], 'prescription initiale hospitalière' ) !== false) $tabRetour[0]['PUH'] = 'oui';
				if (strpos($v['condition'], 'surveillance particulière' ) !== false) $tabRetour[0]['SP'] = 'oui';
				if (strpos($v['condition'], 'prescription réservée aux spécialistes' ) !== false) $tabRetour[0]['PS'] = 'oui';
			}
		}

		return $tabRetour;

	}


  }

  /////////////////////////////////////////////////////
  ////// 37 code nature prestation sécurité sociale d'une spécialité

  public function get_the_prestation($codeid, $typid) {

  }

  /////////////////////////////////////////////////////
  ////// 39. unités possibles pour une spécialité

  public function get_the_unite($codeid, $typid) {
	$data=[];
	if($typid == 0) {
		$codeid = msSQL::sqlUniqueChamp("select codeCIS from bdpm_specialitesVirtuelles where codeSPE = '".msSQL::cleanVar($codeid)."' limit 1");
	}

	if($data = msSQL::sql2tab("select
		c.codeCIS as code_spe,
		c.elementPharmaceutique as unite_prise,
		c.denomination as substance
		from bdpm_compositions as c
		where c.codeCIS = '".msSQL::cleanVar($codeid)."'
	")) {
		foreach($data as $k=>$v) {
			$data[$k]['unite_prise'] = $this->_convertUnitePrise($v['unite_prise']);
		}
	}
	return $data;
  }

  private function _convertUnitePrise($txt) {
	$convert = [
		'solution buvable en gouttes'=>'goutte',
		'solution buvable en ampoules'=>'ampoule',
		'solution buvable'=> 'goutte',
		'émulsion'=>'application',
		'crème'=>'application',
		'pommade'=>'application',
		'poudre'=>'application',
		'gel'=>'application',
		'sirop'=>'cuillère mesure',
	];
	if(key_exists($txt,$convert)) {
		return $convert[$txt];
	} else {
		return $txt;
	}
  }

  /////////////////////////////////////////////////////
  ////// 40. sécabilité

  public function get_the_secabilite($codeid) {

  }

  /////////////////////////////////////////////////////
  ////// 41. description des présentations

  public function get_the_desc_pres($codeid,$typid) {

  }

  /////////////////////////////////////////////////////
  ////// 42. recherche d'hypersensibilité

  public function get_the_allergie($typ,$libcod) {

  }

  /////////////////////////////////////////////////////
  ////// 44. recherche de DC

  public function get_the_denomination_commune($typid, $var, $dc) {
	if($typid == 2) {
		if($data = msSQL::sql2tab("select
				g.codeCIS as code,
				g.codeCIS as speRef,
				g.libelle as text_info,
				1 as prescription_dc,
				SUBSTRING_INDEX(g.libelle, ' - ', 1) as libelle
				from bdpm_groupesGeneriques as g
				where g.codeCIS = '".msSQL::cleanVar($var)."' and typeGene = 0
				limit 1")) {
			return $data;
		} elseif($substances = $this->get_the_sub_spe($var, 2)) {
			return $data=array( 0 => array(
				'code' => $var,
				'speRef' => '',
				'prescription_dc' => 1,
				'text_info' => '',
				'libelle' => implode(' + ' , array_column($substances, 'denominationDosage'))
			));
		} else {
			return $data=array( 0 => array(
				'code' => '',
				'speRef' => '',
				'prescription_dc' => 0,
				'text_info' => '',
				'libelle' => ''
			));
		}

	}
  }

  /////////////////////////////////////////////////////
  ////// 45. recherche de medicament virtuel père

  public function get_the_med_vir_pere($type, $var, $statut) {

  }

  /////////////////////////////////////////////////////
  ////// 46. recherche de substance active / excipient

  public function get_the_composant($type, $var, $typ_composant) {

  }


  /////////////////////////////////////////////////////
  ////// 47. prix unitaire estimatif

  public function get_the_prix_unit_est($list_code, $typid) {
	if($typid === 2) {
		return [];

	} elseif($typid === 1) {
		return [];
	} else {
		return msSQL::sql2tab("select
			p.codeCIS as code,
			REPLACE(p.prix1, ',', '.' ) as prix
			from bdpm_presentationsVirtuelles as p
			where p.prix1 != '' and p.codeCIS in (".msSQL::cleanVar($list_code).");
		");
	}

  }


}
