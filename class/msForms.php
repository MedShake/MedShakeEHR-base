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
 * Manipulation des formulaires et des catégories de formulaires
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */
class msForms
{

/**
 * Type privé ou public du formulaire
 * @var string
 */
  private $_formsType='public';
/**
 * Catégorie du formulaire
 * @var int
 */
  private $_catID;

/**
 * Définir le FormsType
 * @param string $formsType public/private
 */
  public function setFormsType($formsType) {
    if(!in_array($formsType, ['public', 'private'])) {
      throw new Exception('FormsType n\'a pas une valeur correcte');
    }
    return $this->_formsType=$formsType;
  }

/**
 * Définir la catégorie des formulaires
 * @param int $catID catID
 */
  public function setCatID($catID) {
    if(!is_numeric($catID)) {
      throw new Exception('CatID n\'a pas une valeur correcte');
    }
    $this->_catID=$catID;
  }

/**
 * Obtenir la liste des formulaires par name de catégorie
 * @param  string $orderBy chaine de recherche
 * @return array          formulaires
 */
  public function getFormsListByCatName($orderBy = 'c.label asc, f.module, f.id asc') {
    return $this->_getFormsListByCat('name', $orderBy);
  }

/**
 * Obtenir la liste des formulaires par id de catégorie
 * @param  string $orderBy chaine de tri
 * @return array          formulaires
 */
  public function getFormsListByCatID($orderBy = 'c.label asc, f.module, f.id asc') {
    return $this->_getFormsListByCat('id', $orderBy);
  }

/**
 * Obtenir la liste des formulaires par catégories
 * @param  string $type    type de retour : par name ou id de cat
 * @param  string $orderBy chaine de tri
 * @return array          array pas cat
 */
  private function _getFormsListByCat($type, $orderBy = 'c.label asc, f.module, f.id asc') {
    $tab=[];

    if(isset($this->_catID)) {
      $catID=" and f.cat = '".$this->_catID."'";
    } else {
      $catID=null;
    }
    if ($tabTypes=msSQL::sql2tab("select f.id, f.internalName, f.name, f.description, f.module, c.name as catName, c.label as catLabel, c.id as catID
        from forms as f
        left join forms_cat as c on c.id=f.cat
        where f.id > 0 and f.type='".$this->_formsType."' ".$catID."
        group by f.id
        order by ".$orderBy)) {
        foreach ($tabTypes as $v) {
            if($type=='name') {
              $tab[$v['catName']][]=$v;
            } else {
              $tab[$v['catID']][]=$v;
            }
        }
    }
    return $tab;
  }

/**
 * Obtenir la liste des catégories par ID
 * @return array cat par ID
 */
  public static function getCatListByID() {
    return msSQL::sql2tabKey("select id, label from forms_cat order by label", 'id', 'label');
  }

/**
 * Obtenir la liste des catégories par name
 * @return array cat par name
 */
  public static function getCatListByName() {
    return msSQL::sql2tabKey("select internalName, label from forms_cat order by label", 'internalName', 'label');
  }

}
