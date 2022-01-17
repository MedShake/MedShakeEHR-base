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
 * Interogation du modèle de données : data types
 * Traitement d'une donnée avant enregistrement pour formatage
 *
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

class msData extends msDataCat
{

/**
 * @var int  $_typeID concerné
 */
    private $_typeID;
/**
 * @var int $_value
 */
    private $_value;
/**
 * @var $_modules : array des modules concernés
 */
    private $_modules;

/**
 * Vérifier que le type ID existe
 * @param  int $id ID du type
 * @return boolean     true/false
 */
    public static function checkDataTypeExist($id) {
      if(!is_numeric($id)) return false;
      if(msSQL::sqlUniqueChamp("SELECT id FROM data_types WHERE id='".$id."' limit 1")) {
        return true;
      } else {
        return false;
      }
    }

/**
 * Vérifier l'existence d'un data_type par son nom
 * @param  string $name nom du data_type
 * @return bool       true/false
 */
    public function checkDataTypeExistByName($name)
    {
        if ($typeID=msSQL::sqlUniqueChamp("select id from data_types where name='".msSQL::cleanVar($name)."' limit 1")) {
            $this->_typeID=$typeID;
            return true;
        } else {
            return false;
        }
    }

/**
 * Définir la valeur
 * @param string $data valeur
 * @return string retour de la valeur
 */
    public function setValue($data)
    {
        return $this->_value = $data;
    }

/**
 * Définir le typeID
 * @param int $typeID typeID
 * @return int typeID
 */
    public function setTypeID($typeID)
    {
        if ($this->checkDataTypeExist($typeID)) {
            return $this->_typeID = $typeID;
        } else {
            throw new Exception('typeID is not numeric');
        }
    }

/**
 * Définir les modules
 * @param string $modules valeur
 * @return string retour de la valeur
 */
    public function setModules($modules)
    {
      if (is_array($modules)) {
        return $this->_modules = $modules;
      } else {
          throw new Exception('Modules is not array');
      }
    }

/**
 * Obtenir tous les data types d'une catégorie à partir de l'ID de la catégorie
 * @param  int $catID ID de la catégorie
 * @param  array $col   array des colonnes sql à retourner
 * @return array        array
 */
    public function getDataTypesFromCatID($catID, $col=['*'], $orderBy='displayOrder, label')
    {
        if(!is_numeric($catID)) throw new Exception('catID is not numeric');

        if(isset($this->_modules)) {
          $where ="and module in ('".implode("', '", msSQL::cleanArray($this->_modules))."')";
        } else {
          $where = null;
        }

        return msSQL::sql2tab("select ".implode(', ', msSQL::cleanArray($col))." from data_types where cat='".$catID."' ".$where." order by ".$orderBy);
    }

/**
 * Obtenir tous les data types d'une catégorie à partir du nom de la catégorie
 * @param  string $name name de la catégorie
 * @param  array $col   array des colonnes sql à retourner
 * @return array        array
 */
    public function getDataTypesFromCatName($name, $col=['*'], $orderBy='displayOrder, label')
    {
        $catID=$this->getCatIDFromName($name);
        return $this->getDataTypesFromCatID($catID, $col, $orderBy);
    }

/**
 * Obtenir tous les data types d'un groupe à partir du nom du groupe
 * @param  string $groupe groupe
 * @param  array $col    colonnes SQL à retourner
 * @return array         array
 */
    public function getDataTypesFromGroupe($groupe, $col=['*'], $orderBy='displayOrder, label')
    {
        return msSQL::sql2tab("select ".implode(', ', msSQL::cleanArray($col))." from data_types where groupe='".msSQL::cleanVar($groupe)."' order by ".$orderBy);
    }

/**
 * Obtenir les data types à partir d'une liste de name passés dans un array
 * @param  array $listArray liste des types souhaités, par nom
 * @param  array  $col       colonnes SQL à retourner
 * @return array            array
 */
    public function getDataTypesFromNameList($listArray, $col=['*'])
    {
        return msSQL::sql2tab("select ".implode(', ', msSQL::cleanArray($col))." from data_types where name in ('".implode("','", msSQL::cleanArray($listArray))."') order by displayOrder, label");
    }


/**
 * Sortir les infos d'un data type à partir de son ID
 * @param  int $id  ID du type
 * @param  array $col colonnes SQL à retourner
 * @return array      array
 */
    public function getDataType($id, $col=['*'])
    {
        if (!is_numeric($id)) throw new Exception('ID is not numeric');
        return msSQL::sqlUnique("select ".implode(', ', msSQL::cleanArray($col))." from data_types where id='".$id."'");

    }

/**
 * Sortir les infos d'un data type à partir de son name
 * @param  string $name  name du type
 * @param  array $col colonnes SQL à retourner
 * @return array      array
 */
    public function getDataTypeByName($name, $col=['*'])
    {
        return msSQL::sqlUnique("select ".implode(', ', msSQL::cleanArray($col))." from data_types where name='".msSQL::cleanVar($name)."'");

    }

/**
 * Obtenir les Labels des typeID à partir d'un array de typeID
 * @param  array $ar array de typeID
 * @return array     array typeID=>label
 */
    public function getLabelFromTypeID($ar=['1'])
    {
        return msSQL::sql2tabKey("select label, id from data_types where id in ('".implode("','", msSQL::cleanArray($ar))."')", 'id', 'label');
    }

/**
 * Obtenir les Labels des type à partir d'un array de name
 * @param  array $ar array de name
 * @return array     array name=>label
 */
    public function getLabelFromTypeName($ar=['1'])
    {
        return msSQL::sql2tabKey("select label, name from data_types where name in ('".implode("','", msSQL::cleanArray($ar))."') order by displayOrder", 'name', 'label');
    }

/**
 * Obtenir les typeID à partir d'un array de Name
 * @param  array $ar array de name
 * @return array     array name=>typeID
 */
    public static function getTypeIDsFromName($ar=['1'])
    {
        return msSQL::sql2tabKey("select name, id from data_types where name in ('".implode("','", msSQL::cleanArray($ar))."')", 'name', 'id');
    }

/**
 * Obtenir les name à partir d'un array de typeID
 * @param  array $ar array de typeID
 * @return array     array typeID=>name
 */
    public function getNamesFromTypeIDs($ar=['-1'])
    {
        return msSQL::sql2tabKey("select name, id from data_types where id in ('".implode("','", msSQL::cleanArray($ar))."')", 'id', 'name');
    }

/**
 * Obtenir le name à partir de son typeID
 * @param  int $typeID id du type
 * @return string     name
 */
    public static function getNameFromTypeID($typeID)
    {
        if (!is_numeric($typeID)) throw new Exception('TypeID is not numeric');
        return msSQL::sqlUniqueChamp("select name from data_types where id = '".$typeID."' ");
    }

/**
 * Obtenir le typeID à partir de son nom
 * @param  string $name nom du type
 * @return int     typeID
 */
    public static function getTypeIDFromName($name)
    {
        return msSQL::sqlUniqueChamp("select id from data_types where name = '".msSQL::cleanVar($name)."' ");
    }

/**
 * sortir pour les data types de type select un tableau key=>$value pour chaque item option
 * @param  array $typeIDsArray les typeID concernés
 * @return array               Array ('720'=> 'A' : 'plus', 'B' => 'moins')
 */
    public static function getSelectOptionValue($typeIDsArray)
    {
        $tab = msSQL::sql2tabKey("select id, formValues from data_types where formType in ('select', 'radio') and id in ('".implode("', '", msSQL::cleanArray($typeIDsArray))."')", "id", "formValues");
        if (is_array($tab)) {
            foreach ($tab as $k=>$v) {
                $tab[$k]=Spyc::YAMLLoad($v);
            }
        }

        return $tab;
    }

/**
 * sortir pour les data de type select un tableau key=>$value pour chaque item option
 * @param  array $typeIDsArray les typeID concernés
 * @return array               Array ('name'=> 'A' : 'plus', 'B' => 'moins')
 */
    public static function getSelectOptionValueByTypeName($typeArray)
    {
        $tab = msSQL::sql2tabKey("select name, formValues from data_types where formType in ('select', 'radio') and name in ('".implode("', '", msSQL::cleanArray($typeArray))."')", "name", "formValues");
        if (is_array($tab)) {
            foreach ($tab as $k=>$v) {
                $tab[$k]=Spyc::YAMLLoad($v);
            }
        }

        return $tab;
    }

/**
 * Base pour le traitement automatique avant sauvegarder, par typeID
 * @return string valeur formatée si method correspondant existe
 */
    public function treatBeforeSave()
    {
        global $p;
        if (!isset($this->_value)) {
            throw new Exception('Data is not set');
        }
        if (!isset($this->_typeID)) {
            throw new Exception('TypeID is not set');
        }

        $action = "tbs_".msData::getNameFromTypeID($this->_typeID);
        if (isset($p['user']) && isset($p['user']['module'])) {
            $moduleClass="msMod".ucfirst($p['user']['module'])."DataSave";
        }
        if (isset($moduleClass) and method_exists($moduleClass, $action)) {
            $data = new $moduleClass;
            return $data->$action($this->_value);
        } elseif (method_exists('msModBaseDataSave', $action)) {
            $data = new msModBaseDataSave;
            return $data->$action($this->_value);
        } else {
            return $this->_value;
        }

    }

/**
 * Créer ou mettre à jour un data_type
 * @param  array $d array clef=>value pour chaque colonne SQL
 * @return array    array avec message erreur éventuel
 */
    public function createOrUpdateDataType($d)
    {
        global $p, $mysqli;
        $gump=new GUMP('fr');

        if (isset($d['id'])) {
            $gump->validation_rules(array(
              'id'=> 'required|numeric',
              'name'=> 'required|alpha_numeric',
              'label'=> 'required',
              'cat' => 'required|numeric'
          ));
        } else {
            $gump->validation_rules(array(
              'name'=> 'required|alpha_numeric|presence_bdd,data_types',
              'label'=> 'required',
              'cat' => 'required|numeric'
          ));
        }

        $validated_data = $gump->run($d);

        if ($validated_data === false) {
            $return['status']='failed';
            $errors = $gump->get_errors_array();
            $return['msg']=$errors;
            $return['code']=array_keys($errors);
        } else {
            $validated_data['fromID']=$p['user']['id'];
            $validated_data['creationDate']=date("Y-m-d H:i:s");

            if ($typeID=msSQL::sqlInsert('data_types', $validated_data) > 0) {
                $this->_typeID=$typeID;
                $return['status']='ok';
            } else {
                $return['status']='failed';
                $return['msg']=mysqli_error($mysqli);
            }
        }
        return $return;
    }

/**
 * Purger un array de datas sur les critères onlyfor et notfor de chaque item
 * @param  array $tab tableau de courriers / certificats / doc à signer obtenu par getDataTypesFromCatName('catModelesDocASigner', ['id','name','label', 'validationRules as onlyfor', 'validationErrorMsg as notfor']))
 * @param  int $userID ID de l'utilisateur concerné
 * @return void
 */
    public function applyRulesOnlyforNotforOnArray(&$tab, $userID) {
      foreach($tab as $k=>$v) {
        if(isset($v['onlyfor']) and !empty($v['onlyfor'])) {
          $tab[$k]['onlyfor'] = explode(',', $v['onlyfor']);
          $tab[$k]['onlyfor'] = array_filter($tab[$k]['onlyfor'], 'is_numeric');
          if(is_array($tab[$k]['onlyfor']) and !empty($tab[$k]['onlyfor'])) {
            if(count(array_filter($tab[$k]['onlyfor']))>0) {
              if(!in_array($userID, $tab[$k]['onlyfor'])) {
                unset($tab[$k]);
              }
            }
          }
        }
        if(isset($tab[$k]['notfor']) and !empty($tab[$k]['notfor'])) {
          $tab[$k]['notfor'] = explode(',', $tab[$k]['notfor']);
          $tab[$k]['notfor'] = array_filter($tab[$k]['notfor'], 'is_numeric');
          if(is_array($tab[$k]['notfor']) and !empty($tab[$k]['notfor'])) {
            if(in_array($userID, $tab[$k]['notfor'])) {
              unset($tab[$k]);
            }
          }
        }
      }
    }
}
