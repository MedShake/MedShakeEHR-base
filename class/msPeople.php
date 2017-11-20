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
 * Gestion des individus et de leurs datas
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msPeople
{

/**
 * @var int $_toID ID de l'individus concerné
 */
    public $_toID;
/**
 * @var int $_fromID ID de l'utilisteur enregistrant la donnée
 */
    private $_fromID;
/**
 * @var int $_dataset Le jeu de data concerné
 */
    private $_dataset;
/**
 * @var int $_type Type : patient ou pro
 */
    private $_type='patient';
/**
 * @var int $_creationDate Date de création de la donnée (si besoin)
 */
    private $_creationDate;


/**
 * Définir l'individu concerné
 * @param int $v ID de l'individu concerné
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
 * Définir l'utilisateur qui enregsitre la donnée
 * @param int $v ID de l'utilisateur
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
 * Définir la date de création de la donnée enregistrée
 * @param string $v Date au format mysql Y-m-d H:i:s
 * @return void
 */
    public function setCreationDate($v)
    {
        $this->_creationDate=$v;
    }

/**
 * Définir le type d'individu concerné : patient ou pro
 * @param string $t patient|pro
 * @return string type
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
 * @param string $v jeu de données
 * @return string Dataset
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
 * Obtenir les données administratives d'un individu (version complète)
 * @return array Array avec en clef le typeID
 */
    public function getAdministrativesDatas()
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }

        $datas=msSQL::sql2tab("select d.id, d.typeID, d.value, t.label , tt.label as parentLabel, d.parentTypeID, d.creationDate
  			from objets_data as d
  			left join data_types as t on d.typeID=t.id
  			left join data_types as tt on d.parentTypeID=tt.id
  			where d.toID='".$this->_toID."' and d.outdated='' and t.groupe='admin'
  			order by d.parentTypeID ");


        foreach ($datas as $v) {
            $tab[$v['typeID']]=$v;
        }

        return $tab;
    }

/**
 * Obtenir les données administratives d'un individu (version simple, array 1 dimension)
 * @return array Array typeID=>value
 */
    public function getSimpleAdminDatas()
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }

        $tab=msSQL::sql2tabKey("select d.typeID, d.value from objets_data as d
        left join data_types as t on d.typeID=t.id
			  where d.toID='".$this->_toID."' and d.outdated=''  and t.groupe='admin'

			 ", "typeID", "value");

        return $tab;
    }

