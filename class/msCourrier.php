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
 * Extraction de toutes les informations nécessaires à la rédaction
 * d'un courrier, certificat ou ordonnance
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

class msCourrier
{

/**
 * @var int $_objetID du document concerné
 */
    private $_objetID;
/**
 * @var int $_modeleID du document concerné
 */
    private $_modeleID;
/**
 * @var int $_modele du document concerné
 */
    private $_modele;
/**
 * @var int $_patientID du document concerné
 */
    private $_patientID;
/**
 * @var int $_fromID précision si besoin de l'utilisateur considéré comme actif
 */
    private $_fromID;
/**
 * @var int $_module du document concerné
 */
    private $_module;
/**
 * @var array $_objetData
 */
    private $_objetData;


/**
 * Définir l'objetID
 * @param int $objetID objetId du document concerné
 */
    public function setObjetID($objetID)
    {
      if (!is_numeric($objetID)) {
          throw new Exception('ObjetID is not numeric');
      }
      $this->_objetID = $objetID;
      $this->_getObjetData();
      return $this->_objetID;
    }

/**
 * Définir le modeleID
 * @param int $modeleID modeleID du document concerné
 */
    public function setModeleID($modeleID)
    {
        if(!is_numeric($modeleID)) throw new Exception('modeleID is not numeric');
        return $this->_modeleID = $modeleID;
    }

/**
 * Définir le modeleID par le nom du modèle
 * @param int $name modeleID du document concerné
 */
    public function setModeleIDByName($name)
    {
        $id = msData::getTypeIDFromName($name);
        if(!is_numeric($id)) throw new Exception('modeleName is wrong');
        $this->_modele = $name;
        return $this->_modeleID = $id;
    }

/**
 * Définir le patientID
 * @param int $patientID patientID du document concerné
 */
    public function setPatientID($patientID)
    {
        if(!msPeople::checkPeopleExist($patientID)) {
          throw new Exception('patientID does not exist');
        }
        return $this->_patientID = $patientID;
    }

/**
 * Définir l'utilisateur considéré comme actif
 * Utilisé en partciulier pour getCourrierData à la signature numérique
 * @param int $fromID ID de l'utilisateur actif
 */
    public function setFromID($fromID)
    {
        if(!msPeople::checkPeopleExist($fromID)) {
          throw new Exception('fromID does not exist');
        }
        return $this->_fromID = $fromID;
    }

/**
 * Définir le module
 * @param int $module module du document concerné
 */
    public function setModule($module)
    {
        if(!in_array($module, msModules::getInstalledModulesNames())) throw new Exception('Module has wrong value');
        return $this->_module = $module;
    }

/**
 * Obtenir le modele ID
 * @return int modeleID
 */
    public function getModeleID() {

      if (!isset($this->_modeleID)) {
          throw new Exception('ModelID is not defined');
      }

      return $this->_modeleID;
    }

/**
 * Retourner les data courrier à partir d'un objetID
 * @return array Tableau typeID=>value
 */
    public function getDataByObjetID()
    {
        if (!is_numeric($this->_objetID)) {
            throw new Exception('ObjetID is not numeric');
        }
        if(!isset($this->_objetData)) {
          $this->_getObjetData();
        }
        $this->_patientID=$this->_objetData['toID'];
        $tagsValues=[];
        if ($this->_objetData['groupe']=="courrier" or $this->_objetData['groupe']=="doc") {
            $this->_modeleID = $this->_objetData['typeID'];
            $tagsValues=$this->getCourrierData();
        } elseif ($this->_objetData['groupe']=="typecs") {
            $tagsValues=$this->getCrData();
        } elseif ($this->_objetData['groupe']=="ordo") {
            $tagsValues=$this->getOrdoData();
        } elseif($this->_objetData['groupe']=="reglement") {
            $tagsValues=$this->getReglementData();
        }
        return $tagsValues;
    }

/**
 * Sortir tous les data d'un examen à partir du $_objetID pour rédaction de compte rendu examen
 * @return array tableau key=>value
 */
    public function getCrData()
    {
        global $p;
        if (!is_numeric($this->_objetID)) {
            throw new Exception('ObjetID is not numeric');
        }
        if(!isset($this->_objetData)) {
          $this->_getObjetData();
        }

        //ajout
        $tabRetour['date']=$this->_objetData['creationDate'];
        // ajouter tags date
        if($dataDate=$this->_formatDate($tabRetour)) {
          $tabRetour=$tabRetour+$dataDate;
        }
        $tabRetour['objetID']=$this->_objetData['id'];
        $tabRetour['patientID']=$this->_objetData['toID'];
        $tabRetour['instance']=$this->_objetData['instance'];
        $tabRetour['printModel']=$this->getPrintModel($this->_objetData['formValues']).'.html.twig';
        $tabRetour['module']=$this->_getModuleOrigine($this->_objetData['formValues']);

        //patient data
        $tabRetour=$tabRetour+$this->_getPatientData($this->_objetData['toID']);

        //auteur initial data
        $tabRetour=$tabRetour+$this->_getPsData($this->_objetData['fromID'],'AuteurInitial_');

        //utilisateur qui a effectué la tâche si délégation
        if($this->_objetData['byID'] > 0) $tabRetour=$tabRetour+$this->_getPsData($this->_objetData['byID'],'DelegueA_');

        //data utilisateur courant
        if(isset($this->_fromID)) {
          $tabRetour=$tabRetour+$this->_getPsData($this->_fromID,'UtilisateurActif_');
        } elseif(isset($p['user']['id'])) {
          $tabRetour=$tabRetour+$this->_getPsData($p['user']['id'],'UtilisateurActif_');
        }

        //examen data
        $tabRetour=$tabRetour+$this->getExamenData($this->_objetData['toID'], $this->_objetData['formValues'], $this->_objetData['id']);

        //data de l'instance mère
        if ($this->_objetData['instance']>0) {
          $dataInstanceMere=$this->_getInstanceMereData($this->_objetData['instance']);
          if(!empty($dataInstanceMere)) {
            $tabRetour=$tabRetour+$dataInstanceMere;
          }
        }

        ksort($tabRetour, SORT_REGULAR);

        //la class spécifique au module
        $moduleClass="msMod".ucfirst($tabRetour['module'])."DataCourrier";

        //complément dans le module ?
        if (method_exists($moduleClass, "getCrDataCompleteModule")) {
            $moduleClass::getCrDataCompleteModule($tabRetour);
        }

        //complément dans le module pour ce formulaire spécifique ?
        $methodToCall = "getCrDataCompleteModuleForm_".$this->_objetData['formValues'];
        if (method_exists($moduleClass, $methodToCall)) {
            $moduleClass::$methodToCall($tabRetour);
        }

        //calcules complémentaires sur les data si le type rencontré l'implique
        foreach ($tabRetour as $k=>$v) {
          $methodToCall = "type_".$k."_CompleteData";
          if (method_exists($moduleClass, $methodToCall)) {
              $moduleClass::$methodToCall($tabRetour);
          }
        }
        ksort($tabRetour, SORT_REGULAR);
        return $tabRetour;
    }

/**
 * Sortir tous les data d'un examen à partir du $_objetID pour rédaction règlement
 * @return array tableau key=>value
 */
    public function getReglementData()
    {
        global $p;
        if (!is_numeric($this->_objetID)) {
            throw new Exception('ObjetID is not numeric');
        }
        if(!isset($this->_objetData)) {
          $this->_getObjetData();
        }

        //ajout
        $tabRetour['date']=$this->_objetData['creationDate'];
        // ajouter tags date
        if($dataDate=$this->_formatDate($tabRetour)) {
          $tabRetour=$tabRetour+$dataDate;
        }
        $tabRetour['objetID']=$this->_objetData['id'];
        $tabRetour['patientID']=$this->_objetData['toID'];
        $tabRetour['instance']=$this->_objetData['instance'];
        if($printModel = $this->getPrintModel($this->_objetData['formValues'])) {
          $tabRetour['printModel']=$printModel.'.html.twig';
        } else {
          $tabRetour['printModel']='facture.html.twig';
        }
        $tabRetour['module']=$this->_getModuleOrigine($this->_objetData['formValues']);

        //patient data
        $tabRetour=$tabRetour+$this->_getPatientData($this->_objetData['toID']);

        //auteur initial data
        $tabRetour=$tabRetour+$this->_getPsData($this->_objetData['fromID'],'AuteurInitial_');

        //utilisateur qui a effectué la tâche si délégation
        if($this->_objetData['byID'] > 0) $tabRetour=$tabRetour+$this->_getPsData($this->_objetData['byID'],'DelegueA_');

        //data utilisateur courant
        if(isset($this->_fromID)) {
          $tabRetour=$tabRetour+$this->_getPsData($this->_fromID,'UtilisateurActif_');
        } elseif(isset($p['user']['id'])) {
          $tabRetour=$tabRetour+$this->_getPsData($p['user']['id'],'UtilisateurActif_');
        }

        // n° de facture YYYMMDDxxx
        // le dev de cette fonctionnalité a été financée par Mallaury T. (France)
        $data=new msData();
        $porteursReglementIds=array_column($data->getDataTypesFromCatName('porteursReglement', ['id']), 'id');
        if($factureID = msSQL::sqlUniqueChamp("select count(id) from objets_data
        where typeID in ('".implode("','", $porteursReglementIds)."') and DATE(creationDate) = DATE('".$this->_objetData['creationDate']."') and id <= '".$this->_objetData['id']."' and fromID = '".$this->_objetData['fromID']."'")) {
          $factureID=str_pad($factureID, 3, "0", STR_PAD_LEFT);
          $tabRetour['factureID']=$factureID;
          $dateF = DateTime::createFromFormat('Y-m-d H:i:s', $this->_objetData['creationDate']);
          $tabRetour['factureNumero']=$dateF->format('Ymd').$factureID;
        }

        //règlement data
        $reg = new msObjet;
        $reg->setObjetID($this->_objetID);
        if($tabReg = $reg->getObjetAndSons('name')) {
          foreach($tabReg as $name=>$val) {
            $tabReg[$name]=$val['value'];
          }
          if($tabRegDet = json_decode($tabReg['regleDetailsActes'], TRUE)) {
            foreach($tabRegDet as $k=>$v) {
              foreach($v as $k2=>$v2) {
                $tabRegDet[$k][$k2] = $v2;
              }
              $tabReg['regleDetailsActes']=$tabRegDet;
            }
          }
          $tabRetour=$tabRetour + $tabReg;
        }


        ksort($tabRetour, SORT_REGULAR);

        //la class spécifique au module
        $moduleClass="msMod".ucfirst($tabRetour['module'])."DataCourrier";

        //calcules complémentaires sur les data si le type rencontré l'implique
        foreach ($tabRetour as $k=>$v) {
          $methodToCall = "type_".$k."_CompleteData";
          if (method_exists($moduleClass, $methodToCall)) {
              $moduleClass::$methodToCall($tabRetour);
          }
        }
        ksort($tabRetour, SORT_REGULAR);
        return $tabRetour;
    }

/**
 * Retourne les data dans un array pour rédaction courrier / certificat
 * @return array             les patientData
 */
    public function getCourrierData()
    {
        global $p;
        if (!is_numeric($this->_patientID)) {
            throw new Exception('PatientID is not numeric');
        }

        $tabRetour = $this->_getPatientData($this->_patientID);
        $tabRetour['date']=date('Y-m-d H:i:s');
        // ajouter tags date
        if($dataDate=$this->_formatDate($tabRetour)) {
          $tabRetour=$tabRetour+$dataDate;
        }
        $tabRetour['patientID']=$this->_patientID;

		// data de l'auteur initial si l'objet existe
        	if (is_numeric($this->_objetID)) {
			$this->_getObjetData();
			$tabRetour=$tabRetour+$this->_getPsData($this->_objetData['fromID'],'AuteurInitial_');
        	}
		// ou sinon l'auteur initial est l'utilisateur actif
		else if (is_numeric($this->_fromID)) {
			$tabRetour=$tabRetour+$this->_getPsData($this->_fromID, 'AuteurInitial_');
		}

        //data utilisateur courant
        if(isset($this->_fromID)) {
          $tabRetour=$tabRetour+$this->_getPsData($this->_fromID,'UtilisateurActif_');
        } elseif(isset($p['user']['id'])) {
          $tabRetour=$tabRetour+$this->_getPsData($p['user']['id'],'UtilisateurActif_');
        }

        if (!isset($this->_modeleID) and is_numeric($this->_objetID)) {
          $objetData=new msObjet();
          $objetData->setObjetID($this->_objetID);
          $objetData=$objetData->getObjetDataByID(['typeID']);
          $this->_modeleID=$objetData['typeID'];
        }

        if (isset($this->_modeleID)) {
          $objetModule=new msData();
          $objetModule=$objetModule->getDataType($this->_modeleID, ['module','name']);
          $tabRetour['module']=$objetModule['module'];
          $tabRetour['modeleName']=$objetModule['name'];
        } else {
          $tabRetour['module']='';
          $tabRetour['modeleName']='';
        }


        $moduleClass="msMod".ucfirst($tabRetour['module'])."DataCourrier";
        //complément général dans le module ?
        if (method_exists($moduleClass, "getCourrierDataCompleteModule")) {
            $moduleClass::getCourrierDataCompleteModule($tabRetour);
        }

        //complément dans le module pour ce modeleID spécifique ?
        if (isset($this->_modeleID)) {
            $methodToCall = "getCourrierDataCompleteModuleModele_".$tabRetour['modeleName'];
            if (method_exists($moduleClass, $methodToCall)) {
                $moduleClass::$methodToCall($tabRetour);
            }
        }

        //calcules complémentaires sur les data si le type rencontré l'implique
        foreach ($tabRetour as $k=>$v) {
            $methodToCall = "type".$k."CompleteData";
            if (method_exists($moduleClass, $methodToCall)) {
                $moduleClass::$methodToCall($tabRetour);
            }
        }

        return $tabRetour;
    }

/**
 * Retourne les patientData dans un array pour rédaction d'une ordonnance
 * @return array
 */
    public function getOrdoData()
    {
        global $p;
        if (!is_numeric($this->_objetID)) {
            throw new Exception('ObjetID is not numeric');
        }
        if(!isset($this->_objetData)) {
          $this->_getObjetData();
        }

        //patient data
        $tabRetour=$this->_getPatientData($this->_objetData['toID']);
        $tabRetour['patientID'] = $this->_objetData['toID'];

        //auteur initial data
        $tabRetour=$tabRetour+$this->_getPsData($this->_objetData['fromID'],'AuteurInitial_');

        //utilisateur qui a effectué la tâche si délégation
        if($this->_objetData['byID'] > 0) $tabRetour=$tabRetour+$this->_getPsData($this->_objetData['byID'],'DelegueA_');

        //data utilisateur courant
        if(isset($this->_fromID)) {
          $tabRetour=$tabRetour+$this->_getPsData($this->_fromID,'UtilisateurActif_');
        } elseif(isset($p['user']['id'])) {
          $tabRetour=$tabRetour+$this->_getPsData($p['user']['id'],'UtilisateurActif_');
        }

        //examen data
        $examData = new msObjet();
        $examData->setObjetID($this->_objetID);
        $tabRetour=$tabRetour+$examData->getObjetDataByID(['creationDate as date']);

        ksort($tabRetour, SORT_REGULAR);

        $tabRetour['module']='base';
        $moduleClass="msMod".ucfirst($tabRetour['module'])."DataCourrier";
        //complément dans le module ?
        if (method_exists($moduleClass, "getOrdoDataCompleteModule")) {
            $moduleClass::getOrdoDataCompleteModule($tabRetour);
        }

        //calcules complémentaires sur les data si le type rencontré l'implique
        foreach ($tabRetour as $k=>$v) {
            $methodToCall = "type".$k."CompleteData";
            if (method_exists($moduleClass, $methodToCall)) {
                $moduleClass::$methodToCall($tabRetour);
            }
        }

        return $tabRetour;
    }


/**
 * Retourne les patientData dans un array
 * @param  int $patientID ID de l'individu concerné
 * @return array             tableau avec les patientData
 */
    private function _getPatientData($patientID)
    {
        $patientData = new msPeople();
        $patientData->setToID($patientID);
        $tabPatientData = $patientData->getSimpleAdminDatas();

        $dat = new msData();
        $data = $dat->getSelectOptionValue(array_keys($tabPatientData));
        $typeId2name = $dat->getNamesFromTypeIDs(array_keys($tabPatientData));

        foreach ($tabPatientData as $k=>$v) {
            //tags numériques
            if (isset($data[$k][$v])) {
                $tabPatientData[$k]=$data[$k][$v];
                $tabPatientData['val'.$k]=$v;
            } else {
                $tabPatientData[$k]=$v;
            }

            //tags name
            if(array_key_exists($k,$typeId2name )) {
                if (isset($data[$k][$v])) {
                  $tabPatientData[$typeId2name[$k]]=$data[$k][$v];
                  $tabPatientData['val_'.$typeId2name[$k]]=$v;
                } else {
                  $tabPatientData[$typeId2name[$k]]=$v;
                }
            }
        }

        //ajout tags génériques
        $tabPatientData['id']=$patientID;
        $tabPatientData['age']=$patientData->getAge();

        // ajouter tags identité
        if($dataIdentite=$this->_formatIdentites($tabPatientData)) {
          $tabPatientData=$tabPatientData+$dataIdentite;
        }

        // ajouter tag adresse
        if($dataAdresse=$this->_formatAdresse($tabPatientData)) {
          $tabPatientData=$tabPatientData+$dataAdresse;
        }

        return $tabPatientData;
    }

/**
 * Retourne les data du PS dans un array
 * @param  int $pratID ID du praticien concerné
 * @return array             tableau avec data PS
 */
    private function _getPsData($psID, $prefix)
    {
        $psData = new msPeople();
        $psData->setToID($psID);
        $tabData = $psData->getSimpleAdminDatas();

        $dat = new msData();
        $data = $dat->getSelectOptionValue(array_keys($tabData));
        $typeId2name = $dat->getNamesFromTypeIDs(array_keys($tabData));

        foreach ($tabData as $k=>$v) {
            //tags name
            if(array_key_exists($k,$typeId2name )) {
                if (isset($data[$k][$v])) {
                  $tabPsData[$prefix.$typeId2name[$k]]=$data[$k][$v];
                  $tabPsData['val_'.$prefix.$typeId2name[$k]]=$v;
                } else {
                  $tabPsData[$prefix.$typeId2name[$k]]=$v;
                }
            }
        }

        //ajout tags génériques
        $tabPsData[$prefix.'id']=$psID;

        $tabPsData=array_filter($tabPsData);

        // ajouter tags identité
        if(isset($tabPsData[$prefix.'lastname'],$tabPsData[$prefix.'birthname'],$tabPsData[$prefix.'firstname']) and $tabPsData[$prefix.'lastname']!=$tabPsData[$prefix.'birthname']) {
          $tabPsData[$prefix.'identiteUsuelle'] = $tabPsData[$prefix.'firstname'].' '.$tabPsData[$prefix.'lastname'];
          $tabPsData[$prefix.'nomUsuel'] = $tabPsData[$prefix.'lastname'];
        } elseif(isset($tabPsData[$prefix.'lastname'],$tabPsData[$prefix.'firstname'])) {
          $tabPsData[$prefix.'identiteUsuelle'] = $tabPsData[$prefix.'firstname'].' '.$tabPsData[$prefix.'lastname'];
          $tabPsData[$prefix.'nomUsuel'] = $tabPsData[$prefix.'lastname'];
        } elseif(isset($tabPsData[$prefix.'birthname'],$tabPsData[$prefix.'firstname'])) {
          $tabPsData[$prefix.'identiteUsuelle'] = $tabPsData[$prefix.'firstname'].' '.$tabPsData[$prefix.'birthname'];
          $tabPsData[$prefix.'nomUsuel'] = $tabPsData[$prefix.'birthname'];
        }
        if(isset($tabPsData[$prefix.'titre'], $tabPsData[$prefix.'identiteUsuelle'])) {
          $tabPsData[$prefix.'identiteUsuelleTitre'] = $tabPsData[$prefix.'titre'].' '.$tabPsData[$prefix.'identiteUsuelle'];
        } elseif(isset($tabPsData[$prefix.'identiteUsuelle'])) {
          $tabPsData[$prefix.'identiteUsuelleTitre'] = $tabPsData[$prefix.'identiteUsuelle'];
        }

        // adresse pro
        if(isset($tabPsData[$prefix.'numAdressePro'], $tabPsData[$prefix.'rueAdressePro'])) {
          $tabPsData[$prefix.'adresseProLigne1'] = $tabPsData[$prefix.'numAdressePro'].' '.$tabPsData[$prefix.'rueAdressePro'];
        } else if(isset($tabPsData[$prefix.'rueAdressePro'])) {
          $tabPsData[$prefix.'adresseProLigne1'] = $tabPsData[$prefix.'rueAdressePro'];
        }

        if(isset($tabPsData[$prefix.'codePostalPro'], $tabPsData[$prefix.'villeAdressePro'])) {
          $tabPsData[$prefix.'adresseProLigne2'] = $tabPsData[$prefix.'codePostalPro'].' '.$tabPsData[$prefix.'villeAdressePro'];
        } else if(isset($tabPsData[$prefix.'villeAdressePro'])) {
          $tabPsData[$prefix.'adresseProLigne2'] = $tabPsData[$prefix.'villeAdressePro'];
        }

        return $tabPsData;
    }


/**
 * Retourne les données de l'examen
 * @param  int $patientID l'ID de l'individu concerné
 * @param  int $formIN    internalName du formulaire ayant généré ces données
 * @param  int $instance    l'ID d'instance du formulaire (= objetID)
 * @return array            array avec les données de l'examen
 */
    public static function getExamenData($patientID, $formIN, $instance)
    {
        if (!is_numeric($patientID)) throw new Exception('PatientID is not numeric');
        if (!is_string($formIN)) throw new Exception('formIN is not string');
        if (!is_numeric($instance)) throw new Exception('instance is not numeric');

        $examenFormData = new msForm();
        $examenFormData->setformIDbyName($formIN);
        $examenFormData->setInstance($instance);
        $examenFData = $examenFormData->getPrevaluesForPatient($patientID);

        $dat = new msData();
        $data = $dat->getSelectOptionValue(array_keys($examenFData));
        $typeId2name = $dat->getNamesFromTypeIDs(array_keys($examenFData));


        foreach ($examenFData as $k=>$v) {
            //tags numériques
            if (isset($data[$k][$v])) {
                $examenFData[$k]=$data[$k][$v];
                $examenFData['val'.$k]=$v;
            } else {
                $examenFData[$k]=$v;
            }

            // tags name
            if(array_key_exists($k,$typeId2name )) {
                if (isset($data[$k][$v])) {
                  $examenFData[$typeId2name[$k]]=$data[$k][$v];
                  $examenFData['val_'.$typeId2name[$k]]=$v;
                } else {
                  $examenFData[$typeId2name[$k]]=$v;
                }
            }
        }

        return $examenFData;
    }


/**
 * Obtenir les données sur le parent
 * @param  int $instance instance (=objetID du parent)
 * @return array           Array typeID => value
 */
    private function _getInstanceMereData($instance)
    {
        $tab=[];
        if ($data=msSQL::sql2tabKey("select dt.id, dt.name, od.value
        from objets_data as od
        join data_types as dt on dt.id=od.typeID and groupe='medical'
        where od.instance='".$instance."' and od.outdated='' and od.deleted=''", 'name')) {
          foreach($data as $k=>$v) {
            $tab[$k]=$v['value'];
            $tab[$v['id']]=$v['value'];
          }
        }
        return $tab;
    }

/**
 * Obtenir le template à utiliser pour l'impression
 * @param  int $formIN internalName du formulaire
 * @return string          nom du template (sans extension)
 */
    public function getPrintModel($formIN)
    {
        return msForm::getFormUniqueRawField($formIN, 'printModel');
    }

/**
 * Obtenir le module d'origine du formulaire
 * @param  int $formIN internalName du formulaire
 * @return string          nom du module d'origine
 */
    private function _getModuleOrigine($formIN)
    {
        return msForm::getFormUniqueRawField($formIN, 'module');
    }

/**
 * Générer des tags identité
 * @param  array $data data patient
 * @return array       array de data calculées
 */
    private function _formatIdentites($data) {

      $data=array_filter($data);

      // si birthdate absent
      if(!isset($data['birthdate'])) $data['birthdate']='';

      //accord en fonction du genre
      $motNe='né';
      $titreCourt="";
      $titreLong="";

      if(isset($data['val_administrativeGenderCode'])) {
        if($data['val_administrativeGenderCode']=='F') {
          $motNe='née';
          $titreCourt="Mme";
          $titreLong="Madame";
        } elseif($data['val_administrativeGenderCode']=='M') {
          $titreCourt="M.";
          $titreLong="Monsieur";
        }
      }

      $rdata['mOuMmeCourt']=$titreCourt;
      $rdata['mOuMmeLong']=$titreLong;

      if(isset($data['firstname'])) $rdata['prenom']=$data['firstname'];

      if(isset($data['lastname'],$data['birthname'],$data['firstname']) and $data['lastname']!=$data['birthname']) {

        $rdata['nom'] = $data['lastname'];
        $rdata['nomUsageNaissance'] = $rdata['nomsUsageNaissance'] = $data['lastname'].' ('.$motNe.' '.$data['birthname'].')';

        $rdata['identiteUsuelle'] = $data['firstname'].' '.$data['lastname'];
        $rdata['identiteComplete'] = $data['firstname'].' '.$data['lastname'].' ('.$motNe.' '.$data['birthname'].')';
        $rdata['identiteUsuelleTitreCourt'] = $titreCourt.' '.$data['firstname'].' '.$data['lastname'];
        $rdata['identiteCompleteTitreLong'] = $titreLong.' '.$data['firstname'].' '.$data['lastname'].' ('.$motNe.' '.$data['birthname'].')';
        $rdata['identiteCompleteTitreCourt'] = $titreCourt.' '.$data['firstname'].' '.$data['lastname'].' ('.$motNe.' '.$data['birthname'].')';
        $rdata['identiteUsuelleTitreCourtDdn'] = $titreCourt.' '.$data['firstname'].' '.$data['lastname'].' ('.$motNe.' le '.$data['birthdate'].')';
        $rdata['identiteCompleteTitreLongDdn'] = $titreLong.' '.$data['firstname'].' '.$data['lastname'].' ('.$motNe.' '.$data['birthname'].' le '.$data['birthdate'].')';

        $rdata['identiteChainePourTri'] = $data['lastname'].' '.$data['firstname'].' '.$data['birthname'];

      } elseif(isset($data['lastname'],$data['firstname'])) {

        $rdata['nom'] = $rdata['nomUsageNaissance'] = $rdata['nomsUsageNaissance'] = $data['lastname'];

        $rdata['identiteUsuelle'] = $data['firstname'].' '.$data['lastname'];
        $rdata['identiteComplete'] = $data['firstname'].' '.$data['lastname'];
        $rdata['identiteUsuelleTitreCourt'] = $titreCourt.' '.$data['firstname'].' '.$data['lastname'];
        $rdata['identiteCompleteTitreLong'] = $titreLong.' '.$data['firstname'].' '.$data['lastname'];
        $rdata['identiteCompleteTitreCourt'] = $titreCourt.' '.$data['firstname'].' '.$data['lastname'];
        $rdata['identiteUsuelleTitreCourtDdn'] = $titreCourt.' '.$data['firstname'].' '.$data['lastname'].' ('.$motNe.' le '.$data['birthdate'].')';
        $rdata['identiteCompleteTitreLongDdn'] = $titreLong.' '.$data['firstname'].' '.$data['lastname'].' ('.$motNe.' le '.$data['birthdate'].')';

        $rdata['identiteChainePourTri'] = $data['lastname'].' '.$data['firstname'];

      } elseif(isset($data['birthname'],$data['firstname'])) {

        $rdata['nom'] = $rdata['nomUsageNaissance'] = $rdata['nomsUsageNaissance'] = $data['birthname'];

        $rdata['identiteUsuelle'] = $data['firstname'].' '.$data['birthname'];
        $rdata['identiteComplete'] = $data['firstname'].' '.$data['birthname'];
        $rdata['identiteUsuelleTitreCourt'] = $titreCourt.' '.$data['firstname'].' '.$data['birthname'];
        $rdata['identiteCompleteTitreLong'] = $titreLong.' '.$data['firstname'].' '.$data['birthname'];
        $rdata['identiteCompleteTitreCourt'] = $titreCourt.' '.$data['firstname'].' '.$data['birthname'];
        $rdata['identiteUsuelleTitreCourtDdn'] = $titreCourt.' '.$data['firstname'].' '.$data['birthname'].' ('.$motNe.' le '.$data['birthdate'].')';
        $rdata['identiteCompleteTitreLongDdn'] = $titreLong.' '.$data['firstname'].' '.$data['birthname'].' ('.$motNe.' le '.$data['birthdate'].')';

        $rdata['identiteChainePourTri'] = $data['birthname'].' '.$data['firstname'];
      }
      if(isset($rdata)) return $rdata;
    }

/**
 * Générer des tags adresse complémentaires
 * @param  array $data data existantes
 * @return array       data à ajouter
 */
    private function _formatAdresse($data) {
      if(isset($data['streetNumber'], $data['street'])) {
        $rdata['adressePersoLigne1'] = $data['streetNumber'].' '.$data['street'];
      } else if(isset($data['street'])) {
        $rdata['adressePersoLigne1'] = $data['street'];
      }

      if(isset($data['postalCodePerso'], $data['city'])) {
        $rdata['adressePersoLigne2'] = $data['postalCodePerso'].' '.$data['city'];
      } else if(isset($data['city'])) {
        $rdata['adressePersoLigne2'] = $data['city'];
      }

      if(isset($rdata)) return $rdata;
    }

/**
 * Générer des tags date complémentaires
 * @param  array $data data existantes
 * @return array       data à ajouter
 */
    private function _formatDate($data) {
      $date = DateTime::createFromFormat('Y-m-d H:i:s', $data['date']);
      $rdata['dateDateSeule']=$date->format('d/m/Y');
      $rdata['dateHeureSeule']=$date->format('H:i:s');
      $rdata['dateYYYY']=$date->format('Y');
      $rdata['dateMM']=$date->format('m');
      $rdata['dateDD']=$date->format('d');

      if(isset($rdata)) return $rdata;
    }


/**
 * Obtenir les données complètes sur l'objet porteur
 * @return array data sur l'objet
 */
    private function _getObjetData() {
        if (!is_numeric($this->_objetID)) {
            throw new Exception('ObjetID is not numeric');
        }
        $objetData=new msObjet();
        $objetData->setObjetID($this->_objetID);
        return $this->_objetData=$objetData->getCompleteObjetDataByID();
    }

/**
 * Obtenir les tags identités dans un autre context que prod courrier
 * @param  array $data tableau des datas nécessaires
 * @return array       tableau des identités formatées
 */
    public static function getIdentiteTags($data) {
      if(isset($data['administrativeGenderCode'])) {
        $data['val_administrativeGenderCode']=$data['administrativeGenderCode'];
      }
      return self::_formatIdentites($data);
    }
}
