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
        $group= $groupID ? 'groups/'.$groupID.'/' : '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseurl.$group.$req.$api_key.$params);
        if ($commande=='POST') {
//            curl_setopt($ch, CURLOPT_URL, $sb_baseurl.$group.$req.$sb_api_key.$params);
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_POST, true);
        } else if ($commande=='PUT') {
//            curl_setopt($ch, CURLOPT_URL, $sb_baseurl.$group.$req.$sb_api_key.$params);
            curl_setopt($ch, CURLOPT_PUT, true);
        } else if ($commande=='DELETE') {
//            curl_setopt($ch, CURLOPT_URL, $sb_baseurl.$group.$req.$sb_api_key.$params);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        curl_setopt($ch, CURLOPT_USERPWD, $this->_userpwd);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $res=curl_exec($ch);
        curl_close($ch);
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

    public function syncAppointments() {
        $params=$this->_getUserParams();
        if (!array_key_exists('clicRdvUserId', $params)) {
            return false;
        }
        $this->_groupID=explode(':', $params['clicRdvGroupId'])[0];
        $this->_calID=explode(':', $params['clicRdvCalId'])[0];
        $interventions=json_decode($params['clicRdvConsultId'], true);
        if (($res=$this->_sendCurl('GET', 'appointments.json', $this->_groupID, '&results=all')) === false) {
            return false;
        }
        $rdvClic=json_decode($res, true)['records'];
        $clicRDVservice=msSQL::sqlUniqueChamp("SELECT id FROM people WHERE name='clicRDV'");
        $agenda=new msAgenda();
        $agenda->setStartDate(date("Y-m-d H:i:s"));
        $agenda->setEndDate(date("Y-m-d H:i:s", strtotime("+2 year")));
        $agenda->set_userID($this->_userID);
        $agenda->set_fromID($clicRDVservice);
        $events=$agenda->getEvents(['actif','deleted']);
        $modified=$agenda->whoDidIt(['delete', 'move']);
        $rdvLocal=array();
        $rdvClicIdx=array();
        $patients=$this->_getLocalPatients();
        $related=$this->_getRelatedPatients();
        foreach ($events as $k=>$v) {
            //événement déjà sur clic
            if ($v['externid']) {
                $rdvLocal[$v['externid']]=$v;
                continue;
            }
            //on ignore les événements supprimés
            if ($v['statut']=='deleted') {
                continue;
            }
            //sinon, creation de l'événement
            $eventClic=array();
            $eventClic['appointment']=array(
                'date'=>$v['start'],
                'calendar_id'=>$this->_calID,
                'intervention_ids'=>[$interventions[0][$v['type']][0]],
                'comments'=>$v['motif'],
                'from_web'=>0,
                'fiche'=>array()
            );
            if (is_array($related) and array_key_exists($v['patientid'], $related)) {
                $eventClic['appointment']['fiche']['id']=$related[$v['patientid']];
            } else {
                //le patient n'a pas encore de fiche, creation de la fiche patient sur clic
                $patient=new msPeople();
                $patient->setToID($v['patientid']);
                $patientData=$patient->getSimpleAdminDatasByName();
                $eventClic['appointment']['fiche']['group_id']=$this->_groupID;
                $eventClic['appointment']['fiche']['externid']=$v['patientid'];
                $eventClic['appointment']['fiche']['rappel_email']=0;
                if (array_key_exists('firstname', $patientData))
                    $eventClic['appointment']['fiche']['firstname']=$patientData['firstname'];
                if (array_key_exists('birthname', $patientData))
                    $eventClic['appointment']['fiche']['lastname']=$patientData['birthname'];
                if (array_key_exists('lastname', $patientData))
                    $eventClic['appointment']['fiche']['lastname']=$patientData['lastname'];
                if (array_key_exists('mobilePhone', $patientData))
                    $eventClic['appointment']['fiche']['firstphone']=$patientData['mobilePhone'];
                if (array_key_exists('personalEmail', $patientData))
                    $eventClic['appointment']['fiche']['email']=$patientData['personalEmail'];
            }
            //envoi du RDV et récupération de la réponse
            if ($rdv=json_decode($this->_sendCurl('POST', 'appointments.json', $this->_groupID, '', json_encode($eventClic)), true) and array_key_exists('records', $rdv)) {
/*
{"records":[{"id":551780169,"key":"4qsCfB7CaYJ7BpI1kJLn","start":"2018-02-07 15:20:00","end":"2018-02-07 15:55:00","created_since":0,"state":0,"location":"22 rue de Chateaubriand 92290 CHATENAY MALABRY","group_id":123517,"group":{"id":123517,"name":"Steiner Lara","urlname":"steiner-lara"},"calendar_id":613513,"calendar":{"id":613513,"publicname":"Cabinet Châtenay-Malabry","epj":"55226065"},"fiche_id":154699705,"fiche":{"id":154699705,"birthdate":null,"lastname":"BACRI","firstname":"Henri","email":"","firstphone":"","secondphone":"","comments":""},"intervention_id":4761277,"intervention":{"id":4761277,"publicname":"Visite de suivi ","interventionset_id":430859,"length":35},"capacity":1,"resource_id":0,"websource":"","from_web":false,"intervention_name":"Visite de suivi ","intervention_length":35,"calendar_name":"Cabinet Châtenay-Malabry","group_name":"Steiner Lara","comments":"","smsreminder":true}]}                        
*/
                //enregistrement de l'ID externe du RDV 
                msSQL::sqlQuery("UPDATE agenda SET externid='".$rdv['records'][0]['id']."' WHERE id='".$v['id']."'");
                $rdvLocal[$rdv['records'][0]['id']]=$v;
                $rdvClic[]=$rdv['records'][0];
                //récupération de l'ID de fiche quand création 
                if (!is_array($related) or !array_key_exists($v['patientid'], $related)) {
                    $obj=new msObjet();
                    $obj->setToID($v['patientid']);
                    $obj->setFromID($clicRDVservice);
                    $obj->createNewObjetByTypeName('clicRdvPatientId', $rdv['records'][0]['fiche_id']);
                }
            }
        }
        foreach($rdvClic as $k=>$v) {
            $rdvClicIdx[$v['id']]=1;
            if ($v['calendar_id']!=$this->_calID) {
                continue;
            }
            // nouveau rdv sur clicRdv
            if (!array_key_exists($v['id'], $rdvLocal)) {
                //nouveau patient sur clicRdv
                if (!$patients[1] or !array_key_exists($v['fiche_id'], $patients[1])) {
                    $patient=new msPeople();
                    $patient->setFromID($clicRDVservice);
                    $patient->setType('externe');
                    $patientID=$patient->createNew();
                    $patients[1][$v['fiche_id']]=$patientID;
                    $data=new msObjet();
                    $data->setToID($patientID);
                    $data->setFromID($clicRDVservice);
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
                $agenda->set_externID($v['id']);
                $agenda->setStartDate($v['start']);
                $agenda->setEndDate($v['end']);
                $agenda->set_type($interventions[1][$v['intervention_id']][0]);
                $agenda->set_motif($v['comments']);
                $agenda->addOrUpdateRdv();
            } else {
                $rdv=$rdvLocal[$v['id']];
                //si le rdv a été supprimé en local
                if ($rdv['statut']=='deleted' and array_key_exists($rdv['id'], $modified) and 
                  $modified[$rdv['id']]['operation']=='delete' and $modified[$rdv['id']]['fromID']!=$clicRDVservice) {
                    $this->_sendCurl('DELETE', 'appointments/'.$v['id'], $this->_groupID);
                } else if ($v['start'] != $rdv['start']) {
                    //si le rdv a changé en local, on le met à jour sur clicRDV
                    if (array_key_exists($rdv['id'], $modified) and $modified[$rdv['id']]['operation']=='move' and
                      $modified[$rdv['id']]['fromID']!=$clicRDVservice) {
                        $rdvc=array(
                            'id'=>$v['id'],
                            'date'=>$rdv['start']
                        );
                        $this->_sendCurl('PUT', 'vevents', $this->_groupID, '', json_encode($rdvc));
                    //sinon, on met à jour l'événement local
                    } else {
                        $agenda->set_eventID($rdv['id']);
                        $agenda->setStartDate($v['start']);
                        $agenda->setEndDate($v['end']);
                        $agenda->moveEvent();
                    }
                }
            }
        }
        //suppression des rdv qui ont disparu de clicRDV
        foreach($rdvLocal as $k=>$v) {
            if (!array_key_exists($k, $rdvClicIdx) and $v['statut']=="actif") {
                $agenda->set_eventID($v['id']);
                $agenda->delEvent();
            }
        }
    }

}
