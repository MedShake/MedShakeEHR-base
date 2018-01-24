<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://www.github.com/fr33z00>
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
 * Gestion de l'agenda et des rendez-vous clicRDV
 *
 * @author fr33z00 <https://www.github.com/fr33z00>
 */


/*
différents cas à gérer :
- rdv clicrdv fiche connue
    traduction du vevent
- rdv clicrdv fiche inconnue
- rdv medshake nouveau patient
- rdv medshake patient existant
*/

class msClicRDV
{
    private $_userID;
    private $_groupID;
    private $_calID;
    private $_userpwd;

    private function _sendCurl($commande, $req, $groupID='', $params='', $data='') {
        if (!isset($this->_userpwd)) {
            $this->setUserPwd();
        }
        $sb_baseurl='https://sandbox.clicrdv.com/api/v1/';
        $sb_api_key='?apikey=ee0ab7224b97430fbd7dc5a55a7bac40';
        $baseurl='https://www.clicrdv.com/api/v1/';
        $api_key='?apikey=2cb3ec1ad2744d8993529c1961d501ae';
        $group= $groupID ? '' : 'groups/'.$groupID.'/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sb_baseurl.$group.$req.$sb_api_key.$params);
        if ($commande=='POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        } else if ($commande=='PUT') {
            curl_setopt($ch, CURLOPT_URL, $sb_baseurl.$group.$req.$sb_api_key.$params);
            curl_setopt($ch, CURLOPT_PUT, true);
        } else if ($commande=='DELETE') {
            curl_setopt($ch, CURLOPT_URL, $sb_baseurl.$group.$req.$sb_api_key.$params);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } else {
            curl_setopt($ch, CURLOPT_URL, $baseurl.$group.$req.$api_key.$params);
        }
        curl_setopt($ch, CURLOPT_USERPWD, $this->_userpwd);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        if (curl_errno($ch)) {
            $res = 'Error: '.curl_error($ch);
        } else {
            $res = curl_exec($ch);
        }
        return $res;
    }

    public function setUserID($userID) {
        $this->_userID = $userID;
    }

