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
 * Gestion des transmissions entre utilisateurs
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 class msTransmissions
 {
   private $_userID;
   private $_id;
   private $_page=1;
   private $_fromID;
   private $_toID=[];
   private $_aboutID=NULL;
   private $_sujetID;
   private $_priorite = 0;
   private $_statut = 'open';
   private $_sujet;
   private $_texte;
   private $_modeInboxOutbox='inbox';
   private $_traite='nontraitees';
   private $_lecture='toutes';


/**
* Définir l'ID de l'utilisateur concerné
* @param int $id ID de l'utilisateur
*/
  public function setUserID($userID) {
    if (!msPeople::checkPeopleExist($userID)) {
      throw new Exception('UserID does not exist');
    }
    $this->_userID=$userID;
  }

/**
 * Définir l'ID de la transmission
 * @param int $id ID de la transmission
 */
   public function setId($id) {
     if (!is_numeric($id)) throw new Exception('Id is not numeric');
     $this->_id=$id;
   }

/**
 * Définir le type de vue pour la liste des transmissions
 * @param string $modeInboxOutbox inbox ou outbox
 */
   public function setModeInboxOutbox($modeInboxOutbox) {
     if (!in_array($modeInboxOutbox, ['inbox','outbox'])) throw new Exception('ModeInboxOutbox est incorrect');
     $this->_modeInboxOutbox=$modeInboxOutbox;
   }

/**
 * Définir le type de transmission à sélectionner : non traitées, traitées ou toutes
 * @param string $traite nontraitees ou toutes
 */
   public function setTraite($traite) {
     if (!in_array($traite, ['traitees','nontraitees','toutes'])) throw new Exception('Traite est incorrect');
     $this->_traite=$traite;
   }

/**
* Définir le type de transmission à sélectionner : non lues, lues ou toutes
* @param string $traite nontraitees ou toutes
*/
  public function setLecture($lecture) {
    if (!in_array($lecture, ['lues','nonlues','toutes'])) throw new Exception('Lecture est incorrect');
    $this->_lecture=$lecture;
  }

/**
 * Définir l'auteur de la transmission
 * @param int $fromID ID de l'auteur de la transmission
 */
   public function setFromID($fromID) {
     if (!msPeople::checkPeopleExist($fromID)) {
       throw new Exception('FromID does not exist');
     }
     $this->_fromID=$fromID;
   }

/**
 * Définir les destinataires de la transmission
 * @param array $toID array d'ID
 */
   public function setToID($toID) {
     if (!msPeople::checkPeopleExist($toID)) {
       throw new Exception('ToID does not exist');
     }
     $this->_toID[]=$toID;
   }

/**
 * Définir le sujetID de la transmission (ID du parent de la réponse)
 * @param string $sujetID sujetID de la transmission
 */
   public function setSujetID($sujetID) {
     if (!is_numeric($sujetID)) throw new Exception('SujetID is not numeric');
     $this->_sujetID=$sujetID;
   }

/**
 * Définir le patient concerné par la transmission
 * @param int $aboutID ID du patient
 */
   public function setAboutID($aboutID) {
     if (!msPeople::checkPeopleExist($aboutID)) {
       throw new Exception('AboutID does not exist');
     }
     $this->_aboutID=$aboutID;
   }

/**
 * Définir la priorité de la transmission
 * @param int $priorite 0 : normal, 5 : important, 10 : urgent
 */
   public function setPriorite($priorite) {
     if (!is_numeric($priorite)) throw new Exception('Priorite is not numeric');
     $this->_priorite=$priorite;
   }

/**
 * Définir la page de transmissions à afficher
 * @param int $page num de la page
 */
   public function setPage($page) {
     if (!is_numeric($page )) throw new Exception('Page is not numeric');
     $this->_page=$page;
   }

/**
 * Définir le texte de la transmission
 * @param string $texte texte de la transmission
 */
   public function setTexte($texte) {
     $this->_texte=$texte;
   }

/**
 * Définir le sujet de la transmission
 * @param string $sujet sujet de la transmission
 */
   public function setSujet($sujet) {
     $this->_sujet=$sujet;
   }

/**
 * Obtenir la liste des transmissions répondant aux critères de sélection
 * @return array array de transmissions
 */
   public function getTransmissionsListeSujets() {
     global $p;

     $ret=array(
       'data'=>'',
       'nbTotalTran'=>0,
       'nbTransRetour'=>0,
       'nbParPage'=>$p['config']['transmissionsNbParPage'],
       'page'=>$this->_page
     );

     $limitStart=$this->_page*$p['config']['transmissionsNbParPage']-$p['config']['transmissionsNbParPage'];
     $limitNumber=$p['config']['transmissionsNbParPage'];

     $name2typeID = new msData();
     $name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'birthname']);

     $groupby = $groupbycount = '';
     if($this->_modeInboxOutbox == 'outbox') {
       $lj="left join transmissions_to as aut on t.id = aut.sujetID and aut.toID = '".$this->_userID."' ";
       $where = "t.fromID='".$this->_userID."' and ";
       if($this->_traite != 'toutes') {
         $lj.="left join transmissions_to as trto on t.id = trto.sujetID and trto.destinataire = 'oui' ";
         $groupby = " group by t.id, trto.statut, ln.id, bn.id, fn.id, ln1.id, bn1.id, fn1.id ";
         $groupbycount = " group by t.id, trto.statut ";
         if($this->_traite == 'nontraitees') {
           $where .= "trto.statut='open' and ";
         } elseif ($this->_traite == 'traitees') {
           $where .= "trto.statut='checked' and ";
         }
       }

       if($this->_lecture == 'nonlues') {
         $where .= " (aut.dateLecture < t.updateDate or aut.dateLecture is null) and ";
       }  elseif($this->_lecture == 'lues') {
         $where .= " aut.dateLecture >= t.updateDate and ";
       }

     }
     elseif($this->_modeInboxOutbox == 'inbox') {
       $lj="left join transmissions_to as trto on t.id = trto.sujetID and trto.toID = '".$this->_userID."'";
       $where = "t.fromID!='".$this->_userID."' and trto.toID='".$this->_userID."' and ";
       if($this->_traite == 'nontraitees') {
         $where .= "trto.statut='open' and ";
       } elseif ($this->_traite == 'traitees') {
         $where .= "trto.statut='checked' and ";
       }

       if($this->_lecture == 'nonlues') {
         $where .= " (trto.dateLecture < t.updateDate or trto.dateLecture is null) and ";
       }  elseif($this->_lecture == 'lues') {
         $where .= " trto.dateLecture >= t.updateDate and ";
       }

     }

     $ret['nbTotalTran'] = msSQL::sqlUniqueChamp("select count(*) from(select t.id
      from transmissions as t
      ".$lj."
      where  ".$where." t.statut = 'open' and t.sujetID is NULL
      ".$groupbycount."
      ) as a ");

     if($listeSujets = msSQL::sql2tab("select t.id, t.sujet, t.aboutID, t.priorite, t.updateDate, t.registerDate,
      CASE
        WHEN ln.value != '' and bn.value != '' THEN concat(COALESCE(ln.value,'') , ' (' , COALESCE(bn.value,'') , ') ',COALESCE(fn.value,''))
        WHEN bn.value != '' THEN concat(COALESCE(bn.value,'') , ' ' ,COALESCE(fn.value,''))
        ELSE concat(COALESCE(ln.value,'') , ' ' , COALESCE(fn.value,''))
      END as identiteAbout,
      CASE
        WHEN ln1.value != '' THEN concat(COALESCE(ln1.value,'') , ' ' , COALESCE(fn1.value,''))
        ELSE concat(COALESCE(bn1.value,'') , ' ' , COALESCE(fn1.value,''))
      END as identiteAuteur
      from transmissions as t
      ".$lj."
      left join objets_data as ln on ln.toID=t.aboutID and ln.typeID='".$name2typeID['lastname']."' and ln.outdated='' and ln.deleted=''
      left join objets_data as bn on bn.toID=t.aboutID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      left join objets_data as fn on fn.toID=t.aboutID and fn.typeID='".$name2typeID['firstname']."' and fn.outdated='' and fn.deleted=''

      left join objets_data as ln1 on ln1.toID=t.fromID and ln1.typeID='".$name2typeID['lastname']."' and ln1.outdated='' and ln1.deleted=''
      left join objets_data as bn1 on bn1.toID=t.fromID and bn1.typeID='".$name2typeID['birthname']."' and bn1.outdated='' and bn1.deleted=''
      left join objets_data as fn1 on fn1.toID=t.fromID and fn1.typeID='".$name2typeID['firstname']."' and fn1.outdated='' and fn1.deleted=''

      where  ".$where." t.statut = 'open' and t.sujetID is NULL
      ".$groupby."
      order by t.updateDate desc
      limit $limitStart,$limitNumber")) {

        $ret['nbTransRetour']=count($listeSujets);

        foreach($listeSujets as $k=>$v) {
          $listeSujets[$k]['destinataires']=$this->_getTransmissionDestinataires($v['id']);
          $listeSujets[$k]['dateLectureUser']=$this->_getSujetDateLectureUser($v['id'], $this->_userID);
        }
        $ret['data']=$listeSujets;
      }
      return $ret;

   }

