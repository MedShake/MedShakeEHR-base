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
            return $this->$_versionInterpreteur = $v;
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
 * Retourner toutes les datas utiles à la fenêtre de prescription
 * @return array tableau des données utiles à la prescription
 */
    public function lapInstallPrescription() {

      //data légales Thériaque
      $dataTheriaque=$this->getTheriaqueInfos();

      //spécialité
      $dataSpe=$this->getSpecialiteByCode($this->_speThe,1,3);
      $this->_nomSpe = $dataSpe[0]['sp_nom'];
      if($dataSpe[0]['mono_vir'] == 1) {
        $this->_medicVirtuel = 1;
      } else {
        $this->_medicVirtuel = 0;
      }

      // présentation
      $dataPres=$this->_get_the_presentation($this->_presThe, 2);
      // unités
      $dataSpeUnite=$this->getUnite($this->_speThe, 0);
      // voies d'administration possibles
      $dataSpeVoiesAdmin=$this->getVoiesAdministration($this->_speThe);

      //recherche DC
      $dataGenerique=$this->getDC(2, $dataSpe[0]['sp_gsp_code_fk'], 1);
      $this->_nomDC = $dataGenerique[0]['libelle'];
      $this->_prescriptibleEnDC = $dataGenerique[0]['prescription_dc'];

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

      // if(!empty($dataSpeUnite[0]['unite_prise'])) {
      //   $this->_forme=$dataSpeUnite[0]['unite_prise'];
      // } elseif(!empty($dataPres[0]['unite_prise'])) {
      //   $this->_forme=$dataPres[0]['unite_prise'];
      // } elseif(!empty($dataPres[0]['pre_nat'])) {
      //   $this->_forme=$dataPres[0]['pre_nat'];
      // } elseif(isset($dataSpeUnite[0]['ucd'])) {
      //   $this->_forme=$dataSpeUnite[0]['ucd'];
      // } else {
      //   $this->_forme='';
      // }

      $tab = array(
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
        'TheriaqueVersion'=>$dataTheriaque[0]['vers'].' '.$dataTheriaque[0]['date_ext'],
        'conducteur'=>array(
          'reco'=>$dataConducteur[0]['reco'],
          'niveau'=>$dataConducteur[0]['niv'],
          'libelle_niv'=>$dataConducteur[0]['libelle_niv']
        ),
        'dopage'=>$dataDopage[0]['niveau']
      );


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
            $human[] = $humanPosoBase[$k].' '.$humanPosoDuree[$k].' '.$humanPosoTraine[$k];
          } else {
            $human[] = $humanPosoBase[$k].' '.$humanPosoTraine[$k];
          }
          $humanBase[] = $humanPosoBase[$k];
        }
        $this->_prescriptionInterpretee['posoHumanComplete'] = trim(str_replace('  ', ' ', implode("\n", $human)));
        $this->_prescriptionInterpretee['posoHumanCompleteTab'] = $human;
        $this->_prescriptionInterpretee['posoHumanBase'] = trim(str_replace('  ', ' ', implode("\n", $humanBase)));

        // la voie d'administration utilisée (txt humain)
        $this->_prescriptionInterpretee['voieUtilisee'] = ucfirst($this->_voieUtilisee);

        // la poso journalière max toutes lignes confondues
        $this->_prescriptionInterpretee['posoJournaliereMax']=(float)max(array_column($this->_lignesPosologiques,'posoJournaliere'));

        // la poso Max par prise toutes lignes confondues
        $this->_prescriptionInterpretee['posoMaxParPriseMax']=(float)max(array_column($this->_lignesPosologiques,'posoMaxParPrise'));

        // la poso Min par prise toutes lignes confondues (avec retrait 0 !)
        $this->_prescriptionInterpretee['posoMinParPriseMin']=(float)@min(array_filter(array_column($this->_lignesPosologiques,'posoMinParPrise')));

        //print_r($this->_lignesPosologiques);

        // durée totale de la prescription
        $dureeTotale = $this->_dureeTotalePrescription();
        $this->_prescriptionInterpretee['dureeTotaleHuman']=$dureeTotale['dureeTotaleHuman'];
        $this->_prescriptionInterpretee['dureeTotaleMachine']=$dureeTotale['dureeTotaleMachine'];
        $this->_prescriptionInterpretee['dureeTotaleMachineJours']=$dureeTotale['dureeTotaleMachineJours'];
        $this->_prescriptionInterpretee['dureeTotaleMachineJoursAvecRenouv']=$dureeTotale['dureeTotaleMachineJoursAvecRenouv'];
        $this->_prescriptionInterpretee['nbRenouvellements']=$this->_nbRenouvellements;

        // controle secabilité
        $this->_controleSecabilite();

      }

    }