    public function setUserPwd($user='', $pwd='') {
        if (!$user or !$pwd) {
            $user=msSQL::sqlUniqueChamp("SELECT od.value 
              FROM objets_data AS od left join data_types as dt
              ON od.typeID=dt.id AND od.toID='".$this->_userID."' AND od.outdated='' AND od.deleted=''
              WHERE dt.name='clicRdvUserId'");

            $pwd=msSQL::sqlUniqueChamp("SELECT CONVERT(AES_DECRYPT(UNHEX(od.value),@password), CHAR) 
              FROM objets_data AS od left join data_types as dt
              ON od.typeID=dt.id AND od.toID='".$this->_userID."' AND od.outdated='' AND od.deleted=''
              WHERE dt.name='clicRdvPassword'");
        }
        $this->_userpwd = $user.':'.$pwd;

    }


    public function getGroups() {
        return $this->_sendCurl('GET', 'groups'); 
    }

    public function getCalendars($group='') {
        return $this->_sendCurl('GET', 'calendars.json', $group);
    }

    public function getInterventions($group='',$cal='') {
        return $this->_sendCurl('GET', 'interventions.json',$group, '&calendar_ids[]='.$cal);
    }

    private function _getUserParams() {
        return msSQL::sql2tabKey("SELECT dt.name,od.value
        FROM data_types AS dt left join objets_data AS od
        ON od.typeID=dt.id AND od.toID='".$this->_userID."' AND od.outdated='' AND od.deleted=''
        WHERE dt.groupe='user'", 'name', 'value');
    }

    private function _getLocalPatients() {
        $ret=array();
        $ret[]=msSQL::sql2tabKey("SELECT od.toID, od.value 
              FROM objets_data AS od left join data_types AS dt
              ON od.typeID=dt.id AND od.outdated='' AND od.deleted=''
              WHERE dt.name='clicRdvPatientId'", 'toID', 'value');
        $ret[]=msSQL::sql2tabKey("SELECT od.toID, od.value 
              FROM objets_data AS od left join data_types AS dt
              ON od.typeID=dt.id AND od.outdated='' AND od.deleted=''
              WHERE dt.name='clicRdvPatientId'", 'value', 'toID');
        return $ret;
    }

    private function _getRelatedPatients() {
        return msSQL::sql2tabKey("SELECT od.toID, od.value 
              FROM objets_data AS od left join data_types AS dt
              ON od.typeID=dt.id AND od.outdated='' AND od.deleted=''
              WHERE dt.name='relationExternePatient'", 'toID', 'value');
    }

    private function _getLocalAppointments() {
        $params=_getUserParams($this->userID);
        return $this->_sendCurl('GET', 'fiches.json',$params['clicRdvGroupId'], '&calendar_ids=['.$params['clicRdvCalId'].']');
    }

    public function syncAppointments() {
        $params=$this->_getUserParams();
        if (!array_key_exists('clicRdvUserId', $params)) {
            return false;
        }
        $this->_groupID=explode(':', $params['clicRdvGroupId'])[0];
        $this->_calID=explode(':', $params['clicRdvCalId'])[0];
        $interventions=json_decode($params['clicRdvConsultId'], true);
        if (!$rdvClic=json_decode($this->_sendCurl('GET', 'appointments.json', $this->_groupID, '&results=all'), true)['records']) {
            return false;
        }
        $agenda=new msAgenda();
        $agenda->setStartDate(date("Y-m-d H:i:s"));
        $agenda->setEndDate(date("Y-m-d H:i:s", strtotime("+2 year")));
        $agenda->set_userID($this->_userID);
        $events=$agenda->getEvents();
        $rdvLocal=array();
        $patients=$this->_getLocalPatients();
        $related=$this->_getRelatedPatients();
        foreach ($events as $k=>$v) {
            if ($v['externid']) {
                $rdvLocal[$v['externid']]=$v;
            } else {
                //creation de l'événement
                $eventClic=array();
                $eventClic['appointment']=array(
                    'date'=> $v['start'],
                    'calendar_id'=> $this->_calID,
                    'intervention_ids'=>'['.$interventions[0][$v['type']][0].']',
                    'comments'=> $v['motif']
                );
                if (!array_key_exists($v['patientid'], $patients[0]) and !array_key_exists($v['patientid'], $related)) {
                    //le patient n'a pas encore de fiche, creation de la fiche patient sur clic
                    $patient=new msPeople();
                    $patient->setToID($v['patientid']);
                    $patientData=$patient->getSimpleAdminDatas();
                    $eventClic['appointment']['fiche']=array(
                        'group_id'=>$this->_groupID,
                        'firstname'=>$patientData['firstname'],
                        'lastname'=>$patientData['lastname']?:$patientData['birthname'],
                        'firstphone'=>$patientData['mobilePhone']?:'',
                        'externid'=>$v['patientid']
                    );
                    //envoi de l'événement
                    if ($this->_sendCurl('POST', 'appointments.json', $this->_groupID, '', $eventClic)) {
                        //récupération de la fiche
                        if ($fiche=$this->_sendCurl('GET', 'fiches.json',$this->_groupID,'&conditions[0][field]="externid"&conditions[0][op]=%3D&conditions[0][value]='.$v['patientid'])) {
                            //enregistrement de l'id dans la fiche patient
                            $obj=new msObjet();
                            $obj->setToID($v['patientid']);
                            $obj->setFromID($this->_userID);
                            $obj->createNewObjetByTypeName('clicRdvPatientId', $fiche['records'][0]['id']);
                            //recupération du rdv
                            $rdv=$this->_sendCurl('GET', 'appointments.json',$this->_groupID,'&conditions[0][field]="fiche_id"&conditions[0][op]=%3D&conditions[0][value]='.$fiche['records'][0]['id']);
                        }
                    }
                } else {
                    if (array_key_exists($v['patientid'], $related)) {
                        $idpatient=$related[$v['patientid']];
                    } else {
                        $idpatient=$patients[0][$v['patientid']];
                    }
                    if ($this->_sendCurl('POST', 'appointments.json', $this->_groupID, '', $eventClic)) {
                            $rdv=$this->_sendCurl('GET', 'appointments.json',$this->_groupID,'&conditions[0][field]="fiche_id"&conditions[0][op]=%3D&conditions[0][value]='.$idpatient.'&conditions[1][field]="start"&conditions[1][op]=%3D&conditions[1][value]='.$v['start']);
                    }                    
                }
                if($rdv) {
                    //fixe le externid de l'événement
                    msSQL::sqlQuery("UPDATE agenda SET externid='".$rdv['records'][0]['id']."' WHERE id='".$v['id']."'");
                    $rdvLocal[$rdv['records'][0]['id']]=$v;
                }                
            }
        }
        foreach($rdvClic as $k=>$v) {
          if ($v['calendar_id']!=$this->_calID) {
              continue;
          }
          // nouveau rdv sur clicRdv
          if (!array_key_exists($v['id'], $rdvLocal)) {
              //nouveau patient sur clicRdv
              if (!$patients[1] or !array_key_exists($v['fiche_id'], $patients[1])) {
                  $patient=new msPeople();
                  $patient->setFromID($this->_userID);
                  $patient->setType('externe');
                  $patientID=$patient->createNew();
                  $patients[1][$v['fiche_id']]=$patientID;
                  $data=new msObjet();
                  $data->setToID($patientID);
                  $data->setFromID($this->_userID);
                  $data->createNewObjetByTypeName('clicRdvPatientId', $v['fiche_id']);
                  $data->createNewObjetByTypeName('firstname', $v['fiche']['firstname']);
                  $data->createNewObjetByTypeName('lastname', $v['fiche']['lastname']);
                  if ($v['fiche']['birthdate'])
                  $data->createNewObjetByTypeName('birthdate', $v['fiche']['birthdate']);
                  $data->createNewObjetByTypeName('personalEmail', $v['fiche']['email']);
                  if ($v['fiche']['firstphone'] and (!strpos('06', $v['fiche']['firstphone']) or !strpos('07', $v['fiche']['firstphone']))) {
                      $data->createNewObjetByTypeName('mobilePhone', $v['fiche']['firstphone']);
                  } elseif ($v['fiche']['secondphone'] and (!strpos('06', $v['fiche']['secondphone']) or !strpos('07', $v['fiche']['secondphone']))) {
                      $data->createNewObjetByTypeName('mobilePhone', $v['fiche']['secondphone']);
                  } elseif ($v['fiche']['firstphone']) {
                      $data->createNewObjetByTypeName('homePhone', $v['fiche']['firstphone']);
                  }
              } else {
                  $patientID=$patients[1][$v['fiche_id']];
              }
              // on crée le rdv
              $agenda->set_eventID(null);
              $agenda->set_patientID($patientID);
              $agenda->set_userID($this->_userID);
              $agenda->set_fromID(0);
              $agenda->set_externID($v['id']);
              $agenda->setStartDate($v['start']);
              $agenda->setEndDate($v['end']);
              $agenda->set_type($interventions[1][$v['intervention_id']][0]);
              $agenda->set_motif($v['comments']);
              $agenda->addOrUpdateRdv();
          } else {
              //vérifier si le rdv a été modifié
          }
        }
    }

}