/**
 * Obtenir les réponses à une transmission
 * @return array array des réponses
 */
   public function getTransmissionReponses() {

     $name2typeID = new msData();
     $name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'birthname']);

     return $reponses = msSQL::sql2tab("select t.id, t.texte, t.registerDate, t.fromID,
      CASE
        WHEN ln1.value != '' THEN concat(COALESCE(ln1.value,'') , ' ' , COALESCE(fn1.value,''))
        ELSE concat(COALESCE(bn1.value,'') , ' ' , COALESCE(fn1.value,''))
      END as identiteAuteur
      from transmissions as t

      left join objets_data as ln1 on ln1.toID=t.fromID and ln1.typeID='".$name2typeID['lastname']."' and ln1.outdated='' and ln1.deleted=''
      left join objets_data as bn1 on bn1.toID=t.fromID and bn1.typeID='".$name2typeID['birthname']."' and bn1.outdated='' and bn1.deleted=''
      left join objets_data as fn1 on fn1.toID=t.fromID and fn1.typeID='".$name2typeID['firstname']."' and fn1.outdated='' and fn1.deleted=''

      where t.statut = 'open' and t.sujetID='".$this->_sujetID."' and t.id > '".$this->_sujetID."' order by id asc ");
   }

/**
 * Obtenir les data d'une transmission
 * @return array array des data
 */
   public function getTransmission() {

     $name2typeID = new msData();
     $name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'birthname']);

     if($trans = msSQL::sqlUnique("select t.id, t.fromID, t.sujet, t.aboutID, t.priorite, t.updateDate, t.registerDate, t.texte, t.statut,
      CASE
        WHEN ln.value != '' and bn.value != '' THEN concat(COALESCE(ln.value,'') , ' (' , COALESCE(bn.value,'') , ') ',COALESCE(fn.value,''))
        WHEN bn.value != '' THEN concat(COALESCE(bn.value,'') , ' ' ,COALESCE(fn.value,''))
        ELSE concat(COALESCE(ln.value,'') , ' ' , COALESCE(fn.value,''))
      END as identiteAbout,
      CASE
        WHEN ln1.value != '' THEN concat(COALESCE(ln1.value,'') , ' ' , COALESCE(fn1.value,''))
        ELSE concat(COALESCE(bn1.value,'') , ' ' , COALESCE(fn1.value,''))
      END as identiteAuteur
      from transmissions as t
      left join transmissions_to as trto on t.id = trto.sujetID and trto.toID = '".$this->_userID."'

      left join objets_data as ln on ln.toID=t.aboutID and ln.typeID='".$name2typeID['lastname']."' and ln.outdated='' and ln.deleted=''
      left join objets_data as bn on bn.toID=t.aboutID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      left join objets_data as fn on fn.toID=t.aboutID and fn.typeID='".$name2typeID['firstname']."' and fn.outdated='' and fn.deleted=''

      left join objets_data as ln1 on ln1.toID=t.fromID and ln1.typeID='".$name2typeID['lastname']."' and ln1.outdated='' and ln1.deleted=''
      left join objets_data as bn1 on bn1.toID=t.fromID and bn1.typeID='".$name2typeID['birthname']."' and bn1.outdated='' and bn1.deleted=''
      left join objets_data as fn1 on fn1.toID=t.fromID and fn1.typeID='".$name2typeID['firstname']."' and fn1.outdated='' and fn1.deleted=''

      where t.id='".$this->_id."' limit 1")) {


        $trans['destinataires']=$this->_getTransmissionDestinataires($trans['id']);
        $trans['dateLectureUser']=$this->_getSujetDateLectureUser($trans['id'], $this->_userID);

        return $trans;
      }
      return ;
   }

