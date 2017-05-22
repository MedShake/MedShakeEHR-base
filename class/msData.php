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
            throw new Exception('formID is not numeric');
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
        return msSQL::sql2tab("select ".implode(', ', $col)." from data_types where cat='".$catID."' order by displayOrder, label");
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
        if (!isset($this->_value)) {
            throw new Exception('Data is not set');
        }
        if (!isset($this->_typeID)) {
            throw new Exception('TypeID is not set');
        }

        $action = "type".$this->_typeID."TreatBeforeSave";
        if (method_exists("msModuleDataSave", $action)) {
            $data = new msModuleDataSave();
            return $data->$action($this->_value);
        } else {
            return $this->_value;
        }
    }

}
