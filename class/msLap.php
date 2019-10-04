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
 * LAP : basiques pour la sortie d'infos de Thériaque
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msLap
{


 /**
  * @var int $_toID ID de l'individus concerné
  */
     protected $_toID;

 /**
  * @var int $_fromID ID de l'utilisteur enregistrant la donnée
  */
     protected $_fromID;
  /**
   * @var object $_the Instance Thériaque
   */
    protected $_the;
    protected $_classTheriaque;

/**
 * liste des codes spécialité trouvés
 * @var array
 */
    protected $_listeCodeSpeTrouve=[];

 /**
  * Constructeur : choix du fonctionnement
  */
     public function __construct()
     {
         global $p;
         if ($p['config']['theriaqueMode']=='WS') {
             $this->_classTheriaque='msTheriaqueWS';
         } elseif ($p['config']['theriaqueMode']=='PG') {
             $this->_classTheriaque='msTheriaquePG';
         }
         $this->_the = new $this->_classTheriaque;
     }

 /**
  * Définir l'individu concerné
  * @param int $v ID de l'individu concerné
  * @return int toID
  */
     public function setToID($v)
     {
         if (msPeople::checkPeopleExist($v)) {
             $patient= new msPeople;
             $patient->setToID($v);
             $this->_patientAdminData=$patient->getSimpleAdminDatasByName();
             $this->_patientAdminData['age'] = DateTime::createFromFormat('d/m/Y', $this->_patientAdminData['birthdate'])->diff(new DateTime('now'))->y;
             return $this->_toID = $v;
         } else {
             throw new Exception('ToID does not exist');
         }
     }

 /**
  * Définir l'utilisateur qui enregsitre la donnée
  * @param int $v ID de l'utilisateur
  * @return int fromID
  */
     public function setFromID($v)
     {
         if (msPeople::checkPeopleExist($v)) {
             return $this->_fromID = $v;
         } else {
             throw new Exception('FromID does not exist');
         }
     }

/**
 * Obtenir la liste des codes spécialité trouvés pendant la recherche
 * @return array tableau des codes
 */
     public function getListeCodeSpeTrouve() {
       return $this->_listeCodeSpeTrouve;
     }

/**
 * Obtenir les infos légales de la base Thériaque utilisée
 * @return array retourne vers (version) et date_ext (date extraction de la base)
 */
    public function getTheriaqueInfos()
    {
        if ($data=$this->_the->get_the_infos()) {
            $data=$this->_prepareData($data);
            return $data;
        } else {
            return false;
        }
    }

/**
 * Obtenir la DC à partir de la spécialité dont info prescriptibilité en DC ou non
 * @param  int $typid type de recherche (1 : toutes les DC, 2: par code, 3: libellé)
 * @param  string $var   chaine/code à recherche
 * @param  int $dc    param de sélecction (0 : uniquement prescriptible en DC, 1: tout)
 * @return array        array de retour de la DC
 */
    public function getDC($typid, $var, $dc)
    {
        if ($data=$this->_the->get_the_denomination_commune($typid, $var, $dc)) {
            $data=$this->_prepareData($data);
            return $data;
        } else {
            return false;
        }
    }

/**
 * Obtenir des médicaments en recherchant en DC
 * @param  string $txt mot clé de recherche
 * @return array      tableau de retour
 */
    public function getMedicInDC($txt)
    {
        if (strlen($txt)>=3) {
            $rd=[];

            if ($data=$this->_the->get_the_denomination_commune(3, $txt.'%', 0)) {
                // 1 résultat
          if (isset($data->item->code)) {
              $rd[0]=(array)$data->item;
          }
          // plusieurs
          elseif (isset($data->item)) {
              foreach ($data->item as $k=>$v) {
                  $rd[$k]=(array)$v;
              }
          }
                if (!empty($rd)) {
                    $this->attacherPrixMedic($rd, 'code');
                    $this->getStatutDelivrance($rd, 'code');
                }
                return $rd;
            }
        }
    }

/**
 * Obtenir les détails d'une spécialité à partir de son code
 * @param  string $codeid  code spe
 * @param  int $vartyp  code nature de recherche
 * @param  int $monovir code monovir (0 : classique, 1 virtuelle, 3 sans filtre)
 * @return array          tableau de la spé
 */
public function getSpecialiteByCode($codeid, $vartyp, $monovir)
{
    $data=$this->_the->get_the_specialite($codeid, $vartyp, $monovir);
    $data=$this->_prepareData($data);
    return $data;
}

/**
 * Obtenir des spécialités à partir d'une lite de codes
 * @param  string $codeid  codes des spe
 * @param  int $vartyp  code nature de recherche
 * @param  int $monovir code monovir (0 : classique, 1 virtuelle, 3 sans filtre)
 * @return array          tableau des spés
 */
public function getSpecialitesByCodes($codeid, $vartyp, $monovir)
{
    $data=$this->_the->get_the_specialite_multi_codeid($codeid, $vartyp, $monovir);
    $data=$this->_prepareData($data);
    return $data;
}

/**
 * Informations sur la sécabilité
 * @param  string $codeid code spécialité
 * @return array         tableau sécabilité
 */
public function getSpeSecabiliteByCode($codeid)
{
    $data=$this->_the->get_the_secabilite($codeid);
    $data=$this->_prepareData($data);
    return $data;
}

/**
 * Informations sur les unités de prise
 * @param  string $codeid code
 * @param  string $typid  type du code
 * @return array         tableau des unités
 */
public function getUnite($codeid, $typid)
{
    $data=$this->_the->get_the_unite($codeid, $typid);
    $data=$this->_prepareData($data);
    return $data;
}

/**
 * Informations sur les unités de prise via description de présentations
 * @param  string $codeid code
 * @param  string $typid  type du code (0: The, 1: CIP7, 2: CIP13)
 * @return array         tableau des unités
 */
public function getUniteViaPres($codeid, $typid)
{
    $data=$this->_the->get_the_desc_pres($codeid, $typid);
    $data=$this->_prepareData($data);
    return $data;
}

/**
 * Retrouver le générique
 * @param  string $codeid code spécialité
 * @param  string $vartyp  type du code
 * @return array         tableau des unités
 */
public function getGenerique($codeid, $vartyp)
{
    $data=$this->_the->get_the_gen_spe($codeid, $vartyp);
    $data=$this->_prepareData($data);
    return $data;
}

/**
 * Informations sur les voie d'administration
 * @param  int $codeid code spécialité thériaque
 * @return array         tableau des voies
 */
public function getVoiesAdministration($codeid)
{
    $data=$this->_the->get_the_voie_spe($codeid);
    $data=$this->_prepareData($data);
    return $data;
}

/**
 * Obtenir les présentation d'une spécialité
 * @param  array $rd      tableau des médicaments
 * @param  string $colCode colonne du tableau où trouver le code theriaque
 * @param  string $typCode type du code passé (1 code thériaque cf doc)
 * @return array          tableau d'entrée modifié
 */
public function getPresentations(&$rd, $colCode, $typCode)
{
    global $p;
    foreach ($rd as $k=>$v) {

        //liste des codes spé traités
        if(!in_array($v[$colCode], $this->_listeCodeSpeTrouve)) $this->_listeCodeSpeTrouve[]=$v[$colCode];

        $rd[$k]['presentations']=$this->_get_the_presentation($v[$colCode], $typCode);
        if (!empty($rd[$k]['presentations'])) {
            foreach ($rd[$k]['presentations'] as $presK=>$presV) {

                // on se débarasse des médic hospitaliers si ...
                if ($p['config']['theriaqueShowMedicHospi'] == 'false' and $presV['reservhop'] != 'NON') {
                    unset($rd[$k]['presentations'][$presK]);
                    continue;
                }
                // on se débarasse des non commercialisés si ...
                if ($p['config']['theriaqueShowMedicNonComer'] == 'false' and $presV['pre_etat_commer'] == 'S') {
                    unset($rd[$k]['presentations'][$presK]);
                    continue;
                }

                $rd[$k]['presentations'][$presK]['rbtVille']=$this->_get_the_pre_rbt($presV['pre_ean_ref'], 2)['rbtVille'];
            }
        }

        // on se débarasse de la spécialité si elle ne contient plus de présentation
        if (empty($rd[$k]['presentations'])) {
            unset($rd[$k]);
        }
    }
}

/**
 * Obtenir les présentations
 * @param  string $codesTheriaque    code thériaque
 * @param  string $typCode             type du code 0 : theriaque ... (cf doc Theriaque)
 * @return array                      tableau
 */
protected function _get_the_presentation($codeTheriaque, $typCode)
{
    $rd=[];
    if ($data=$this->_the->get_the_presentation_v2($codeTheriaque, $typCode)) {
        $rd=$this->_prepareData($data);
        return $rd;
    }
}

/**
 * Obtenir le % de remboursement sécu en ville
 * @param  string $code   code CIP de la présentation
 * @param  int $typCode type du code (1 CIP7, 2 CIP13)
 * @return string          % de rbt
 */
    private function _get_the_pre_rbt($code, $typCode)
    {
        if (empty($code)) {
            return false;
        }
        $rd=[];
        if ($data=$this->_the->get_the_pre_rbt($code, $typCode)) {
            if (is_object($data)) {
                $data=msTools::objectToArray($data);
                if (isset($data['item'])) {
                    $data=$data['item'];
                }
            }
            if (!empty($data)) {
                $tab['rbtVille'] = '';
                $ou=array_column($data, 'type');
                if (isset($data[array_search('1', $ou)]['info_1'])) {
                    $tab['rbtVille'] = $data[array_search('1', $ou)]['info_1'];
                }

                return $tab;
            }
            return false;
        }
    }


/**
 * Obtenir des substances par mot clé.
 * @param  string $txt mot clé de recherche
 * @return array      tableau de retour
 */
    public function getSubstances($txt, $type)
    {
        if (strlen($txt)>=3) {
            $rd=[];
            if ($data=$this->_the->get_the_sub_txt($txt, $type)) {
                $rd=$this->_prepareData($data);
                if (!empty($rd)) {
                    return $rd;
                }
            }
        }
    }

/**
 * Obtenir des substances par code spécialité.
 * @param  int $codeid code spé
 * @param  int $typeid infos à retourner
 * @return array         array des substances
 */
    public function getSubstancesBySpe($codeid, $typeid)
    {
        $rd=[];
        if ($data=$this->_the->get_the_sub_spe($codeid,$typeid)) {
            $rd=$this->_prepareData($data);
            if (!empty($rd)) {
                return $rd;
            }
        }
      }

/**
 * Obtenir un tableau récapitulatif simplifié des substances actives
 * @param  int $codeSpe code spécialité
 * @return array           array code -> label
 */
    public function getSubtancesActivesTab($codeSpe) {
      if($data = $this->getSubstancesBySpe($codeSpe, 2)) {
        foreach($data as $k=>$v) {
          if(substr($v['typsubst'], -1) == 'A') {
            $tab[$v['codesubst']] = $v['libsubst'];
          }
        }
        return $tab;
      }
    }

/**
 * Obtenir les informations dopage
 * @param  int $codeid code
 * @param  int $typid  type du code
 * @return array         array des infos dopage
 */
    public function getDopage($codeid, $typid) {
      if ($data=$this->_the->get_the_dopage($codeid, $typid)) {
          $rd=$this->_prepareData($data);
          if (!empty($rd)) {
              return $rd;
          }
      }
    }

/**
 * Obtenir les infos de conduite
 * @param  int $codeid code
 * @param  int $typid  type du code
 * @return array         array des infos conducteur
 */
    public function getConducteur($codeid, $typid) {
      if ($data=$this->_the->get_the_conducteur($codeid, $typid)) {
          $rd=$this->_prepareData($data);
          if (!empty($rd)) {
              return $rd;
          }
      }
    }

/**
 * Obtenir les informations de dispensation
 * @param  string $codecip code cip
 * @param  int $vartyp  type du code cip : 1 = cip7, 2 = cip13
 * @return array           array des infos
 */
    public function getDispensation($codecip, $vartyp) {
      if ($data=$this->_the->get_the_pre_dsp($codecip, $vartyp)) {
          $rd=$this->_prepareData($data);
          if (!empty($rd)) {
              return $rd;
          }
      }
    }

/**
 * Obtenir les indications sur une recherche texte en vue d'obtenir ensuite
 * les codes spés de l'indication
 * @param  string $txt texte de recherche d'indication
 * @return [type]      [description]
 */
  public function getIndicsByTxt($txt, $sel='') {
    $rd=[];
    if (strlen($txt)>=3) {
      if ($data=$this->_the->get_the_ind_txt($txt.'%')) {
        $fiches=$this->_prepareData($data);
        foreach($fiches as $fiche) {
          $lib=$this->_the->get_the_det_ind($fiche['codeind'], 7);
          $lib=$this->_prepareData($lib);
          $rd[$lib[0]['libcourt']][]=$lib[0]['codedoc'];
        }
        ksort($rd);
      }
    }
    return $rd;
  }

/**
 * Obtenir des médicaments via substance
 * @param  string $txt mot clé de recherche
 * @param  string $type 1 : sa / 2 : excipient / 0 : sa + excipient
 * @param  string $monovir type de recherche 0 : spé / 1 : dci / 3 : tout
 * @return array      tableau de retour
 */
    public function getMedicBySub($txt, $type, $monovir)
    {
        global $p;
        if (strlen($txt)>=3) {
          $subsTab=[];
          if($intersectionTab=$this->getCodesSpesListBySub ($txt, $type, $monovir)) {
            if ($data=$this->_the->get_the_specialite_multi_codeid(implode(",", $intersectionTab), 1, $monovir)) {
                $rd=$this->_prepareData($data);
                if (!empty($rd)) {
                    $this->getPresentations($rd, 'sp_code_sq_pk', 1);
                    $this->attacherPrixMedic($rd, 'sp_code_sq_pk');
                    $this->getStatutDelivrance($rd, 'sp_code_sq_pk');
                }
                $rd['substances']=$subsTab;
                return $rd;
            }
          }
        }
    }

/**
 * Obtenir une liste de codes spécialités qui contiennent plusieurs substances
 * @param  string $txt     texte de recherche ( séparateur : +)
 * @param  string $type    $type 1 : sa / 2 : excipient / 0 : sa + excipient
 * @param  string $monovir type de recherche 0 : spé / 1 : dci / 3 : tout
 * @return array          tableau des codes spécialité
 */
    public function getCodesSpesListBySub ($txt, $type, $monovir) {
      if (strlen($txt)>=3) {
        global $p;
          if ($p['config']['theriaqueMode'] == 'WS') {
              $colonne = 'code_sq_pk';
          } elseif ($p['config']['theriaqueMode'] == 'PG') {
              $colonne = 'sac_code_sq_pk';
          }


          $substances = explode('+',$txt);
          if(!empty($substances)) {
            foreach($substances as $k=>$substance) {
              $subsTab=$this->getSubstances(trim($substance), $type);

              if(empty($subsTab)) continue;
              $subs=implode(",", array_column($subsTab, $colonne));
              $tabSpe=$this->_the->get_the_specialite_multi_codeid($subs, 7, $monovir);
              if(!empty($tabSpe)) {
                $tabSpe=$this->_prepareData($tabSpe);
                $tabSpes[$k]=array_column($tabSpe, 'sp_code_sq_pk');
              }
            }
          }
          if(!empty($tabSpes)) {
            if(count($tabSpes)>1) {
              $intersectionTab = call_user_func_array('array_intersect', $tabSpes);
            } else {
              $intersectionTab=$tabSpes[0];
            }
            return $intersectionTab;
          }
          return false;
        }
        return false;
    }

/**
 * Obtenir des médicaments par recherche texte sur le nom
 * @param  string $txt mot clé de recherche
 * @param  string $monovir type de recherche 0 : spé / 1 : dci / 3 : tout
 * @return array      tableau de retour
 */
    public function getMedicByName($txt, $monovir)
    {
        if (strlen($txt)>=3) {
            $rd=[];
            if ($data=$this->_the->get_the_spe_txt($txt, $monovir)) {
                $rd=$this->_prepareData($data);
                // natural sorting => confié maintenant à jquey stupid table
                //msTools::array_natsort_by('sp_nom', $rd);
                if (!empty($rd)) {
                    $this->getPresentations($rd, 'sp_code_sq_pk', 1);
                    $this->attacherPrixMedic($rd, 'sp_code_sq_pk');
                    $this->getStatutDelivrance($rd, 'sp_code_sq_pk');
                }
                return $rd;
            }
        }
    }

/**
 * Obtenir des médicaments via codes indications
 * @param  string $classe classe de recherche
 * @param  string $monovir type de recherche 0 : spé / 1 : dci / 3 : tout
 * @return array      tableau de retour
 */
    public function getMedicByCodeIndic($classe, $monovir)
    {
        if (strlen($classe)>=1) {
            $rd=[];
            if ($data=$this->_the->get_the_specialite_multi_codeid($classe, 6, $monovir)) {
                $rd=$this->_prepareData($data);
                if (!empty($rd)) {
                    $this->getPresentations($rd, 'sp_code_sq_pk', 1);
                    $this->attacherPrixMedic($rd, 'sp_code_sq_pk');
                    $this->getStatutDelivrance($rd, 'sp_code_sq_pk');
                }
                return $rd;
            }
        }
    }

/**
 * Obtenir des médicaments via classe atc
 * @param  string $classe classe de recherche
 * @param  string $monovir type de recherche 0 : spé / 1 : dci / 3 : tout
 * @return array      tableau de retour
 */
    public function getMedicByATC($classe, $monovir)
    {
        if (strlen($classe)>=1) {
            $rd=[];
            if ($data=$this->_the->get_the_specialite_multi_codeid($classe.'%', 10, $monovir)) {
                $rd=$this->_prepareData($data);
                if (!empty($rd)) {
                    $this->getPresentations($rd, 'sp_code_sq_pk', 1);
                    $this->attacherPrixMedic($rd, 'sp_code_sq_pk');
                    $this->getStatutDelivrance($rd, 'sp_code_sq_pk');
                }
                return $rd;
            }
        }
    }

public function getResultatsDet($listeCodes) {
    if($spe=$this->getSpecialitesByCodes($listeCodes,1,3)) {
      foreach($spe as $k=>$v) {
        $spe[$k]['suba']=$this->_prepareData($this->_the->get_the_sub_spe($v['sp_code_sq_pk'], 2));
        $spe[$k]['sube']=$this->_prepareData($this->_the->get_the_sub_spe($v['sp_code_sq_pk'], 1));
      }

      if (!empty($spe)) {
          $this->getPresentations($spe, 'sp_code_sq_pk', 1);
          $this->attacherPrixMedic($spe, 'sp_code_sq_pk');
          $this->getStatutDelivrance($spe, 'sp_code_sq_pk');
      }
    }
    return $spe;
}

/**
 * Attacher le prix par UCD de chaque médic à son tableau
 * @param  array $tabMedic   tableau des médic
 * @param  string $col       colonne du tableau d'entrée qui contient le code thériaque du medoc
 * @return array            array d'entrée avec prixEstim
 */
public function attacherPrixMedic(&$tabMedic, $col)
{
    if (is_array($tabMedic)) {
        $codesTheriaque=array_column($tabMedic, $col);
        $codesTheriaqueListe=implode(',', $codesTheriaque);

        if ($data=$this->_get_the_prix_unit_est($codesTheriaqueListe, 0)) {
            $prixParUcd=array_column((array)$data, 'prix', 'code');
            foreach ($tabMedic as $k=>$v) {
                if (isset($prixParUcd[$v[$col]])) {
                    $tabMedic[$k]['prixEstim']=$prixParUcd[$v[$col]];
                }
            }
        }
    }
}

private function getStatutDelivrance(&$tabMedic, $col) {
  if (is_array($tabMedic)) {
    foreach($tabMedic as $k=>$v) {
      $data=$this->_the->get_the_presdel($v[$col],0);
      if(!empty($data)) {
        $data = $this->_prepareData($data);
        $tabMedic[$k]['statutDelivrance'] = $data[0];
      }
    }
  }
}

/**
 * Obtenir les fiches posologies
 * @param  string $codesFiche code(s) des fiches
 * @return array             fiches
 */
public function getFichePosologies($codesFiche)
{
    $rd=[];
    if ($data=$this->_the->get_the_poso_text($codesFiche)) {
        $rd=$this->_prepareData($data);
    }
    return $rd;
}

/**
 * Obtenir les terrains / voie / id des fiches posologiques
 * @param  string $codeSpe      code spécialité
 * @param  string $codesTerrain codes terrain
 * @return array               tableau terrains + voie > indications
 */
public function getIndicationsPosologies($codeSpe, $codesTerrain='')
{
    $rd=[];
    if ($data=$this->_the->get_the_poso($codeSpe, $codesTerrain)) {
        $rd=$this->_prepareData($data);

        $tabVoies=[];
        $cleindication=-1;
      //tt primaire des données
      foreach ($rd as $t) {
          if ($t['typ']==0 or $t['typ']=='') {
              $fichesPoso[$t['nofic']]['ficheID']=$t['nofic'];
          } elseif ($t['typ']==1) {
              if (!in_array($t['info_01'], $tabVoies)) {
                  $tabVoies[]=$t['info_01'];
              }
              $cleVoie=array_search($t['info_01'], $tabVoies);
              $fichesPoso[$t['nofic']]['voies'][$cleVoie]=$t['info_01'];
          } elseif ($t['typ']==2) {
              $fichesPoso[$t['nofic']]['terrains'][$t['valeur_01']][$t['valeur_02']]=$t['info_01'];
          } elseif ($t['typ']==3) {
              //if(!empty($t['info_01'])) {
            $cleindication++;
              $fichesPoso[$t['nofic']]['indications'][$cleindication]=$t['info_01'];
          //}
          } elseif ($t['typ']==4) {
              $fichesPoso[$t['nofic']]['indications'][$cleindication].= ' '.$t['info_01'];
          } elseif ($t['typ']==5) {
              $fichesPoso[$t['nofic']]['references'][]=$t['info_01'];
          }
      }
      // mise en ligne des terrains
      foreach ($fichesPoso as $idfiche=>$val) {
          foreach ($val['terrains'] as $k=>$v) {
              $fichesPoso[$idfiche]['terrains'][$k] = implode(' ', $v);
          }
      }

        foreach ($fichesPoso as $idfiche=>$val) {
            foreach ($val['terrains'] as $terrain) {
                foreach ($val['voies'] as $voie) {
                    foreach ($val['indications'] as $indication) {
                        $tab[$terrain.' - VOIE '.$voie][$indication][]=$idfiche;
                        ksort($tab[$terrain.' - VOIE '.$voie]);
                    }
                }
            }
        }
        ksort($tab);
        return $tab;
    }
}

/**
 * Obtenir le prix unitaire estimatif
 * @param  string $codesTheriaqueListe liste des codes séparé par virgule
 * @param  string $typCode             type du code 0 : theriaque / 1 : UCD7 / 2 : UCD 13
 * @return array                      tableau
 */
private function _get_the_prix_unit_est($codesTheriaqueListe, $typCode)
{
    $rd=[];
    if ($data=$this->_the->get_the_prix_unit_est($codesTheriaqueListe, $typCode)) {
        $rd=$this->_prepareData($data);
        return $rd;
    }
}

/**
 * Attacher le remboursement Sésu de chaque médic à son tableau
 * @param  array $tabMedic   tableau des médic
 * @param  string $col       colonne du tableau d'entrée qui contient le code thériaque du medoc
 * @return array            array d'entrée avec codePrestaSecu
 */
public function attacherPrestaSecuMedic(&$tabMedic, $col)
{
    if (is_array($tabMedic)) {
        $codesTheriaque=array_column($tabMedic, $col);
        $codesTheriaqueListe=implode(',', $codesTheriaque);

        if ($data=$this->_get_the_prix_unit_est($codesTheriaqueListe, 0)) {
            $prixParUcd=array_column((array)$data, 'cpss', 'cip13');
            foreach ($tabMedic as $k=>$v) {
                if (isset($prixParUcd[$v[$col]])) {
                    $tabMedic[$k]['codePrestaSecu']=$prixParUcd[$v[$col]];
                }
            }
        }
    }
}

/**
 * Obtenir le code prestation sécu
 * @param  string $codesTheriaqueListe liste des codes séparé par virgule
 * @param  string $typCode             type du code 0 : theriaque ... (cf doc Theriaque)
 * @return array                      tableau
 */
private function _get_the_prestation($codesTheriaqueListe, $typCode)
{
    $rd=[];
    if ($data=$this->_the->get_the_prestation($codesTheriaqueListe, $typCode)) {
        $rd=$this->_prepareData($data);
        return $rd;
    }
}

/**
 * Recherche de code / libelle CIM 10 par mot clé
 * @param  string $txt mot clé
 * @return array      résultats de recherche
 */
    public function getCIM10fromKeywords($txt)
    {
        if (strlen($txt)>=3) {
            $rd=[];
            if ($data=$this->_the->get_the_cim_10(3, $txt)) {
                $data=$this->_prepareData($data);
          // 1 résultat
          if (isset($data['item']['code'])) {
              $rd[(string)$data['item']['code']]=(string)$data['item']['libelle_long'];
              return $rd;
          }
          // plusieurs
          elseif (isset($data[0])) {
              foreach ($data as $k=>$d) {
                  if (isset($d['code'], $d['libelle_long'])) {
                      $rd[$d['code']]=$d['libelle_long'];
                  }
              }
          }
            }
            return $rd;
        }
    }

/**
 * Obtenir le libelle long CIM 10 via son code
 * @param  string $code code CIM 10
 * @return string       libelle CIM 10
 */
    public function getCIM10LabelFromCode($code)
    {
        if ($data=$this->_the->get_the_cim_10(2, $code)) {
            if (isset($data->item)) {
                return (string)$data->item->libelle_long;
            }
        }
        return false;
    }

/**
 * Recherche de code / libelle Allergie par mot clé
 * @param  string $txt mot clé
 * @return array      résultats de recherche
 */
    public function getAllergieFromKeywords($txt)
    {
        if (strlen($txt)>=3) {
            $rd=[];
            if ($data=$this->_the->get_the_allergie(3, $txt)) {
                $data=$this->_prepareData($data);
                // 1 résultat
                if (isset($data['item']['code'])) {
                    $rd[(string)$data['item']['code']]=(string)$data['item']['libelle'];
                    return $rd;
                }
                // plusieurs
                elseif (isset($data[0])) {
                    foreach ($data as $k=>$d) {
                        if (isset($d['code'], $d['libelle'])) {
                            $rd[$d['code']]=$d['libelle'];
                        }
                    }
                }
            }
            return $rd;
        }
    }

public function getEffetsIndesirables($codeSpe, $typeEI) {
  $rd=[];
  if ($data=$this->_the->get_the_effind_spe($codeSpe, $typeEI)) {
      return $this->_prepareData($data);
  }
}


/**
 * Transformer un objet en array avec correction du niveau si 1 seul retour
 * @param  array $data array d'entrée
 * @return array       array de sortie
 */
    protected function _prepareData($data)
    {
        $rd=[];
        if (empty((array) $data)) return $rd;
        if (is_object($data)) {
            $data=msTools::objectToArray($data);
            if (isset($data['item']['0'])) {
                $rd=$data['item'];
            } elseif (isset($data['item'])) {
                $rd[0]=$data['item'];
            } else {
                $rd[0]=$data;
            }
        } else {
            $rd=$data;
        }
        return $rd;
    }
}
