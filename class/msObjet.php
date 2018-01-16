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
 * Manipulation des objets (enregistrement des data)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msObjet
{
    /**
     * @var int ID de l'objet
     */
    public $_ID;
    /**
     * @var int ID de l'individu concerné par l'objet
     */
    public $_toID;
    /**
     * @var int ID de l'utilisateur qui enregistre l'objet
     */
    private $_fromID;
    /**
     * @var string jeu de données
     */
    private $_dataset;
    /**
     * @var string type de l'individu
     */
    private $_type='patient';
    /**
     * @var string date au format mySQL
     */
    private $_creationDate;
    /**
     * @var string date au format mySQL
     */
    private $_registerDate;

/**
 * Définir l'objet concerné
 * @param int $v ID de l'objet
 * @return int ID
 */
    public function setID($v)
    {
        if (is_numeric($v)) {
            return $this->_ID = $v;
        } else {
            throw new Exception('ID is not numeric');
        }
    }

/**
 * Définir l'individu concerné
 * @param int $v ID de l'individu
 * @return int toID
 */
    public function setToID($v)
    {
        if (is_numeric($v)) {
            return $this->_toID = $v;
        } else {
            throw new Exception('ToID is not numeric');
        }
    }

/**
 * Obtenir le toID
 * @return int toID
 */
public function getToID()
{
  return $this->_toID;
}

/**
 * Définir le user qui enregistre l'objet
 * @param [type] $v [description]
 * @return int fromID
 */
    public function setFromID($v)
    {
        if (is_numeric($v)) {
            return $this->_fromID = $v;
        } else {
            throw new Exception('FromID is not numeric');
        }
    }

/**
 * Définir la date de création affichée de l'objet
 * @param string $v date au format mysql (Y-m-d H:i:s)
 */
    public function setCreationDate($v)
    {
        $this->_creationDate=$v;
    }

/**
 * Définir la registerDate
 * uniquement pour des besoins spéciifques (import)
 * utiliser la creationDate pour changer la date affichée
 * @param string $v date au format mysql (Y-m-d H:i:s)
 */
    public function setRegisterDate($v)
    {
        $this->_registerDate=$v;
    }

/**
 * Définir le type de l'objet
 * @param string $t TypeID
 */
    public function setType($t)
    {
        if (in_array($t, array('patient', 'pro'))) {
            return $this->_type = $t;
        } else {
            throw new Exception('Type n\'est pas d\'une valeur autorisée');
        }
    }

/**
 * Définir le jeu de données
 * @param string $v dataset utilisé
 */
    public function setDataset($v)
    {
        if (is_string($v)) {
            return $this->_dataset = $v;
        } else {
            throw new Exception('Dataset is not string');
        }
    }

/**
 * Obtenir les data de base de l'objet à partir de son ID
 * @param  int $id  ID de l'objet
 * @param  array $col colonnes SQL à rapatrier
 * @return array      Array
 */
    public function getObjetDataByID($id, $col=['*'])
    {
        return msSQL::sqlUnique("select ".implode(', ', $col)." from objets_data where id='".$id."'");
    }

/**
 * Obtenir toutes les datas sur l'objet à partir de son ID
 * @param  int $id ID de l'objet
 * @return array     Array
 */
    public function getCompleteObjetDataByID($id)
    {
        $docTypeID = msData::getTypeIDFromName('docType');
        return msSQL::sqlUnique("select pd.* , t.label, t.groupe, t.formValues, doc.value as ext
        from objets_data as pd
        left join data_types as t on t.id=pd.typeID
        left join objets_data as doc on doc.instance=pd.id and doc.typeID='".$docTypeID."'
        where pd.id='".$id."'");
    }

/**
 * Obtenir les datas de l'objet ainsi que celles de ses enfants
 * @param  int $id ID de l'objet
 * @param  string $by clef du tableau
 * @return array     Array avec datas objet et de ses enfants
 */
    public function getObjetAndSons($id, $by='typeID')
    {
        return msSQL::sql2tabKey("select o.*, t.name
        from objets_data as o
        left join data_types as t on o.typeID=t.id
        where o.id='".$id."' or o.instance='".$id."' and o.outdated='' and o.deleted='' ", $by);
    }

/**
 * Créer ou mettre à jour un objet par son nom
 *
 * @param  int $name       name du type de l'objet
 * @param  string $value        value de l'objet
 * @param  int $parentID     ID du parent de l'objet
 * @param  int $parentTypeID typeID du parent de l'objet
 * @param  int $objetID      ID de l'objet (si mise à jour en particulier)
 * @return int|false                 Retourne ID de l'objet ou false si problème
 */
    public function createNewObjetByTypeName($name, $value, $parentID='0', $parentTypeID='0', $objetID='')
    {
        $typeID = msData::getTypeIDFromName($name);
        if (!is_numeric($typeID)) {
            throw new Exception('TypeID is not numeric');
        } else {
            return $this->createNewObjet($typeID, $value, $parentID, $parentTypeID, $objetID);
        }
    }


/**
 * Créer ou mettre à jour un objet
 * C'est la fonction clef : action en fonction du groupe de données et de la durée de vie
 * attribuée au modèle.
 * En règle général : on crée toujours une nouvelle entrée si l'utilisateur qui agit
 * n'est pas le même que le précédent.
 *
 * @param  int $typeID       typeID de l'objet
 * @param  string $value        value de l'objet
 * @param  int $parentID     ID du parent de l'objet
 * @param  int $parentTypeID typeID du parent de l'objet
 * @param  int $objetID      ID de l'objet (si mise à jour en particulier)
 * @return int|false                 Retourne ID de l'objet ou false si problème
 */
    public function createNewObjet($typeID, $value, $parentID='0', $parentTypeID='0', $objetID='')
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }
        if (!is_numeric($this->_fromID)) {
            throw new Exception('FromID is not numeric');
        }
        if (!is_numeric($typeID)) {
            throw new Exception('TypeID is not numeric');
        }

      //infos déterminées par le type et traitement de la value
      $data = new msData();
        $data->setValue($value);
        $data->setTypeID($typeID);
        $d=$data->getDataType($typeID);
        $value = $data->treatBeforeSave();

      $pd=array(
        'fromID' => $this->_fromID,
        'toID' => $this->_toID,
        'typeID' => $typeID,
        'parentTypeID' => $parentTypeID,
        'instance'=> $parentID,
        'value' => $value
      );

      //si creationDate est fixée
      if (isset($this->_creationDate)) {
          $pd['creationDate']=$this->_creationDate;
      }

      //mode à adopter en fonction du type d'objet
      if ($d['groupe']=='typecs' or $d['groupe']=='mail' or $d['groupe']=='doc' or $d['groupe']=='relation') {
          $lastID=msSQL::sqlInsert('objets_data', $pd);

      } elseif ($d['groupe']=='ordo' or $d['groupe']=='courrier') {

            //recup le titre
            if (is_numeric($objetID)) {
                $pd['titre']=msSQL::sqlUniqueChamp("select titre from objets_data where id='".$objetID."' limit 1");
            }

            //on regarde le précédent enregistrement pour l'objet et on update si durationLife ok ou si editeur n'est pas le même.
            if ($precedent=msSQL::sqlUnique("select id, UNIX_TIMESTAMP(DATE_ADD(creationDate, INTERVAL ".$d['durationLife']." SECOND)) as expirationtimestamp, fromID
            from objets_data
            where id = '".$objetID."'
            order by id desc limit 1")) {
                if ($precedent['expirationtimestamp']>time() and $precedent['fromID']==$this->_fromID) {
                    $pd['id']=$precedent['id'];
                    $pd['updateDate'] = date("Y/m/d H:i:s");
                }
            }
          $lastID=msSQL::sqlInsert('objets_data', $pd);

      } elseif ($d['groupe']=='reglement') {
          if (is_numeric($objetID)) {
              $pd['id']=$objetID;
              $pd['updateDate'] = date("Y/m/d H:i:s");
          } elseif ($parentID > 0) {
              if ($precedent=msSQL::sqlUniqueChamp("select id
                from objets_data
                where instance='".$parentID."' and typeID = '".$typeID."'
                order by id desc limit 1")) {
                  $pd['id']=$precedent;
                  $pd['updateDate'] = date("Y/m/d H:i:s");
              }
          }

          $lastID=msSQL::sqlInsert('objets_data', $pd);
      } elseif ($d['groupe']=='user') {

          //on regarde le précédent enregistrement pour l'objet et on update si durationLife ok ou si editeur n'est pas le même.
          if ($precedent=msSQL::sqlUnique("select id, fromID
          from objets_data
          where typeID = '".$typeID."' and toID='".$this->_toID."'
          order by id desc limit 1")) {
              if ($precedent['fromID']==$this->_fromID) {
                  $pd['id']=$precedent['id'];
                  $pd['updateDate'] = date("Y/m/d H:i:s");
              }
          }
          $lastID=msSQL::sqlInsert('objets_data', $pd);
      } else {

            //on regarde le précédent du même parent
            $precedent=msSQL::sqlUnique("select id, UNIX_TIMESTAMP(DATE_ADD(creationDate, INTERVAL ".$d['durationLife']." SECOND)) as expirationtimestamp, fromID
            from objets_data
            where typeID='".$typeID."'
            and toID = '".$this->_toID."'
            and instance = '".$parentID."'
            and outdated = ''
            order by id desc limit 1");

            //on update si ...
            if (isset($precedent['id'])) {
                if ($precedent['expirationtimestamp']>time() and $precedent['fromID']==$this->_fromID) {
                    $pd['id']=$precedent['id'];
                    $pd['updateDate'] = date("Y/m/d H:i:s");
                }
            }
          $lastID=msSQL::sqlInsert('objets_data', $pd);

          msSQL::sqlQuery("update objets_data set outdated='y' where typeID='".$typeID."' and toID='".$this->_toID."' and id < ".$lastID." and instance='".$parentID."' ");
      }

        if (is_numeric($lastID)) {
            return $lastID;
        } else {
            return false;
        }
    }

/**
 * Définir un titre pour l'objet
 * @param int $id    ID de l'objet
 * @param string $title Titre à attribuer
 */
    public static function setTitleObjet($id, $title)
    {
        $data=array(
        'id'=>$id,
        'titre'=>$title
      );
        msSQL::sqlInsert('objets_data', $data);
    }

/**
 * Changer la creationDate d'un objet
 */
    public function changeCreationDate() {
      if (!isset($this->_ID)) {
          throw new Exception('ID is not defined');
      }
      if (!isset($this->_creationDate)) {
          throw new Exception('CreationDate is not defined');
      }

      $data=array(
        'id'=>$this->_ID,
        'creationDate'=>$this->_creationDate
      );
      msSQL::sqlQuery("update objets_data set creationDate='".$this->_creationDate."' where instance='".$this->_ID."' ");
      return msSQL::sqlInsert('objets_data', $data);

    }

/**
 * Obtenir le dernier objet d'un type particulier pour un patient particulier
 * @return array tableau avec information sur l'objet
 */
      public function getLastObjetByTypeName($name) {
        if (!isset($this->_toID)) {
            throw new Exception('toID is not defined');
        }

        $name2typeID=new msData;

        if($name2typeID=$name2typeID->getTypeIDsFromName([$name, 'lastname', 'firstname', 'birthname'])) {
          $data=msSQL::sqlUnique("select pd.* , t.label, t.groupe, t.formValues, p.value as prenom,
          CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom
          from objets_data as pd
          left join data_types as t on t.id=pd.typeID
          left join objets_data as n on n.toID=pd.fromID and n.outdated='' and n.deleted='' and n.typeID='".$name2typeID['lastname']."'
          left join objets_data as p on p.toID=pd.fromID and p.outdated='' and p.deleted='' and p.typeID='".$name2typeID['firstname']."'
          left join objets_data as bn on bn.toID=pd.fromID and bn.outdated='' and bn.deleted='' and bn.typeID='".$name2typeID['birthame']."'
          where pd.toID='".$this->_toID."' and pd.typeID = '".$name2typeID[$name]."' and pd.deleted='' and pd.outdated=''
          order by updateDate desc
          limit 1");
        }


      }

}