/**
* Obtenir les destinataires d'une transmission
* @param  int $sujetID ID de la transmission
* @return array          array des destinataires
*/
  private function _getTransmissionDestinataires($sujetID) {
    $name2typeID = new msData();
    $name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'birthname']);

    return msSQL::sql2tab("select trto.toID,trto.statut, trto.dateLecture, trto.destinataire,
         CASE
          WHEN ln.value != '' THEN concat(COALESCE(ln.value,'') , ' ' , COALESCE(fn.value,''))
          ELSE concat(COALESCE(bn.value,'') , ' ' , COALESCE(fn.value,''))
         END as identiteDestinataire
         from transmissions_to as trto
         left join objets_data as ln on ln.toID=trto.toID and ln.typeID='".$name2typeID['lastname']."' and ln.outdated='' and ln.deleted=''
         left join objets_data as bn on bn.toID=trto.toID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
         left join objets_data as fn on fn.toID=trto.toID and fn.typeID='".$name2typeID['firstname']."' and fn.outdated='' and fn.deleted=''
         where trto.sujetID = '".$sujetID."' and trto.statut != 'deleted' and destinataire='oui'
          ");
  }

/**
 * Obentenir les destinataires possibles d'une nouvelle transmission
 * @return array array des destinataires
 */
   public function getTransmissionDestinatairesPossibles() {
     $desti= new msPeople();
     return $desti->getUsersListForService('transmissionsPeutRecevoir');
   }