/**
 * Détecter un problème de sécabilité
 * @return boolean true si pb de sécabilité
 */
    private function _controleSecabilite() {
      //si sécabilité inexistante
      if($this->_divisibleEn == -1 and $this->_prescriptionInterpretee['posoMinParPriseMin'] < 1) {
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
          $human = $m['doseMatin'] .' '. $this->_uniteAccordee($this->_uniteUtilisee,$m['doseMatinMath']) . ' matin midi et soir ';
          $tab['nbPrisesParUniteTemps']=4;
        }
        // si poso m m s est différente
        else {
          if ($m['doseMatinMath'] > 0) $pmms[] = $m['doseMatin'].' ' . $this->_uniteAccordee($this->_uniteUtilisee,$m['doseMatinMath']) . ' le matin';
          if ($m['doseMidiMath'] > 0) $pmms[] = $m['doseMidi'].' ' . $this->_uniteAccordee($this->_uniteUtilisee,$m['doseMidiMath']) . ' le midi';
          if ($m['doseSoirMath'] > 0) $pmms[] = $m['doseSoir'].' ' . $this->_uniteAccordee($this->_uniteUtilisee,$m['doseSoirMath']) . ' le soir';
          if ($m['doseCoucherMath'] > 0) $pmms[] = $m['doseCoucher'].' ' . $this->_uniteAccordee($this->_uniteUtilisee,$m['doseCoucherMath']) . ' au coucher';

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
        $tab['posoJournaliere'] = (float)$m['doseMatinMath'] + (float)$m['doseMidiMath'] + (float)$m['doseSoirMath'] + (float)$m['doseCoucherMath'];
        $tab['posoDosesSuccessives'] = array($m['doseMatinMath'], $m['doseMidiMath'], $m['doseSoirMath'], $m['doseCoucherMath']);
        $tab['nbPrisesParUniteTempsUnite'] ='';
        $tab['posoJours'] = $m['joursSemaine'];
        $tab['posoMaxParPrise'] = (float)max($m['lesDoses']);
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
        $tab['posoJournaliere'] = (float)$m['doseMath'] * $m['multipleDoseMath'];
        $tab['posoTheriaqueMode'] = 1;
        $tab['posoDosesSuccessives'] = array($m['doseMath']);
        $tab['nbPrisesParUniteTempsUnite'] = $m['multipleUnite'];
        $tab['posoJours'] = $m['joursSemaine'];
        $tab['posoMaxParPrise'] = (float)$m['doseMath'];
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
            $tab['posoJournaliere'] = 0;
            $tab['posoTheriaqueMode'] = 1;
            $tab['posoDosesSuccessives'] = 0;
            $tab['nbPrisesParUniteTempsUnite'] = 0;
            $tab['posoJours'] = '';
            $tab['posoMaxParPrise'] = 0;
            $tab['posoMinParPrise'] = 0;
            $tab['humanPosoBase'] = 'posologie inconnue';
            $tab['humanPosoDuree'] = ' - ' . $m['dureeNumeric'] . ' ' . $dureeHuman;
            $tab['humanPosoTraine'] = '';
            $tab['regEx']='2';

            $this->_lignesPosologiques[] = $tab;
            return $this->_lignesTraitees[$indexLigne] = $tab;
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
      $regEx[1][1] = "/^(et|puis)?\s*([0-9\/,\.]+) ([0-9]+)x(h|j|s|m){1}(?: ([lmMjvsdip]*))? (?:([0-9]+)(h|j|s|m))?(.*)/i";

      // posologie inconnue
      $regEx[1][2] = "/^(nc|\?) (?:([0-9]+)(j|s|m))/i";

      return $regEx[$this->_versionInterpreteur];

    }

/**
 * Accorder des unités
 * @param  string $forme mot à accorder
 * @param  int $nb    nombre
 * @return string        mot accordé
 */
    private function _uniteAccordee($forme, $nb) {
      $f=array(
        'APPLICATION(S)' => array('s'=>'application', 'p'=>'applications'),
        'application' => array('s'=>'application', 'p'=>'applications'),
        'COMPRIME(S)' => array('s'=>'comprimé', 'p'=>'comprimés'),
        'comprimé' => array('s'=>'comprimé', 'p'=>'comprimés'),
        'comprime' => array('s'=>'comprimé', 'p'=>'comprimés'),
        'DOSE(S)' => array('s'=>'dose', 'p'=>'doses'),
        'EMPLATRE(S)' => array('s'=>'emplâtre', 'p'=>'emplâtres'),
        'GELULE' => array('s'=>'gélule', 'p'=>'gélules'),
        'gélule' => array('s'=>'gélule', 'p'=>'gélules'),
        'GOUTTE(S)' => array('s'=>'goutte', 'p'=>'gouttes'),
        'goutte' => array('s'=>'goutte', 'p'=>'gouttes'),
        'PULVERISATION(S)' => array('s'=>'puverisation', 'p'=>'pulverisations'),
        'pulverisation(s)' => array('s'=>'pulvérisation', 'p'=>'pulvérisations'),
        'puverisation' => array('s'=>'pulvérisation', 'p'=>'pulvérisations'),
        'RECIPIENT(S) UNIDOSE(S)' => array('s'=>'récipient unidose', 'p'=>'récipients unidoses'),
        'SACHET(S)' => array('s'=>'sachet', 'p'=>'sachets'),
        'SUPPOSITOIRE' => array('s'=>'suppositoire', 'p'=>'suppositoires'),
      );
      if(key_exists($forme,$f)) {
        if($nb>1) return $f[$forme]['p'];
        else return $f[$forme]['s'];
      }
      return strtolower($forme);

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

}
