<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * Manipulation des data générées pour export
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */
class msExportData
{
/**
 * ID du form concerné
 * @var int
 */
  private $_formID;
/**
 * Id du/des data types qui implémentent le formulaire
 * @var array
 */
  private $_formDataTypesImplementation;
/**
 * Date de début pour l'export
 * @var string
 */
  private $_date_start;
/**
 * Date de fin pour l'export
 * @var [type]
 */
  private $_date_end;
/**
 * Date à considérer registerDate ou creationDate
 * @var string
 */
  private $_date_type;
/**
 * Liste des praticiens dont il faut exporter les form
 * @var array
 */
  private $_pratList=[];
/**
 * Champs administratif à inclure pour le patient
 * @var array
 */
  private $_dataAdminPatientList=[];
/**
 * Champs administratif à inclure pour le praticien
 * @var array
 */
  private $_dataAdminPratList=[];
/**
 * Champs du formulaire à inclure à l'export
 * @var array
 */
  private $_formFieldList=[];
/**
 * ObjetsID instances du formulaire
 * @var array
 */
  private $_allObjetsID=[];
/**
 * ID des praticiens qui sont fromID des objets instances
 * @var array
 */
  private $_allPratID=[];
/**
 * Data administratif par praticen ID
 * @var array
 */
  private $_pratAdminData;
/**
 * Tableau de référence pour le tris des champs
 * @var array
 */
  private $_sortTab;

  private $_optionSelect;

