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
 * LAP : analyse de prescription
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msLapPrescription extends msLap
{
  private $_txtPrescription;
  private $_speThe;
  private $_presThe;
  private $_medicVirtuel;
  private $_nomSpe;
  private $_nomDC;
  private $_versionInterpreteur;
  private $_divisibleEn;
  private $_uniteUtilisee;
  private $_uniteUtiliseeOrigine;
  private $_unitesConversion;
  private $_unitesPossibles;
  private $_voieUtilisee;
  private $_lignes;
  private $_lignesTraitees;
  private $_lignesPosologiques;
  private $_prescriptibleEnDC;
  private $_prescriptionInterpretee;
  private $_nbRenouvellements;
  private $_tauxrbt;
  private $_prixucd;
  private $_datePremierePrise;
  private $_stupefiant;
  private $_genreNombre;


/**
 * Définir le texte frappé
 * @param string $txt texte de la prescription
 * @return string texte
 */
    public function setTxtPrescription($txt)
    {
        if (is_string($txt)) {
            return $this->_txtPrescription = rtrim($txt, "\n");
        } else {
            throw new Exception('Txt is not string');
        }
    }

/**
 * Définir le code spécialité Thériaque
 * @param string $c code spé
 * @return string code spé
 */
    public function setSpeThe($c)
    {
        if (is_string($c)) {
            return $this->_speThe = $c;
        } else {
            throw new Exception('SpeThe is not string');
        }
    }

/**
 * Définir le code présentation Thériaque
 * @param string $c code pres
 * @return string code pres
 */
    public function setPresThe($c)
    {
        if (is_string($c)) {
            return $this->_presThe = $c;
        } else {
            throw new Exception('PresThe is not string');
        }
    }

/**
 * Définir le nom de spécialité
 * @param string $c code pres
 * @return string code pres
 */
    public function setNomSpe($s)
    {
        if (is_string($s)) {
            return $this->_nomSpe = $s;
        } else {
            throw new Exception('NomSpe is not string');
        }
    }

/**
 * Définir le nom en DC
 * @param string $c code pres
 * @return string code pres
 */
    public function setNomDC($s)
    {
        if (is_string($s)) {
            return $this->_nomDC = $s;
        } else {
            throw new Exception('NomDC is not string');
        }
    }

/**
 * Définir medic viruel ou pas
 * @param string $v medic virt
 * @return string medic virt
 */
    public function setMedicVirtuel($v)
    {
        return $this->_medicVirtuel = $v;
    }

/**
 * Définir sécabilité
 * @param string $v medic virt
 * @return string medic virt
 */
    public function setDivisibleEn($v)
    {
        return $this->_divisibleEn = $v;
    }

/**
 * Définir unité utilisée
 * @param string $v medic virt
 * @return string medic virt
 */
    public function setUniteUtilisee($v)
    {
        //on détermine le genre de l'unité
        $this->_genreNombre=$this->_uniteAccordee($v, '-1');

        return $this->_uniteUtilisee = $v;
    }

/**
 * Définir unité utilisée origine
 * @param string $v medic virt
 * @return string medic virt
 */
    public function setUniteUtiliseeOrigine($v)
    {
        return $this->_uniteUtiliseeOrigine = $v;
    }

/**
 * Définir unité conversion
 * @param string $v medic virt
 * @return string medic virt
 */
    public function setUnitesConversion($v)
    {
        return $this->_unitesConversion = $v;
    }

/**
 * Définir voie utilisée
 * @param string $v medic virt
 * @return string medic virt
 */
    public function setVoieUtilisee($v)
    {
        return $this->_voieUtilisee = $v;
    }

/**
 * Définir presciptibleEnDC
 * @param string $v presciptibleEnDC
 * @return string presciptibleEnDC
 */
    public function setPrescriptibleEnDC($v)
    {
        return $this->_prescriptibleEnDC = $v;
    }

/**
 * Définir la version de l'interpréteur à utiliser
 * @param string $v version interpréteur
 * @return string version intrpréteur
 */
    public function setVersionInterpreteur($v)
    {
        if (is_numeric($v)) {
            return $this->_versionInterpreteur = $v;
        } else {
            throw new Exception('VersionInterpreteur is not string');
        }
    }

/**
 * Définir le nb de renouvellement
 * @param int $nb nb renouvellement
 * @return int nb renouvellement
 */
    public function setNbRenouvellement($nb)
    {
        if (is_numeric($nb)) {
            return $this->_nbRenouvellements = $nb;
        } else {
            throw new Exception('nbRenouvellements is not numeric');
        }
    }

/**
 * Définir le taux de remboursement
 * @param string $taux taux rbt
 * @return string taux rbt
 */
    public function setTauxRbt($taux)
    {
      return $this->_tauxrbt = $taux;
    }

/**
 * Définir le prix par ucd
 * @param float $prix prix par ucd
 * @return float prix par ucd
 */
    public function setPrixUcd($prix)
    {
      return $this->_prixucd = $prix;
    }

/**
 * Définir la date de 1ere prise
 * @param string $date date au format d/m/Y
 * @return string date
 */
    public function setDatePremierePrise($date)
    {
      return $this->_datePremierePrise = $date;
    }

/**
 * Définir stupéfiant
 * @param string $stup stup o/n
 * @return string stup
 */
    public function setStupefiant($stup)
    {
      return $this->_stupefiant = $stup;
    }

/**
 * Obtenir les infos de prise en charge ALD
 * @param  string $code   code CIP de la présentation
 * @param  int $typCode type du code (1 CIP7, 2 CIP13)
 * @return string          % de rbt
 */
    private function _get_the_ald_info($code, $typCode)
    {
        if (empty($code)) {
            return false;
        }
        if (isset($this->_the)) {
            $the=$this->_the;
        } else {
            $this->_the=$the=new $this->_classTheriaque;
        }
        $rd=[];
        if ($data=$the->get_the_pre_rbt($code, $typCode)) {
            if (is_object($data)) {
                $data=msTools::objectToArray($data);
                if (isset($data['item'])) {
                    $data=$data['item'];
                }
            }
            if (!empty($data)) {
              $aldTab=[];
                foreach($data as $k=>$v) {
                  if($v['type']=='3') {
                    $analyse = $this->_analyseAldTheriaque($v['info_1']);

                    if(!empty($analyse)) {
                      $aldTab=$aldTab+$analyse;
                    }
                  }
                }
                return $aldTab;
            }
            return false;
        }
    }

    private function _analyseAldTheriaque($ligne) {
              // 1: "Accident vasculaire cérébral invalidant"
              // 2: "Insuffisances médullaires et autres cytopénies chroniques"
              // 3: "Artériopathies chroniques avec manifestations ischémiques"
              // 4: "Bilharziose compliquée"
              // 5: "Insuffisance cardiaque grave, troubles du rythme graves, cardiopathies valvulaires graves, cardiopathies  congénitales graves"
              // 6: "Maladies chroniques actives du foie et cirrhoses"
              // 7: "Déficit immunitaire primitif grave nécessitant un traitement prolongé, infection par le virus de 9: l'immuno-déficience humaine (VIH)"
              // 8: "Diabète de type 1 et diabète de type 2"
              // 9: "Formes graves des affections neurologiques et musculaires (dont myopathie), épilepsie grave"
              // 10: "Hémoglobinopathies, hémolyses, chroniques constitutionnelles et acquises sévères"
              // 11: "Hémophilies et affections constitutionnelles de l'hémostase graves"
              // 12: "Maladie coronaire"
              // 13: "Insuffisance respiratoire chronique grave"
              // 14: "Maladie d'Alzheimer et autres démences"
              // 15: "Maladie de Parkinson"
              // 16: "Maladies métaboliques héréditaires nécessitant un traitement prolongé spécialisé"
              // 17: "Mucoviscidose"
              // 18: "Néphropathie chronique grave et syndrome néphrotique primitif"
              // 19: "Paraplégie"
              // 20: "Vascularites, lupus érythémateux systémique, sclérodermie systémique"
              // 21: "Polyarthrite rhumatoïde évolutive"
              // 22: "Affections psychiatriques de longue durée"
              // 23: "Rectocolite hémorragique et maladie de Crohn évolutives"
              // 24: "Sclérose en plaques"
              // 25: "Scoliose idiopathique structurale évolutive (dont l'angle est égal ou supérieur à 25 degrés) jusqu'à maturation rachidienne"
              // 26: "Spondylarthrite grave"
              // 27: "Suites de transplantation d'organe"
              // 28: "Tuberculose active, lèpre"
              // 29: "Tumeur maligne, affection maligne du tissu lymphatique ou hématopoïétique"
              // 31: "Affection hors liste"
              // 32: "Etat polypathologique"


              $tab=[];
              if(strpos($ligne, 'SANS RAPPORT')) $rapport='n'; else $rapport='o';

              if(strpos($ligne, 'MALADIE CORONAIRE')) {
                $tab[12]=$rapport;
              }
              elseif(strpos($ligne, 'INS RESP C GRAVE')) {
                $tab[13]=$rapport;
              }
              elseif(strpos($ligne, 'DIABETE')) {
                $tab[8]=$rapport;
              }
              elseif(strpos($ligne, 'ARTERIOPATHIES')) {
                $tab[3]=$rapport;
              }
              elseif(strpos($ligne, 'CARDIOPATHIES')) {
                $tab[5]=$rapport;
              }

              return $tab;
    }

/**
 * Retourner toutes les datas utiles à la fenêtre de prescription
 * @return array tableau des données utiles à la prescription
 */
    public function lapInstallPrescription() {

      //spécialité
      $dataSpe=$this->getSpecialiteByCode($this->_speThe,1,3);
      $this->_nomSpe = trim($dataSpe[0]['sp_nom'], '§ ');
      if($dataSpe[0]['mono_vir'] == 1 or $dataSpe[0]['mono_vir'] =='t') {
        $this->_medicVirtuel = 1;
      } else {
        $this->_medicVirtuel = 0;
      }

      // présentation
      $dataPres=$this->_get_the_presentation($this->_presThe, 2);
      // hospitalier ?
      if($dataPres[0]['reservhop'] == 'NON') {
        $reservHopital = 'n';
      } else {
        $reservHopital = 'o';
      }
      // unités
      $dataSpeUnite=$this->getUnite($this->_speThe, 0);
      // voies d'administration possibles
      $dataSpeVoiesAdmin=$this->getVoiesAdministration($this->_speThe);
      // modalités dispensation
      $dataPresDispensation=$this->getDispensation($this->_presThe, 1);
      $infosDispensation=$this->_analyseInfosDispensation($dataPresDispensation);

      // condition ald
      $dataAld=$this->_get_the_ald_info($this->_presThe, 1);

      //recherche DC
      $dataGenerique=$this->getDC(2, $dataSpe[0]['sp_gsp_code_fk'], 1);
      $this->_nomDC = trim($dataGenerique[0]['libelle'], '§ ');
      // corrections pour sortie postgre
      if($dataGenerique[0]['prescription_dc'] == 't') $dataGenerique[0]['prescription_dc'] = 1;
      if($dataGenerique[0]['prescription_dc'] == 'f') $dataGenerique[0]['prescription_dc'] = 0;
      $this->_prescriptibleEnDC = $dataGenerique[0]['prescription_dc'];

      // substances actives
      $dataSubActives = $this->getSubtancesActivesTab($this->_speThe);

      //recherche du générique
      //$dataGenerique=$this->getGenerique($this->_speThe, 4);
      //$this->_nomDC = $dataGenerique[0]['gsp_nom'];

      //dopage
      $dataDopage=$this->getDopage($this->_speThe, 0);
      //conducteur
      $dataConducteur=$this->getConducteur($this->_speThe, 0);

      //sécabilité
      $dataSpeSecabilite=$this->getSpeSecabiliteByCode($this->_speThe);
      if(!empty($dataSpeSecabilite[0]['coeff'])) {
        $this->_divisibleEn=$dataSpeSecabilite[0]['coeff'];
      } else {
        $this->_divisibleEn=-1;
      }

      // unités possibles de prise
      $this->_unitesPossibles = [];
      if(!empty($dataSpeUnite[0]['ucd'])) {
        $this->_unitesPossibles['ucd']=$this->_uniteAccordee($dataSpeUnite[0]['ucd'],1);
      }
      if(!empty($dataSpeUnite[0]['unite_prescription'])) {
        $this->_unitesPossibles['unite_prescription']=$this->_uniteAccordee($dataSpeUnite[0]['unite_prescription'],1);
      }
      if(!empty($dataSpeUnite[0]['unite_prise'])) {
        $this->_unitesPossibles['unite_prise']=$this->_uniteAccordee($dataSpeUnite[0]['unite_prise'],1);
      }
      // en désespoire de cause : NON CAR INCOHERENT
      // if(empty($this->_unitesPossibles)) {
      //   if($unitesPoss=$this->getUniteViaPres($this->_presThe, 1)) {
      //     $this->_unitesPossibles['unite'] = $this->_uniteAccordee($unitesPoss[0]['unite'],1);
      //   }
      // }
      //
      if(empty($this->_unitesPossibles)) {
        if(!empty($dataPres[0]['unit_prise'])) {
           $this->_unitesPossibles['unit_prise']=$this->_uniteAccordee($dataPres[0]['unit_prise'],1);
        }
      }

      // SAM
      $sams=$this->getSamList4Spe($this->_speThe);

      $tab['medicData'] = array(
        'speThe'=>$this->_speThe,
        'presThe'=> $this->_presThe,
        'nomSpe'=> $this->_nomSpe,
        'nomDC'=> $this->_nomDC,
        'medicVirtuel'=> $this->_medicVirtuel,
        'divisibleEn'=> $this->_divisibleEn,
        'unitesPossibles'=> $this->_unitesPossibles,
        'unitesConversion'=> $dataSpeUnite[0],
        'nomUtileFinal' => $this->determineNomUtileFinal(),
        'voiesPossibles'=>$dataSpeVoiesAdmin,
        'prescriptibleEnDC'=>$this->_prescriptibleEnDC,
        'codeATC'=>$dataSpe[0]['sp_catc_code_fk'],
        'codeCIS'=>$dataSpe[0]['sp_nl'],
        'codeCIP7'=>$this->_presThe,
        'codeCIP13'=>$dataPres[0]['pre_ean_ref'],
        'codeUCD'=>$dataSpe[0]['sp_cipucd'],
        'codeUCD13'=>$dataSpe[0]['sp_cipucd13'],
        'conducteur'=>array(
          'reco'=>$dataConducteur[0]['reco'],
          'niveau'=>$dataConducteur[0]['niv'],
          'libelle_niv'=>$dataConducteur[0]['libelle_niv']
        ),
        'dopage'=>$dataDopage[0]['niveau'],
        'substancesActives'=>$dataSubActives,
        'prixucd'=>$this->_prixucd,
        'tauxrbt'=>$this->_tauxrbt,
        'ald'=>$dataAld,
        'stup'=>$infosDispensation['stupefiant'],
        'reservHopital'=>$reservHopital,
        'sams'=>$sams
      );

      // information sur la dernière utilisation de cette spé.
      $tab['lastPrescription']=$this->_getLastPrescriptionData();

      return $tab;

    }

/**
 * Obtenir des infos sur la dernière prescription de cette spécialité
 * @return array tab des infos (voie, unité, consignes)
 */
    private function _getLastPrescriptionData() {
      $tab = [];
      $idLigneMedic='';

      $data = new msData();
      $name2typeID=$data->getTypeIDsFromName(['lapMedicamentSpecialiteCodeTheriaque']);



      // on cherche d'abord pour le patient en cours, même prat
      if($idLigneMedic = msSQL::sqlUniqueChamp("select `instance` from `objets_data` where `typeID`='".$name2typeID['lapMedicamentSpecialiteCodeTheriaque']."' and `toID`='".$this->_toID."' and `fromID`='".$this->_fromID."' and `value`='".msSQL::cleanVar($this->_speThe)."' order by `id` desc limit 1")) {}
      // autre patient même prat
      elseif($idLigneMedic = msSQL::sqlUniqueChamp("select `instance` from `objets_data` where `typeID`='".$name2typeID['lapMedicamentSpecialiteCodeTheriaque']."' and `fromID`='".$this->_fromID."' and `value`='".msSQL::cleanVar($this->_speThe)."' order by `id` desc limit 1")) {}
      // autre prat
      else {
        $idLigneMedic = msSQL::sqlUniqueChamp("select `instance` from `objets_data` where `typeID`='".$name2typeID['lapMedicamentSpecialiteCodeTheriaque']."' and `value`='".msSQL::cleanVar($this->_speThe)."' order by `id` desc limit 1");
      }

      if($idLigneMedic > 0) {

        $idLignePres = msSQL::sqlUniqueChamp("select `instance` from `objets_data` where `id`='".$idLigneMedic."'  limit 1");
        $infosMedic = json_decode(msSQL::sqlUniqueChamp("select `value` from `objets_data` where `id`='".$idLigneMedic."'  limit 1"), TRUE);
        $infosLignePres = json_decode(msSQL::sqlUniqueChamp("select `value` from `objets_data` where `id`='".$idLignePres."'  limit 1"), TRUE);

        if(!empty($infosMedic) and !empty($infosLignePres)) {
          if(isset($infosMedic['uniteUtiliseeOrigine'])) $tab['uniteUtiliseeOrigine']=$infosMedic['uniteUtiliseeOrigine'];
          if(isset($infosMedic['voieUtiliseeCode'])) $tab['voieUtiliseeCode']=$infosMedic['voieUtiliseeCode'];
          if(isset($infosMedic['consignesPrescription'])) $tab['consignesPrescription']=$infosMedic['consignesPrescription'];
        }
      }
      return $tab;
    }

/**
 * Interpréter une prescription
 * @return array résultats d'interprétation
 */
    public function interpreterPrescription()
    {
      if(!isset($this->_versionInterpreteur)) $this->_versionInterpreteur=1;

      if($this->_lignes=explode("\n", $this->_txtPrescription)) {
        foreach($this->_lignes as $indexLigne=>$ligne) {
          $this->interpreterLigne($indexLigne, $ligne);
        }
        // retour de la version interpréteur
        $this->_prescriptionInterpretee['versionInterpreteur']=$this->_versionInterpreteur;

        // nombre de lignes et regex
        $this->_prescriptionInterpretee['posoFrappeeNbDelignes']=count($this->_lignesTraitees);
        $this->_prescriptionInterpretee['posoFrappeeNbDelignesPosologiques']=count($this->_lignesPosologiques);
        $this->_prescriptionInterpretee['regEx']=array_column($this->_lignesPosologiques,'regEx','indexLigne');

        // mode Theriaque à adopter pour chaque ligne posologique
        $this->_prescriptionInterpretee['posoTheriaqueMode']=array_column($this->_lignesPosologiques,'posoTheriaqueMode','indexLigne');

        // données des lignes pour analyse lap
        $this->_prescriptionInterpretee['posoDosesSuccessives']=array_column($this->_lignesPosologiques,'posoDosesSuccessives','indexLigne');
        $this->_prescriptionInterpretee['posoDureesSuccessives']=array_column($this->_lignesPosologiques,'duree','indexLigne');
        $this->_prescriptionInterpretee['posoDureesUnitesSuccessives']=array_column($this->_lignesPosologiques,'dureeUnite','indexLigne');
        $this->_prescriptionInterpretee['posoJours']=array_column($this->_lignesPosologiques,'posoJours','indexLigne');
        $this->_prescriptionInterpretee['nbPrisesParUniteTemps']=array_column($this->_lignesPosologiques,'nbPrisesParUniteTemps','indexLigne');
        $this->_prescriptionInterpretee['nbPrisesParUniteTempsUnite']=array_column($this->_lignesPosologiques,'nbPrisesParUniteTempsUnite','indexLigne');

        // le texte posologique humain
        $humanPosoBase = array_column($this->_lignesPosologiques,'humanPosoBase');
        $posoDuree = array_column($this->_lignesPosologiques,'duree');
        $humanPosoDuree = array_column($this->_lignesPosologiques,'humanPosoDuree');
        $humanPosoTraine = array_column($this->_lignesPosologiques,'humanPosoTraine');
        foreach($humanPosoBase as $k=>$v) {
          if($posoDuree[$k] > 0) {
            $human[] = trim(str_replace('  ', ' ', $humanPosoBase[$k].' '.$humanPosoDuree[$k].' '.$humanPosoTraine[$k]));
          } else {
            $human[] = trim(str_replace('  ', ' ', $humanPosoBase[$k].' '.$humanPosoTraine[$k]));
          }
          $humanBase[] = $humanPosoBase[$k];
        }
        //$this->_prescriptionInterpretee['posoHumanComplete'] = trim(str_replace('  ', ' ', implode("\n", $human)));
        $this->_prescriptionInterpretee['posoHumanCompleteTab'] = $human;
        $this->_prescriptionInterpretee['posoHumanBase'] = trim(str_replace('  ', ' ', implode("\n", $humanBase)));

        // la voie d'administration utilisée (txt humain)
        $this->_prescriptionInterpretee['voieUtilisee'] = ucfirst($this->_voieUtilisee);

        // la poso journalière max toutes lignes confondues
        //$this->_prescriptionInterpretee['posoJournaliereMax']=(float)max(array_column($this->_lignesPosologiques,'posoJournaliere'));

        // la poso Max par prise toutes lignes confondues
        //$this->_prescriptionInterpretee['posoMaxParPriseMax']=(float)max(array_column($this->_lignesPosologiques,'posoMaxParPrise'));

        // la poso Min par prise toutes lignes confondues (avec retrait 0 !)
        $this->_prescriptionInterpretee['posoMinParPriseMin']=(float)@min(array_filter(array_column($this->_lignesPosologiques,'posoMinParPrise')));


        // durée totale de la prescription
        $dureeTotale = $this->_dureeTotalePrescription();
        $this->_prescriptionInterpretee['dureeTotaleHuman']=$dureeTotale['dureeTotaleHuman'];
        $this->_prescriptionInterpretee['dureeTotaleMachine']=$dureeTotale['dureeTotaleMachine'];
        $this->_prescriptionInterpretee['dureeTotaleMachineJours']=$dureeTotale['dureeTotaleMachineJours'];
        $this->_prescriptionInterpretee['dureeTotaleMachineMinutes']=$dureeTotale['dureeTotaleMachineMinutes'];
        $this->_prescriptionInterpretee['dureeTotaleMachineJoursAvecRenouv']=$dureeTotale['dureeTotaleMachineJoursAvecRenouv'];
        $this->_prescriptionInterpretee['nbRenouvellements']=$this->_nbRenouvellements;

        // controle secabilité
        $this->_controleSecabilite();

        // total prescrit en unité
        $this->_calcTotalUnitesPrescrites();

      }

    }

/**
 * Détecter un problème de sécabilité
 * @return boolean true si pb de sécabilité
 */
    private function _controleSecabilite() {
      $tot=0;
      if(is_array($this->_prescriptionInterpretee['posoDosesSuccessives'])) {
        foreach($this->_prescriptionInterpretee['posoDosesSuccessives'] as $tab) {
          $tot=$tot+array_sum($tab);
        }
      }

      // si rien de prescrit (poso = 0)
      if($tot==0) {
        $this->_prescriptionInterpretee['alerteSecabilite'] = false;
      }
      //si sécabilité inexistante
      elseif($this->_divisibleEn == -1 and $this->_prescriptionInterpretee['posoMinParPriseMin'] < 1) {
        $this->_prescriptionInterpretee['alerteSecabilite'] = true;
      }
      // unité utilisée : ucd
      elseif($this->_uniteUtiliseeOrigine == 'ucd') {
        if($this->_prescriptionInterpretee['posoMinParPriseMin'] < ( 1 / $this->_divisibleEn)) {
          $this->_prescriptionInterpretee['alerteSecabilite'] = true;
        }
      }
      // unité utilisée : unite_prescription
      elseif($this->_uniteUtiliseeOrigine == 'unite_prescription') {
        if($this->_prescriptionInterpretee['posoMinParPriseMin'] < $this->_unitesConversion['dose_fractionnee']) {
          $this->_prescriptionInterpretee['alerteSecabilite'] = true;
        }
      }
    }

/**
 * Sortir les résultats d'interprétation d'une poso en JSON
 * @return [type] [description]
 */
    public function getPrescriptionInterpreteeJSON() {
      return json_encode($this->_prescriptionInterpretee);
    }

/**
 * Déterminer le nom utile entre nom de spé et DCI en fonction des data du medic
 * @return string nom retenu
 */
    private function determineNomUtileFinal() {
      if($this->_prescriptibleEnDC != '1') return $this->_nomSpe;
      if($this->_medicVirtuel == '1') {
        return $this->_nomDC;
      } else {
        return $this->_nomDC.' ('.$this->_nomSpe.')';
      }
    }

/**
 * Obtenir les sams concernés par le code spécialité
 * @param  int $code code spécialité
 * @return array       array des SAM
 */
    public function getSamList4Spe($code) {
      global $p;
      if(is_file($p['homepath'].'ressources/SAM/samSpeCorrespondances')) {
        $filecontent = file_get_contents($p['homepath'].'ressources/SAM/samSpeCorrespondances');
        $tabCorrespondance = unserialize($filecontent);
        $rd=[];
        foreach($tabCorrespondance as $sam=>$codesArray) {
          if(in_array($code, $codesArray)) {
            $rd[]=$sam;
          }
        }
        return $rd;
      } else {
        throw new Exception('Le fichier de correspondance SAM <=> spécialité est introuvable');
      }
    }

/**
 * Interpréter une ligne en la passant dans les regex
 * @param  int $indexLigne index de la ligne
 * @param  string $ligne      ligne de prescription
 * @return array             données sur la ligne
 */
    public function interpreterLigne($indexLigne, $ligne) {
        $regExs=$this->_getTheRegEx();
        $ligne=$ligne.' ';
        foreach($regExs as $regExIndex=>$regEx){
          if(preg_match($regEx,$ligne,$m)) {
            $methodeName='_traiterLigneRegEx'.$regExIndex.'V'.$this->_versionInterpreteur;
            return $this->$methodeName($indexLigne, $m);
          }
        }
    }

/**
 * Traiter la regEx 0 de la version 1 de l'interpréteur
 * @param  int $indexLigne index de la ligne traitée
 * @param  array $m          match retour de la regex
 * @return array             données sur la ligne
 */
    private function _traiterLigneRegEx0V1($indexLigne, $m) {
        $human='';
        $math = new Webit\Util\EvalMath\EvalMath;

        $m['prefixeLigne'] = $m[1];
        $m['doseMatin'] = str_replace(".", ",", $m[2]);
        $m['doseMidi'] = str_replace(".", ",", $m[3]);
        $m['doseSoir'] = str_replace(".", ",", $m[4]);
        $m['doseCoucher'] = str_replace(".", ",", @$m[5]);
        $m['joursSemaine'] = @$m[6];
        $m['dureeNumeric'] = @$m[7];
        $m['dureeUnite'] = @$m[8];
        $m['traine'] = trim(@$m[9]);

        // correction valeurs absentes
        if(empty($m['doseCoucher'])) $m['doseCoucher']=0;

        //conversion virgule en point
        $m['doseMatinMath'] = $math->evaluate(str_replace(",", ".", $m['doseMatin']));
        $m['doseMidiMath'] = $math->evaluate(str_replace(",", ".", $m['doseMidi']));
        $m['doseSoirMath'] =  $math->evaluate(str_replace(",", ".", $m['doseSoir']));
        $m['doseCoucherMath'] = $math->evaluate(str_replace(",", ".", $m['doseCoucher']));
        $m['lesDoses'] = array($m['doseMatinMath'], $m['doseMidiMath'], $m['doseSoirMath'], $m['doseCoucherMath'], '0');

        // durée
        $dureeHuman=$this->_dureeAbrevEnMots($m['dureeNumeric'], $m['dureeUnite']);

        // cas de posologie nulle => arrêt pendant x jour
        if($m['doseMatinMath'] == 0 and $m['doseMidiMath'] == 0 and $m['doseSoirMath'] == 0 and $m['doseCoucherMath'] == 0) {
          $human = 'arrêt ';
          $tab['nbPrisesParUniteTemps']=0;
        }
        // cas ou la posologie m m s c est égale
        elseif ($m['doseMatinMath'] == $m['doseMidiMath'] and $m['doseMatinMath'] == $m['doseSoirMath'] and $m['doseCoucher'] == 0 ) {
          $human = $this->_uniteFxStup($m['doseMatin']) .' '. $this->_uniteAccordee($this->_uniteUtilisee,$m['doseMatinMath']) . ' matin midi et soir ';
          $tab['nbPrisesParUniteTemps']=4;
        }
        // si poso m m s est différente
        else {
          if ($m['doseMatinMath'] > 0) $pmms[] = $this->_uniteFxStup($m['doseMatin']).' ' . $this->_uniteAccordee($this->_uniteUtilisee,$m['doseMatinMath']) . ' le matin';
          if ($m['doseMidiMath'] > 0) $pmms[] = $this->_uniteFxStup($m['doseMidi']).' ' . $this->_uniteAccordee($this->_uniteUtilisee,$m['doseMidiMath']) . ' le midi';
          if ($m['doseSoirMath'] > 0) $pmms[] = $this->_uniteFxStup($m['doseSoir']).' ' . $this->_uniteAccordee($this->_uniteUtilisee,$m['doseSoirMath']) . ' le soir';
          if ($m['doseCoucherMath'] > 0) $pmms[] = $this->_uniteFxStup($m['doseCoucher']).' ' . $this->_uniteAccordee($this->_uniteUtilisee,$m['doseCoucherMath']) . ' au coucher';

          $tab['nbPrisesParUniteTemps']=count($pmms);
          $human .= implode(', ',$pmms).' ';

        }

        if ($m['joursSemaine']) $human .= $this->_days($m['joursSemaine']);
        if ($m['prefixeLigne']) $human = $m['prefixeLigne'] . ' ' . $human;

        // tableau de retour
        $tab['indexLigne'] = $indexLigne;
        $tab['typeLigne'] = 'posologie';
        $tab['prefixeLigne'] = $m['prefixeLigne'];
        $tab['duree'] = $m['dureeNumeric'];
        $tab['dureeUnite'] = $m['dureeUnite'];
        $tab['posoTheriaqueMode'] = 0;

        $tab['posoDosesSuccessives'] = array($m['doseMatinMath'], $m['doseMidiMath'], $m['doseSoirMath'], $m['doseCoucherMath']);
        $tab['nbPrisesParUniteTempsUnite'] ='';
        $tab['posoJours'] = $m['joursSemaine'];
        //$tab['posoMaxParPrise'] = (float)max($m['lesDoses']);
        $tab['posoMinParPrise'] = (float)@min(array_filter($m['lesDoses']));
        $tab['humanPosoBase'] = $human;
        $tab['humanPosoDuree'] = 'pendant ' . $m['dureeNumeric'] . ' ' . $dureeHuman;
        $tab['humanPosoTraine'] = $m['traine'];
        $tab['regEx']='0';
        //print_r($tab);

        $this->_lignesPosologiques[] = $tab;
        return $this->_lignesTraitees[$indexLigne] = $tab;
    }

/**
 * Traiter la regEx 1 de la version 1 de l'interpréteur
 * @param  int $indexLigne index de la ligne traitée
 * @param  array $m          match retour de la regex
 * @return array             données sur la ligne
 */
    private function _traiterLigneRegEx1V1($indexLigne, $m) {
        $human='';
        $math = new Webit\Util\EvalMath\EvalMath;

        //print_r($m);
        $m['prefixeLigne'] = $m[1];
        $m['dose'] = str_replace(".", ",", $m[2]);
        $m['multipleDose'] = $m[3];
        $m['multipleUnite'] = $m[4];
        $m['joursSemaine'] = @$m[5];
        $m['dureeNumeric'] = @$m[6];
        $m['dureeUnite'] = @$m[7];
        $m['traine'] = trim(@$m[8]);

        // correction valeurs absentes

        //conversion virgule en point
        $m['doseMath'] = $math->evaluate(str_replace(",", ".", $m['dose']));
        $m['multipleDoseMath'] = $math->evaluate(str_replace(",", ".", $m['multipleDose']));

        // durée
        $dureeHuman=$this->_dureeAbrevEnMots($m['dureeNumeric'], $m['dureeUnite']);

        // cas de posologie nulle => arrêt pendant x jour
        if($m['doseMath'] == 0) {
          $human = 'arrêt ';
          $tab['nbPrisesParUniteTemps']=0;
        } else {
          $human = $m['dose'] .' '. $this->_uniteAccordee($this->_uniteUtilisee,$m['doseMath']) . ' '.$m['multipleDose'].' fois par '.$this->_dureeAbrevEnMots(1, $m['multipleUnite']).' ';
          $tab['nbPrisesParUniteTemps']=$m['multipleDoseMath'];
        }

        if ($m['joursSemaine']) $human .= $this->_days($m['joursSemaine']);
        if ($m['prefixeLigne']) $human = $m['prefixeLigne'] . ' ' . $human;

        // tableau de retour
        $tab['indexLigne'] = $indexLigne;
        $tab['typeLigne'] = 'posologie';
        $tab['prefixeLigne'] = $m['prefixeLigne'];
        $tab['duree'] = $m['dureeNumeric'];
        $tab['dureeUnite'] = $m['dureeUnite'];
        //$tab['posoJournaliere'] = (float)$m['doseMath'] * $m['multipleDoseMath'];
        $tab['posoTheriaqueMode'] = 1;
        $tab['posoDosesSuccessives'] = array($m['doseMath']);
        $tab['nbPrisesParUniteTempsUnite'] = $m['multipleUnite'];
        $tab['posoJours'] = $m['joursSemaine'];
        //$tab['posoMaxParPrise'] = (float)$m['doseMath'];
        $tab['posoMinParPrise'] = (float)$m['doseMath'];
        $tab['humanPosoBase'] = $human;
        $tab['humanPosoDuree'] = 'pendant ' . $m['dureeNumeric'] . ' ' . $dureeHuman;
        $tab['humanPosoTraine'] = $m['traine'];
        $tab['regEx']='1';

        $this->_lignesPosologiques[] = $tab;
        return $this->_lignesTraitees[$indexLigne] = $tab;
    }

    /**
     * Traiter la regEx 2 de la version 1 de l'interpréteur
     * @param  int $indexLigne index de la ligne traitée
     * @param  array $m          match retour de la regex
     * @return array             données sur la ligne
     */
        private function _traiterLigneRegEx2V1($indexLigne, $m) {
            $human='';

            $m['dureeNumeric'] = @$m[2];
            $m['dureeUnite'] = @$m[3];

            // durée
            $dureeHuman=$this->_dureeAbrevEnMots($m['dureeNumeric'], $m['dureeUnite']);


            // tableau de retour
            $tab['indexLigne'] = $indexLigne;
            $tab['typeLigne'] = 'posologienc';
            $tab['prefixeLigne'] = '';
            $tab['duree'] = $m['dureeNumeric'];
            $tab['dureeUnite'] = $m['dureeUnite'];
            $tab['nbPrisesParUniteTemps']=0;
            //$tab['posoJournaliere'] = 0;
            $tab['posoTheriaqueMode'] = 1;
            $tab['posoDosesSuccessives'] = 0;
            $tab['nbPrisesParUniteTempsUnite'] = 0;
            $tab['posoJours'] = '';
            //$tab['posoMaxParPrise'] = 0;
            $tab['posoMinParPrise'] = 0;
            $tab['posoTotal'] = 0;
            $tab['humanPosoBase'] = 'posologie inconnue';
            $tab['humanPosoDuree'] = ' - ' . $m['dureeNumeric'] . ' ' . $dureeHuman;
            $tab['humanPosoTraine'] = '';
            $tab['regEx']='2';

            $this->_lignesPosologiques[] = $tab;
            return $this->_lignesTraitees[$indexLigne] = $tab;
        }

/**
 * Traiter la regEx 3 de la version 1 de l'interpréteur
 * @param  int $indexLigne index de la ligne traitée
 * @param  array $m          match retour de la regex
 * @return array             données sur la ligne
 */
    private function _traiterLigneRegEx3V1($indexLigne, $m) {
        $human='';
        $math = new Webit\Util\EvalMath\EvalMath;

        //print_r($m);
        $m['prefixeLigne'] = $m[1];
        $m['dose'] = str_replace(".", ",", $m[2]);
        $m['multipleDose'] = $m[3];
        $m['multipleUnite'] = $m[4];
        $m['joursSemaine'] = @$m[5];
        $m['dureeNumeric'] = @$m[6];
        $m['dureeUnite'] = @$m[7];
        $m['traine'] = trim(@$m[8]);


        //conversion virgule en point
        $m['doseMath'] = $math->evaluate(str_replace(",", ".", $m['dose']));
        $m['multipleDoseMath'] = $math->evaluate(str_replace(",", ".", $m['multipleDose']));

        // durée
        $dureeHuman=$this->_dureeAbrevEnMots($m['dureeNumeric'], $m['dureeUnite']);

        // cas de posologie nulle => arrêt pendant x jour
        if($m['doseMath'] == 0) {
          $human = 'arrêt ';
          $tab['nbPrisesParUniteTemps']=0;
        } else {
          if($m['multipleUnite'] == 'j' or $m['multipleUnite']=='m') {
            $liaison = 'tous les';
          } else {
            $liaison = 'toutes les';
          }

          $human = $m['dose'] .' '. $this->_uniteAccordee($this->_uniteUtilisee,$m['doseMath']) . ' '. $liaison.' ';
          if($m['multipleDose']>1) $human .= $m['multipleDose'];
          $human .=' '.$this->_uniteTempsAccordee($m['multipleUnite'], 10);
          $tab['nbPrisesParUniteTemps']=$m['multipleDoseMath'];
        }

        if ($m['joursSemaine']) $human .= ' '.$this->_days($m['joursSemaine']);
        if ($m['prefixeLigne']) $human = $m['prefixeLigne'] . ' ' . $human;

        // tableau de retour
        $tab['indexLigne'] = $indexLigne;
        $tab['typeLigne'] = 'posologie';
        $tab['prefixeLigne'] = $m['prefixeLigne'];
        $tab['duree'] = $m['dureeNumeric'];
        $tab['dureeUnite'] = $m['dureeUnite'];
        //$tab['posoJournaliere'] = '';
        $tab['posoTheriaqueMode'] = 1;
        $tab['posoDosesSuccessives'] = array($m['doseMath']);
        $tab['nbPrisesParUniteTempsUnite'] = $m['multipleUnite'];
        $tab['posoJours'] = $m['joursSemaine'];
        //$tab['posoMaxParPrise'] = (float)$m['doseMath'];
        $tab['posoMinParPrise'] = (float)$m['doseMath'];
        $tab['humanPosoBase'] = $human;
        $tab['humanPosoDuree'] = 'pendant ' . $m['dureeNumeric'] . ' ' . $dureeHuman;
        $tab['humanPosoTraine'] = $m['traine'];
        $tab['regEx']='3';

        $this->_lignesPosologiques[] = $tab;
        return $this->_lignesTraitees[$indexLigne] = $tab;
    }


    private function _calcTotalUnitesPrescrites() {
      $total=[];
      foreach($this->_lignesTraitees as $k=>$l) {

        //correction de la durée si absente : on prend la durée totale
        if(empty($l['duree'])) {
            $l['duree']=$this->_prescriptionInterpretee['dureeTotaleMachineMinutes'];
            $l['dureeUnite'] = 'i';
        }

        if($l['regEx']=='0') {
          $posoJournaliere = (float)$l['posoDosesSuccessives'][0] + (float)$l['posoDosesSuccessives'][1] + (float)$l['posoDosesSuccessives'][2] + (float)$l['posoDosesSuccessives'][3];
          if(!empty($l['posoJours'])) {
            $nbReelJours=$this->_calculerNbReelDeJours($l['duree'], $l['dureeUnite'],$l['posoJours']);
          } else {
            $nbReelJours=$this->_convertToMinutes($l['duree'], $l['dureeUnite']) / (60 * 24);
          }
          $total[$k] = $posoJournaliere * $nbReelJours;
        }

        elseif($l['regEx']=='1') {
          if($l['nbPrisesParUniteTemps'] > 0) {
            $fqcMinutes = $this->_convertToMinutes(1, $l['nbPrisesParUniteTempsUnite']) / $l['nbPrisesParUniteTemps'];

            if(!empty($l['posoJours'])) {
              $dureeMinutes = $this->_calculerNbReelDeJours($l['duree'], $l['dureeUnite'],$l['posoJours']) * 60 * 24;
            } else {
              $dureeMinutes = $this->_convertToMinutes($l['duree'], $l['dureeUnite']);
            }
            if((float)$l['posoDosesSuccessives'][0] > 0) {
              $total[$k] = $dureeMinutes / $fqcMinutes * (float)$l['posoDosesSuccessives'][0];
            } else {
              $total[$k] = 0;
            }
          } else {
            $total[$k] = 0;
          }
        }

        elseif($l['regEx']=='3') {
          $fqcMinutes = $this->_convertToMinutes($l['nbPrisesParUniteTemps'], $l['nbPrisesParUniteTempsUnite']);
          if(!empty($l['posoJours'])) {
            $dureeMinutes = $this->_calculerNbReelDeJours($l['duree'], $l['dureeUnite'],$l['posoJours']) * 60 * 24;
          } else {
            $dureeMinutes = $this->_convertToMinutes($l['duree'], $l['dureeUnite']);
          }
          if((float)$l['posoDosesSuccessives'][0] > 0) {
            $total[$k] = $dureeMinutes / $fqcMinutes * (float)$l['posoDosesSuccessives'][0];
          } else {
            $total[$k] = 0;
          }
        }
      }

      $this->_prescriptionInterpretee['totalUnitesPrescrites'] = array_sum($total);
    }

/**
 * Convertir une durée en minutes
 * @param  [type] $nb   [description]
 * @param  [type] $unit [description]
 * @return [type]       [description]
 */
    private function _convertToMinutes($nb, $unit) {
      if($unit=='i') return $nb;
      elseif($unit=='h') return $nb*60;
      elseif($unit=='j') return $nb*60*24;
      elseif($unit=='s') return $nb*60*24*7;
      elseif($unit=='m') return $nb*60*24*7*4;
    }

/**
 * Calculer le nombre de jour réel entre 2 dates quand précision jour de la semaine ou pairs / impaires
 * @param  int $dureeNumeric durée
 * @param  string $dureeUnite   unité de durée sur 1 lettre
 * @param  string $joursSemaine les jours sur 1 lettre, concaténés
 * @return int                le nombre de jour
 */
private function _calculerNbReelDeJours($dureeNumeric, $dureeUnite,$joursSemaine) {

  $debut = DateTime::createFromFormat('d/m/Y H:i:s', $this->_datePremierePrise.' 00:00:00');
  $minutesAjoutees = $this->_convertToMinutes($dureeNumeric, $dureeUnite) - 1;
  $fin = DateTime::createFromFormat('d/m/Y H:i:s', $this->_datePremierePrise.' 00:00:00');
  $fin = $fin->add(new DateInterval('PT' . $minutesAjoutees . 'M'));

  if (strstr($joursSemaine, 'i') != FALSE or strstr($joursSemaine, 'p') != FALSE) {
    $p=0;
    $i=0;
    $all_days = new DatePeriod($debut, new DateInterval('P1D'), $fin);
    foreach ($all_days as $day) {
        $d = (int)$day->format('d');
        if($d % 2 == 0) $p++; else $i++;
    }
    if (strstr($joursSemaine, 'i') != FALSE) $count[]=$i;
    if (strstr($joursSemaine, 'p') != FALSE) $count[]=$p;
    return array_sum($count);

  } else {
    if (strstr($joursSemaine, 'l') != FALSE) $count[]=$this->_dayCount($debut,$fin,1);
    if (strstr($joursSemaine, 'm') != FALSE) $count[]=$this->_dayCount($debut,$fin,2);
    if (strstr($joursSemaine, 'M') != FALSE) $count[]=$this->_dayCount($debut,$fin,3);
    if (strstr($joursSemaine, 'j') != FALSE) $count[]=$this->_dayCount($debut,$fin,4);
    if (strstr($joursSemaine, 'v') != FALSE) $count[]=$this->_dayCount($debut,$fin,5);
    if (strstr($joursSemaine, 's') != FALSE) $count[]=$this->_dayCount($debut,$fin,6);
    if (strstr($joursSemaine, 'd') != FALSE) $count[]=$this->_dayCount($debut,$fin,7);

    return array_sum($count);
  }
}

/**
 * Compter le nombre de jour pour un jour de semaine particulier entre 2 dates
 * @param  string  $from date de début au format datetime
 * @param  string  $to   date de fin au format datetime
 * @param  integer $day  jour de la semaine
 * @return int     nombre de jour
 */
    private function _dayCount($from, $to, $day) {

        $wF = $from->format('w');
        $wT = $to->format('w');
        if ($wF < $wT)       $isExtraDay = $day >= $wF && $day <= $wT;
        else if ($wF == $wT) $isExtraDay = $wF == $day;
        else                 $isExtraDay = $day >= $wF || $day <= $wT;

        return floor($from->diff($to)->days / 7) + $isExtraDay;
    }


/**
 * Obtenir mot en fonction abréviation de durée
 * @param  int $nb    nombre
 * @param  string $abrev abréviation à considérer
 * @return string        mot accordé
 */
    private function _dureeAbrevEnMots($nb, $abrev) {
      if(empty($nb) or empty($abrev)) return;
      $duree = [
        'i'=> 'minute',
        'h'=> 'heure',
        'j'=> 'jour',
        's'=> 'semaine',
        'm'=> 'mois'
      ];
      if ($nb > 1 and $abrev != 'm') {
        return $duree[$abrev] . 's';
      } else {
        return $duree[$abrev];
      }
    }

/**
 * Transforme lmMjvsd en jour
 * @param  string liste chaine entrée
 * @return string       chaine formatée
 */
    private function _days($liste) {
      $human=[];
      if (strstr($liste, 'l') != FALSE) $human[]='lundis';
      if (strstr($liste, 'm') != FALSE) $human[]='mardis';
      if (strstr($liste, 'M') != FALSE) $human[]='mercredis';
      if (strstr($liste, 'j') != FALSE) $human[]='jeudis';
      if (strstr($liste, 'v') != FALSE) $human[]='vendredis';
      if (strstr($liste, 's') != FALSE) $human[]='samedis';
      if (strstr($liste, 'd') != FALSE) $human[]='dimanches';
      if (strstr($liste, 'i') != FALSE) $human[]='jours impairs';
      if (strstr($liste, 'p') != FALSE) $human[]='jours pairs';
      $humanString = implode(' ', $human);
      if (count($human) > 0) {
        $humanString = 'les ' . $humanString . ' ';
        return $humanString;
      } else {
        return '';
      }
    }

/**
 * Sortir les regex pour l'interprétation en fonction de la version interpréteur
 * @return array jeu de regex relatif à la version de l'interpréteur
 */
    private function _getTheRegEx() {
      if (!isset($this->_versionInterpreteur)) {
        throw new Exception('La version de l\'interpréteur n\'est pas définie');
      }
      $regEx[1][0] = "/^(et|puis)?\s*([0-9\/,\.+]+) ([0-9\/,\.+]+) ([0-9\/,\.+]+)(?: ([0-9\/,\.+]+))?(?: ([lmMjvsdip]*))? (?:([0-9]+)(j|s|m))?(.*)/i";

      // 1 6xh|j|s|m 6h|j|s|m jp|ji
      $regEx[1][1] = "/^(et|puis)?\s*([0-9\/,\.]+) ([0-9]+)x(i|h|j|s|m){1}(?: ([lmMjvsdip]*))? (?:([0-9]+)(i|h|j|s|m))?(.*)/i";

      // posologie inconnue
      $regEx[1][2] = "/^(nc|\?) (?:([0-9]+)(j|s|m))/i";

      // 1 6xh|j|s|m 6h|j|s|m jp|ji
      $regEx[1][3] = "/^(et|puis)?\s*([0-9\/,\.]+) ([0-9]+)(i|h|j|s|m){1}(?: ([lmMjvsdip]*))? (?:([0-9]+)(i|h|j|s|m))?(.*)/i";

      return $regEx[$this->_versionInterpreteur];

    }

/**
 * Accorder unités de temps
 * @param  string $abrev mot à accorder
 * @param  int $nb    nombre
 * @return string        mot accordé
 */
    private function _uniteTempsAccordee($abrev, $nb) {
      $f=array(
        'i' => array('s'=>'minute', 'p'=>'minutes'),
        'h' => array('s'=>'heure', 'p'=>'heures'),
        'j' => array('s'=>'jour', 'p'=>'jours'),
        's' => array('s'=>'semaine', 'p'=>'semaines'),
        'm' => array('s'=>'mois', 'p'=>'mois'),
      );
      if(key_exists($abrev,$f)) {
        if($nb>1) return $f[$abrev]['p'];
        else return $f[$abrev]['s'];
      }
      return strtolower($abrev);

    }

/**
 * Accorder des unités
 * @param  string $forme mot à accorder
 * @param  int $nb    nombre
 * @return string        mot accordé
 */
    private function _uniteAccordee($forme, $nb) {
      $forme = $formeOriginelle = strtolower($forme);
      $forme = msTools::stripAccents(str_replace('(s)', '', $forme));
      $f=array(
        'ampoule' => array('s'=>'ampoule', 'p'=>'ampoules', 'g'=>'f'),
        'application' => array('s'=>'application', 'p'=>'applications', 'g'=>'f'),
        'comprime' => array('s'=>'comprimé', 'p'=>'comprimés', 'g'=>'m'),
        'dose' => array('s'=>'dose', 'p'=>'doses', 'g'=>'f'),
        'dose kg' => array('s'=>'dose kg', 'p'=>'doses kg', 'g'=>'f'),
        'emplatre' => array('s'=>'emplâtre', 'p'=>'emplâtres', 'g'=>'m'),
        'flacon' => array('s'=>'flacon', 'p'=>'flacon', 'g'=>'m'),
        'flacon pressurise' => array('s'=>'flacon pressurisé', 'p'=>'flacons pressurisés', 'g'=>'m'),
        'gelule' => array('s'=>'gélule', 'p'=>'gélules', 'g'=>'f'),
        'goutte' => array('s'=>'goutte', 'p'=>'gouttes', 'g'=>'f'),
        'microgramme' => array('s'=>'microgramme', 'p'=>'microgrammes', 'g'=>'m'),
        'pansement' => array('s'=>'pansement', 'p'=>'pansements', 'g'=>'m'),
        'poche' => array('s'=>'poche', 'p'=>'poches', 'g'=>'f'),
        'pulverisation' => array('s'=>'pulvérisation', 'p'=>'pulvérisations', 'g'=>'f'),
        'puverisation' => array('s'=>'pulvérisation', 'p'=>'pulvérisations', 'g'=>'f'),
        'recipient unidose' => array('s'=>'récipient unidose', 'p'=>'récipients unidoses', 'g'=>'m'),
        'sachet' => array('s'=>'sachet', 'p'=>'sachets', 'g'=>'m'),
        'seringue preremplie' => array('s'=>'seringue preremplie', 'p'=>'seringues preremplies', 'g'=>'f'),
        'suppositoire' => array('s'=>'suppositoire', 'p'=>'suppositoires', 'g'=>'m'),
        'tube' => array('s'=>'tube', 'p'=>'tubes', 'g'=>'m'),
      );
      if(key_exists($forme,$f)) {
        if($nb=='-1') return $f[$forme]['g'];
        elseif($nb>1) return $f[$forme]['p'];
        else return $f[$forme]['s'];
      }
      return $formeOriginelle;

    }
/**
 * Durée total entre plusieurs lignes de prescription
 * @param  array  $indexLignes index des lignes à considérer
 * @return array              retour en human & machine
 */
    private function _dureeTotalePrescription($indexLignes=[]) {
      $duree = [
        'i'=> 0,
        'h'=> 0,
        'j'=> 0,
        's'=> 0,
        'm'=> 0
      ];
      // toutes les lignes traitées
      if(empty($indexLignes)) {
        foreach($this->_lignesTraitees as $l) {
          if(!empty($l['duree'])) $duree[$l['dureeUnite']]=$duree[$l['dureeUnite']]+$l['duree'];
        }
      }
      // sinon
      else {
        foreach($this->_lignesTraitees as $k=>$l) {
          if(in_array($k,$indexLignes) and !empty($l['duree'])) $duree[$l['dureeUnite']]=$duree[$l['dureeUnite']]+$l['duree'];
        }
      }

      if($duree['i'] / 60 >= 1) {
        $duree['h'] = $duree['h'] + floor($duree['i'] / 60);
        $duree['i'] = $duree['i'] % 60;
      }
      if($duree['h'] / 24 >= 1) {
        $duree['j'] = $duree['j'] + floor($duree['h'] / 24);
        $duree['h'] = $duree['h'] % 24;
      }
      if($duree['j'] / 7 >= 1) {
        $duree['s'] = $duree['s'] + floor($duree['j'] / 7);
        $duree['j'] = $duree['j'] % 7;
      }
      if($duree['s'] / 4 >= 1) {
        $duree['m'] = $duree['m'] + floor($duree['s'] / 4);
        $duree['s'] = $duree['s'] % 4;
      }
      $dureeTotaleHuman =[];
      foreach($duree as $dureeUnite=>$nb) {
        if($nb > 0) $dureeTotaleHuman[] = $nb.' '.$this->_dureeAbrevEnMots($nb, $dureeUnite);
      }

      //retour en jours si durée < 15j
      if($duree['m']==0 and $duree['s']<2 and $duree['s']>0 and $duree['i']==0 and $duree['h']==0) {
        $nouvelleDureeJours = $duree['s']*7 + $duree['j'];
        if($nouvelleDureeJours>1) $nouvelleDureeJours=$nouvelleDureeJours.' jours'; else $nouvelleDureeJours=$nouvelleDureeJours.' jour';
        $dureeTotaleHuman=array($nouvelleDureeJours);
      }

      $retour['dureeTotaleMachineMinutes']= $duree['i']
        + ($duree['h'] * 60 )
        + ($duree['j'] * 60 * 24 )
        + ($duree['s'] * 60 * 24 * 7)
        + ($duree['m'] * 60 * 24 * 28);

      $retour['dureeTotaleHuman']=implode(' ',array_reverse($dureeTotaleHuman));
      $retour['dureeTotaleMachine']= $duree;
      $retour['dureeTotaleMachineJours']= $duree['j'] + ($duree['s'] * 7) + ($duree['m'] * 28);
      if($retour['dureeTotaleMachineJours'] > 0) {
        $retour['dureeTotaleMachineJoursAvecRenouv']=$retour['dureeTotaleMachineJours']*($this->_nbRenouvellements + 1);
      } else {
        $retour['dureeTotaleMachineJoursAvecRenouv']=$this->_nbRenouvellements + 1;
      }
      return $retour;
    }

/**
 * Obtenir le statut de stupéfiant en analysant les infos de dispensation
 * @param  array $tab infos dispensation
 * @return array      infos stup
 */
    private function _analyseInfosDispensation($tab) {
      $rd=[];
      $rd['stupefiant'] = 'n';
        if(!empty($tab)) {
          $acol = array_column($tab, 'info_1');
          if(in_array('STUPEFIANT', $acol)) {
            $rd['stupefiant'] = 'o';
          }
        }
      return $rd;
    }

/**
 * Obtenir la poso en lettres si classé stupéfiant
 * @param  string $nombre nombre
 * @return string         nombre, en lettre si nécessaire
 */
    private function _uniteFxStup($nombre) {
      if($this->_stupefiant == 'n') {
        return $nombre;
      } else {
        return $this->_convertNombreLettres($nombre).' ('.$nombre.')';
      }
    }

/**
 * Convertir un nombre en lettres avec accord du genre
 * @param  float  $nombre nombre
 * @return string         nombre en lettres
 */
    private function _convertNombreLettres($nombre) {
      $math = new Webit\Util\EvalMath\EvalMath;
      $nombre = $math->evaluate(str_replace(",", ".", $nombre));

      if($this->_genreNombre == 'f') {
        $pattern = '%spellout-cardinal-feminine';
      } else {
        $pattern = '%spellout-cardinal-masculine';
      }

      $fmt = new NumberFormatter( 'fr', NumberFormatter::SPELLOUT);
      $fmt->setTextAttribute(NumberFormatter::DEFAULT_RULESET, $pattern);

      if(strpos($nombre, ',') != false) {
        $part=explode(',', $nombre);
        $part[0]=$fmt->format($part[0]);
        $part[1]=$fmt->format($part[1]);
        return implode(' virgule ', $part);
      } else {
        return $fmt->format($nombre);
      }
    }

}