/**
 * Sortir tous les types et les valeurs liées à partir d'un groupe de cat
 * @param  string $groupe groupe de données
 * @param  array $col    colonnes sql à retourner
 * @return array         Array de résultat
 */
    public function getPeopleDataFromDataTypeGroupe($groupe, $col=['*'])
    {
        return msSQL::sql2tab("select ".implode(', ', $col)."
        from data_types as dt
        left join objets_data as od on od.typeID=dt.id and od.toID='".$this->_toID."' and od.outdated='' and od.deleted=''
        where dt.groupe='".$groupe."'
        order by dt.displayOrder, dt.label");
    }
/**
 * Obtenir la liste des utilisateurs ayant accès à un service
 * @param  string $service service spécifique
 * @return array          tableau userID=>identité
 */
  public function getUsersListForService($service) {

    $typeID=msData::getTypeIDFromName($service);

    return msSQL::sql2tabKey("select p.id, concat(o2.value , ' ' , o.value) as identite
        from people as p
        join objets_data as dt on dt.toID=p.id and dt.typeID='".$typeID."' and dt.value='true'
        left join objets_data as o on o.toID=p.id and o.typeID=2 and o.outdated=''
        left join objets_data as o2 on o2.toID=p.id and o2.typeID=3 and o2.outdated=''
        where p.pass!='' order by identite", 'id', 'identite');
  }

  /**
   * Obtenir la liste des utilisateurs ayant une valeur spécifique pour un paramètre de configuration donné
   * @param  string $param param spécifique
   * @return array          tableau
   */
    public function getUsersWithSpecificParam($param) {

      $typeID=msData::getTypeIDFromName($param);

      if($data=msSQL::sql2tab("select p.id, concat(o2.value , ' ' , o.value) as identite, dt.value
          from people as p
          join objets_data as dt on dt.toID=p.id and dt.typeID='".$typeID."'
          left join objets_data as o on o.toID=p.id and o.typeID=2 and o.outdated=''
          left join objets_data as o2 on o2.toID=p.id and o2.typeID=3 and o2.outdated=''
          where p.pass!='' order by identite")) {
            $tab=array();
            foreach($data as $v) {
                $tab[$v['id']]['identite']=$v['identite'];
                $tab[$v['id']]['paramValue']=$v['value'];
            }
            return $tab;
          }
    }

/**
 * Historique complet des actes pour un individu
 * @return array Array multi avec année en clef de 1er niveau
 */
    public function getHistorique()
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }

        $name2typeID = new msData();
        $name2typeID = $name2typeID->getTypeIDsFromName(['mailPorteur', 'docPorteur', 'docType', 'docOrigine', 'dicomStudyID', 'ordoPorteur', 'reglePorteur']);

      if ($data = msSQL::sql2tab("select p.id, p.fromID, p.instance as parentID, p.important, p.titre, DATE_FORMAT(p.creationDate,'%d/%m/%Y') as creationTime, DATE_FORMAT(p.creationDate,'%Y') as creationYear,  p.updateDate, t.id as typeCS, t.groupe, t.label, t.formValues as formName, n.value as prenom, f.printModel, mail.id as sendMail, doc.value as fileext, doc2.value as docOrigine, img.value as dicomStudy,
      CASE WHEN DATE_ADD(p.creationDate, INTERVAL t.durationLife second) < NOW() THEN 'copy' ELSE 'update' END as iconeType
      from objets_data as p
      left join data_types as t on p.typeID=t.id
      left join objets_data as n on n.toID=p.fromID and n.typeID=3 and n.outdated=''
      left join objets_data as mail on mail.instance=p.id and mail.typeID='".$name2typeID['mailPorteur']."'
      left join objets_data as doc on doc.instance=p.id and doc.typeID='".$name2typeID['docType']."'
      left join objets_data as doc2 on doc2.instance=p.id and doc2.typeID='".$name2typeID['docOrigine']."'
      left join objets_data as img on img.instance=p.id and img.typeID='".$name2typeID['dicomStudyID']."'
      left join forms as f on f.internalName=t.formValues
      where (t.groupe in ('typeCS', 'courrier') or (t.groupe = 'doc' and  t.id='".$name2typeID['docPorteur']."') or (t.groupe = 'ordo' and  t.id='".$name2typeID['ordoPorteur']."')  or (t.groupe = 'reglement' and  t.id='".$name2typeID['reglePorteur']."') or (t.groupe='mail' and t.id='".$name2typeID['mailPorteur']."' and p.instance='0')) and p.toID='".$this->_toID."' and p.outdated='' and p.deleted=''
      group by p.id, n.value, mail.id, doc.value, doc2.value, img.value
      order by p.creationDate desc")) {
          foreach ($data as $v) {
              $return[$v['creationYear']][]=$v;
          }

          return $return;
      }
    }

/**
 * Historique des actes du jour pour un individu
 * @return array Array multi.
 */
    public function getToday()
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }

        $name2typeID = new msData();
        $name2typeID = $name2typeID->getTypeIDsFromName(['mailPorteur', 'docPorteur', 'docType', 'docOrigine', 'dicomStudyID', 'ordoPorteur', 'reglePorteur']);

        return msSQL::sql2tab("select p.id, p.fromID, p.instance as parentID, p.important, p.titre, DATE_FORMAT(p.creationDate,'%d/%m/%Y') as creationDate, DATE_FORMAT(p.creationDate,'%H:%i:%s') as creationTime,  p.updateDate, t.id as typeCS, t.groupe, t.label, t.formValues as formName, n.value as prenom, f.printModel, mail.id as sendMail, doc.value as fileext, doc2.value as docOrigine, img.value as dicomStudy,
        CASE WHEN DATE_ADD(p.creationDate, INTERVAL t.durationLife second) < NOW() THEN 'copy' ELSE 'update' END as iconeType
        from objets_data as p
        left join data_types as t on p.typeID=t.id
        left join objets_data as n on n.toID=p.fromID and n.typeID=3 and n.outdated=''
        left join objets_data as mail on mail.instance=p.id and mail.typeID='".$name2typeID['mailPorteur']."'
        left join objets_data as doc on doc.instance=p.id and doc.typeID='".$name2typeID['docType']."'
        left join objets_data as doc2 on doc2.instance=p.id and doc2.typeID='".$name2typeID['docOrigine']."'
        left join objets_data as img on img.instance=p.id and img.typeID='".$name2typeID['dicomStudyID']."'
        left join forms as f on f.internalName=t.formValues
        where (t.groupe in ('typeCS', 'courrier') or (t.groupe = 'doc' and  t.id='".$name2typeID['docPorteur']."') or (t.groupe = 'ordo' and  t.id='".$name2typeID['ordoPorteur']."')   or (t.groupe = 'reglement' and  t.id='".$name2typeID['reglePorteur']."') or (t.groupe='mail' and t.id='".$name2typeID['mailPorteur']."' and p.instance='0')) and p.toID='".$this->_toID."' and p.outdated='' and p.deleted='' and DATE(p.creationDate) = CURDATE()
        group by p.id, n.value, mail.id, doc.value, doc2.value, img.value 
        order by p.creationDate desc");
    }

/**
 * Calcul l'age
 * @return int Retourne l'age
 */
    public function getAge()
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }
        if ($birthdate=msSQL::sqlUniqueChamp("select value from objets_data where toID='".$this->_toID."' and typeID='8' order by id desc limit 1")) {
            $age = DateTime::createFromFormat('d/m/Y', $birthdate)->diff(new DateTime('now'))->y;
            return $age;
        } else {
            return false;
        }
    }

/**
 * Créer un nouvel individu
 * @return int ID du nouvel individu
 */
    public function createNew()
    {
        if (!is_numeric($this->_fromID)) {
            throw new Exception('FromID is not numeric');
        } else {
            $data=array(
                'pass' => '',
                'type' => $this->_type,
                'registerDate' => date("Y/m/d H:i:s"),
                'fromID' => $this->_fromID
            );

            //pour import
            if (isset($this->_toID)) {
                $data['id']=$this->_toID;
            }
            if (isset($this->_creationDate)) {
                $data['registerDate']=$this->_creationDate;
            }


            $this->_toID=msSQL::sqlInsert('people', $data);

            return $this->_toID;
        }
    }
}
