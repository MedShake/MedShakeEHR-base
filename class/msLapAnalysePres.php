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
 * LAP : analyse des prescriptions
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msLapAnalysePres extends msLap
{
    /**
     * objet patient pour analyseur Thériaque
     * @var array
     */
    private $_objetPatient;
    private $_patientPhysioControleData;
    /**
     * Contenu de l'ordonnance courante
     * @var array
     */
    private $_ordonnanceContenu;
    /**
     * Contenu du traitement en cours
     * @var array
     */
    private $_ttEnCoursContenu;
    /**
     * Date de début de l'ordonnance en cours
     * @var object
     */
    private $_dateBorneDebutOrdo;
    /**
     * Date de fin de l'ordonnance en cours
     * @var object
     */
    private $_dateBorneFinOrdo;
    /**
     * Zone en cours (ALD, G, TTChro, TTponc)
     * @var string
     */
    private $_zoneOrdo;
    /**
     * Index ligne de prescription en cours dans la zone G ou ALD
     * @var int
     */
    private $_indexLignePresOrdo;
    /**
     * Index du médicament en en cours dans la ligne
     * @var [type]
     */
    private $_indexMedicPresOrdo;
    /**
     * Index de la poso Thériaque du medic
     * @var [type]
     */
    private $_indexMedicPosoPresOrdo;

    /**
     * Index toute zone des lignes de prescription de l'ordo
     * @var int
     */
    private $_indexAbsoluLignePresOrdo;
    /**
     * Objet prescriptions pour analyseur Thériaque
     * @var array
     */
    private $_objetPrescription;
    /**
     * Objet posologie pour analyseur Thériaque
     * @var [type]
     */
    private $_objetPosoPres;
    /**
     * Correspondance entre index passé à Thériaque et ordo
     * @var array
     */
    private $_correspondanceLignes;
    /**
     * Retour d'analyse brut de Thériaque
     * @var array
     */
    private $_analyseResults;
    /**
     * Retour d'analyse retravaillé (niveau des sub array)
     * @var array
     */
    private $_analyseResultsReformate;
    /**
     * Date de départ pour l'incrémentation sur ligne posologique d'un medic
     * @var [type]
     */
    private $_dateDepart;
    /**
     * Alertes sur les redondances
     * @var array
     */
    private $_alertesRedondances;
    /**
     * Alertes sur les posologies
     * @var array
     */
    private $_alertesPosologies;
    /**
     * Alertes sur les allergies
     * @var array
     */
    private $_alertesAllergies;
    /**
     * Alertes CIPEMG
     * @var array
     */
    private $_alertesCIPEMG;
    /**
     * Alertes grossesse et allaitement
     * @var [type]
     */
    private $_alertesGrossesse;
    /**
     * Alertes interactions
     * @var [type]
     */
    private $_alertesInteractions;
    /**
     * Alertes incompatibilités physico-chimiques
     * @var [type]
     */
    private $_alertesIncompatibilites;

    private $_alertesDopageEtConducteur;

    private $_tabLignesRisqueAllergiques;

    private $_ligneObjetIdDejaVus;

/**
 * Set the value of patient objet
 * @param mixed _ordonnanceContenu
 * @return self
 */
    public function setObjetPatient($_objetPatient)
    {
        $this->_objetPatient = $_objetPatient;
        return $this->_objetPatient;
    }
    public function setPatientPhysioControleData($_patientPhysioControleData)
    {
        $this->_patientPhysioControleData = $_patientPhysioControleData;
        return $this->_patientPhysioControleData;
    }

/**
 * Set the value of Ordonnance Contenu
 * @param mixed _ordonnanceContenu
 * @return self
 */
    public function setOrdonnanceContenu($_ordonnanceContenu)
    {
        $this->_ordonnanceContenu = $_ordonnanceContenu;
        //on sort les dates bornes de l'ordo.
        $this->_getDatesBornesOrdoEnCours();
        //on fixe l'index absolu à zéro
        $this->_indexAbsoluLignePresOrdo=0;
        return $this->_ordonnanceContenu;
    }
/**
 * Obtenir l'objet prescriptions
 * @return array tableau prescriptions
 */
    public function getObjetPrescription() {
      return $this->_objetPrescription;
    }
/**
 * Obtenir l'objet patient
 * @return array tableau patient
 */
    public function getObjetPatient() {
      return $this->_objetPatient;
    }
/**
 * Obtenir l'objet posologies
 * @return array tableau posologies
 */
    public function getObjetPosoPres() {
      return $this->_objetPosoPres;
    }
/**
 * Obtenir le tableau correspondance des lignes
 * @return array tableau de correspondance
 */
    public function getCorrespondanceLignes() {
      return $this->_correspondanceLignes;
    }
/**
 * Obtenir les résultats bruts d'analyse Thériaque
 * @return [type] [description]
 */
    public function getBrutAnalyseResults() {
      return $this->_analyseResults;
    }
/**
 * Obtenir les résultats reformatés d'analyse Thériaque
 * @return [type] [description]
 */
    public function getFormateAnalyseResults() {
      return $this->_analyseResultsReformate;
    }

/**
 * Générer le html sur le retour d'analyse
 * @return string html
 */
    public function getHtmlAnalysesResults() {

      $html = new msGetHtml;
      $html->set_template('inc-lapAnalyseResultats');
      $data=array(
        'aredondances'=> $this->getAlertesRedondances(),
        'aposologies'=> $this->getAlertesPosologies(),
        'aallergies'=> $this->getAlertesAllergies(),
        'acipemg'=> $this->getAlertesCIPEMG(),
        'agrossesse'=> $this->getAlertesGrossesse(),
        'ainteractions'=> $this->getAlertesInteractions(),
        'aincompatibilites'=> $this->getAlertesIncompatibilites(),
        'adopageconduc'=>$this->getAlertesDopageEtConducteur(),
        'apatient'=>$this->_patientPhysioControleData,
        'corLi'=>$this->_correspondanceLignes,
        'ordo'=>$this->_ordonnanceContenu
      );

      return $html->genererHtmlVar($data);
    }

/**
 * Créer les objets nécessaires à l'analyse à partir du contenu de l'ordo courante
 * @return void
 */
    public function getObjetsFromOrdo() {
      $zones=array('ordoMedicsALD','ordoMedicsG');
      foreach($zones as $zone) {
        if(isset($this->_ordonnanceContenu[$zone])) {
          $this->_zoneOrdo=$zone;
          foreach($this->_ordonnanceContenu[$zone] as $k=>$v) {
            $this->_indexLignePresOrdo = $k;
            $lt=$this->_prepareLignePrescriptionAnalyseTheriaque();
          }
        }
      }
    }

/**
 * Ajouter le traitement en cours aux objets
 */
    public function getObjetsFromTTenCours() {
      $lapTTenCours=new msLapOrdo;
      $lapTTenCours->setToID($this->_toID);
      $this->_ttEnCoursContenu=$lapTTenCours->getTTenCours();

      //si ordo vide, on génère des dates bornes sur 1 mois
      if(!isset($this->_dateBorneDebutOrdo)) {
        $this->_dateBorneDebutOrdo = new DateTime();
        $this->_dateBorneFinOrdo = $this->_dateBorneDebutOrdo->add(new DateInterval('P27D'));
      }

      $zones=array('TTPonctuels','TTChroniques');
      foreach($zones as $zone) {
        if(isset($this->_ttEnCoursContenu[$zone])) {
          if(!empty($this->_ttEnCoursContenu[$zone])) {
            $this->_zoneOrdo=$zone;
            foreach($this->_ttEnCoursContenu[$zone] as $ligneIndex => $ligne) {
              $this->_indexLignePresOrdo = $ligneIndex;
              $lt=$this->_prepareLignePrescriptionAnalyseTheriaque();
            }
          }
        }
      }

    }

/**
 * Obtenir l'analyse Thériaque
 * @return void
 */
    public function getAnalyseTheriaque() {
      if (isset($this->_the)) {
          $the=$this->_the;
      } else {
          $this->_the=$the=new $this->_classTheriaque;
      }

      $data = $the->get_analyse_ordonnance($this->_objetPatient, $this->_objetPrescription, $this->_objetPosoPres, '', '', '');

      $this->_analyseResults = $data['brut'];
      $this->_analyseResultsReformate =  $data['formate'];


    }

/**
 * Obtenir les infos de redondances sur le traitement à partir données brutes
 * @return array tableau
 */
    public function getAlertesRedondances() {
      $redon=[];
      if(!empty($this->_analyseResultsReformate['alertes_redondance'])) {
        foreach($this->_analyseResultsReformate['alertes_redondance'] as $k=>$v) {
          $redon[$v['groupe']][]=array(
            'alerte'=>$v,
            'medic'=>$this->_getMedicFromIndiceLignePrescription($v['indiceligneprescription'])
          );
        }
      }
      return $this->_alertesRedondances=$redon;
    }

/**
 * Obtenir les alertes posologiques
 * @return array tableau
 */
    public function getAlertesPosologies() {
      $tab=[];
      if(!empty($this->_analyseResultsReformate['alertes_posologie'])) {
        foreach($this->_analyseResultsReformate['alertes_posologie'] as $k=>$v) {
          $tab[$v['indiceligneprescription']]['medic']=$this->_getMedicFromIndiceLignePrescription($v['indiceligneprescription']);
          $tab[$v['indiceligneprescription']][$v['id_cat_alerte']][]=$v;
        }
      }
      ksort($tab);
      return $this->_alertesPosologies=$tab;
    }

/**
 * Obtenir les alertes allergies
 * @return array tableau
 */
    public function getAlertesAllergies() {
      $tab=[];
      if(!empty($this->_analyseResultsReformate['alertes_cipemg'])) {
        foreach($this->_analyseResultsReformate['alertes_cipemg'] as $k=>$v) {
          if($v['id_type_alerte']=='L') {
            $tab[$v['id_ter_com']][]=array(
              'alerte'=>$v,
              'medic'=>$this->_getMedicFromIndiceLignePrescription($v['indiceligneprescription'])
            );
            $this->_tabLignesRisqueAllergiques[] = $v['indiceligneprescription'];
          }
        }
      }
      return $this->_alertesAllergies=$tab;
    }

/**
 * Retourner les lignes d'ordo qui correspondent à un risque allergique
 * @return array lignes d'ordonnance
 */
    public function getLignesRisqueAllergique() {
      if(!empty($this->_tabLignesRisqueAllergiques)) {
        foreach($this->_tabLignesRisqueAllergiques as $l) {
          $tab[$this->_correspondanceLignes[$l]['zone']][$this->_correspondanceLignes[$l]['ligne']][$this->_correspondanceLignes[$l]['medic']]=1;
        }
        return $tab;
      }
      return [];
    }

/**
 * Obtenir les alertes cipemg
 * @return array tableau
 */
    public function getAlertesCIPEMG() {
      $tab=[];
      if(!empty($this->_analyseResultsReformate['alertes_cipemg'])) {
        foreach($this->_analyseResultsReformate['alertes_cipemg'] as $k=>$v) {
          if($v['id_type_alerte']!='L') {
            $tab[$v['indiceligneprescription']]['medic']=$this->_getMedicFromIndiceLignePrescription($v['indiceligneprescription']);
            $tab[$v['indiceligneprescription']][$v['id_nature_ci']][]=$v;
          }
        }
      }
      ksort($tab);
      return $this->_alertesCIPEMG=$tab;
    }

/**
 * Obtenir les alertes grossesse et allaitement
 * @return array tableau
 */
    public function getAlertesGrossesse() {
      $tab=[];
      if(!empty($this->_analyseResultsReformate['alertes_grossesse'])) {
        foreach($this->_analyseResultsReformate['alertes_grossesse'] as $k=>$v) {
          $tab[$v['indiceligneprescription']]['medic']=$this->_getMedicFromIndiceLignePrescription($v['indiceligneprescription']);
          $tab[$v['indiceligneprescription']][$v['niv_reco']][]=$v;
        }
      }
      ksort($tab);
      return $this->_alertesGrossesse=$tab;
    }

/**
 * Obtenir les alertes interactions
 * @return array  tableau
 */
    public function getAlertesInteractions() {
      $tab=[];
      if(!empty($this->_analyseResultsReformate['alertes_interaction'])) {
        foreach($this->_analyseResultsReformate['alertes_interaction'] as $k=>$v) {
          $tab[$v['niveau']][]=array(
            'medic1'=>$this->_getMedicFromIndiceLignePrescription($v['indiceligneprescription_1']),
            'medic2'=>$this->_getMedicFromIndiceLignePrescription($v['indiceligneprescription_2']),
            'infos'=>$v
          );
        }

      }
      return $this->_alertesInteractions=$tab;
    }

/**
 * Obtenir les alertes incompatibilités physico-chimiques
 * @return array  tableau
 */
    public function getAlertesIncompatibilites() {
      $tab=[];
      if(!empty($this->_analyseResultsReformate['alertes_incompatibilite'])) {
        foreach($this->_analyseResultsReformate['alertes_incompatibilite'] as $k=>$v) {
          $tab[]=array(
            'medic1'=>$this->_getMedicFromIndiceLignePrescription($v['indiceligneprescription_1']),
            'medic2'=>$this->_getMedicFromIndiceLignePrescription($v['indiceligneprescription_2']),
            'infos'=>$v
          );
        }

      }
      return $this->_alertesIncompatibilites=$tab;
    }

/**
 * Obtenir les alertes dopage et conducteur
 * @return array tableau
 */
    public function getAlertesDopageEtConducteur() {
      $zones=[];
      $tab=[];
      if(isset($this->_ordonnanceContenu)) {
        array_push($zones, 'ordoMedicsALD','ordoMedicsG');
      }
      if(isset($this->_ttENCoursContenu)) {
        array_push($zones, 'TTPonctuels','TTChroniques');
      }
      if(!empty($zones)) {
        foreach($zones as $zone) {
          if($zone == 'ordoMedicsALD' or $zone == 'ordoMedicsG') $tabZone=@$this->_ordonnanceContenu[$zone];
          elseif($zone == 'TTPonctuels' or $zone == 'TTChroniques') $tabZone=@$this->_ttEnCoursContenu[$zone];
          if(!empty($tabZone)) {
            foreach($tabZone as $ligneIndex=>$ligneData) {
              foreach($ligneData['medics'] as $medic) {
                if($medic['conducteur']['reco'] == '1') {
                  $tab['aconducteur'][$medic['conducteur']['niveau']][]=array(
                    'zone'=>$zone,
                    'medicNameUtile'=>$medic['nomUtileFinal'],
                    'conduc'=>$medic['conducteur']
                  );
                }

                if($medic['dopage'] > 0) {
                  $tab['adopage'][$medic['dopage']][]=array(
                    'zone'=>$zone,
                    'medicNameUtile'=>$medic['nomUtileFinal'],
                    'dopageNiveau'=>$medic['dopage']
                  );
                }

              }
            }
          }
        }
        if(isset($tab['aconducteur'])) krsort($tab['aconducteur']);
        return $this->_alertesDopageEtConducteur = $tab;
      }
    }

/**
 * Préparer les lignes de prescription pour l'analyse
 * @return void
 */
    private function _prepareLignePrescriptionAnalyseTheriaque() {
      if($this->_zoneOrdo == 'TTChroniques' or $this->_zoneOrdo == 'TTPonctuels') {
        $l=$this->_ttEnCoursContenu[$this->_zoneOrdo][$this->_indexLignePresOrdo];
      } else {
        $l=$this->_ordonnanceContenu[$this->_zoneOrdo][$this->_indexLignePresOrdo];
      }

      // si on est en TTChro et que l'objetID existe déjà on exclue la ligne car
      // un renouv la concernant est passé dans l'ordo
      if($this->_zoneOrdo == 'TTChroniques' and is_array($this->_ligneObjetIdDejaVus)) {
        if(in_array($l['ligneData']['objetID'],$this->_ligneObjetIdDejaVus)) return;
      }
      // on stocke les objetID des lignes s'ils existent pour exclure ensuite les lignes
      // du TT en cours qui serait un renouv d'un tt chronique (crit 74)
      if(isset($l['ligneData']['objetID']) and $l['ligneData']['objetID']>0) $this->_ligneObjetIdDejaVus[] = $l['ligneData']['objetID'];

      if(!empty($l['medics'])) {

        // on boucle sur les medic de la ligne
        foreach($l['medics'] as $indexMedic=>$m) {

            //fixe la date de départ pour ce médicament (= date départ de la ligne)
            if($this->_zoneOrdo == 'TTChroniques') {
              $this->_dateDepart=$this->_dateBorneDebutOrdo;
            } else {
              $this->_dateDepart=DateTime::createFromFormat('d/m/Y', $l['ligneData']['dateDebutPrise']);
            }
            //index de médicament en cours
            $this->_indexMedicPresOrdo = $indexMedic;

            // déterminer fonctionnement de la prescription de ce medic
            // Si des pas de jour de prise (lundi ou pair, impair ...)
            // on présume que chaque ligne a une durée
            if(empty(array_filter($m['posoJours']))) {
              // on boucle sur les lignes de posologie
              foreach($m['posoDureesUnitesSuccessives'] as $posoTheK=>$posoThe) {
                $this->_indexMedicPosoPresOrdo = $posoTheK;
                $this->_prepareMedicAnalyseTheSimplex();
              }
            }
            // sinon prescription complexe avec précisions sur jours
            else {
              $this->_prepareMedicAnalyseTheComplex();
            }

        }
      }
    }

/**
 * Préparer les objets en cas de ligne complexe (jours précisés)
 * @return void
 */
    private function _prepareMedicAnalyseTheComplex() {
      if($this->_zoneOrdo == 'TTChroniques' or $this->_zoneOrdo == 'TTPonctuels') {
        $l=$this->_ttEnCoursContenu[$this->_zoneOrdo][$this->_indexLignePresOrdo];
      } else {
        $l=$this->_ordonnanceContenu[$this->_zoneOrdo][$this->_indexLignePresOrdo];
      }
      $m=$l['medics'][$this->_indexMedicPresOrdo];

      //on boucle sur les jours pour établir tableau par type de jour
      $jours=[];
      foreach($m['posoJours'] as $indexPoso=>$v) {
        $j = array_fill_keys ( str_split($v) , $m['posoDosesSuccessives'][$indexPoso] );
        $jours=$jours+$j;
        unset($j);
      }
      // mode de fonctionnement
      if(isset($jours['i'])) $mode='pi'; else $mode='dow';

      // établir le tableau des jours et remplir
      $jour = DateTime::createFromFormat('d/m/Y', $l['ligneData']['dateDebutPrise']);
      $end = DateTime::createFromFormat('d/m/Y', $l['ligneData']['dateFinPriseAvecRenouv']);
      while($jour <= $end) {
        $jourKey=$jour->format('d/m/Y');
        if($mode=='pi') {
          if($jour->format('d') % 2 == 0) {
            $cal[$jourKey]=$jours['p'];
          } else {
            $cal[$jourKey]=$jours['i'];
          }
        } else {
          if($jour->format('N') == 1 and isset($jours['l'])) {
            $cal[$jourKey]=$jours['l'];
          } elseif($jour->format('N') == 2 and isset($jours['m'])) {
            $cal[$jourKey]=$jours['m'];
          } elseif($jour->format('N') == 3 and isset($jours['M'])) {
            $cal[$jourKey]=$jours['M'];
          } elseif($jour->format('N') == 4 and isset($jours['j'])) {
            $cal[$jourKey]=$jours['j'];
          } elseif($jour->format('N') == 5 and isset($jours['v'])) {
            $cal[$jourKey]=$jours['v'];
          } elseif($jour->format('N') == 6 and isset($jours['s'])) {
            $cal[$jourKey]=$jours['s'];
          } elseif($jour->format('N') == 7 and isset($jours['d'])) {
            $cal[$jourKey]=$jours['d'];
          }

        }
        $jour=$jour->add(new DateInterval('P1D'));
      }

      //boucle sur le calendrier
      foreach($cal as $date=>$posoj) {
        $this->_indexAbsoluLignePresOrdo ++;
        $this->_correspondanceLignes[$this->_indexAbsoluLignePresOrdo]=array(
          'zone'=>$this->_zoneOrdo,
          'ligne'=>$this->_indexLignePresOrdo,
          'medic'=>$this->_indexMedicPresOrdo,
          'poso'=>$this->_indexMedicPosoPresOrdo
        );

        $pres=[];
        //indice de la ligne de prescription absolu
        $pres['indiceligneprescription']=$this->_indexAbsoluLignePresOrdo;
        // date de début
        $pres['datedebut']=$date;
        // date de fin
          $pres['datefin']=$date;

        // code identifiant produit
        $pres['idprod']=$m['speThe'];
        $pres['typeprod']=0;
        //$pres['contenance_ud']=$m['unitesConversion']['nb_up']; //1000 mg
        //$pres['unite_contenance']=$m['unitesConversion']['unite_prescription']; //mg
        //$pres['vecteur_inj']='';
        //$pres['materiau_cont_inj']='';

        $this->_objetPrescription[]=$pres;

        $indexPosologie=1;
        foreach($posoj as $k=>$dose) {
          if($dose != '0') {
            $poso=[];
            $poso['posologie']=$indexPosologie;
            $poso['indiceligneprescription']=$this->_indexAbsoluLignePresOrdo;
            $poso['typeposo']=0;
            //$poso['valduree']='';
            //$poso['idduree']='';
            $poso['quantiteunite']=str_replace('.',',',$dose);
            $idunite = $this->_convertUniteUtiliOrigine2IdUnite($m['uniteUtiliseeOrigine']);
            $poso['idunite']=$m['unitesConversion'][$idunite];
            //$poso['valrepetition']='';
            //$poso['typerepetition']='';
            //$poso['nb_elements']='';
            //$poso['surface_element']='';

            $this->_objetPosoPres[]=$poso;
            $indexPosologie++;

          }
        }

      }

    }

/**
 * Préparer l'analyse pour une ligne simple
 * @return void
 */
    private function _prepareMedicAnalyseTheSimplex() {
      if($this->_zoneOrdo == 'TTChroniques' or $this->_zoneOrdo == 'TTPonctuels') {
        $l=$this->_ttEnCoursContenu[$this->_zoneOrdo][$this->_indexLignePresOrdo];
      } else {
        $l=$this->_ordonnanceContenu[$this->_zoneOrdo][$this->_indexLignePresOrdo];
      }
      $m=$l['medics'][$this->_indexMedicPresOrdo];

      // on boucle sur le nombre de renouv
      $boucleRenouv=$l['ligneData']['nbRenouvellements']+1;
      while($boucleRenouv>0) {

        // si la ligne poso a bien une unité de temps
        if($m['posoDureesUnitesSuccessives'][$this->_indexMedicPosoPresOrdo]) {
          //incrémenter et sauver correpondance
          $this->_indexAbsoluLignePresOrdo++;
          $this->_correspondanceLignes[$this->_indexAbsoluLignePresOrdo]=array(
            'zone'=>$this->_zoneOrdo,
            'ligne'=>$this->_indexLignePresOrdo,
            'medic'=>$this->_indexMedicPresOrdo,
            'poso'=>$this->_indexMedicPosoPresOrdo
          );

          $duree = $m['posoDureesSuccessives'][$this->_indexMedicPosoPresOrdo];
          $unite = $m['posoDureesUnitesSuccessives'][$this->_indexMedicPosoPresOrdo];


          $pres=[];
          //indice de la ligne de prescription absolu
          $pres['indiceligneprescription']=$this->_indexAbsoluLignePresOrdo;
          // date de début
          $pres['datedebut']=$this->_dateDepart->format('d/m/Y');
          // date de fin
          $dateFin=$this->_dateDepart->add(new DateInterval($this->_formatDateTimeAddString($duree, $unite)));
          $pres['datefin']=$dateFin->format('d/m/Y');
          // nouvelle date de départ
          $this->_dateDepart=$dateFin->add(new DateInterval($this->_formatDateTimeAddString(2, 'j')));

          // code identifiant produit
          $pres['idprod']=$m['speThe'];
          $pres['typeprod']=0;
          //$pres['contenance_ud']=$m['unitesConversion']['nb_up']; //1000 mg
          //$pres['unite_contenance']=$m['unitesConversion']['unite_prescription']; //mg
          //$pres['vecteur_inj']='';
          //$pres['materiau_cont_inj']='';

          $this->_objetPrescription[]=$pres;
          $indexPosologie=1;

          //en fonction du mode Thériaque
          // mode 0 : autant de tab que de prise
          if($m['posoTheriaqueMode'][$this->_indexMedicPosoPresOrdo] == 0 ) {
            foreach($m['posoDosesSuccessives'][$this->_indexMedicPosoPresOrdo] as $k=>$dose) {
              if($dose != '0') {
                $poso=[];
                $poso['posologie']=$indexPosologie;
                $poso['indiceligneprescription']=$this->_indexAbsoluLignePresOrdo;
                $poso['typeposo']=0;
                //$poso['valduree']='';
                //$poso['idduree']='';
                $poso['quantiteunite']=str_replace('.',',',$dose);
                $idunite = $this->_convertUniteUtiliOrigine2IdUnite($m['uniteUtiliseeOrigine']);
                $poso['idunite']=$m['unitesConversion'][$idunite];
                //$poso['valrepetition']='';
                //$poso['typerepetition']='';
                //$poso['nb_elements']='';
                //$poso['surface_element']='';

                $this->_objetPosoPres[]=$poso;
                $indexPosologie++;
              }
            }
          }
          // mode 1 : 1 tab
          else {
            $dose = $m['posoDosesSuccessives'][0][0];
            $duree = $m['posoDureesSuccessives'][0];
            $dureeUnite = $m['posoDureesUnitesSuccessives'][0];
            $freq = $m['nbPrisesParUniteTemps'][0];
            $freqUnite = $m['nbPrisesParUniteTempsUnite'][0];
            if($dose != '0') {
              $poso=[];
              $poso['posologie']=$indexPosologie;
              $poso['indiceligneprescription']=$this->_indexAbsoluLignePresOrdo;
              $poso['typeposo']=1;

              if($dureeUnite == 'h' or $dureeUnite == 'i') {
                $poso['valduree']=$duree;
                if($dureeUnite == 'h') {
                  $poso['idduree']=1;
                } elseif($dureeUnite == 'i') {
                  $poso['idduree']=2;
                }
              }
              $poso['quantiteunite']=str_replace('.',',',$dose);
              $idunite = $this->_convertUniteUtiliOrigine2IdUnite($m['uniteUtiliseeOrigine']);
              $poso['idunite']=$m['unitesConversion'][$idunite];
              $poso['valrepetition'] = $freq;
              if($freqUnite == 'j') {
                $poso['typerepetition']=0;
              } elseif($freqUnite == 'h') {
                $poso['typerepetition']=1;
              } elseif($freqUnite == 'i') {
                $poso['typerepetition']=2;
              }
              //$poso['nb_elements']='';
              //$poso['surface_element']='';

              $this->_objetPosoPres[]=$poso;
              $indexPosologie++;
            }
          }
        }
        $boucleRenouv--;
      }
    }

/**
 * Obtenir la bonne syntaxe pour ajout de durée à datetime
 * @param  int $duree durée
 * @param  string $unite unité
 * @return string   syntxe pour dateinterval
 */
    private function _formatDateTimeAddString($duree, $unite) {
      if($unite=='m') return 'P'.($duree * 28 - 1).'D';
      if($unite=='s') return 'P'.($duree * 7 - 1).'D';
      if($unite=='j') return 'P'.($duree-1).'D';
      if($unite=='h') return 'PT'.$duree.'H';
      return false;
    }

/**
 * Conversion des unités vers le code unité
 * @param  string $uorigine unité d'origine
 * @return string           code unité
 */
    private function _convertUniteUtiliOrigine2IdUnite($uorigine) {
      if($uorigine=='ucd') return 'code_ud';
      if($uorigine=='unite_prescription') return 'code_up';
      if($uorigine=='unite_prise') return 'code_ups';
      if($uorigine=='unite_vol') return 'code_unite_vol';
      return false;
    }

/**
 * Obtenir les data du médicaments à partir de l'index de ligne Thériaque
 * @param  int  $indiceLignePrescription index ligne Thériaque
 * @return array  tableau
 */
    private function _getMedicFromIndiceLignePrescription($indiceLignePrescription) {
      $ligneInfos=$this->_correspondanceLignes[$indiceLignePrescription];
      if($ligneInfos['zone'] =='TTPonctuels' or $ligneInfos['zone']=='TTChroniques') {
        return $this->_ttEnCoursContenu[$ligneInfos['zone']][$ligneInfos['ligne']]['medics'][$ligneInfos['medic']];
      } else {
        return $this->_ordonnanceContenu[$ligneInfos['zone']][$ligneInfos['ligne']]['medics'][$ligneInfos['medic']];
      }
    }

/**
 * Obtenir les dates bornes de l'ordo courante
 * @return void
 */
    private function _getDatesBornesOrdoEnCours() {
      $zones=array('ordoMedicsALD','ordoMedicsG');
      $debut=new DateTime();
      $fin=new DateTime();
      foreach($zones as $zone) {
        if(isset($this->_ordonnanceContenu[$zone])) {
          foreach($this->_ordonnanceContenu[$zone] as $ligne) {
              if(DateTime::createFromFormat('d/m/Y', $ligne['ligneData']['dateDebutPrise']) < $debut) $debut=DateTime::createFromFormat('d/m/Y', $ligne['ligneData']['dateDebutPrise']);
              if(DateTime::createFromFormat('d/m/Y', $ligne['ligneData']['dateFinPriseAvecRenouv']) > $fin) $fin=DateTime::createFromFormat('d/m/Y', $ligne['ligneData']['dateFinPriseAvecRenouv']);
          }
        }
      }
      $this->_dateBorneDebutOrdo = $debut;
      $this->_dateBorneFinOrdo = $fin;
    }
}