  private $_tabCorrespondances;

/**
 * Définir le numéro du formulaire
 * @param int $formID L'ID du formulaire
 */
    public function setFormID($formID)
    {
      if (is_numeric($formID)) {
        $this->_formID = $formID;
        $this->_getFormDataTypesImplementation();
        return $this->_formID;
      } else {
        throw new Exception('formID is not numeric');
      }
    }

/**
 * Définir la date de début dd/mm/YYYY
 * @param string $date dd/mm/YYYY
 */
    public function setDateStart($date) {
      if(msTools::validateDate($date, 'd/m/Y')) {
        $d = DateTime::createFromFormat('d/m/Y', $date);
        $this->_date_start = $d->format('Y-m-d 00:00:00');
      }
    }

/**
 * Définir la date de fin
 * @param string $date dd/mm/YYYY
 */
    public function setDateEnd($date) {
      if(msTools::validateDate($date, 'd/m/Y')) {
        $d = DateTime::createFromFormat('d/m/Y', $date);
        $this->_date_end = $d->format('Y-m-d 23:59:59');
      }
    }

/**
 * Définir la date à considérer
 * @param string $type registerDate ou creationDate
 */
    public function setDateType($type) {
      if(in_array($type, ['registerDate', 'creationDate'])) {
        $this->_date_type = $type;
      } else {
        throw new Exception('DateType n\'est pas valide');
      }
    }

/**
 * Définir le comportement à adopter pour les select
 * @param string $optionSelect [description] selectCode ou selectValue
 */
    public function setOptionSelect($optionSelect) {
      if(in_array($optionSelect, ['selectCode', 'selectValue'])) {
        $this->_optionSelect = $optionSelect;
      } else {
        throw new Exception('OptionSelect n\'est pas valide');
      }
    }

/**
 * Définir le tableau de référence pour le tri final
 * @param array $tab
 */
    public function setSortTab($tab) {
      $this->_sortTab=$tab;
    }

/**
 * Ajouter un champ administratif patient à exporter
 * @param string $val data type name
 */
    public function addToDataAdminPatientList($val) {
      if(!in_array($val, $this->_dataAdminPatientList)) $this->_dataAdminPatientList[]=$val;
    }

/**
 * Ajouter un champ administratif praticien à exporter
 * @param string $val data type name
 */
    public function addToDataAdminPratList($val) {
      if(!in_array($val, $this->_dataAdminPratList)) $this->_dataAdminPratList[]=$val;
    }

/**
 * Ajouter un champ du formulaire à exporter
 * @param string $val data type name
 */
    public function addToFormFieldList($val) {
      if(!in_array($val, $this->_formFieldList)) $this->_formFieldList[]=$val;
    }

/**
 * Ajouter un praticien à la liste des prat dont il faut exporter les form
 * @param int $val pratID
 */
    public function addToPratList($val) {
      if(!in_array($val, $this->_pratList) and is_numeric($val)) $this->_pratList[]=$val;
    }

/**
 * Obtenir les champs administratifs patient à exporter
 * @return array data type names
 */
    public function getDataAdminPatientList() {
      return $this->_dataAdminPatientList=msSQL::cleanArray($this->_dataAdminPatientList);
    }

/**
 * Obtenir les champs administratifs prat à exporter
 * @return array data type names
 */
    public function getDataAdminPratList() {
      return $this->_dataAdminPratList=msSQL::cleanArray($this->_dataAdminPratList);
    }

/**
 * Obtenir les champs du formulaire à exporter
 * @return array data type names
 */
    public function getFormFieldList() {
      return $this->_formFieldList=msSQL::cleanArray($this->_formFieldList);
    }

/**
 * Obtenir les ID des prat concernés par l'export
 * @return array data type names
 */
    public function getPratList() {
      return $this->_pratList=array_unique($this->_pratList);
    }

/**
 * Obtenir les datas pour l'export
 * @return array data pour construire le fichier d'export
 */
    public function getTabData() {
      if($this->_optionSelect=="selectValue") {
        $this->getTabCorrespondances();
      }
      $this->_getAllObjetsID();
      $this->_getAllPratAdminData();
      return $this->_getAllObjetsAndChildsData();
    }

/**
 * Obtenir le tableau de correspondance
 * @return array
 */
    public function getTabCorrespondances() {
      if(isset($this->_tabCorrespondances)) return $this->_tabCorrespondances;
      $data = new msData;
      $corrPatient = $data->getSelectOptionValueByTypeName($this->_dataAdminPatientList);
      $corrPrat = $data->getSelectOptionValueByTypeName($this->_dataAdminPratList);
      $corrForm = $data->getSelectOptionValueByTypeName($this->_formFieldList);

      return $this->_tabCorrespondances=array_merge(
        msTools::getPrefixKeyArray($corrPatient, 'patient_'),
        msTools::getPrefixKeyArray($corrPrat, 'praticien_'),
        msTools::getPrefixKeyArray($corrForm, 'data_'),
      );
    }

/**
 * Obtenir les objetsID à exporter
 * @return array objetID
 */
    private function _getAllObjetsID() {
      return $this->_allObjetsID=msSQL::sql2tabSimple("select id from objets_data where typeID in ('".implode("', '", $this->_formDataTypesImplementation)."') and fromID in ('".implode("', '", $this->_pratList)."')  ".$this->_formatDateParameters()." ");
    }

/**
 * Obtenir un array de référence avec les différentes données admin pour chaque prat
 * @return array données admin de chaque prat
 */
    private function _getAllPratAdminData() {
      if(empty($this->getDataAdminPratList())) return [];

      if($this->_allPratID = msSQL::sql2tabSimple("select distinct(fromID) from objets_data where id in ('".implode("', '", $this->_allObjetsID)."')")) {
        $people = new msPeople;
        foreach($this->_allPratID as $PratID) {
          $people->setToID($PratID);
          $dataPrat=$people->getSimpleAdminDatasByName($this->getDataAdminPratList());
          foreach($this->getDataAdminPratList() as $v) {
            if($this->_optionSelect=="selectValue" and isset($this->_tabCorrespondances['praticien_'.$v][$dataPrat[$v]])) {
              $this->_pratAdminData[$PratID]['praticien_'.$v]=$this->_tabCorrespondances['praticien_'.$v][$dataPrat[$v]];
            } elseif(isset($dataPrat[$v])) {
              $this->_pratAdminData[$PratID]['praticien_'.$v]=$dataPrat[$v];
            } else {
              $this->_pratAdminData[$PratID]['praticien_'.$v]='';
            }
          }
        }
        return $this->_pratAdminData;
      }
    }

/**
 * Obtenir les données admin d'un patient
 * @param  int $patientID patientID
 * @return array            données admin patient
 */
    private function _getPatientAdminData($patientID) {
      if(empty($this->getDataAdminPatientList())) return [];
      $people = new msPeople;
      $people->setToID($patientID);
      $dataPatient=$people->getSimpleAdminDatasByName($this->getDataAdminPatientList());
      foreach($this->getDataAdminPatientList() as $v) {
        if($this->_optionSelect=="selectValue" and isset($this->_tabCorrespondances['patient_'.$v][$dataPatient[$v]])) {
          $patient['patient_'.$v]=$this->_tabCorrespondances['patient_'.$v][$dataPatient[$v]];
        } elseif(isset($dataPatient[$v])) {
          $patient['patient_'.$v]=$dataPatient[$v];
        } else {
          $patient['patient_'.$v]='';
        }
      }
      return $patient;
    }

/**
 * Obtenir toutes les données formulaire consolidées avec data prat et patient
 * @return array données des formulaires consolidées
 */
    private function _getAllObjetsAndChildsData()
    {
      $tab=[];
      $data = msSQL::sql2tabKey("select o.id, o.value, o.instance, o.typeID, o.fromID, o.toID, o.creationDate, o.registerDate, o.updateDate, t.name
      from objets_data as o
      left join data_types as t on o.typeID=t.id
      where (o.id in ('".implode("', '", $this->_allObjetsID)."') or (o.instance in ('".implode("', '", $this->_allObjetsID)."') and t.name in ('".implode("', '", $this->getFormFieldList())."'))) and o.outdated='' and o.deleted='' ", 'id');
      if($data) {
        foreach($data as $k=>$v) {
          if(in_array($v['typeID'], $this->_formDataTypesImplementation)) {
            $tab[$k]['id']=$k;
            $tab[$k]['praticien_id']=$v['fromID'];
            $tab[$k]['patient_id']=$v['toID'];
            $tab[$k]['date_effective']=$v['creationDate'];
            $tab[$k]['date_saisie']=$v['registerDate'];
            $tab[$k]['date_modification']=$v['updateDate'];
            if(isset($this->_pratAdminData[$v['fromID']])) $tab[$k]=$tab[$k]+$this->_pratAdminData[$v['fromID']];
            $tab[$k]=$tab[$k]+$this->_getPatientAdminData($v['toID']);
          } else {
            if($this->_optionSelect=="selectValue" and isset($this->_tabCorrespondances['data_'.$v['name']][$v['value']])) {
              $tab[$v['instance']]['data_'.$v['name']]=$this->_tabCorrespondances['data_'.$v['name']][$v['value']];
            } else {
              $tab[$v['instance']]['data_'.$v['name']]=$v['value'];
            }
          }
        }
        // correction valeurs manquantes
        foreach($this->getFormFieldList() as $v) {
          foreach($this->_allObjetsID as $id) {
            if(!isset($tab[$id]['data_'.$v])) $tab[$id]['data_'.$v]='';
          }
        }
        foreach($tab as $k=>$v) {
          $tab[$k] = array_merge(array_flip($this->_sortTab), $tab[$k]);
        }
      }
      return $tab;
    }

/**
 * Obtenir les ID des data types qui implémentent le formulaire
 * @return array data type ID
 */
    private function _getFormDataTypesImplementation() {
      $form = new msForm;
      $form->setFormID($this->_formID);
      return $this->_formDataTypesImplementation = $form->getFormDataTypesImplementation();
    }

/**
 * Obtenir la chaine SQL pour la limitation des dates
 * @return string chaine SQL
 */
    private function _formatDateParameters() {
      $dateSqlString='';
      if(isset($this->_date_start)) $dateSqlString.=" and ".$this->_date_type." >= '".$this->_date_start."'";
      if(isset($this->_date_end)) $dateSqlString.=" and ".$this->_date_type." <= '".$this->_date_end."'";
      return $dateSqlString;
    }

}
