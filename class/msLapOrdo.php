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
 * LAP : gestion ordonnances, liste de traitements, historiques ...
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msLapOrdo extends msLap
{
    private $_ordonnanceID;
    private $_samsListInOrdo=[];

/**
 * Définir l'ordonnance concernée
 * @param int $v ID de l'ordonnance concernée
 * @return int toID
 */
    public function setOrdonnanceID($id)
    {
        if (is_numeric($id)) {
            return $this->_ordonnanceID = $id;
        } else {
            throw new Exception('OrdonnanceID is not numeric');
        }
    }

/**
 * Obtenir le tanleau des samID présent dans l'ordo.
 * @return array tableau des samID
 */
    public function getSamsListInOrdo() {
      return $this->_samsListInOrdo;
    }

/**
 * Obtenir les datas de l'ordonnance
 * @return array data ordonnance
 */
    public function getOrdonnance() {
      if(!isset($this->_ordonnanceID)) throw new Exception('OrdonnanceID is not defined');

      $data = new msData();
      $name2typeID=$data->getTypeIDsFromName(['lapLignePrescription','lapLigneMedicament','lastname','firstname','birthname']);

      $tab['ordoData']=msSQL::sqlUnique("select o.*, CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom, p.value as prenom
      from objets_data as o
      left join objets_data as n on n.toID=o.fromID and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
      left join objets_data as p on p.toID=o.fromID and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
      left join objets_data as bn on bn.toID=o.fromID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      where o.id='".$this->_ordonnanceID."'
      group by  o.id, n.id, p.id, bn.id order by o.id desc");
      $tab['ordoData']['value']=json_decode($tab['ordoData']['value'], TRUE);

      //extraction des lignes de prescrition
      if($lignes = msSQL::sql2tabKey("select * from objets_data where instance='".$this->_ordonnanceID."' and typeID='".$name2typeID['lapLignePrescription']."' order by id", 'id')) {
        foreach($lignes as $k=>$l) {
          $lignes[$k]['ligneData']=json_decode($l['value'], true);
          $lignes[$k]['ligneData']['objetID']=$k;
        }
        //extraction des medicaments
        if($medicaments = msSQL::sql2tab("select * from objets_data where instance in (".implode(',', array_column($lignes,'id')).") and typeID='".$name2typeID['lapLigneMedicament']."' order by id")) {
          foreach($medicaments as $k=>$m) {
            $medic=json_decode($m['value'],true);
            $medic['objetID']=$m['id'];
            $lignes[$m['instance']]['medics'][]=$medic;
          }
        }

        //préparation tableau final
        foreach($lignes as $ligne) {
          if($ligne['ligneData']['isALD']=='true') {
            $zone='ordoMedicsALD';
          } else {
            $zone='ordoMedicsG';
          }
          if(!empty($ligne['medics'])) {
            $tab[$zone][]=array(
              'ligneData'=>$ligne['ligneData'],
              'medics'=>$ligne['medics']
            );
          }
        }

        return $tab;

      }
    }

/**
 * Sauver une ligne de prescription
 * @param  array $ligne data de la ligne de prescription
 * @return [type]        [description]
 */
    public function saveLignePrescription($ligne)
    {
        if(!empty($ligne['medics'])) {
          global $p;
          $lap = new msObjet();
          $lap->setFromID($p['user']['id']);
          $lap->setToID($this->_toID);

          if(is_numeric($this->_ordonnanceID)) {
            $ligneID=$lap->createNewObjetByTypeName('lapLignePrescription', json_encode($ligne['ligneData']),$this->_ordonnanceID);
          } else {
            $ligneID=$lap->createNewObjetByTypeName('lapLignePrescription', json_encode($ligne['ligneData']));
          }

          if (is_numeric($ligneID)) {
              // infos sur la ligne
              $lap->createNewObjetByTypeName('lapLignePrescriptionDatePriseDebut', $ligne['ligneData']['dateDebutPrise'], $ligneID);
              $lap->createNewObjetByTypeName('lapLignePrescriptionDatePriseFin', $ligne['ligneData']['dateFinPrise'], $ligneID);
              $lap->createNewObjetByTypeName('lapLignePrescriptionDatePriseFinAvecRenouv', $ligne['ligneData']['dateFinPriseAvecRenouv'], $ligneID);
              $lap->createNewObjetByTypeName('lapLignePrescriptionDureeJours', $ligne['ligneData']['dureeTotaleMachineJours'], $ligneID);
              $lap->createNewObjetByTypeName('lapLignePrescriptionIsALD', $ligne['ligneData']['isALD'], $ligneID);
              $lap->createNewObjetByTypeName('lapLignePrescriptionIsChronique', $ligne['ligneData']['isChronique'], $ligneID);

              // on note la ligne qui a servi pour le renouv
              if(isset($ligne['ligneData']['objetID'])) {
                if($ligne['ligneData']['objetID']>0) {
                    $lap->createNewObjetByTypeName('lapLignePrescriptionRenouvelle', $ligne['ligneData']['objetID'], $ligneID);
                }
              }

        // Médicaments
        foreach ($ligne['medics'] as $k=>$m) {
            $medicamentID=$lap->createNewObjetByTypeName('lapLigneMedicament', json_encode($ligne['medics'][$k]), $ligneID);
            if (is_numeric($medicamentID)) {
                $lap->createNewObjetByTypeName('lapMedicamentSpecialiteCodeTheriaque', $m['speThe'], $medicamentID);
                $lap->createNewObjetByTypeName('lapMedicamentPresentationCodeTheriaque', $m['presThe'], $medicamentID);
                $lap->createNewObjetByTypeName('lapMedicamentSpecialiteNom', $m['nomSpe'], $medicamentID);
                $lap->createNewObjetByTypeName('lapMedicamentDC', $m['nomDC'], $medicamentID);
                $lap->createNewObjetByTypeName('lapMedicamentCodeATC', $m['codeATC'], $medicamentID);
                $lap->createNewObjetByTypeName('lapMedicamentEstPrescriptibleEnDC', $m['prescriptibleEnDC'], $medicamentID);
                if(isset($m['prescriptionMotif']) and !empty(trim($m['prescriptionMotif']))) $lap->createNewObjetByTypeName('lapMedicamentMotifPrescription', $m['prescriptionMotif'], $medicamentID);

                if(!empty($m['substancesActives'])) {
                  foreach($m['substancesActives'] as $k=>$v) {
                    $lap->createNewObjetByTypeName('lapMedicamentCodeSubstanceActive', $k, $medicamentID);
                  }
                }

                if(isset($m['sams']) and !empty($m['sams'])) {
                  foreach($m['sams'] as $k=>$v) {
                    if(!in_array($v, $this->_samsListInOrdo)) $this->_samsListInOrdo[]=$v;
                  }
                }
            }
          }
        }
      }
    }

/**
 * Obtenir l'historique des ordonnances
 * @return array tableau de l'historique
 */
    public function getHistoriqueOrdos($annee) {
      $data = new msData();
      $name2typeID=$data->getTypeIDsFromName(['lapOrdonnance','firstname','lastname','birthname']);

      return msSQL::sql2tabKey("select o.*, CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom, p.value as prenom, month(o.registerDate) as mois
      from objets_data as o
      left join objets_data as n on n.toID=o.fromID and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
      left join objets_data as p on p.toID=o.fromID and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
      left join objets_data as bn on bn.toID=o.fromID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      where o.typeID='".$name2typeID['lapOrdonnance']."' and o.toID='".$this->_toID."' and o.deleted='' and o.outdated='' and YEAR(o.registerDate) = '".msSQL::cleanVar($annee)."'
      group by  o.id, n.id, p.id, bn.id order by o.id desc", 'id');
    }

/**
 * Obtenir le traitement en cours
 * @return array tableau 2 entrées : TTChroniques, TTPonctuels
 */
    public function getTTenCours()
    {
        if (!isset($this->_toID)) {
            throw new Exception('ToID is not numeric');
        }
        $ligne=[];
        $data = new msData();
        $name2typeID=$data->getTypeIDsFromName(['lapLignePrescription','lapLigneMedicament','lapLignePrescriptionIsChronique','lapLignePrescriptionDatePriseDebut', 'lapLignePrescriptionDatePriseFinAvecRenouv', 'lapLignePrescriptionDatePriseFinEffective', 'lapLignePrescriptionRenouvelle']);


        if ($lignesPresTTchro=msSQL::sql2tab("select lp.id, lp.value
          from objets_data as lp
          left join objets_data as chro on chro.instance=lp.id and chro.typeID='".$name2typeID['lapLignePrescriptionIsChronique']."'
          left join objets_data as dfe on dfe.instance=lp.id and dfe.typeID='".$name2typeID['lapLignePrescriptionDatePriseFinEffective']."'

          left join objets_data as re on re.value=lp.id and re.typeID='".$name2typeID['lapLignePrescriptionRenouvelle']."'

          where lp.typeID='".$name2typeID['lapLignePrescription']."' and lp.toID='".$this->_toID."' and lp.outdated='' and lp.deleted='' and chro.value='true' and re.value is null
          and (STR_TO_DATE(dfe.value, '%d/%m/%Y') > CURDATE() or dfe.value is null)
          ")) {
            foreach ($lignesPresTTchro as $l) {
                $ligne['TTChroniques'][$l['id']]['ligneData']=json_decode($l['value'], true);
                $ligne['TTChroniques'][$l['id']]['ligneData']['objetID']=$l['id'];
            }

            if ($lignesMedicsTTchro=msSQL::sql2tab("select id, value, instance from objets_data where typeID='".$name2typeID['lapLigneMedicament']."' and instance in (".implode(',', array_column($lignesPresTTchro, 'id')).") and outdated='' and deleted='' ")) {
                foreach ($lignesMedicsTTchro as $m) {
                    $medic=json_decode($m['value'], true);
                    $medic['objetID']=$m['id'];
                    $ligne['TTChroniques'][$m['instance']]['medics'][]=$medic;
                }
            }
            $whereExclu="and lp.id not in (".implode(',', array_column($lignesPresTTchro, 'id')).")";
        } else {
          $whereExclu="";
        }

        if ($lignesPresTTponct=msSQL::sql2tab("select lp.id, lp.value
          from objets_data as lp
          left join objets_data as dd on dd.instance=lp.id and dd.typeID='".$name2typeID['lapLignePrescriptionDatePriseDebut']."'
          left join objets_data as df on df.instance=lp.id and df.typeID='".$name2typeID['lapLignePrescriptionDatePriseFinAvecRenouv']."'
          left join objets_data as dfe on dfe.instance=lp.id and dfe.typeID='".$name2typeID['lapLignePrescriptionDatePriseFinEffective']."'

          left join objets_data as re on re.value=lp.id and re.typeID='".$name2typeID['lapLignePrescriptionRenouvelle']."'

          where lp.typeID='".$name2typeID['lapLignePrescription']."' and lp.toID='".$this->_toID."' and lp.outdated='' and lp.deleted='' and re.value is null ".$whereExclu."
          and STR_TO_DATE(dd.value, '%d/%m/%Y') <= CURDATE()
          and STR_TO_DATE(df.value, '%d/%m/%Y') >= CURDATE()
          and (STR_TO_DATE(dfe.value, '%d/%m/%Y') > CURDATE() or dfe.value is null)
          ")) {
            foreach ($lignesPresTTponct as $l) {
                $ligne['TTPonctuels'][$l['id']]['ligneData']=json_decode($l['value'], true);
                $ligne['TTPonctuels'][$l['id']]['ligneData']['objetID']=$l['id'];
            }

            if ($lignesMedicsTTponct=msSQL::sql2tab("select id, value, instance from objets_data where typeID='".$name2typeID['lapLigneMedicament']."' and instance in (".implode(',', array_column($lignesPresTTponct, 'id')).") and outdated='' and deleted='' ")) {
                foreach ($lignesMedicsTTponct as $m) {
                    $medic=json_decode($m['value'], true);
                    $medic['objetID']=$m['id'];
                    $ligne['TTPonctuels'][$m['instance']]['medics'][]=$medic;
                }
            }
        }

        if(isset($ligne['TTPonctuels'])) $ligne['TTPonctuels']=array_values($ligne['TTPonctuels']);
        if(isset($ligne['TTChroniques'])) $ligne['TTChroniques']=array_values($ligne['TTChroniques']);

        return $ligne;
    }

/**
 * Obtenir les années distinctes pour lesquelles il existe des ordonnances pour le patient
 * @return array tableau des années (desc)
 */
    public function getHistoriqueAnneesDistinctesOrdos() {
      $data = new msData();
      $name2typeID=$data->getTypeIDsFromName(['lapOrdonnance']);
      return msSQL::sql2tabKey("select distinct(YEAR(registerDate)) as annee from objets_data where toID='".$this->_toID."' and typeID='".$name2typeID['lapOrdonnance']."' order by annee desc", 'annee', 'annee');
    }

/**
 * Obtenir les années distinctes pour lesquelles il y a eu presciption interne ou par tiers
 * @return array tableau des années
 */
    public function getHistoriqueAnneesDistinctesMedics() {
      $data = new msData();
      $name2typeID=$data->getTypeIDsFromName(['lapLignePrescription','lapLigneMedicament','lapLignePrescriptionIsChronique','lapLignePrescriptionDatePriseDebut', 'lapLignePrescriptionDatePriseFinAvecRenouv', 'lapLignePrescriptionDatePriseFinEffective']);

      $tabretour=[date('Y')];
      if ($lignesPres=msSQL::sql2tab("select YEAR(STR_TO_DATE(dd.value, '%d/%m/%Y')) as dd, YEAR(STR_TO_DATE(df.value, '%d/%m/%Y')) as df, YEAR(STR_TO_DATE(dfe.value, '%d/%m/%Y')) as dfe
        from objets_data as lp
        left join objets_data as dd on dd.instance=lp.id and dd.typeID='".$name2typeID['lapLignePrescriptionDatePriseDebut']."'
        left join objets_data as df on df.instance=lp.id and df.typeID='".$name2typeID['lapLignePrescriptionDatePriseFinAvecRenouv']."'
        left join objets_data as dfe on dfe.instance=lp.id and dfe.typeID='".$name2typeID['lapLignePrescriptionDatePriseFinEffective']."'
        where lp.typeID='".$name2typeID['lapLignePrescription']."' and lp.toID='".$this->_toID."' and lp.outdated='' and lp.deleted=''
        ")) {

          foreach($lignesPres as $v) {
            if(!in_array($v['dd'],$tabretour) and !empty($v['dd'])) $tabretour[]=$v['dd'];
            if(!in_array($v['df'],$tabretour) and !empty($v['df'])) $tabretour[]=$v['df'];
            if(!in_array($v['dfe'],$tabretour) and !empty($v['dfe'])) $tabretour[]=$v['dfe'];
          }

        }
        rsort($tabretour);
        return $tabretour;
    }

/**
 * Obtenir l'historique des traitements pour une année donnée
 * @param  int $year année
 * @return array       tableau d'historique
 */
    public function getHistoriqueTT($year) {
      $data = new msData();
      $name2typeID=$data->getTypeIDsFromName(['lapLignePrescription','lapLigneMedicament','lapLignePrescriptionIsChronique','lapLignePrescriptionDatePriseDebut', 'lapLignePrescriptionDatePriseFinAvecRenouv', 'lapLignePrescriptionDatePriseFinEffective']);

      $final=[];
      if ($lignesPres=msSQL::sql2tabKey("select lp.id, lp.value, dfe.value as dfe, lp.instance as ordonnanceID
        from objets_data as lp
        left join objets_data as dd on dd.instance=lp.id and dd.typeID='".$name2typeID['lapLignePrescriptionDatePriseDebut']."'
        left join objets_data as df on df.instance=lp.id and df.typeID='".$name2typeID['lapLignePrescriptionDatePriseFinAvecRenouv']."'
        left join objets_data as dfe on dfe.instance=lp.id and dfe.typeID='".$name2typeID['lapLignePrescriptionDatePriseFinEffective']."'
        where lp.typeID='".$name2typeID['lapLignePrescription']."' and lp.toID='".$this->_toID."' and lp.outdated='' and lp.deleted=''
        and (YEAR(STR_TO_DATE(dd.value, '%d/%m/%Y')) = '".msSQL::cleanVar($year)."'
        or YEAR(STR_TO_DATE(df.value, '%d/%m/%Y')) = '".msSQL::cleanVar($year)."'
        or YEAR(STR_TO_DATE(dfe.value, '%d/%m/%Y')) = '".msSQL::cleanVar($year)."')
        ", 'id')) {

          if ($lignesMedics=msSQL::sql2tab("select id, value, instance from objets_data where typeID='".$name2typeID['lapLigneMedicament']."' and instance in (".implode(',', array_column($lignesPres, 'id')).") and outdated='' and deleted='' ")) {
            foreach($lignesMedics as $medic) {
              $medics[$medic['instance']][$medic['id']]=$medic;
              $medics[$medic['instance']][$medic['id']]['value']=json_decode($medic['value'],true);
              $medics[$medic['instance']][$medic['id']]['ligneData']=json_decode($lignesPres[$medic['instance']]['value'],true);
              $medics[$medic['instance']][$medic['id']]['ordonnanceID']=$lignesPres[$medic['instance']]['ordonnanceID'];
            }
          }
          foreach($lignesPres as $lp) {
            $lp['value']=json_decode($lp['value'],true);

            //start
            $dd=explode('/',$lp['value']['dateDebutPrise']);
            if(isset($medics[$lp['id']])) {
              if(isset($final[$dd[1]][$dd[0]]['start'])) {
                $final[$dd[1]][$dd[0]]['start']=$final[$dd[1]][$dd[0]]['start']+$medics[$lp['id']];
              } else {
                $final[$dd[1]][$dd[0]]['start']=$medics[$lp['id']];
              }
            }

            //final
            if($lp['dfe'] != '' ) $df=explode('/',$lp['dfe']); else $df=explode('/',$lp['value']['dateFinPrise']);
            if(isset($medics[$lp['id']])) {
              if(isset($final[$df[1]][$df[0]]['stop'])) {
                $final[$df[1]][$df[0]]['stop']=$final[$df[1]][$df[0]]['stop']+$medics[$lp['id']];
              } else {
                $final[$df[1]][$df[0]]['stop']=$medics[$lp['id']];
              }
            }
          }
          krsort($final);
          foreach($final as $mois=>$data) {
            krsort($final[$mois]);
          }
          foreach($final as $mois=>$data) {
            $final[strftime('%B', mktime(0, 0, 0, $mois, 1, 2018))]=$data;
            unset($final[$mois]);
          }
        }
        return $final;
    }

/**
 * Obtenir les catégories de rangement des prescriptions préétablies
 * @return array tableau par catID
 */
    public function getCatPresPre() {
      return msSQL::sql2tab("select c.*, count(p.id) as enfants
 			from prescriptions_cat as c
 			left join prescriptions as p on c.id=p.cat
       where c.type='lap' and c.fromID = '".$this->_fromID."'
 			group by c.id
 			order by c.displayOrder asc, c.label asc");
    }

/**
 * Obtenir les prescriptions préétablies par cat
 * @return array tableau par ctaID
 */
    public function getPresPre() {
      $tab=[];
      if($data=msSQL::sql2tab("select p.*
        from prescriptions as p
        left join prescriptions_cat as c on c.id=p.cat
        where p.fromID = '".$this->_fromID."' and c.type='lap'
        group by p.id
        order by c.label asc, p.label asc")) {
        foreach ($data as $v) {
            $tab[$v['cat']][]=$v;
        }
      }
      return $tab;
    }

}
