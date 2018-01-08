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
 * @edited fr33z00 <https://github.com/fr33z00>
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
 * @var int $_patientID du document concerné
 */
    private $_patientID;
/**
 * @var int $_module du document concerné
 */
    private $_module;



/**
 * Définir l'objetID
 * @param int $data objetId du document concerné
 */
    public function setObjetID($data)
    {
        return $this->_objetID = $data;
    }

/**
 * Définir le modeleID
 * @param int $data modeleID du document concerné
 */
    public function setModeleID($data)
    {
        return $this->_modeleID = $data;
    }

/**
 * Définir le patientID
 * @param int $data patientID du document concerné
 */
    public function setPatientID($data)
    {
        return $this->_patientID = $data;
    }

/**
 * Définir le module
 * @param int $data module du document concerné
 */
    public function setModule($data)
    {
        return $this->_module = $data;
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

        $doc = new msObjet();
        $data=$doc->getCompleteObjetDataByID($this->_objetID);
        $this->_patientID=$data['toID'];
        $this->_module=msSQL::sqlUniqueChamp("SELECT module FROM people WHERE id=".$data['fromID']);

        if ($data['groupe']=="courrier") {
            $this->_modeleID = $data['typeID'];
            $tagsValues=$this->getCourrierData();
        } elseif ($data['groupe']=="typecs") {
            $tagsValues=$this->getCrData();
        } elseif ($data['groupe']=="ordo") {
            $tagsValues=$this->getOrdoData();
        }
        return $tagsValues;
    }

/**
 * Sortir tous les data d'un examen à partir du $_objetID pour rédaction de compte rendu examen
 * @return array tableau avec 3 clefs principales au 1er niveau : examenData, grossesseData, patientData
 */
    public function getCrData()
    {
        if (!is_numeric($this->_objetID)) {
            throw new Exception('ObjetID is not numeric');
        }

        $objetData=new msObjet();
        $objetData=$objetData->getCompleteObjetDataByID($this->_objetID);

        //ajout
        $tabRetour['date']=$objetData['creationDate'];
        $tabRetour['objetID']=$objetData['id'];
        $tabRetour['patientID']=$objetData['toID'];
        $tabRetour['instance']=$objetData['instance'];
        $tabRetour['printModel']=$this->_getPrintModel($objetData['formValues']).'.html.twig';

        //patient data
        $tabRetour=$tabRetour+$this->_getPatientData($objetData['toID']);
        //examen data
        $tabRetour=$tabRetour+$this->getExamenData($objetData['toID'], $objetData['formValues'], $objetData['id']);
        //data de l'instance mère
        if ($objetData['instance']>0) {
            $tabRetour=$tabRetour+$this->_getInstanceMereData($objetData['instance']);
        }

        ksort($tabRetour, SORT_REGULAR);

        if (!isset($this->_module)) {
          $this->_module=msSQL::sqlUniqueChamp("SELECT module FROM people WHERE id=".$objetData['fromID']);
        }
        $tabRetour['module']=$this->_module;
        $moduleName="msMod".ucfirst($tabRetour['module'])."DataCourrier";
        //complément dans le module ?
        if (method_exists($moduleName, "getCrDataCompleteModule")) {
            call_user_func($moduleName.'::getCrDataCompleteModule',$tabRetour);
        }

        //complément dans le module pour ce formulaire spécifique ?
         $methodToCall = "getCourrierDataCompleteModuleForm_".$objetData['formValues'];
         if (method_exists($moduleName, $methodToCall)) {
               call_user_func($moduleName.'::$methodToCall',$tabRetour);
         }


        //calcules complémentaires sur les data si le type rencontré l'implique
        foreach ($tabRetour as $k=>$v) {
            $methodToCall = "type".$k."CompleteData";
            if (method_exists($moduleName, $methodToCall)) {
                call_user_func($moduleName.'::$methodToCall',$tabRetour);
            }
        }
        ksort($tabRetour, SORT_REGULAR);
        return $tabRetour;
    }

