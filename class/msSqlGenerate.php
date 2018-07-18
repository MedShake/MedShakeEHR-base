<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Générer du SQL pour export
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


class msSqlGenerate
{
  private $_actes_fields;
  private $_actes_values;
  private $_actes_base_fields;
  private $_actes_base_values;
  private $_actes_cat_fields;
  private $_actes_cat_values;
  private $_configuration_fields;
  private $_configuration_values;
  private $_data_cat_fields;
  private $_data_cat_values;
  private $_data_types_fields;
  private $_data_types_values;
  private $_forms_fields;
  private $_forms_values;
  private $_forms_cat_fields;
  private $_forms_cat_values;
  private $_system_fields;
  private $_system_values;

/**
 * Obtenir le SQL complet d'un module
 * @param  string $name nom du module
 * @return string       code SQL
 */
  public function getSqlForModule($name) {

    //system
    $system=msSQL::sqlUnique("select * from system where name='".$name."'");
    unset($system['id']);
    $this->_system_fields=$this->_getSqlFieldsPart($system);
    $this->_system_values[]=$this->_getSqlValuesPart($system);

    //configuration
    if($configurations=msSQL::sql2tab("select * from configuration where module='".$name."' and level='module'")) {
      foreach($configurations as $configuration) {
        unset($configuration['id']);
        if(!isset($this->_configuration_fields)) $this->_configuration_fields=$this->_getSqlFieldsPart($configuration);
        $this->_configuration_values[]=$this->_getSqlValuesPart($configuration);
      }
    }

    //actes
    $this->_prepareSqlForActes($name);

    //formulaires
    if($listesForms=msSQL::sql2tabSimple("select internalName from forms where module='".$name."'")) {
      foreach($listesForms as $formName) {
        $this->_prepareSqlForForm($formName);
      }
    }

    //autres data_type
    $this->_prepareSqlForDataTypes ($name, ['admin', 'medical']);

    return $this->_composeSql();

  }

/**
 * Obtenir le SQL pour un formulaire particulier
 * @param  string $name internalNamename du formulaire
 * @return string       code SQL
 */
  public function getSqlForForm($name) {
    $this-> _prepareSqlForForm($name);
    return $this->_composeSql();
  }

/**
 * Composer le SQL pour data_types data_cat d'un module et groupe particuliers
 * @param  string $module      name du module
 * @param  array  $notInGroupe tableau des groupes de data à NE PAS inclure
 * @return void
 */
  public function _prepareSqlForDataTypes ($module, $notInGroupe=['']) {
    if($typesData=msSQL::sql2tab("select * from data_types where module='".$module."' and  groupe not in ('".implode("', '", $notInGroupe)."')")) {
      $cat=array_unique(array_column($typesData, 'cat'));
      $catData=msSQL::sql2tab("select * from data_cat where id in ('".implode("', '", $cat)."')");

      // data_cat
      foreach($catData as $v) {
        unset($v['id']);
        $v['fromID']='1';
        $v['creationDate']=date("Y-m-d H:i:s");
        if(!isset($this->_data_cat_fields)) $this->_data_cat_fields=$this->_getSqlFieldsPart($v);
        if(!isset($this->_data_cat_values[$v['name']])) $this->_data_cat_values[$v['name']]=$this->_getSqlValuesPart($v);
      }

      // data
      foreach($typesData as $v) {
        unset($v['id']);
        $catID=$v['cat'];
        $v['fromID']='1';
        $v['creationDate']=date("Y-m-d H:i:s");
        if(isset($v['cat'])) $v['cat']='@catID';
        if(!isset($this->_data_types_fields)) $this->_data_types_fields=$this->_getSqlFieldsPart($v);
        if(!isset($this->_data_types_values[$catID][$v['name']])) $this->_data_types_values[$catID][$v['name']]=$this->_getSqlValuesPart($v);
      }
    }

  }

/**
 * Composer le SQL pour les actes actes_base et actes_cat
 * @param  string $name name du module
 * @return void
 */
  public function _prepareSqlForActes($name) {
    $collecteCcamNgap=[];

    if($cats=msSQL::sql2tab("select * from actes_cat where module='".$name."'")) {
      foreach($cats as $cat) {
        unset($cat['id']);
        $cat['fromID']=1;
        $cat['creationDate']=date("Y-m-d H:i:s");
        if(!isset($this->_actes_cat_fields)) $this->_actes_cat_fields=$this->_getSqlFieldsPart($cat);
        $this->_actes_cat_values[]=$this->_getSqlValuesPart($cat);
      }
    }

    if($actes=msSQL::sql2tab("select a.* , c.name as catName
      from actes as a
      left join actes_cat as c on c.id=a.cat
      where c.module='".$name."' and a.toID='0'
      group by a.id")) {
        $collecteCcamNgap=[];
        foreach($actes as $acte) {
          $catName=$acte['catName'];
          unset($acte['id'],$acte['catName']);
          $acte['fromID']=1;
          $acte['creationDate']=date("Y-m-d H:i:s");
          $acte['cat']='@catID';
          if(!isset($this->_actes_fields)) $this->_actes_fields=$this->_getSqlFieldsPart($acte);
          $this->_actes_values[$catName][]=$this->_getSqlValuesPart($acte);

          //collecter actes NGAP/CCAM
          $details=Spyc::YAMLLoad($acte['details']);
          if(is_array($details)) {
            $collecteCcamNgap=array_merge($collecteCcamNgap, array_keys($details));
          }
        }
    }

    // recherche de méthode informative dans la class Honoraires du module
    $listFromModule = [];
    $className = 'msMod'.ucfirst($name).'CalcHonoraires';
    if(class_exists($className, TRUE)) {
      if(method_exists($className, 'getActesModuleSqlExtraction')) {
        $listFromModule = $className::getActesModuleSqlExtraction();
      }
    }

    // extraction finale des actes NGAP / CCAM nécessaires
    $collecteCcamNgap=array_unique(array_merge($collecteCcamNgap, $listFromModule));
    if($actesbase=msSQL::sql2tab("select * from actes_base where code in ('".implode("', '", $collecteCcamNgap)."') order by type, code")) {
      foreach($actesbase as $actebase) {
        unset($actebase['id']);
        $actebase['fromID']=1;
        $actebase['creationDate']=date("Y-m-d H:i:s");
        if(!isset($this->_actes_base_fields)) $this->_actes_base_fields=$this->_getSqlFieldsPart($actebase);
        $this->_actes_base_values[]=$this->_getSqlValuesPart($actebase);
    }
  }

  }


/**
 * Composer le SQL pour un formulaire particulier
 * @param  string $name internalName du formulaire
 * @return void
 */
  private function _prepareSqlForForm($name) {
    $form = new msForm();
    $form->setFormIDbyName($name);
    //extraire tous les types du form
    $types=$form->formExtractDistinctTypes();

    if($typesData=msSQL::sql2tab("select * from data_types where id in ('".implode("', '", $types)."')")) {
      $cat=array_unique(array_column($typesData, 'cat'));
      $catData=msSQL::sql2tab("select * from data_cat where id in ('".implode("', '", $cat)."')");

      // data_cat
      foreach($catData as $v) {
        unset($v['id']);
        $v['fromID']='1';
        $v['creationDate']=date("Y-m-d H:i:s");
        if(!isset($this->_data_cat_fields)) $this->_data_cat_fields=$this->_getSqlFieldsPart($v);
        if(!isset($this->_data_cat_values[$v['name']])) $this->_data_cat_values[$v['name']]=$this->_getSqlValuesPart($v);
      }

      // data
      foreach($typesData as $v) {
        unset($v['id']);
        $catID=$v['cat'];
        $v['fromID']='1';
        $v['creationDate']=date("Y-m-d H:i:s");
        if(isset($v['cat'])) $v['cat']='@catID';
        if(!isset($this->_data_types_fields)) $this->_data_types_fields=$this->_getSqlFieldsPart($v);
        if(!isset($this->_data_types_values[$catID][$v['name']])) $this->_data_types_values[$catID][$v['name']]=$this->_getSqlValuesPart($v);
      }

      // form
      $v=msSQL::sqlUnique("select * from forms where id='".$form->getFormID()."' limit 1");
      $catForm = $v['cat'];
      unset($v['id']);
      $catID=$v['cat'];
      if(isset($v['cat'])) $v['cat']='@catID';
      if(!isset($this->_forms_fields)) $this->_forms_fields=$this->_getSqlFieldsPart($v);
      if(!isset($this->_forms_values[$catID][$v['internalName']])) $this->_forms_values[$catID][$v['internalName']]=$this->_getSqlValuesPart($v);

      // form cat
      $v=msSQL::sqlUnique("select * from forms_cat where id='".$catForm."' limit 1");
      unset($v['id']);
      $v['fromID']='1';
      $v['creationDate']=date("Y-m-d H:i:s");
      if(!isset($this->_forms_cat_fields)) $this->_forms_cat_fields=$this->_getSqlFieldsPart($v);
      if(!isset($this->_forms_cat_values[$v['name']])) $this->_forms_cat_values[$v['name']]=$this->_getSqlValuesPart($v);
    }
  }

/**
 * Composer le SQL
 * @return string code SQL
 */
  private function _composeSql() {
    $string='';

    //actes_cat
    if(isset($this->_actes_cat_values)) {
      $string.="-- actes_cat\n";
      $string.="INSERT IGNORE INTO `actes_cat` ".$this->_actes_cat_fields." VALUES\n";
      $string.=implode(",\n", $this->_actes_cat_values).";\n\n";
    }

    //actes_base
    if(isset($this->_actes_base_values)) {
      $string.="-- actes_base\n";
      $string.="INSERT IGNORE INTO `actes_base` ".$this->_actes_base_fields." VALUES\n";
      $string.=implode(",\n", $this->_actes_base_values).";\n\n";
    }

    //actes
    if(isset($this->_actes_values)) {
      $string.="-- actes\n";
      foreach($this->_actes_values as $catName=>$values) {
        $string.="SET @catID = (SELECT actes_cat.id FROM actes_cat WHERE actes_cat.name='".$catName."');\n";
        $string.="INSERT IGNORE INTO `actes` ".$this->_actes_fields." VALUES\n";
        $string.=implode(",\n", $this->_actes_values[$catName]).";\n\n";
      }
    }

    //data_cat
    if(isset($this->_data_cat_values)) {
      $string.="-- data_cat\n";
      $string.="INSERT IGNORE INTO `data_cat` ".$this->_data_cat_fields." VALUES\n";
      $string.=implode(",\n", $this->_data_cat_values).";\n\n";
    }

    //data_types
    if(isset($this->_data_types_values)) {
      $string.="-- data_types\n";
      foreach($this->_data_types_values as $cat=>$values) {
        $catName = msData::getCatNameFromCatID($cat);
        $string.="SET @catID = (SELECT data_cat.id FROM data_cat WHERE data_cat.name='".$catName."');\n";
        $string.="INSERT IGNORE INTO `data_types` ".$this->_data_types_fields." VALUES\n";
        $string.=implode(",\n", $this->_data_types_values[$cat]).";\n\n";
      }
    }

    //configuration
    if(isset($this->_configuration_values)) {
      $string.="-- configuration\n";
      $string.="INSERT IGNORE INTO `configuration` ".$this->_configuration_fields." VALUES\n";
      $string.=implode(",\n", $this->_configuration_values).";\n\n";
    }

    //forms cat
    if(isset($this->_forms_cat_values) and !empty($this->_forms_cat_values)) {
      $string.="-- forms_cat\n";
      $string.="INSERT IGNORE INTO `forms_cat` ".$this->_forms_cat_fields." VALUES\n";
      $string.=implode(",\n", $this->_forms_cat_values).";\n\n";
    }

    //forms
    if(isset($this->_forms_values)) {
      $string.="-- forms\n";
      foreach($this->_forms_values as $cat=>$values) {
        $catName = msForm::getCatNameFromCatID($cat);
        $string.="SET @catID = (SELECT forms_cat.id FROM forms_cat WHERE forms_cat.name='".$catName."');\n";
        $string.="INSERT IGNORE INTO `forms` ".$this->_forms_fields." VALUES\n";
        $string.=implode(",\n", $this->_forms_values[$cat]).";\n\n";
      }
    }

    //system
    if(isset($this->_system_values) and !empty($this->_system_values)) {
      $string.="-- system\n";
      $string.="INSERT IGNORE INTO `system` ".$this->_system_fields." VALUES\n";
      $string.=implode(",\n", $this->_system_values).";\n\n";
    }

    return $string;

  }

/**
 * Générer un chainon de la partie values d'un INSERT
 * @param  array $a tableau col=>value
 * @return string    chaine ('value', 'value' ...)
 */
  private function _getSqlValuesPart($a) {

    if(!empty($a)) {
      $p=[];
      foreach($a as $v) {
        if($v == NULL) {
          $p[] = 'NULL';
        } elseif(is_int($v)) {
          $p[] = $v;
        } elseif($v == '@catID') {
          $p[] = $v;
        } else {
          $v = addslashes($v);
          $v = str_replace("\n", '\n', $v);
          $v = str_replace("\r", '\r', $v);
          $p[] = "'".$v."'";
        }
      }
      return '('.implode(', ',$p).')';
    } else {
      return '';
    }

  }

/**
 * Générer la partie initiale d'un INSERT (noms des colonnes)
 * @param  array $a tableau col=>$value
 * @return string    chaine (col1, col2 ...)
 */
  private function _getSqlFieldsPart($a) {
    return "(`".implode("`, `", array_keys($a))."`)";
  }
}