/**
 * Définir la date de lecture d'une transmission pour l'utilisateur courant
 */
   public function setTranmissionDateLecture() {
     $transto=array(
       'sujetID'=>$this->_sujetID,
       'toID'=>$this->_userID,
       'dateLecture'=>date('Y-m-d H:i:s')
     );
     return msSQL::sqlInsert('transmissions_to', $transto);
   }

/**
 * Definir une transmission comme traitée pour l'utilisateur courant
 */
   public function setTranmissionMarquer() {
     $statutActuel = msSQL::sqlUniqueChamp("select statut from transmissions_to where sujetID='".$this->_id."' and toID='".$this->_userID."' limit 1");
     if($statutActuel=='open') {
       $statutActuel='checked';
     } else {
       $statutActuel='open';
     }
     $transto=array(
       'sujetID'=>$this->_id,
       'toID'=>$this->_userID,
       'statut'=>$statutActuel
     );
     return msSQL::sqlInsert('transmissions_to', $transto);
   }

/**
 * Définir une transmission comme effacée
 */
   public function setTranmissionSupp() {
     $trans=array(
       'id'=>$this->_id,
       'statut'=>'deleted'
     );
     return msSQL::sqlInsert('transmissions', $trans);
   }

/**
 * Définir une nouvelle transmission ou l'éditer en base
 */
   public function setTranmissionPoster() {
     $trans=array(
       'fromID'=>$this->_userID,
       'sujet'=>$this->_sujet,
       'texte'=>$this->_texte,
       'priorite'=>$this->_priorite
     );
     if($this->_aboutID > 0) {
       $trans['aboutID']=$this->_aboutID;
     }
     //edition
     if($this->_id > 0) {
       $trans['id']=$this->_id;
       // effacer les destinataires retirés à l'édition
       msSQL::sqlQuery("update transmissions_to set statut='deleted' where sujetID='".$this->_id."' and toID not in ('".implode("','", $this->_toID)."') ");
       // repasser à open pour les destinataires conservés
       msSQL::sqlQuery("update transmissions_to set statut='open' where sujetID='".$this->_id."' and toID in ('".implode("','", $this->_toID)."') ");
     }

     if($sujetID = msSQL::sqlInsert('transmissions', $trans)) {
       // enregistrement des destinataires
       foreach($this->_toID as $toID) {
         $trans=array(
           'sujetID'=>$sujetID,
           'toID'=>$toID,
           'destinataire'=>'oui',
         );
         if($toID == $this->_userID) $trans['dateLecture']=date('Y-m-d H:i:s');
         msSQL::sqlInsert('transmissions_to', $trans);
       }
       // enregistrement de l'auteur comme non destinataire (controle de date de lecture)
       if(!in_array($this->_userID, $this->_toID)) {
         $trans=array(
           'sujetID'=>$sujetID,
           'toID'=>$this->_userID,
           'destinataire'=>'non',
           'dateLecture'=>date('Y-m-d H:i:s')
         );
         msSQL::sqlInsert('transmissions_to', $trans);
       }

       if($this->_id > 0) {
         return $this->_id;
       } else {
         return $sujetID;
       }
     }
   }

