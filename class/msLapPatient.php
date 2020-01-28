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
 * LAP : méthodes concernant le patient et ses atcd
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msLapPatient extends msLap
{

/**
* @var array $_patientAdminData données administratives du patient
*/
   protected $_patientAdminData;

/**
* Obtenir les données administratives patient
* @return array tableau de résultats des tests
*/
   public function getPatientAdminData()
   {
       return $this->_patientAdminData;
   }

/**
 * Obetnir l'objet patient pour analyse Theriaque
 * @return array data patient
 */
     public function getPatientObjetTheriaque() {
        global $p;
        $patient= new msPeople;
        $patient->setToID($this->_toID);

        $data=new msObjet;
        $data->setToID($this->_toID);
        $tab['datenaissance']=$data->getLastObjetValueByTypeName('birthdate');
        $tab['sexe']=$data->getLastObjetValueByTypeName('administrativeGenderCode');
        $tab['taille']=$data->getLastObjetValueByTypeName('taillePatient');
        $tab['poids']=$data->getLastObjetValueByTypeName('poids');
        $tab['surfacecorp']='';

        // grossesse et allaitement
        $tab['grossesse']=0;
        $tab['date_grossesse']='';
        $tab['typ_date_gross']=1;
        $tab['allaitement']=0;
        if( $tab['sexe'] == 'F') {
         $grossesse=$this->_checkGrossesse();
         if($grossesse['statut'] == 'grossesseEnCours') {
           $tab['grossesse']=1;
           $tab['date_grossesse']=$grossesse['ddg'];
           $tab['typ_date_gross']=1;
         }
         if($data->getLastObjetValueByTypeName('allaitementActuel') == 'true') {
           $tab['allaitement']=1;
         } else {
           $tab['allaitement']=0;
         }
        }

        $tab['age_procreer']='';

        $tab['clairance']=$data->getLastObjetValueByTypeName('clairanceCreatinine');
        $tab['insufhepat']=$data->getLastObjetValueByTypeName('insuffisanceHepatique');
        if(!is_numeric($tab['insufhepat'])) $tab['insufhepat']=0;

        $tab['etatpatho']='';

        $etatpatho_cim=$patient->getAtcdAndAldCim10Codes();
        if(!empty($etatpatho_cim)) $tab['etatpatho_cim']=implode(", ",$etatpatho_cim); else $tab['etatpatho_cim']='';

        //allergies
        if(!empty(trim($p['config']['lapAllergiesStrucPersoPourAnalyse']))) {
          $hypersensibilite=$patient->getAllergiesCodes($p['config']['lapAllergiesStrucPersoPourAnalyse']);
          if(!empty($hypersensibilite)) $tab['hypersensibilite']=implode(', ', $hypersensibilite); else $tab['hypersensibilite']='';
        }

        return $tab;
     }

/**
* Effectuer tous les tests de controle patients
* @return array tableau de résultats des tests
*/
  public function getPatientBasicPhysioDataControle()
  {
      if (!isset($this->_toID)) {
          throw new Exception('ToID is not numeric');
      } else {
          $data['poids']=$this->_checkPoids();
          $data['taillePatient']=$this->_checkTaillePatient();
          $data['clairanceCreatinine']=$this->_checkClairanceCreatinine();
          $data['creatinine']=$this->_checkCreatinine();
          $data['allaitement']=$this->_checkAllaitement();
          $data['grossesse']=$this->_checkGrossesse();
          $data['grossesseSimple']=$this->_checkGrossesseSimple();
          $data['statutHepatique']=$this->_checkStatutHepatique();
          $data['statutFxRenale']=$this->_checkInsuffisanceRenale();

          return $data;
      }
  }

/**
* Sortir et vérifier poids patient
* @return array array sur infos poids patient
*/
private function _checkPoids()
{
   $data=new msObjet;
   $data->setToID($this->_toID);

   if ($data=$data->getLastObjetByTypeName('poids')) {
       $now = new DateTime();
       $now->setTime(0, 0, 0);
       $datePoids = DateTime::createFromFormat('Y-m-d H:i:s', $data['updateDate']);
       $datePoids->setTime(0, 0, 0);
       $interval = $now->diff($datePoids);
       $nbjours=(integer)$interval->format('%a');

       if (is_numeric($data['value']) and $data['value']>30) {
           $statut='ok';
       } elseif (is_numeric($data['value']) and $data['value']>0 and $data['value']<=30 and $nbjours==0) {
           $statut='ok';
       } elseif (is_numeric($data['value']) and $data['value']>0 and $data['value']<=30 and $nbjours!=0) {
           $statut='oldValue';
       } else {
           $statut='incorectValue';
       }

       $rd=array(
        'statut'=>$statut,
        'date'=>$data['updateDate'],
        'from'=>$data['prenom'].' '.$data['nom'],
        'fromID'=>$data['fromID'],
        'value'=>$data['value']
      );

       return $rd;
   } else {
       return $rd=array('statut'=>'missingValue');
   }
}

/**
* Sortir et vérifier taille patient
* @return array array sur infos taille patient
*/
private function _checkTaillePatient()
{
    $data=new msObjet;
    $data->setToID($this->_toID);

    if ($data=$data->getLastObjetByTypeName('taillePatient')) {
        if (is_numeric($data['value']) and $data['value']>20 and $data['value']<=250) {
            $statut='ok';
        } else {
            $statut='incorectValue';
        }

        $rd=array(
       'statut'=>$statut,
       'date'=>$data['updateDate'],
       'from'=>$data['prenom'].' '.$data['nom'],
       'fromID'=>$data['fromID'],
       'value'=>$data['value']
     );

        return $rd;
    } else {
        return $rd=array('statut'=>'missingValue');
    }
}


 /**
  * Sortir et vérifier le stade insuffisance rénale
  * @return array array sur infos insuffisance rénale
  */
  private function _checkInsuffisanceRenale()
  {
      $data=new msObjet;
      $data->setToID($this->_toID);
      $statut='statutIRInconnu';
      if ($data=$data->getLastObjetByTypeName('insuffisanceRenale')) {
          if ($data['value'] == 'z') {
              $statut='statutIRInconnu';
          } elseif ($data['value'] == 'n') {
              $statut='statutIROk';
          } elseif ($data['value'] == '1') {
              $statut='statutIRStade1';
          } elseif ($data['value'] == '2') {
              $statut='statutIRStade2';
          } elseif ($data['value'] == '3') {
              $statut='statutIRStade3';
          } elseif ($data['value'] == '4') {
              $statut='statutIRStade4';
          } elseif ($data['value'] == '5') {
              $statut='statutIRStade5';
          }

          $rd=array(
           'statut'=>$statut,
           'date'=>$data['updateDate'],
           'from'=>$data['prenom'].' '.$data['nom'],
           'fromID'=>$data['fromID'],
           'value'=>$data['value']
         );

          return $rd;
      } else {
          return $rd=array('statut'=>'missingValue');
      }
  }

/**
 * Sortir et vérifier clairance créatinine patient
 * @return array array sur infos clairance créatinine patient
 */
 private function _checkClairanceCreatinine()
 {
     $data=new msObjet;
     $data->setToID($this->_toID);

     if ($data=$data->getLastObjetByTypeName('clairanceCreatinine')) {
         if (is_numeric($data['value'])) {
             $statut='ok';
         } else {
             $statut='incorectValue';
         }

         $rd=array(
          'statut'=>$statut,
          'date'=>$data['updateDate'],
          'from'=>$data['prenom'].' '.$data['nom'],
          'fromID'=>$data['fromID'],
          'value'=>$data['value']
        );

         return $rd;
     } else {
         return $rd=array('statut'=>'missingValue');
     }
 }

/**
* Sortir et vérifier créatinine patient (2 unités gérées)
* @return array array sur infos créatinine patient
*/
 private function _checkCreatinine()
 {
   $data=new msObjet;
   $data->setToID($this->_toID);

   $creatinineMgL=$data->getLastObjetByTypeName('creatinineMgL');
   $creatinineMicroMolL=$data->getLastObjetByTypeName('creatinineMicroMolL');

   if(isset($creatinineMgL['updateDate']) and isset($creatinineMicroMolL['updateDate'])) {
     if( $creatinineMgL['updateDate'] > $creatinineMicroMolL['updateDate'] ) {

       $rd=array(
         'statut'=>'ok',
         'date'=>$creatinineMgL['updateDate'],
         'from'=>$creatinineMgL['prenom'].' '.$creatinineMgL['nom'],
         'fromID'=>$creatinineMgL['fromID'],
         'value'=>$creatinineMgL['value'],
         'units'=>'mg/L'
       );

     } else {

       $rd=array(
         'statut'=>'ok',
         'date'=>$creatinineMicroMolL['updateDate'],
         'from'=>$creatinineMicroMolL['prenom'].' '.$creatinineMicroMolL['nom'],
         'fromID'=>$creatinineMicroMolL['fromID'],
         'value'=>$creatinineMicroMolL['value'],
         'units'=>'µmol/L'
       );

     }
   } elseif(isset($creatinineMgL['updateDate'])) {

     $rd=array(
       'statut'=>'ok',
       'date'=>$creatinineMgL['updateDate'],
       'from'=>$creatinineMgL['prenom'].' '.$creatinineMgL['nom'],
       'fromID'=>$creatinineMgL['fromID'],
       'value'=>$creatinineMgL['value'],
       'units'=>'mg/L'
     );

   } elseif(isset($creatinineMicroMolL['updateDate'])) {

     $rd=array(
       'statut'=>'ok',
       'date'=>$creatinineMicroMolL['updateDate'],
       'from'=>$creatinineMicroMolL['prenom'].' '.$creatinineMicroMolL['nom'],
       'fromID'=>$creatinineMicroMolL['fromID'],
       'value'=>$creatinineMicroMolL['value'],
       'units'=>'µmol/L'
     );

   } else {
     $rd=array(
       'statut'=>'missingValue',
     );
   }
   return $rd;
 }

 /**
  * Sortir et vérifier le statut hépatique
  * @return array array sur infos statut hépatique
  */
  private function _checkStatutHepatique()
  {
      $data=new msObjet;
      $data->setToID($this->_toID);
      $statut='statutHepatiqueInconnu';
      if ($data=$data->getLastObjetByTypeName('insuffisanceHepatique')) {
          if ($data['value'] == 'z') {
              $statut='statutHepatiqueInconnu';
          } elseif ($data['value'] == 'n') {
              $statut='statutHepatiqueOk';
          } elseif ($data['value'] == '1') {
              $statut='statutHepatiqueIhl';
          } elseif ($data['value'] == '2') {
              $statut='statutHepatiqueIhm';
          } elseif ($data['value'] == '3') {
              $statut='statutHepatiqueIhs';
          }

          $rd=array(
           'statut'=>$statut,
           'date'=>$data['updateDate'],
           'from'=>$data['prenom'].' '.$data['nom'],
           'fromID'=>$data['fromID'],
           'value'=>$data['value']
         );

          return $rd;
      } else {
          return $rd=array('statut'=>'missingValue');
      }
  }

/**
* Sortir et vérifier allaitement patient
* @return array array sur infos allaitement patient
*/
private function _checkAllaitement()
{
    if ($this->_patientAdminData['administrativeGenderCode']!='F') {
        return $rd=array('statut'=>'notConcerned');
    } else {
        $data=new msObjet;
        $data->setToID($this->_toID);
        $data->getLastObjetByTypeName('allaitementActuel');
        if ($data=$data->getLastObjetByTypeName('allaitementActuel')) {
          if($data['value']=='true') {
            $rd['statut']='allaitementEnCours';

            // correction statut si déclaré depuis + 3ans
            $now = new DateTime();
            $date = new DateTime($data['creationDate']);
            $date->add(new DateInterval('P3Y'));
            if($date < $now) $rd['statut']='alerteAllaitementLong';

          } else {
            $rd['statut']='absenceAllaitement';
          }
          $rd['date']=$data['creationDate'];
          $rd['from']=$data['prenom'].' '.$data['nom'];
          $rd['fromID']=$data['fromID'];
          $rd['basedOn']='value';
        } else {
          $rd['statut']='absenceAllaitement';
          $rd['basedOn']='missingValue';
        }
        return $rd;
    }
}

/**
 * Sortir et vérifier l'état de grossesse
 * @return array array sur infos DDR patient
 */
 private function _checkGrossesse()
 {
     global $p;
     if ($this->_patientAdminData['administrativeGenderCode']!='F') {
         return $rd=array('statut'=>'notConcerned');
     } else {

       //chercher une grossesse en cours (cad si pas de type 245 associé)
       $name2typeID=new msData;
         $name2typeID = $name2typeID->getTypeIDsFromName(['groFermetureSuivi', 'nouvelleGrossesse']);
         if ($findGro=msSQL::sqlUnique("select pd.id as idGro, eg.id as idFin
           from objets_data as pd
           left join objets_data as eg on pd.id=eg.instance and eg.typeID='".$name2typeID['groFermetureSuivi']."' and eg.outdated='' and eg.deleted=''
           where pd.toID='".$this->_toID."' and pd.typeID='".$name2typeID['nouvelleGrossesse']."' and pd.outdated='' and pd.deleted='' order by pd.creationDate desc
           limit 1")) {
             if (!$findGro['idFin']) {
                 $objet=new msObjet;
                 $objet->setToID($this->_toID);

                 //$moduleClass="msMod".ucfirst($p['user']['module'])."CalcMed";
                 $moduleClass="msModGynobsCalcMed";

                 if ($data=$objet->getLastObjetByTypeName('ddgReel', $findGro['idGro'])) {
                    $rd['ddg']=$data['value'];
                    $rd['basedOn']='ddgReel';
                 } elseif ($data=$objet->getLastObjetByTypeName('DDR', $findGro['idGro'])) {
                    if(empty($data['value'])) return $rd=array('statut'=>'missingValue');
                    $rd['ddr']=$data['value'];
                    $rd['ddg']=$moduleClass::ddr2ddg($data['value']);
                    $rd['basedOn']='DDR';
                 } else {
                    return $rd=array('statut'=>'missingValue');
                 }
                 if(isset($rd['ddg'])) {
                   $rd['terme']=$moduleClass::ddg2terme($rd['ddg'], date('d/m/Y'));
                   $rd['termeMath']=$moduleClass::ddg2termeMath($rd['ddg'], date('d/m/Y'));
                 }

                 if ($rd['termeMath']>46) {
                     $rd['statut']='termeDepasse46SA';
                 } else {
                     $rd['statut']='grossesseEnCours';
                 }
                 $rd['date']=$data['updateDate'];
                 $rd['from']=$data['prenom'].' '.$data['nom'];
                 $rd['fromID']=$data['fromID'];

                 return $rd;
             } else {
                 return $rd=array('statut'=>'absenceGrossesse');
             }
         } else {
             return $rd=array('statut'=>'absenceGrossesse');
         }
     }
 }

 /**
 * Sortir et vérifier grossesse sur simple switch on/off
 * @return array array sur infos grossesse patient
 */
 private function _checkGrossesseSimple()
 {
     if ($this->_patientAdminData['administrativeGenderCode']!='F') {
         return $rd=array('statut'=>'notConcerned');
     } else {
         $data=new msObjet;
         $data->setToID($this->_toID);
         $data->getLastObjetByTypeName('grossesseActuelle');
         if ($data=$data->getLastObjetByTypeName('grossesseActuelle')) {
           if($data['value']=='true') {
             $rd['statut']='grossesseEnCours';
           } else {
             $rd['statut']='absenceGrossesse';
           }
           $rd['date']=$data['creationDate'];
           $rd['from']=$data['prenom'].' '.$data['nom'];
           $rd['fromID']=$data['fromID'];
           $rd['basedOn']='value';
         } else {
           $rd['statut']='absenceGrossesse';
           $rd['basedOn']='missingValue';
         }
         return $rd;
     }
 }


}