/**
 * Retourne les patientData dans un array pour rédaction courrier / certificat
 * @return array             les patientData dans un ['patientData'=>]
 */
    public function getCourrierData()
    {
        if (!is_numeric($this->_patientID)) {
          throw new Exception('PatientID is not numeric');
        }
        $tabRetour = $this->_getPatientData($this->_patientID);
        $tabRetour['date']=date('Y-m-d H:i:s');
        $tabRetour['patientID']=$this->_patientID;

        $moduleName="msMod".ucfirst(isset($this->_module) ? $this->_module : "base")."DataCourrier";
        //complément général dans le module ?
        if (method_exists($moduleName, "getCourrierDataCompleteModule")) {
           call_user_func($moduleName.'::getCourrierDataCompleteModule',$tabRetour);
        }

        //complément dans le module pour ce modeleID spécifique ?
        if (isset($this->_modeleID)) {
           $methodToCall = "getCourrierDataCompleteModuleModele".$this->_modeleID;
           if (method_exists($moduleName, $methodToCall)) {
               call_user_func($moduleName.'::$methodToCall',$tabRetour);
           }
        }

        //calcules complémentaires sur les data si le type rencontré l'implique
        foreach ($tabRetour as $k=>$v) {
           $methodToCall = "type".$k."CompleteData";
           if (method_exists($moduleName, $methodToCall)) {
               call_user_func($moduleName.'::$methodToCall',$tabRetour);
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
        if (!is_numeric($this->_objetID)) {
            throw new Exception('ObjetID is not numeric');
        }

        $objetData=new msObjet();
        $objetData=$objetData->getCompleteObjetDataByID($this->_objetID);

        //patient data
        $tabRetour=$this->_getPatientData($objetData['toID']);
        $tabRetour['patientID'] = $objetData['toID'];

        //examen data
        $examData = new msObjet();
        $tabRetour=$tabRetour+$examData->getObjetDataByID($this->_objetID, ['creationDate as date']);

        ksort($tabRetour, SORT_REGULAR);

        if (!isset($this->_module)) {
          $this->_module=msSQL::sqlUniqueChamp("SELECT module FROM people WHERE id=".$objetData['fromID']);
        }
        $tabRetour['module']=$this->_module;
        $moduleName="msMod".ucfirst($tabRetour['module'])."DataCourrier";
        //complément dans le module ?
        if (method_exists($moduleName, "getOrdoDataCompleteModule")) {
          call_user_func($moduleName.'::getOrdoDataCompleteModule',$tabRetour);
        }

        //calcules complémentaires sur les data si le type rencontré l'implique
        foreach ($tabRetour as $k=>$v) {
          $methodToCall = "type".$k."CompleteData";
          if (method_exists($moduleName, $methodToCall)) {
              call_user_func($moduleName.'::$methodToCall',$tabRetour);
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

        $data = new msData();
        $data = $data->getSelectOptionValue(array_keys($tabPatientData));

        foreach ($tabPatientData as $k=>$v) {
            if (isset($data[$k][$v])) {
                $tabPatientData[$k]=$data[$k][$v];
                $tabPatientData['val'.$k]=$v;
            }
        }

        $tabPatientData['id']=$patientID;
        $tabPatientData['age']=$patientData->getAge();

        return $tabPatientData;
    }

/**
 * Retourne les données de l'examen
 * @param  int $patientID l'ID de l'individu concerné
 * @param  int $formIN    internalName du formulaire ayant généré ces données
 * @param  int $instance    l'ID d'instance du formulaire (= objetID)
 * @return array            array avec les données de l'examen
 */
    public function getExamenData($patientID, $formIN, $instance)
    {
        if (!isset($patientID)) {
            throw new Exception('PatientID is not defined');
        }
        if (!isset($formIN)) {
            throw new Exception('formIN is not defined');
        }

        $examenFormData = new msForm();
        $examenFormData->setformIDbyName($formIN);
        $examenFormData->setInstance($instance);
        $examenFData = $examenFormData->getPrevaluesForPatient($patientID);

        $data = new msData();
        $data = $data->getSelectOptionValue(array_keys($examenFData));

        foreach ($examenFData as $k=>$v) {
            if (isset($data[$k][$v])) {
                $examenFData[$k]=$data[$k][$v];
                $examenFData['val'.$k]=$v;
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
        return msSQL::sql2tabKey("select od.typeID, od.value
        from objets_data as od
        join data_types as dt on dt.id=od.typeID and groupe='medical'
        where od.instance='".$instance."' and od.outdated='' and od.deleted=''", 'typeID', 'value');
    }

/**
 * Obtenir le template à utiliser pour l'mpression
 * @param  int $formIN internalName du formulaire
 * @return string          nom du template (sans extension)
 */
    private function _getPrintModel($formIN)
    {
        return msSQL::sqlUniqueChamp("select printModel from forms where internalName='".$formIN."' limit 1");
    }
}