/**
 * Obtenir le nombre de transmissions reçues non lues
 * @return int nb de transmissions reçues non lues
 */
   public function getNbTransmissionsRecuesNonLues() {
     return msSQL::sqlUniqueChamp("select count(tt.sujetID) from transmissions_to as tt
     left join transmissions as t on tt.sujetID = t.id
     where tt.toID = '".$this->_userID."' and tt.destinataire='oui' and t.statut='open' and (tt.dateLecture < t.updateDate or tt.dateLecture is null)");
   }

/**
* Obtenir le nombre de transmissions envoyées non lues
* @return int nb de transmissions envoyées non lues
*/
   public function getNbTransmissionsEnvoyeesNonLues() {
     return msSQL::sqlUniqueChamp("select count(tt.sujetID) from transmissions_to as tt
     left join transmissions as t on tt.sujetID = t.id
     where tt.toID = '".$this->_userID."' and tt.destinataire='non' and t.statut='open' and (tt.dateLecture < t.updateDate or tt.dateLecture is null)");
   }

/**
 * Obtenir le nb de transmissions non lues par priorité
 * @return array toutes=>int, importantes=>int, urgentes=>int
 */
   public function getNbTransmissionsNonLuesParPrio() {
     $tab=array(
       'toutes'=>0,
       'importantes'=>0,
       'urgentes'=>0
     );
     if($transmissionsNbNonLues=msSQL::sql2tabKey("select count(tt.sujetID) as nb, priorite from transmissions_to as tt
     left join transmissions as t on tt.sujetID = t.id
     where tt.toID = '".$this->_userID."' and t.statut='open' and (tt.dateLecture < t.updateDate or tt.dateLecture is null) group by priorite", 'priorite')) {
       $tab['toutes'] = array_sum(array_column($transmissionsNbNonLues, 'nb'));
       if(isset($transmissionsNbNonLues[5]['nb'])) {
         $tab['importantes'] = $transmissionsNbNonLues[5]['nb'];
       }
       if(isset($transmissionsNbNonLues[10]['nb'])) {
         $tab['urgentes'] = $transmissionsNbNonLues[10]['nb'];
       }
     }
     return $tab;
   }

/**
 * Définir en base une nouvelle réponse à une transmission ou l'éditer
 */
   public function setTranmissionReponsePoster() {
     $trans=array(
       'sujetID'=>$this->_sujetID,
       'fromID'=>$this->_userID,
       'texte'=>$this->_texte
     );
     if(isset($this->_id)) {
       $trans['id']=$this->_id;
     }

     if(msSQL::sqlInsert('transmissions', $trans)) {
       msSQL::sqlQuery("update transmissions set updateDate=NOW() where id='".$this->_sujetID."' limit 1");
       msSQL::sqlQuery("update transmissions_to set statut='open' where sujetID='".$this->_sujetID."' and statut='checked' and toID != '".$this->_userID."'");
       $this->setTranmissionDateLecture();
     }
   }

/**
 * Obtenir la date de lecture d'une transmission pour un utilisateur
 * @param  int $sujetID ID de la transmission
 * @param  int $toID    ID de l'utilisateur
 * @return string          date de lecture
 */
   private function _getSujetDateLectureUser($sujetID, $toID) {
     return msSQL::sqlUniqueChamp("select dateLecture from transmissions_to where sujetID='".$sujetID."' and toID='".$toID."' limit 1");
   }

/**
 * Purger la base de transmissions des transmissions non updatées depuis transmissionsPurgerNbJours jours
 * @return void
 */
   public function purgerTransmissions() {
     global $p;
     if($p['config']['transmissionsPurgerNbJours'] > 0) {
       $ids = msSQL::sql2tabSimple("select id from transmissions where updateDate < DATE_SUB(NOW() , INTERVAL ".$p['config']['transmissionsPurgerNbJours']." DAY)");
       if(!empty($ids)) {
         msSQL::sqlQuery("delete from transmissions where id in ('".implode("', '", $ids)."')");
         msSQL::sqlQuery("delete from transmissions_to where sujetID in ('".implode("', '", $ids)."')");
       }
     }
   }

 }
