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
 * Interogation du modèle de données
 * Traitement d'une donnée avant enregistrement pour formatage
 *
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msData
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
        if (is_numeric($typeID)) {
            return $this->_typeID = $typeID;
        } else {
            throw new Exception('typeID is not numeric');
        }
    }


/**
 * Obtenir toutes les données types d'une catégorie
 * @param  int $catID ID de la catégorie
 * @param  array $col   array des colonnes sql à retourner
 * @return array        array
 */
    public function getDataTypesFromCatID($catID, $col=['*'])
    {
        if(!is_numeric($catID)) throw new Exception('catID is not numeric');
        return msSQL::sql2tab("select ".implode(', ', $col)." from data_types where cat='".$catID."' order by displayOrder, label");
    }

/**
 * Obtenir toutes les données types d'une catégorie
 * @param  string $name name de la catégorie
 * @param  array $col   array des colonnes sql à retourner
 * @return array        array
 */
    public function getDataTypesFromCatName($name, $col=['*'])
    {
        $catID=$this->getCatIDFromName($name);
        return $this->getDataTypesFromCatID($catID, $col);
    }


/**
 * Obtenir toutes les données types à partir d'un groupe
 * @param  string $groupe groupe
 * @param  array $col    colonnes SQL à retourner
 * @return array         array
 */
    public function getDataTypesFromGroupe($groupe, $col=['*'])
    {
        return msSQL::sql2tab("select ".implode(', ', $col)." from data_types where groupe='".$groupe."' order by displayOrder, label");
    }


/**
 * Sortir les infos d'un type à partir de son ID
 * @param  int $id  ID du type
 * @param  array $col colonnes SQL à retourner
 * @return array      array
 */
    public function getDataType($id, $col=['*'])
    {
        return msSQL::sqlUnique("select ".implode(', ', $col)." from data_types where id='".$id."'");

    }

/**
 * Obtenir les Labels des typeID à partir d'un array de typeID
 * @param  array $ar array de typeID
 * @return array     array typeID=>label
 */
    public function getLabelFromTypeID($ar=['1'])
    {
        return msSQL::sql2tabKey("select label, id from data_types where id in (".implode(',', $ar).")", 'id', 'label');
    }

/**
 * Obtenir les typeID à partir d'un array de Name
 * @param  array $ar array de name
 * @return array     array name=>typeID
 */
    public function getTypeIDsFromName($ar=['1'])
    {
        return msSQL::sql2tabKey("select name, id from data_types where name in ('".implode("','", $ar)."')", 'name', 'id');
    }

/**
 * Obtenir le typeID à partir de son nom
 * @param  string $name nom du type
 * @return int     typeID
 */
    public static function getTypeIDFromName($name)
    {
        return msSQL::sqlUniqueChamp("select id from data_types where name = '".$name."' ");
    }

/**
 * Obtenir le catID à partir de son nom
 * @param  string $name nom du type
 * @return int     catID
 */
    public static function getCatIDFromName($name)
    {
        return msSQL::sqlUniqueChamp("select id from data_cat where name = '".$name."' ");
    }

/**
 * sortir pour les data de type select un tableau key=>$value pour chaque item option
 * @param  array $typeIDsArray les typeID concernés
 * @return array               Array ('720'=> 'A' : 'plus', 'B' => 'moins')
 */
    public function getSelectOptionValue($typeIDsArray)
    {
        $tab = msSQL::sql2tabKey("select id, formValues from data_types where formType='select' and id in ('".implode("', '", $typeIDsArray)."')", "id", "formValues");
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

        $action = "type".$this->_typeID."TreatBeforeSave";
        $moduleName="msMod".ucfirst($p['user']['module'])."DataSave";
        if (method_exists($moduleName, $action)) {
            $data = new $moduleName;
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
        $gump=new GUMP();
        $d = $gump->sanitize($d);

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
            $return['msg']=$gump->get_errors_array();
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
}
