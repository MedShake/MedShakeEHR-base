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
 * @contrib fr33z00 <https://www.github.com/fr33z00>
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
 * date de naissance
 * @var string
 */
    private $_birthdate;
/**
 * age du patient à différent format
 * @var array
 */
    private $_ageFormats;

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
        if (in_array($t, array('patient', 'pro', 'externe'))) {
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
 * Est-ce un patient externe?
 * @return value true/false
 */
    public function isExterne() {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }
        return msSQL::sqlUniqueChamp("SELECT type='externe' FROM people WHERE id='".$this->_toID."' limit 1")==1;
    }

/**
 * Obtenir le module pour un user
 * @return value module
 */
    public function getModule() {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }
        return msSQL::sqlUniqueChamp("SELECT module FROM people WHERE id='".$this->_toID."' limit 1");
    }

/**
 * Obtenir le type du dossier
 * @return string patient / pro / deleted / externe ...
 */
    public function getPeopleType() {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }
        return msSQL::sqlUniqueChamp("SELECT type FROM people WHERE id='".$this->_toID."' limit 1");
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

        if($datas=msSQL::sql2tab("select d.id, d.typeID, d.value, t.name, t.label , tt.label as parentLabel, d.parentTypeID, d.creationDate
  			from objets_data as d
  			left join data_types as t on d.typeID=t.id
  			left join data_types as tt on d.parentTypeID=tt.id
  			where d.toID='".$this->_toID."' and d.outdated='' and t.groupe='admin'
  			order by d.parentTypeID ")) {

          foreach ($datas as $v) {
              if($v['name']=='birthdate') $this->_birthdate=$v['value'];
              $tab[$v['typeID']]=$v;
              $tab[$v['name']]=$v;
          }
          return $tab;
        }


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

        $tab=msSQL::sql2tabKey("select d.typeID, d.value
        from objets_data as d
        left join data_types as t on d.typeID=t.id
			  where d.toID='".$this->_toID."' and d.outdated=''  and t.groupe='admin'", "typeID", "value");

        return $tab;
    }

/**
 * Obtenir les données administratives d'un individu avec key name
 * @return array Array name=>value
 */
    public function getSimpleAdminDatasByName()
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }

        $tab=msSQL::sql2tabKey("select t.name, d.value
        from objets_data as d
        left join data_types as t on d.typeID=t.id
			  where d.toID='".$this->_toID."' and d.outdated=''  and t.groupe='admin'", "name", "value");

        if(isset($tab['birthdate'])) $this->_birthdate=$tab['birthdate'];

        return $tab;
    }

    /**
     * Obtenir les pros en relation avec ce patient
     * @return array array des pros en relation
     */
    public function getRelationsWithPros()
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }

        $name2typeID = new msData();
        $name2typeID = $name2typeID->getTypeIDsFromName(['relationID', 'relationPatientPraticien', 'relationPatientPatient', 'titre', 'firstname', 'lastname', 'birthname']);

        return msSQL::sql2tab("select o.value as pratID, c.value as typeRelation, p.value as prenom, t.value as titre, CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom
        from objets_data as o
        inner join objets_data as c on c.instance=o.id and c.typeID='".$name2typeID['relationPatientPraticien']."' and c.value != 'patient'
        left join objets_data as n on n.toID=o.value and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
        left join objets_data as bn on bn.toID=o.value and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
        left join objets_data as p on p.toID=o.value and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
        left join objets_data as t on t.toID=o.value and t.typeID='".$name2typeID['titre']."' and t.outdated='' and t.deleted=''
        where o.toID='".$this->_toID."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated=''
        group by o.value, c.id, bn.id, n.id, p.id, t.id
        order by typeRelation = 'MT' desc, nom asc");
    }

    /**
     * Obtenir les autres patients liés généalogiquement avec ce patient
     * @return array array des autres patients
     *
     */
    public function getRelationsWithOtherPatients()
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }

        $name2typeID = new msData();
        $name2typeID = $name2typeID->getTypeIDsFromName(['relationID', 'relationPatientPraticien', 'relationPatientPatient', 'titre', 'firstname', 'lastname', 'birthdate', 'birthname']);

        return msSQL::sql2tab("select o.value as patientID, c.value as typeRelation, p.value as prenom, d.value as ddn, CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom
      from objets_data as o
      inner join objets_data as c on c.instance=o.id and c.typeID='".$name2typeID['relationPatientPatient']."'
      left join objets_data as n on n.toID=o.value and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
      left join objets_data as bn on bn.toID=o.value and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      left join objets_data as p on p.toID=o.value and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
      left join objets_data as d on d.toID=o.value and d.typeID='".$name2typeID['birthdate']."' and p.outdated='' and p.deleted=''
      where o.toID='".$this->_toID."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated=''
      group by o.value, c.id, bn.id, n.id, p.id, d.id
      order by nom asc");
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
  public function getUsersListForService($service)
  {
      $name2typeID = new msData();
      $name2typeID = $name2typeID->getTypeIDsFromName([$service, 'firstname', 'lastname', 'birthname']);

      if (msConfiguration::getDefaultParameterValue($service)=='true') {
          $forbiddenIDs=msSQL::sql2tabKey("SELECT toID FROM configuration WHERE level='user' and name='".$service."' and value='false'", 'toID', 'toID')?:array();
          return msSQL::sql2tabKey("select p.id, CASE WHEN o.value != '' THEN concat(o2.value , ' ' , o.value) ELSE concat(o2.value , ' ' , bn.value) END as identite
            from people as p
            left join objets_data as o on o.toID=p.id and o.typeID='".$name2typeID['lastname']."' and o.outdated='' and o.deleted=''
            left join objets_data as bn on bn.toID=p.id and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
            left join objets_data as o2 on o2.toID=p.id and o2.typeID='".$name2typeID['firstname']."' and o2.outdated='' and o2.deleted=''
            where p.pass!='' and p.id not in ('".implode("','", $forbiddenIDs)."') order by identite", 'id', 'identite');
      } else {
          $allowedIDs=msSQL::sql2tabKey("SELECT toID FROM configuration WHERE level='user' and name='".$service."' and value='true'", 'toID', 'toID')?:array();
          return msSQL::sql2tabKey("select p.id, CASE WHEN o.value != '' THEN concat(o2.value , ' ' , o.value) ELSE concat(o2.value , ' ' , bn.value) END as identite
            from people as p
            left join objets_data as o on o.toID=p.id and o.typeID='".$name2typeID['lastname']."' and o.outdated='' and o.deleted=''
            left join objets_data as bn on bn.toID=p.id and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
            left join objets_data as o2 on o2.toID=p.id and o2.typeID='".$name2typeID['firstname']."' and o2.outdated='' and o2.deleted=''
            where p.pass!='' and p.id in ('".implode("','", $allowedIDs)."') order by identite", 'id', 'identite');
      }
  }

  /**
   * Obtenir la liste des utilisateurs ayant une valeur spécifique pour un paramètre de configuration donné
   * @param  string $param param spécifique
   * @return array          tableau
   */
    public static function getUsersWithSpecificParam($param)
    {
        $name2typeID = new msData();
        $name2typeID = $name2typeID->getTypeIDsFromName([$param, 'firstname', 'lastname', 'birthname']);

        if ($data=msSQL::sql2tab("select p.id, c.value, CASE WHEN o.value != '' THEN concat(o2.value , ' ' , o.value) ELSE concat(o2.value , ' ' , bn.value) END as identite
          from people as p
          join configuration as c on c.toID=p.id and c.name='".$param."'
          left join objets_data as o on o.toID=p.id and o.typeID='".$name2typeID['lastname']."' and o.outdated='' and o.deleted=''
          left join objets_data as bn on bn.toID=p.id and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
          left join objets_data as o2 on o2.toID=p.id and o2.typeID='".$name2typeID['firstname']."' and o2.outdated='' and o2.deleted=''
          where p.pass!='' order by identite")) {
            $tab=array();
            foreach ($data as $v) {
                $tab[$v['id']]['identite']=$v['identite'];
                $tab[$v['id']]['paramValue']=$v['value'];
            }
            return $tab;
        }
    }
/**
 * Obtenir les ALD enregistrées pour le patient
 * @return array array des ALD
 */
    public function getALD()
    {
      if (!is_numeric($this->_toID)) {
          throw new Exception('ToID is not numeric');
      }

      $name2typeID = new msData();
      $name2typeID = $name2typeID->getTypeIDsFromName(['csAldDeclaration', 'firstname', 'lastname', 'birthname']);

      if($csAldID=msSQL::sql2tabKey("select p.id, n1.value as prenom, CASE WHEN n2.value != '' THEN n2.value ELSE bn.value END as nom
      from objets_data as p
      left join objets_data as n1 on n1.toID=p.fromID and n1.typeID='".$name2typeID['firstname']."' and n1.outdated='' and n1.deleted=''
      left join objets_data as n2 on n2.toID=p.fromID and n2.typeID='".$name2typeID['lastname']."' and n2.outdated='' and n2.deleted=''
      left join objets_data as bn on bn.toID=p.fromID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      where p.toID='".$this->_toID."' and p.typeID='".$name2typeID['csAldDeclaration']."' and p.deleted='' and p.outdated=''", 'id')) {
        foreach($csAldID as $id=>$v) {
            $ald=new msObjet;
            $rd[$id]=$ald->getObjetAndSons($id, 'name');
            $rd[$id]['fromName']=$v['prenom'].' '.$v['nom'];
            $selectedAldLabel=new msData;
            $selectedAldLabel = $selectedAldLabel->getSelectOptionValue([$rd[$id]['aldNumber']['typeID']]);

            $rd[$id]['aldLabel']=$selectedAldLabel[$rd[$id]['aldNumber']['typeID']][$rd[$id]['aldNumber']['value']];

        }
        return $rd;
      }

    }

/**
 * Obtenir les atcd structurés enregistrées pour le patient
 * @param  string $parentTypeName   typeName du parent porter de l'ATCD
 * @return array array des ALD
 */
    public function getAtcdStruc($parentTypeName)
    {
      if (!is_numeric($this->_toID)) {
          throw new Exception('ToID is not numeric');
      }

      $msdata = new msData();
      $name2typeID = $msdata->getTypeIDsFromName(['csAtcdStrucDeclaration', 'firstname', 'lastname', 'birthname',$parentTypeName]);

      if(isset($name2typeID[$parentTypeName])) {
        $rd['parentLabel']=$msdata->getLabelFromTypeID([$name2typeID[$parentTypeName]]);
        $rd['parentLabel']=$rd['parentLabel'][$name2typeID[$parentTypeName]];
      }

      if(!isset($name2typeID[$parentTypeName])) return false;

      if($csAldID=msSQL::sql2tabKey("select p.id, n1.value as prenom, CASE WHEN n2.value != '' THEN n2.value ELSE bn.value END as nom
      from objets_data as p
      left join objets_data as n1 on n1.toID=p.fromID and n1.typeID='".$name2typeID['firstname']."' and n1.outdated='' and n1.deleted=''
      left join objets_data as n2 on n2.toID=p.fromID and n2.typeID='".$name2typeID['lastname']."' and n2.outdated='' and n2.deleted=''
      left join objets_data as bn on bn.toID=p.fromID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      where p.toID='".$this->_toID."' and p.typeID='".$name2typeID['csAtcdStrucDeclaration']."' and p.deleted='' and p.outdated='' and p.instance='".$name2typeID[$parentTypeName]."' ", 'id')) {
        foreach($csAldID as $id=>$v) {
            $ald=new msObjet;
            $rd['atcd'][$id]=$ald->getObjetAndSons($id, 'name');
            $rd['atcd'][$id]['fromName']=$v['prenom'].' '.$v['nom'];
        }

      }
      $rd['parentTypeID']=$name2typeID[$parentTypeName];
      $rd['parentTypeName']=$parentTypeName;
      return $rd;
    }

/**
 * Obtenir tous les codes PERSO et ACTIFS (!) CIM10  d'un patient pour le LAP.
 * @return array tableau de codes CIM10 perso actifs
 */
    public function getAtcdAndAldCim10Codes () {
      global $p;
      if (!is_numeric($this->_toID)) {
          throw new Exception('ToID is not numeric');
      }
      $msdata = new msData();
      $name2typeID = $msdata->getTypeIDsFromName(['atcdStrucCIM10', 'aldCIM10','csAldDeclaration', 'atcdStrucCIM10InLap']);
      $parentsAutorises = $msdata->getTypeIDsFromName(explode(',', $p['config']['lapAtcdStrucPersoPourAnalyse']));

      if(!empty($parentsAutorises)) {
        return msSQL::sql2tabSimple("select o.value
        from objets_data as o
        left join objets_data as p on p.id=o.instance
        left join objets_data as ac on ac.instance=o.instance and ac.typeID='".$name2typeID['atcdStrucCIM10InLap']."'
        where o.toID='".$this->_toID."' and
          ((o.typeID ='".$name2typeID['atcdStrucCIM10']."' and p.instance in ('".implode("','", $parentsAutorises)."') and ac.value != 'n')
          or
          (o.typeID = '".$name2typeID['aldCIM10']."' and p.typeID= '".$name2typeID['csAldDeclaration']."'))
          and o.deleted='' and o.outdated=''
        group by o.id
        ");
      } else {
        return [];
      }

    }

/**
 * Obtenir les allergies structurées enregistrées pour le patient
 * @param  string $parentTypeName   typeName du parent porter des allergies
 * @return array array des ALD
 */
    public function getAllergies($parentTypeName)
    {
      if (!is_numeric($this->_toID)) {
          throw new Exception('ToID is not numeric');
      }

      $name2typeID = new msData();
      $name2typeID = $name2typeID->getTypeIDsFromName(['allergieCodeTheriaque', 'allergieLibelleTheriaque', 'firstname', 'lastname', 'birthname', $parentTypeName]);

      if(!isset($name2typeID[$parentTypeName])) return false;

      $rd['allergiesData']=msSQL::sql2tabKey("select p.*, CASE WHEN n2.value != '' THEN concat(n1.value, ' ',n2.value) ELSE concat(n1.value, ' ', bn.value) END as fromName, p1.value as libelle
      from objets_data as p
      left join objets_data as p1 on p1.instance=p.id and p1.typeID='".$name2typeID['allergieLibelleTheriaque']."' and p1.outdated='' and p1.deleted=''
      left join objets_data as n1 on n1.toID=p.fromID and n1.typeID='".$name2typeID['firstname']."' and n1.outdated='' and n1.deleted=''
      left join objets_data as n2 on n2.toID=p.fromID and n2.typeID='".$name2typeID['lastname']."' and n2.outdated='' and n2.deleted=''
      left join objets_data as bn on bn.toID=p.fromID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      where p.toID='".$this->_toID."' and p.typeID='".$name2typeID['allergieCodeTheriaque']."' and p.deleted='' and p.outdated='' and p.instance='".$name2typeID[$parentTypeName]."' ", 'id');

      $rd['parentTypeID']=$name2typeID[$parentTypeName];
      $rd['parentTypeName']=$parentTypeName;
      return $rd;
    }

/**
 * Obtenir les codes allergies structurées enregistrées pour le patient
 * @param  string $parentTypeName   typeName du parent porteur des allergies
 * @return array array des ALD
 */
    public function getAllergiesCodes($parentTypeName)
    {
      if (!is_numeric($this->_toID)) {
          throw new Exception('ToID is not numeric');
      }
      $name2typeID = new msData();
      $name2typeID = $name2typeID->getTypeIDsFromName(['allergieCodeTheriaque', $parentTypeName]);
      if(!isset($name2typeID[$parentTypeName])) return false;
      $rd=[];
      $rd=msSQL::sql2tabSimple("select p.value
      from objets_data as p
      where p.toID='".$this->_toID."' and p.typeID='".$name2typeID['allergieCodeTheriaque']."' and p.deleted='' and p.outdated='' and p.instance='".$name2typeID[$parentTypeName]."' ");

      return $rd;
    }

/**
 * Historique complet des actes pour un individu
 * @return array Array multi avec année en clef de 1er niveau
 */
    public function getHistorique($objetID='')
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }

        $data = new msData();
        $porteursOrdoIds=array_column($data->getDataTypesFromCatName('porteursOrdo', ['id']), 'id');
        $porteursReglementIds=array_column($data->getDataTypesFromCatName('porteursReglement', ['id']), 'id');
        $name2typeID=$data->getTypeIDsFromName(['mailPorteur', 'docPorteur', 'docType', 'docOrigine', 'dicomStudyID', 'firstname', 'lastname', 'birthname','csAtcdStrucDeclaration','lapOrdonnance']);

        if ($data = msSQL::sql2tab("select p.id, p.fromID, p.toID, p.instance as parentID, p.important, p.titre, p.registerDate, DATE_FORMAT(p.creationDate,'%d/%m/%Y') as creationTime, DATE_FORMAT(p.creationDate,'%d/%m') as creationTimeShort, p.creationDate,  DATE_FORMAT(p.creationDate,'%Y') as creationYear,  p.updateDate, t.id as typeCS, t.name, t.module as module, t.groupe, t.label, t.formValues as formName, n1.value as prenom, f.printModel, mail.instance as sendMail, doc.value as fileext, doc2.value as docOrigine, img.value as dicomStudy,
        CASE WHEN DATE_ADD(p.creationDate, INTERVAL t.durationLife second) < NOW() THEN 'copy' ELSE 'update' END as iconeType, CASE WHEN n2.value != '' THEN n2.value  ELSE bn.value END as nom
        from objets_data as p
        left join data_types as t on p.typeID=t.id
        left join objets_data as n1 on n1.toID=p.fromID and n1.typeID='".$name2typeID['firstname']."' and n1.outdated='' and n1.deleted=''
        left join objets_data as n2 on n2.toID=p.fromID and n2.typeID='".$name2typeID['lastname']."' and n2.outdated='' and n2.deleted=''
        left join objets_data as bn on bn.toID=p.fromID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
        left join objets_data as mail on mail.instance=p.id and mail.typeID='".$name2typeID['mailPorteur']."'
        left join objets_data as doc on doc.instance=p.id and doc.typeID='".$name2typeID['docType']."'
        left join objets_data as doc2 on doc2.instance=p.id and doc2.typeID='".$name2typeID['docOrigine']."'
        left join objets_data as img on img.instance=p.id and img.typeID='".$name2typeID['dicomStudyID']."'
        left join forms as f on f.internalName=t.formValues
        where (t.groupe in ('typeCS', 'courrier')
              or (t.groupe = 'doc' and  t.id='".$name2typeID['docPorteur']."')
              or (t.groupe = 'ordo' and  t.id in ('".implode("','", $porteursOrdoIds)."'))
              or (t.groupe = 'ordo' and  t.id='".$name2typeID['lapOrdonnance']."')
              or (t.groupe = 'reglement' and  t.id in ('".implode("','", $porteursReglementIds)."'))
              or (t.groupe='mail' and t.id='".$name2typeID['mailPorteur']."' and p.instance='0')
              )
        and p.toID='".$this->_toID."' and p.outdated='' and p.deleted='' and t.id!='".$name2typeID['csAtcdStrucDeclaration']."'".
        ($objetID ? (" and p.id=".$objetID) : "").
        " group by p.id, bn.value, n1.value, n2.value, mail.instance, doc.value, doc2.value, img.value, f.id
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
    public function getToday($limit='')
    {
        if (!is_numeric($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }

        $data = new msData();
        $porteursOrdoIds=array_column($data->getDataTypesFromCatName('porteursOrdo', ['id']), 'id');
        $porteursReglementIds=array_column($data->getDataTypesFromCatName('porteursReglement', ['id']), 'id');
        $name2typeID=$data->getTypeIDsFromName(['mailPorteur', 'docPorteur', 'docType', 'docOrigine', 'dicomStudyID', 'firstname', 'lastname', 'birthname','csAtcdStrucDeclaration','lapOrdonnance']);

        return msSQL::sql2tab("select p.id, p.fromID, p.toID, p.instance as parentID, p.important, p.titre, p.registerDate, p.creationDate, DATE_FORMAT(p.creationDate,'%H:%i:%s') as creationTime, DATE_FORMAT(p.creationDate,'%H:%i') as creationTimeShort, p.updateDate, t.id as typeCS, t.name, t.groupe, t.label, t.module as module, t.formValues as formName, n1.value as prenom, f.printModel, mail.instance as sendMail, doc.value as fileext, doc2.value as docOrigine, img.value as dicomStudy,
        CASE WHEN DATE_ADD(p.creationDate, INTERVAL t.durationLife second) < NOW() THEN 'copy' ELSE 'update' END as iconeType, CASE WHEN n2.value != '' THEN n2.value  ELSE bn.value END as nom
        from objets_data as p
        left join data_types as t on p.typeID=t.id
        left join objets_data as n1 on n1.toID=p.fromID and n1.typeID='".$name2typeID['firstname']."' and n1.outdated='' and n1.deleted=''
        left join objets_data as n2 on n2.toID=p.fromID and n2.typeID='".$name2typeID['lastname']."' and n2.outdated='' and n2.deleted=''
        left join objets_data as bn on bn.toID=p.fromID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
        left join objets_data as mail on mail.instance=p.id and mail.typeID='".$name2typeID['mailPorteur']."'
        left join objets_data as doc on doc.instance=p.id and doc.typeID='".$name2typeID['docType']."'
        left join objets_data as doc2 on doc2.instance=p.id and doc2.typeID='".$name2typeID['docOrigine']."'
        left join objets_data as img on img.instance=p.id and img.typeID='".$name2typeID['dicomStudyID']."'
        left join forms as f on f.internalName=t.formValues
        where (t.groupe in ('typeCS', 'courrier')
          or (t.groupe = 'doc' and  t.id='".$name2typeID['docPorteur']."')
          or (t.groupe = 'ordo' and  t.id in ('".implode("','", $porteursOrdoIds)."'))
          or (t.groupe = 'ordo' and  t.id='".$name2typeID['lapOrdonnance']."')
          or (t.groupe = 'reglement' and  t.id in ('".implode("','", $porteursReglementIds)."'))
          or (t.groupe='mail' and t.id='".$name2typeID['mailPorteur']."' and p.instance='0'))
        and p.toID='".$this->_toID."' and p.outdated='' and p.deleted='' and DATE(p.creationDate) = CURDATE() and t.id!='".$name2typeID['csAtcdStrucDeclaration']."'
        group by p.id, bn.value, n1.value, n2.value, mail.instance, doc.value, doc2.value, img.value, f.id
        order by p.creationDate desc ".$limit);
    }

/**
 * Calcul de l'age du patient
 * @return string age à afficher
 */
    public function getAge()
    {
      if(isset($this->_ageFormats['ageDisplay'])) return $this->_ageFormats['ageDisplay'];
      else return $this->getAgeFormats()['ageDisplay'];
    }

/**
 * Calcul de l'age sous différents formats
 * @return array array de l'age au différents formats
 */
    public function getAgeFormats() {

      if (!is_numeric($this->_toID)) {
          throw new Exception('ToID is not numeric');
      }

      if(isset($this->_ageFormats)) {
        return $this->_ageFormats;
      }

      if(isset($this->_birthdate)) {
        $birthdate=$this->_birthdate;
      } else {
        $typeID=msData::getTypeIDFromName('birthdate');
        $birthdate=msSQL::sqlUniqueChamp("select value from objets_data where toID='".$this->_toID."' and typeID='".$typeID."' order by id desc limit 1");
      }

      if (isset($birthdate)) {

          // age à aficher
          $annees = DateTime::createFromFormat('d/m/Y', $birthdate)->diff(new DateTime('now'))->y;
          $mois = DateTime::createFromFormat('d/m/Y', $birthdate)->diff(new DateTime('now'))->m;
          $jours = DateTime::createFromFormat('d/m/Y', $birthdate)->diff(new DateTime('now'))->d;
          if ($annees>=3) {
            $ageDisplay = $annees.' ans';
          } elseif (($annees*12+$mois)>=3){
            $ageDisplay = ($annees*12+$mois).' mois';
          } elseif (((30*$mois+$jours)/7)>=2){
            $ageDisplay = round((30*$mois+$jours)/7).' semaines';
          } else {
            $ageDisplay = $jours.' jours';
          }

          // différences
          $dtNaissance = DateTime::createFromFormat('d/m/Y', $birthdate);
          $dtNow = new DateTime;
          $interval = $dtNaissance->diff($dtNow);

          return $this->_ageFormats = array(
            'birthdate'=>$birthdate,
            'ageDisplay'=>$ageDisplay,
            'ageTotalDays'=>$interval->format('%a'),
            'ageTotalYears'=>$interval->format('%y'),
            'ageTotalMonths'=>$interval->m + 12*$interval->y,
            'ageComposantes'=>array(
              'y'=>$interval->format('%y'),
              'm'=>$interval->format('%m'),
              'd'=>$interval->format('%d')
            )
          );

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

/**
 * Obtenir l'historique des valeurs pour une data patient
 * @param  string $name     name
 * @param  string $borneInf borne inférieure de date
 * @param  string $borneSup borne supérieure de date
 * @return array           array date=>datas
 */
    public function getDataHistoricalValues($name, $borneInf="1971-01-01 00:00:00", $borneSup="NOW()") {
      if (!is_numeric($this->_toID)) {
          throw new Exception('ToID is not numeric');
      }
      if (!isset($name)) {
          throw new Exception('Name is not defined');
      }

      $data = new msData();
      $name2typeID=$data->getTypeIDsFromName(['firstname', 'lastname', 'birthname', $name]);

      return msSQL::sql2tabKey("select v.registerDate, v.value, DATE_FORMAT(v.registerDate, '%Y-%m-%d') as dateonly, DATE_FORMAT(v.registerDate,'%H:%i:%s') as timeonly, CASE WHEN n2.value != '' THEN n2.value  ELSE bn.value END as nom, n1.value as prenom
      from objets_data as v
      left join objets_data as n1 on n1.toID=v.fromID and n1.typeID='".$name2typeID['firstname']."' and n1.outdated='' and n1.deleted=''
      left join objets_data as n2 on n2.toID=v.fromID and n2.typeID='".$name2typeID['lastname']."' and n2.outdated='' and n2.deleted=''
      left join objets_data as bn on bn.toID=v.fromID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      where v.toID='".$this->_toID."' and v.typeID='".$name2typeID[$name]."' and v.deleted='' and v.registerDate >= '".msSQL::cleanVar($borneInf)."' and v.registerDate <= '".msSQL::cleanVar($borneSup)."'
      group by v.id, n1.id, n2.id, bn.id
      order by v.registerDate desc", 'registerDate');

    }

/**
 * Obtenir les années distinctes pour lesquelles il existe des valeurs de la data
 * @param  string $name name
 * @return array       tableau des années
 */
    public function getDataHistoricalValuesDistinctYears($name) {
      if (!is_numeric($this->_toID)) {
          throw new Exception('ToID is not numeric');
      }

      $data = new msData();
      $name2typeID=$data->getTypeIDsFromName([$name]);
      return msSQL::sql2tabSimple("select YEAR(registerDate) as year from objets_data where toID='".$this->_toID."' and typeID='".$name2typeID[$name]."' and deleted='' ");
    }

}
