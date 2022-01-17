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
 * @contrib fr33z00 <https://github.com/fr33z00>
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
     * @var int ID de l'utilisateur propriétaire de l'objet
     */
    private $_fromID;
    /**
     * @var int ID de l'utilisateur qui enregistre l'objet si différent du propriétaire
     */
    private $_byID;
    /**
     * @var string date au format mySQL
     */
    private $_creationDate;
    /**
     * @var string date au format mySQL
     */
    private $_registerDate;

/**
 * Vérifier que l'objet ID existe
 * @param  int $id ID de l'objet
 * @return boolean     true/false
 */
    public static function checkObjetExist($id) {
      if(!is_numeric($id)) return false;
      if(msSQL::sqlUniqueChamp("SELECT id FROM objets_data WHERE id='".$id."' limit 1")) {
        return true;
      } else {
        return false;
      }
    }

/**
 * Définir l'objet concerné
 * @param int $v ID de l'objet
 * @return int ID
 */
    public function setObjetID($v)
    {
        if ($this->checkObjetExist($v)) {
            return $this->_ID = $v;
        } else {
            throw new Exception('Objet do not exist');
        }
    }

/**
 * Définir l'individu concerné
 * @param int $v ID de l'individu
 * @return int toID
 */
    public function setToID($v)
    {
        if (msPeople::checkPeopleExist($v)) {
            return $this->_toID = $v;
        } else {
            throw new Exception('ToID does not exist');
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
 * Définir le propriétaire de l'objet
 * @param [type] $v [description]
 * @return int fromID
 */
    public function setFromID($v)
    {
        if (msPeople::checkPeopleExist($v)) {
            return $this->_fromID = $v;
        } else {
            throw new Exception('FromID does not exist');
        }
    }

/**
 * Définir le user qui enregistre l'objet quand différent du propriétaire
 * @param [type] $v [description]
 * @return int fromID
 */
    public function setByID($v)
    {
        if (msPeople::checkPeopleExist($v)) {
            return $this->_byID = $v;
        } else {
            throw new Exception('byID does not exist');
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
 * uniquement pour des besoins spécifiques (import)
 * utiliser la creationDate pour changer la date affichée
 * @param string $v date au format mysql (Y-m-d H:i:s)
 */
    public function setRegisterDate($v)
    {
        $this->_registerDate=$v;
    }

/**
 * Obtenir les data de base de l'objet à partir de son ID
 * @param  array $col colonnes SQL à rapatrier
 * @return array      Array
 */
    public function getObjetDataByID($col=['*'])
    {
        if(!isset($this->_ID)) throw new Exception('ID is not set');
        return msSQL::sqlUnique("select ".implode(', ', msSQL::cleanArray($col))." from objets_data where id='".$this->_ID."'");
    }

/**
 * Obtenir le nom du formulaire d'origine à partir d'un objetID
 * @return string nom du formulaire d'origine
 */
    public function getOriginFormNameFromObjetID() {
      return msSQL::sqlUniqueChamp("select t.formValues
      from objets_data as pd
      left join data_types as t on t.id=pd.typeID
      where pd.id='".$this->_ID."' and t.groupe='typecs' limit 1");
    }

/**
 * Obtenir toutes les datas sur l'objet à partir de son ID
 * @return array     Array
 */
    public function getCompleteObjetDataByID()
    {
        if(!isset($this->_ID)) throw new Exception('ID is not set');
        $docTypeID = msData::getTypeIDFromName('docType');
        return msSQL::sqlUnique("select pd.* , t.name, t.label, t.groupe, t.formValues, t.module, t.placeholder, doc.value as ext
        from objets_data as pd
        left join data_types as t on t.id=pd.typeID
        left join objets_data as doc on doc.instance=pd.id and doc.typeID='".$docTypeID."'
        where pd.id='".$this->_ID."'");
    }

/**
 * Obtenir les datas de l'objet ainsi que celles de ses enfants
 * @param  string $by clef du tableau
 * @return array     Array avec datas objet et de ses enfants
 */
    public function getObjetAndSons($by='typeID')
    {
        if(!is_numeric($this->_ID)) throw new Exception('ID is not set');
        return msSQL::sql2tabKey("select o.*, t.name
        from objets_data as o
        left join data_types as t on o.typeID=t.id
        where (o.id='".$this->_ID."' or o.instance='".$this->_ID."') and o.outdated='' and o.deleted='' ", $by);
    }


/**
 * Obtenir certains enfants d'un objet en spécifiant le nom des types concernés
 * @param  array  $names tableau de types
 * @param  string $by    clef du tableau à retourner
 * @return array        tableau name=>
 */
    public function getObjetChildsByNames($names=[], $by='name') {
        $data = new msData;
        $name2typeID=$data->getTypeIDsFromName($names);
        if(!empty($name2typeID)) {
          return msSQL::sql2tabKey("select o.*, t.name
          from objets_data as o
          left join data_types as t on o.typeID=t.id
          where o.typeID in ('".implode("', '", $name2typeID)."') and o.instance='".$this->_ID."' and o.outdated='' and o.deleted='' ", $by);
        }
    }

/**
 * Marquer DELETED l'objet ainsi que ses enfants
 * @return string résultat sql
 */
    public function setDeletedObjetAndSons()
    {
        if (!isset($this->_fromID)) throw new Exception('FromID is not set');
        if (!isset($this->_ID)) throw new Exception('ID is not set');

        msSQL::sqlQuery("update objets_data set deleted='y', deletedByID='".$this->_fromID."', updateDate=NOW() where id='".$this->_ID."' ");

        if($tab=msSQL::sql2tabSimple("select id from objets_data where instance='".$this->_ID."'")) {
          foreach($tab as $sid) {
            $this->_ID = $sid;
            $this->setDeletedObjetAndSons();
          }
        }

        return true;
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
        if (!is_numeric($parentID)) {
            throw new Exception('ParentID is not numeric');
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
      if (isset($this->_byID)) {
          $pd['byID']=$this->_byID;
      }

      //si creationDate est fixée
      if (isset($this->_creationDate)) {
          $pd['creationDate']=$this->_creationDate;
      }

      //////mode à adopter en fonction du type d'objet

      if ($d['groupe']=='typecs' or $d['groupe']=='mail' or $d['groupe']=='doc') {

          // création d'un nouvel objet sans considération des objets antérieurs
          // but : enregistrement d'objets qui n'ont pas vocation a être édités secondairement,
          // uniquement marqués "deleted" si besoin.

          $lastID=msSQL::sqlInsert('objets_data', $pd);

      }    elseif($d['groupe']=='relation') {

          //Idem précédent mais on vérifie si l'enregistrement n'existe pas déjà avec mêmes caractéristiques pour ne pas dupliquer.
          $where = [];
          foreach($pd as $k=>$v){
            $where[$k]=$k."='".msSQL::cleanVar($v)."'";
          }
          unset($where['fromID']);
          $lastID=msSQL::sqlUniqueChamp("SELECT id from objets_data where ".implode(' and ', $where)." and deleted='' and outdated='' limit 1");
          if(empty($lastID)) {
            $lastID=msSQL::sqlInsert('objets_data', $pd);
          }

      } elseif ($d['groupe']=='ordo' or $d['groupe']=='courrier') {

          // création d'un nouvel objet uniquement si auteur différent ou si durée de vie dépassée (ou si précédent effacé),
          // pas de marquage des versions précédentes comme outdated
          // but : générer des versions sucessives toutes visibles à partir du moment où la durée de vie
          // (temps autorisé d'édition) est dépassée ou que l'auteur n'est pas le même.


          if (is_numeric($objetID)) {
              //recup le titre
              $pd['titre']=msSQL::sqlUniqueChamp("select titre from objets_data where id='".$objetID."' limit 1");

              //on regarde le précédent enregistrement pour l'objet et on update si durationLife ok ou si editeur n'est pas le même.
              if ($precedent=msSQL::sqlUnique("select id, CASE WHEN DATE_ADD(creationDate, INTERVAL ".$d['durationLife']." SECOND) > NOW() THEN '' ELSE 'y' END as outdated, fromID
              from objets_data
              where id = '".$objetID."' and deleted = ''
              order by id desc limit 1")) {
                  if ($precedent['outdated'] == '' and $precedent['fromID']==$this->_fromID) {
                      $pd['id']=$precedent['id'];
                      $pd['updateDate'] = date("Y-m-d H:i:s");
                  }
              }
          }
          $lastID=msSQL::sqlInsert('objets_data', $pd);

      } elseif ($d['groupe']=='reglement') {

          // attachement dès que possible à l'objet antérieur existant, sans notion d'auteur.
          // but : données de réglement sans historique possible.

          if (is_numeric($objetID)) {
              $pd['id']=$objetID;
              $pd['updateDate'] = date("Y-m-d H:i:s");
          } elseif ($parentID > 0) {
              if ($precedent=msSQL::sqlUniqueChamp("select id
                from objets_data
                where instance='".$parentID."' and typeID = '".$typeID."' and deleted = ''
                order by id desc limit 1")) {
                  $pd['id']=$precedent;
                  $pd['updateDate'] = date("Y-m-d H:i:s");
              }
          }

          $lastID=msSQL::sqlInsert('objets_data', $pd);
      } elseif ($d['groupe']=='user') {

          // création d'un nouvel objet uniquement si auteur différent ou si précédent effacé,
          // pas d'entrée en jeu de la durée de vie du type
          // on marque "deleted" les anciens éléments du même type
          // but : pas de log excessif des versions de paramétrage utilisateur,
          // en particulier quand il change lui même des valeurs (log si un tiers)

          //on regarde le précédent enregistrement pour l'objet et on update si editeur n'est pas le même.
          if ($precedent=msSQL::sqlUnique("select id, fromID
          from objets_data
          where typeID = '".$typeID."' and toID='".$this->_toID."' and outdated = '' and deleted = ''
          order by id desc limit 1")) {
              if ($precedent['fromID']==$this->_fromID) {
                  $pd['id']=$precedent['id'];
                  $pd['updateDate'] = date("Y-m-d H:i:s");
              }
          }
          if($lastID=msSQL::sqlInsert('objets_data', $pd)) {
              msSQL::sqlQuery("update objets_data set deleted='y', deletedByID='".$this->_fromID."' where typeID='".$typeID."' and toID='".$this->_toID."' and id < ".$lastID);
          }

      } elseif ($d['groupe']=='admin') {

          // création d'un nouvel enregistrement uniquement si la valeur est modifiée ou si elle est inexistante. Les auteurs de mise à jour ne sont donc pas consignés tant que la valeur est identique.
          // but : ne pas loguer de simples données administratives si elles n'évoluent pas véritablement

          //on regarde le précédent du même parent
          $precedent=msSQL::sqlUnique("select id, value, CASE WHEN DATE_ADD(creationDate, INTERVAL ".$d['durationLife']." SECOND) > NOW() THEN '' ELSE 'y' END as outdated, fromID
          from objets_data
          where typeID='".$typeID."'
          and toID = '".$this->_toID."'
          and instance = '".$parentID."'
          and outdated = '' and deleted = ''
          order by id desc limit 1");

          // insert si
          if ((isset($precedent['id']) and $value != $precedent['value']) or !isset($precedent['id'])) {

            // on met jour si on est dans la période de durée de vie et auteur identique
            if (isset($precedent['id']) and $precedent['outdated'] == '' and $precedent['fromID']==$this->_fromID) {
                $pd['id']=$precedent['id'];
                $pd['updateDate'] = date("Y-m-d H:i:s");
            }

            $lastID=msSQL::sqlInsert('objets_data', $pd);

            msSQL::sqlQuery("update objets_data set outdated='y' where typeID='".$typeID."' and toID='".$this->_toID."' and id < ".$lastID." and instance='".$parentID."' ");
          }

          if (isset($precedent['id']) and !isset($lastID)) {
            $lastID = $precedent['id'];
          }


      }

      // types : medical / dicom
      else {

          // cas général : création d'un nouvel objet uniquement si auteur différent ou si durée de vie dépassée (ou si précédent effacé),
          // marquage des versions précédentes comme outdated
          // but : enregistrement susccessif complet des modifications concernées

          //on regarde le précédent du même parent
          $precedent=msSQL::sqlUnique("select id, CASE WHEN DATE_ADD(creationDate, INTERVAL ".$d['durationLife']." SECOND) > NOW() THEN '' ELSE 'y' END as outdated, fromID
          from objets_data
          where typeID='".$typeID."'
          and toID = '".$this->_toID."'
          and instance = '".$parentID."'
          and outdated = '' and deleted = ''
          order by id desc limit 1");

          //on update si ...
          if (isset($precedent['id'])) {
              if ($precedent['outdated'] == '' and $precedent['fromID']==$this->_fromID) {
                  $pd['id']=$precedent['id'];
                  $pd['updateDate'] = date("Y-m-d H:i:s");
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
        if (!self::checkObjetExist($id)) throw new Exception('ID do not exist');
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
      msSQL::sqlQuery("update objets_data set creationDate='".msSQL::cleanVar($this->_creationDate)."' where instance='".$this->_ID."' ");
      return msSQL::sqlInsert('objets_data', $data);

    }

/**
* Obtenir le dernier objet d'un type particulier pour un patient particulier
* @param  string $name nom du type de l'objet
* @param  string $instance int de l'instance de l'objet
* @return array tableau avec information sur l'objet
*/
    public function getLastObjetByTypeName($name, $instance=false) {
      if (!isset($this->_toID)) {
          throw new Exception('toID is not defined');
      }

      if(is_numeric($instance)) {
        $where = " and pd.instance='".$instance."'";
      } else {
        $where = null;
      }

      $name2typeID=new msData;

      if($name2typeID=$name2typeID->getTypeIDsFromName([$name, 'lastname', 'firstname', 'birthname'])) {
        if(isset($name2typeID[$name])) {
          if($data=msSQL::sqlUnique("select pd.* , t.label, t.groupe, t.formValues, p.value as prenom,
          CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom
          from objets_data as pd
          left join data_types as t on t.id=pd.typeID
          left join objets_data as n on n.toID=pd.fromID and n.outdated='' and n.deleted='' and n.typeID='".$name2typeID['lastname']."'
          left join objets_data as p on p.toID=pd.fromID and p.outdated='' and p.deleted='' and p.typeID='".$name2typeID['firstname']."'
          left join objets_data as bn on bn.toID=pd.fromID and bn.outdated='' and bn.deleted='' and bn.typeID='".$name2typeID['birthname']."'
          where pd.toID='".$this->_toID."' and pd.typeID = '".$name2typeID[$name]."' and pd.deleted='' and pd.outdated='' $where
          order by updateDate desc
          limit 1")) {
            return $data;
          } else {
            return false;
          }
        } else {
          return false;
        }
      }

      return false;
    }

/**
* Obtenir la valeur du dernier objet d'un type particulier pour un patient particulier
* @param  string $name nom du type de l'objet
* @param  string $instance int de l'instance de l'objet
* @return string chaine avec la valeur de l'objet
*/
    public function getLastObjetValueByTypeName($name, $instance=false) {
      if (!isset($this->_toID)) {
          throw new Exception('toID is not defined');
      }

      if(is_numeric($instance)) {
        $where = " and pd.instance='".$instance."'";
      } else {
        $where = null;
      }

      $name2typeID=new msData;

      if($name2typeID=$name2typeID->getTypeIDsFromName([$name])) {
        if(isset($name2typeID[$name])) {
          if($data=msSQL::sqlUniqueChamp("select pd.value
          from objets_data as pd
          where pd.toID='".$this->_toID."' and pd.typeID = '".$name2typeID[$name]."' and pd.deleted='' and pd.outdated='' $where
          order by updateDate desc
          limit 1")) {
            return $data;
          } else {
            return false;
          }
        } else {
          return false;
        }
      }
      return false;
    }

/**
 * Obtenir la liste des ID pour un type donnée et un patient donné
 * @param  string $name name du type
 * @param  string $parentId instance
 * @return array       tableau id=>date création
 */
    public function getListObjetsIdFromName($name, $parentId = '') {
      if (!isset($this->_toID)) {
          throw new Exception('toID is not defined');
      }
      if (!empty($parentId) and !is_numeric($parentId)) {
          throw new Exception('ParentID is not numeric');
      }

      if (is_numeric($parentId)) {
          $whereInstance = ' and pd.instance="'.$parentId.'"';
      } else {
          $whereInstance = '';
      }

      $name2typeID=new msData;

      if($name2typeID=$name2typeID->getTypeIDsFromName([$name])) {
        if($data=msSQL::sql2tabKey("select pd.id, pd.creationDate
        from objets_data as pd
        where pd.toID='".$this->_toID."' and pd.typeID = '".$name2typeID[$name]."' and pd.deleted='' and pd.outdated='' ".$whereInstance."
        order by  pd.creationDate", 'id', 'creationDate')) {
          return $data;
        }
      }
      return false;
    }

/**
 * Obtenir l'historique de création d'un data type particulier, tout patient
 * @param  string  $name  data type name
 * @param  integer $start start sql
 * @param  integer $limit limit sql
 * @return array         tableau de résultat
 */
    public function getHistoriqueDataType($name, $start=0, $limit=50)
    {

        if (!is_numeric($start)) throw new Exception('Start is not numeric');
        if (!is_numeric($limit)) throw new Exception('Limit is not numeric');

        $data = new msData();
        $name2typeID=$data->getTypeIDsFromName(['mailPorteur', 'docPorteur', 'docType', 'docOrigine', 'dicomStudyID', 'firstname', 'lastname', 'birthname','csAtcdStrucDeclaration','lapOrdonnance', $name]);
        if(isset($name2typeID[$name])) {
          $data = msSQL::sql2tab("select p.id, p.fromID, p.toID, p.instance as parentID, p.important, p.titre, p.registerDate, t.formValues as formName, n1.value as pratPrenom,
          CASE WHEN n2.value != '' THEN n2.value  ELSE bn.value END as pratNom,

          n1b.value as patientPrenom,
          CASE WHEN n2b.value != '' and bnb.value != '' THEN CONCAT(n2b.value, ' (', bnb.value , ')')
          WHEN n2b.value != '' THEN n2b.value
          ELSE bnb.value END as patientNom

          from objets_data as p
          left join data_types as t on p.typeID=t.id
          left join objets_data as n1 on n1.toID=p.fromID and n1.typeID='".$name2typeID['firstname']."' and n1.outdated='' and n1.deleted=''
          left join objets_data as n2 on n2.toID=p.fromID and n2.typeID='".$name2typeID['lastname']."' and n2.outdated='' and n2.deleted=''
          left join objets_data as bn on bn.toID=p.fromID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''

          left join objets_data as n1b on n1b.toID=p.toID and n1b.typeID='".$name2typeID['firstname']."' and n1b.outdated='' and n1b.deleted=''
          left join objets_data as n2b on n2b.toID=p.toID and n2b.typeID='".$name2typeID['lastname']."' and n2b.outdated='' and n2b.deleted=''
          left join objets_data as bnb on bnb.toID=p.toID and bnb.typeID='".$name2typeID['birthname']."' and bnb.outdated='' and bnb.deleted=''

          where p.typeID ='".$name2typeID[$name]."' and p.outdated='' and p.deleted=''
          group by p.id, bn.value, n1.value, n2.value, bnb.value, n1b.value, n2b.value
          order by p.creationDate desc
          limit ".$start.",".$limit);

          return $data;
        } else {
          return [];
        }

    }

/**
 * Obtenir le nombre total d'objets d'un type particulier
 * @param  string  $name           data type name
 * @param  string  $outdated       '' ou 'y'
 * @param  string  $deleted        '' ou 'y'
 * @param  boolean $noTestPatients pas les patients tests
 * @return array                  tableau de retour
 */
    public function getNumberOfObjetOfType($name, $outdated='', $deleted='', $noTestPatients=true ) {
      global $p;
      if (!in_array($outdated, ['','y'])) {
          throw new Exception('Outdated wrong value');
      }
      if (!in_array($deleted, ['','y'])) {
          throw new Exception('Deleted wrong value');
      }
      if (!is_bool($noTestPatients)) {
          throw new Exception('NoTestPatient wrong value');
      }
      if($noTestPatients) {
        $statsExclusionPatients=msSQL::cleanArray(explode(',',$p['config']['statsExclusionPatients']));
      } else {
        $statsExclusionPatients=[];
      }

      $typeID=msData::getTypeIDFromName($name);

      return msSQL::sqlUniqueChamp("select count(id) from objets_data as d where d.typeID='".$typeID."' and d.toID not in ('".implode("', '", $statsExclusionPatients)."') and d.outdated='".$outdated."' and d.deleted='".$deleted."'");
    }

/**
 * Obtenir l'évolution contextuelle (patient, instance) des valeurs d'un data type
 * @param  string  $name     nom du data type
 * @param  integer $instance instance
 * @return array             array registerDate desc.
 */
    public function getDataTypeContextualHistoric($name, $instance=0) {

      if (!isset($this->_toID)) throw new Exception('toID is not defined');
      if (!is_numeric($instance)) throw new Exception('Instance is not numeric');

      $typeID=msData::getTypeIDFromName($name);

      $data = new msData();
      $name2typeID=$data->getTypeIDsFromName(['firstname', 'lastname', 'birthname']);

      return msSQL::sql2tab("SELECT o.*, n1.value as pratPrenom,
      CASE WHEN n2.value != '' THEN n2.value  ELSE bn.value END as pratNom
        FROM objets_data as o

        left join objets_data as n1 on n1.toID=o.fromID and n1.typeID='".$name2typeID['firstname']."' and n1.outdated='' and n1.deleted=''
        left join objets_data as n2 on n2.toID=o.fromID and n2.typeID='".$name2typeID['lastname']."' and n2.outdated='' and n2.deleted=''
        left join objets_data as bn on bn.toID=o.fromID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''

        WHERE o.toID = '".$this->_toID."' AND o.typeID = '".$typeID."' and o.instance='".$instance."'
        order by o.registerDate desc");
    }

/**
 * Obtenir les valeurs d'un data type
 * @param  string $name data_type
 * @return array       array des différentes valeurs non outdated non deleted
 */
    public function getDataTypePatientActiveValues($name) {
      if (!isset($this->_toID)) throw new Exception('toID is not defined');

      $typeID=msData::getTypeIDFromName($name);

      $tab = (array) msSQL::sql2tabSimple("SELECT o.value
        FROM objets_data as o
        WHERE o.toID = '".$this->_toID."' AND o.typeID = '".$typeID."' and o.outdated = '' and o.deleted = ''");
      $tab = array_unique($tab);
      return $tab;
    }


}
