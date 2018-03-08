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
        $tab['birthdate']=$data->getLastObjetValueByTypeName('birthdate');
        $tab['sexe']=$data->getLastObjetValueByTypeName('administrativeGenderCode');
        $tab['taille']=$data->getLastObjetValueByTypeName('taillePatient');
        $tab['poids']=$data->getLastObjetValueByTypeName('poids');
        $tab['surfacecorp']='';

        // grossesse et allaitement
        $tab['grossesse']=0;
        $tab['date_grossesse']='';
        $tab['type_date_gross']=1;
        $tab['allaitement']=0;
        if( $tab['sexe'] == 'F') {
         $grossesse=$data->_checkGrossesse();
         if($grossesse['statut'] == 'grossesseEnCours') {
           $tab['grossesse']=1;
           $tab['date_grossesse']=$grossesse['ddg'];
           $tab['type_date_gross']=1;
         }

         $grossesse=$data->_checkAllaitement();
         if($grossesse['statut'] == 'allatementEnCours') {
           $tab['allaitement']=1;
         }
        }

        $tab['age_procreer']='';

        $tab['clairance']='';
        $tab['insufhepat']='';

        $tab['etatpatho']='';


        $tab['etatpatho_cim']='';


        //allergies
        if(!empty(trim($p['config']['lapAllergiesStrucPersoPourAnalyse']))) {
          $tab['hypersensibilite']=implode(',', $patient->getAllergiesCodes($p['config']['lapAllergiesStrucPersoPourAnalyse']));
        }
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
          $data['allaitement']=$this->_checkAllaitement();
          $data['grossesse']=$this->_checkGrossesse();

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
                     $rd['ddr']=$data['value'];
                     $rd['ddg']=$moduleClass::ddr2ddg($data['value']);
                     $rd['basedOn']='DDR';
                 } else {
                     return $rd=array('statut'=>'missingValue');
                 }
                 $rd['terme']=$moduleClass::ddg2terme($rd['ddg'], date('d/m/Y'));
                 $rd['termeMath']=$moduleClass::ddg2termeMath($rd['ddg'], date('d/m/Y'));

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
}
