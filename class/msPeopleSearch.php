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
 * Reherche des individus ou des utilisateurs 
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msPeopleSearch
{
  private $_peopleType=['patient'];
  private $_criteresRecherche=[];
  private $_colonnesRetour=[];
  private $_nameSearchMode = 'BnOrLn';
  private $_whereClauses = [];
  private $_limitStart = 0;
  private $_limitNumber = 50;

/**
 * Définir le tableau de critères de recherche
 * @param array $criteresRecherche tableau typeName => valeur recherche
 */
  public function setCriteresRecherche($criteresRecherche) {
    if(!is_array($criteresRecherche)) throw new Exception('CriteresRecherche is not an array');
      $this->_criteresRecherche=$criteresRecherche;
  }

/**
 * Définir les colonnes à retourner
 * @param array $colonnesRetour tableau des typeName à retourner
 */
  public function setColonnesRetour($colonnesRetour) {
    if(!is_array($colonnesRetour)) throw new Exception('ColonnesRetour is not an array');
      $this->_colonnesRetour=$colonnesRetour;
  }

/**
 * Définir une clause supplémentaire de la condition where
 * @param string $whereClause clause exprimé en sql
 */
  public function setWhereClause($whereClause) {
    $this->_whereClauses[]=$whereClause;
  }

/**
 * Définir le type de people défini dans la recherche
 * @param array $peopleType array des types concernés
 */
  public function setPeopleType($peopleType) {
    $this->_peopleType=$peopleType;
  }

/**
 * Définir le mode de recherche goupeBnFnOrLnFn ou goupeBnLn
 * @param string $nameSearchMode mode de recherche
 */
  public function setNameSearchMode($nameSearchMode) {
    $this->_nameSearchMode=$nameSearchMode;
  }

/**
 * Définir le rang du résultat où commencer
 * @param int $limitStart rang du résultat
 */
  public function setLimitStart($limitStart) {
    if(!is_numeric($limitStart)) throw new Exception('LimitStart is not numeric');
    $this->_limitStart=$limitStart;
  }

/**
 * Nombre de résultats à retourner
 * @param int $limitNumber nombre de résultats
 */
  public function setLimitNumber($limitNumber) {
    if(!is_numeric($limitNumber)) throw new Exception('LimitNumber is not numeric');
    $this->_limitNumber=$limitNumber;
  }

/**
 * Obtenir la chaîne SQL de recherche
 * @return string requète sql
 */
  public function getSql() {

    return $sql='select p.type, p.id as peopleID, '.implode(', ', $this->_makeSqlSelect()).'
    from people as p
    '.implode(' ', $this->_makeSqlJoin()). '
    where p.type in ("'.implode('", "', $this->_peopleType).'") and '.implode( ' and ', $this->_makeSqlWhere()).' '.implode(' ', $this->_whereClauses).'
    order by trim(identite)
    limit '.$this->_limitStart.','.$this->_limitNumber;
  }

/**
 * Fabriquer les paramètres SQL select pour la requète finale
 * @return array
 */
  private function _makeSqlSelect () {

    if(!in_array('lastname', $this->_colonnesRetour)) $this->_colonnesRetour[]='lastname';
    if(!in_array('birthname', $this->_colonnesRetour)) $this->_colonnesRetour[]='birthname';
    if(!in_array('firstname', $this->_colonnesRetour)) $this->_colonnesRetour[]='firstname';

    $name2typeID = new msData();
    $name2typeID = $name2typeID->getTypeIDsFromName($this->_colonnesRetour);

    $sp[0]='';
    foreach($this->_colonnesRetour as $v) {
      if($v=='identite') {
        $sp[0]= 'CASE WHEN d'.$name2typeID['lastname'].'.value !="" and d'.$name2typeID['birthname'].'.value !="" THEN concat(COALESCE(d'.$name2typeID['lastname'].'.value,""), " ", COALESCE(d'.$name2typeID['firstname'].'.value,""), " (", COALESCE(d'.$name2typeID['birthname'].'.value,"") ,")")
        WHEN d'.$name2typeID['birthname'].'.value !="" THEN concat(COALESCE(d'.$name2typeID['birthname'].'.value,""), " ", COALESCE(d'.$name2typeID['firstname'].'.value,""))
        WHEN d'.$name2typeID['lastname'].'.value !="" THEN concat(COALESCE(d'.$name2typeID['lastname'].'.value,""), " ", COALESCE(d'.$name2typeID['firstname'].'.value,""))
        ELSE concat("(inconnu) ", COALESCE(d'.$name2typeID['firstname'].'.value,""))
        END as identite';
      } else {
        if(isset($name2typeID[$v])) $sp[]= 'd'.$name2typeID[$v].'.value as '.$v;
      }
    }
    return $sp;
  }

/**
 * Fabriquer les paramètres SQL leftjoin pour la requète finale
 * @return array
 */
  private function _makeSqlJoin () {

    $tab=array_unique(array_merge(array_keys($this->_criteresRecherche) , $this->_colonnesRetour));

    if(!in_array('lastname', $tab)) $tab[]='lastname';
    if(!in_array('birthname', $tab)) $tab[]='birthname';
    if(!in_array('firstname', $tab)) $tab[]='firstname';

    $name2typeID = new msData();
    $name2typeID = $name2typeID->getTypeIDsFromName($tab);

    foreach($tab as $v) {
        if(isset($name2typeID[$v])) $sp[$name2typeID[$v]]='left join objets_data as d'.$name2typeID[$v].' on d'.$name2typeID[$v].'.toID=p.id and d'.$name2typeID[$v].'.typeID='.$name2typeID[$v].' and d'.$name2typeID[$v].'.outdated=\'\' and d'.$name2typeID[$v].'.deleted=\'\'';
    }

    return $sp;
  }

/**
 * Fabriquer les paramètres SQL where pour la requète finale
 * @return array conditions where
 */
  private function _makeSqlWhere() {
    if(!array_key_exists('lastname', $this->_criteresRecherche)) $this->_criteresRecherche['lastname']='';
    if(!array_key_exists('birthname', $this->_criteresRecherche)) $this->_criteresRecherche['birthname']='';
    if(!array_key_exists('firstname', $this->_criteresRecherche)) $this->_criteresRecherche['firstname']='';

    $name2typeID = new msData();
    $name2typeID = $name2typeID->getTypeIDsFromName(array_keys($this->_criteresRecherche));

    foreach($this->_criteresRecherche as $k=>$v) {
        if(in_array($k, ['birthname', 'lastname']) and $this->_nameSearchMode == 'BnFnOrLnFn') {
          $sp['where'][0]="(concat(d".$name2typeID['birthname'].".value, ' ', d".$name2typeID['firstname'].".value) like '%".msSQL::cleanVar($this->_criteresRecherche['birthname'])."%' or concat(d".$name2typeID['lastname'].".value, ' ', d".$name2typeID['firstname'].".value) like '%".msSQL::cleanVar($this->_criteresRecherche['birthname'])."%') ";

        } elseif(in_array($k, ['birthname', 'lastname']) and $this->_nameSearchMode == 'BnOrLn') {
          $sp['where'][0]="((d".$name2typeID['lastname'].".value like '".msSQL::cleanVar($this->_criteresRecherche['lastname'])."%' and d".$name2typeID['lastname'].".outdated='') or (d".$name2typeID['birthname'].".value like '".msSQL::cleanVar($this->_criteresRecherche['birthname'])."%' and d".$name2typeID['birthname'].".outdated='') ) ";

        } else {
          if($v!=null and $k) $sp['where'][$name2typeID[$k]]="d".$name2typeID[$k].".value like '".msSQL::cleanVar($v)."%'";

        }
    }
    return $sp['where'];

  }

  /**
   * Obtenir la liste des utilisateurs
   * @param  string $orderBy paramètres order by
   * @return array
   */
    public static function getUsersList($orderBy='') {
      if(empty($orderBy)) {
        $orderBy='pp.id';
      } else {
        $orderBy=msSQL::cleanVar($orderBy);
      }
      $name2typeID = new msData();
      $name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'birthname']);

      return msSQL::sql2tab("select pp.id, pp.name, pp.rank, pp.module, p.value as prenom, CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom
       from people as pp
       left join objets_data as n on n.toID=pp.id and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
       left join objets_data as bn on bn.toID=pp.id and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
       left join objets_data as p on p.toID=pp.id and p.typeID='".$name2typeID['firstname']."' and p.outdated=''  and p.deleted=''
       where pp.name!='' and pp.pass!='' order by ".$orderBy);
    }


}
