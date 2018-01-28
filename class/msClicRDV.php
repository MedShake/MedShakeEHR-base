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
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_POST, true);
        } else if ($commande=='PUT') {
//            curl_setopt($ch, CURLOPT_URL, $sb_baseurl.$group.$req.$sb_api_key.$params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        } else if ($commande=='DELETE') {
//            curl_setopt($ch, CURLOPT_URL, $sb_baseurl.$group.$req.$sb_api_key.$params);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        curl_setopt($ch, CURLOPT_USERPWD, $this->_userpwd);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            echo 'curl error on: '.$baseurl.$group.$req.$api_key.$params.'\n';
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

    //liste des patients qui ont un id clic connu
    private function _getLocalPatients() {
        $ret=array();
        //ret[0]=array(idMedShake=>idClicRDV)
        $ret[0]=msSQL::sql2tabKey("SELECT od.toID, od.value 
              FROM objets_data AS od left join data_types AS dt
              ON od.typeID=dt.id AND od.outdated='' AND od.deleted=''
              WHERE dt.name='clicRdvPatientId'", 'toID', 'value');
        //ret[1]=array(idClicRDV=>idMedShake)
        if (!is_array($ret[0])) {
            return(array(array(), array()));
        }
        $ret[1]=array_flip($ret[0]);
        return $ret;
    }

    // liste des patients qui sont sous un id externe et un interne
    // survient quand créés dans MedShake puis ont pris rdv sur clic 
    private function _getRelatedPatients() {
        $ret=array();
        // array(idPatientExterne=>idPatientInterne)
        $ret[0]=msSQL::sql2tabKey("SELECT od.toID, od.value 
              FROM objets_data AS od left join data_types AS dt
              ON od.typeID=dt.id AND od.outdated='' AND od.deleted=''
              WHERE dt.name='relationExternePatient'", 'toID', 'value');
        if (!is_array($ret[0])) {
            return(array(array(), array()));
        }
        $ret[1]=array_flip($ret[0]);
    }

    public function syncEvents() {
        $params=$this->_getUserParams();
        if (!array_key_exists('clicRdvUserId', $params)) {
            return false;
        }
        $this->_groupID=explode(':', $params['clicRdvGroupId'])[0];
        $this->_calID=explode(':', $params['clicRdvCalId'])[0];
        $interventions=json_decode($params['clicRdvConsultId'], true);
        $clicRDVservice=msSQL::sqlUniqueChamp("SELECT id FROM people WHERE name='clicRDV'");
        $startdate=date("Y-m-d H:i:s");
        $enddate=(date("Y-m-d H:i:s", strtotime("+2 year")));
        if (($res=$this->_sendCurl('GET', 'vevents.json', $this->_groupID, '&results=all&calendar_id='.$this->_calID.
          '&conditions[0][field]=type&conditions[0][op]=%21%3D&conditions[0][value]=VfreebusyEvent'.
          '&conditions[1][field]=taker&conditions[1][op]=%21%3D&conditions[1][value]=clicRDV'.
          '&conditions[2][field]=start&conditions[2][op]=%3E%3D&conditions[2][value]='.str_replace(' ', '%20', $startdate).
          '&conditions[3][field]=end&conditions[3][op]=%3C%3D&conditions[3][value]='.str_replace(' ', '%20', $enddate))) === false) {
            return false;
        }
        $res=json_decode($res, true);
        $rdvClic=array();
        if (array_key_exists('records', $res)) {
            $rdvClic=$res['records'];
        }
        $patient=new msPeople();
        $agenda=new msAgenda();
        $agenda->setStartDate($startdate);
        $agenda->setEndDate($enddate);
        $agenda->set_userID($this->_userID);
        $agenda->set_fromID($clicRDVservice);
        $events=$agenda->getEvents(['actif','deleted']);
        $knownEvents=array();
        $patients=$this->_getLocalPatients();
        if (!is_array($patients[1])) {
            $patients[1]=array();
        }
        $relatedPatients=$this->_getRelatedPatients();
        if (!is_array($relatedPatients[0])) {
            $relatedPatients[0]=array();
            $relatedPatients[1]=array();
        }
        //sens local => clicRDV
        foreach ($events as $k=>$vlocal) {
            //événement déjà sur clic
            if ($vlocal['externid']) {
                $knownEvents[$vlocal['externid']]=$k;
                continue;
            }
            //on ignore les événements supprimés (pour l'instant)
            if ($vlocal['statut']=='deleted') {
                continue;
            }
            $text='fermé';
            if ($vlocal['type']!='[off]' and $vlocal['patientid']) {
                $patient->setToID($vlocal['patientid']);
                $patientData=$patient->getSimpleAdminDatasByName();
                if (array_key_exists('birthname', $patientData))
                    $text=$patientData['birthname'];
                if (array_key_exists('lastname', $patientData))
                    $text=$patientData['lastname'];
                if (array_key_exists('firstname', $patientData))
                    $text.=' '.$patientData['firstname'];
            }
            $eventClic=array();
            $eventClic['vevent']=array(
                'start'=>$vlocal['start'],
                'end'=>$vlocal['end'],
                'calendar_id'=>$this->_calID,
                'text'=>$text,
                'intervention_id'=>$vlocal['type']=='[off]'?0:$interventions[0][$vlocal['type']][0],
                'taker'=>'MedShakeEHR',
                'comments'=>$vlocal['motif'],
                'from_web'=>0
            );
            if ($vlocal['type']=='[off]' or !$vlocal['patientid']) {
                $eventClic['vevent']['fiche_id']=0;
                $eventClic['vevent']['colorref']='#CCCCCC';
            //le patient interne a une fiche sur clic
            } elseif (array_key_exists($vlocal['patientid'], $patients[0])) {
                $eventClic['vevent']['fiche_id']=$patients[0][$vlocal['patientid']];
            // le patient interne est lié à un externe qui a une fiche sur clic
            } elseif (array_key_exists($patients[0][$vlocal['patientid']], $relatedPatients[0])) {
                $eventClic['vevent']['fiche_id']=$relatedPatients[0][$patients[0][$vlocal['patientid']]];
            } else {
                //le patient n'a pas encore de fiche sur clic, donc on la crée
                $ficheClic=array();
                $ficheClic['fiche']=array();
                $ficheClic['fiche']['group_id']=$this->_groupID;
                $ficheClic['fiche']['externid']=$vlocal['patientid'];
                $ficheClic['fiche']['rappel_email']=0;
                if (array_key_exists('firstname', $patientData))
                    $ficheClic['fiche']['firstname']=$patientData['firstname'];
                if (array_key_exists('birthname', $patientData))
                    $ficheClic['fiche']['lastname']=$patientData['birthname'];
                if (array_key_exists('lastname', $patientData))
                    $ficheClic['fiche']['lastname']=$patientData['lastname'];
                if (array_key_exists('mobilePhone', $patientData))
                    $ficheClic['fiche']['firstphone']=$patientData['mobilePhone'];
                if (array_key_exists('personalEmail', $patientData))
                    $ficheClic['fiche']['email']=$patientData['personalEmail'];
                if ($res=json_decode($this->_sendCurl('POST', 'fiches.json', $this->_groupID, '', json_encode($ficheClic)), true) and array_key_exists('records', $res)) {
                    $obj=new msObjet();
                    $obj->setToID($vlocal['patientid']);
                    $obj->setFromID($clicRDVservice);
                    $obj->createNewObjetByTypeName('clicRdvPatientId', $res['records'][0]['id']);
                    $patients[0][$vlocal['patientid']]=$res['records'][0]['id'];
                }
            }
            //envoi de l'événement et récupération de la réponse
            if ($evtc=json_decode($this->_sendCurl('POST', 'vevents.json', $this->_groupID, '', json_encode($eventClic)), true)) {
                //enregistrement de son ID externe dans la base et dans les événements
                msSQL::sqlQuery("UPDATE agenda SET externid='".$evtc['id']."' WHERE id='".$vlocal['id']."'");
                $events[$k]['externid']=$evtc['id'];
                $knownEvents[$evtc['id']]=$k;
            }
        }
        //sens clicRDV => local
        foreach($rdvClic as $vclic) {
            //événement inconnu en local, et non supprimé sur clic 
            if (!$vclic['deleted'] and !array_key_exists($vclic['id'], $knownEvents)) {
                //patient 0 (fermetures)
                if (!$vclic['fiche_id']) {
                    $patientID=0;
                //patient connu
                } elseif (array_key_exists($vclic['fiche_id'], $patients[1])) {
                    $patientID=$patients[1][$vclic['fiche_id']];
                    //si le patient est de type externe et lié à un patient interne, l'événement est assigné à l'interne
                    if (array_key_exists($patientID, $relatedPatients[1])) {
                        $patientID=$relatedPatients[1][$patientID];
                    }
                //sinon on le crée
                } elseif ($fiche=json_decode($this->_sendCurl('GET', 'fiches/'.$vclic['fiche_id'].'.json', $this->_groupID), true) and array_key_exists('id', $fiche)){
                    $patient->setFromID($clicRDVservice);
                    $patient->setType('externe');
                    $patientID=$patient->createNew();
                    $patients[1][$fiche['id']]=$patientID;
                    $data=new msObjet();
                    $data->setToID($patientID);
                    $data->setFromID($clicRDVservice);
                    $data->createNewObjetByTypeName('clicRdvPatientId', $fiche['id']);
                    $data->createNewObjetByTypeName('firstname', $fiche['firstname']);
                    $data->createNewObjetByTypeName('lastname', $fiche['lastname']);
                    if ($fiche['birthdate'])
                    $data->createNewObjetByTypeName('birthdate', $fiche['birthdate']);
                    $data->createNewObjetByTypeName('personalEmail', $fiche['email']);
                    if ($fiche['firstphone'] and (!strpos('06', $fiche['firstphone']) or !strpos('07', $fiche['firstphone']))) {
                        $data->createNewObjetByTypeName('mobilePhone', $fiche['firstphone']);
                    } elseif ($fiche['secondphone'] and (!strpos('06', $fiche['secondphone']) or !strpos('07', $fiche['secondphone']))) {
                        $data->createNewObjetByTypeName('mobilePhone', $fiche['secondphone']);
                    } elseif ($fiche['firstphone']) {
                        $data->createNewObjetByTypeName('homePhone', $fiche['firstphone']);
                    }
                } else {
                    die("Erreur lors de la récupération d'une fiche\n".json_encode($fiche).'\n');
                }
                //on crée l'événement
                $agenda->set_eventID(null);
                $agenda->set_patientID($patientID);
                $agenda->set_externID($vclic['id']);
                $agenda->setStartDate($vclic['start']);
                $agenda->setEndDate($vclic['end']);
                $agenda->set_type($vclic['intervention_id']?$interventions[1][$vclic['intervention_id']][0]:'[off]');
                $agenda->set_motif($vclic['comments']);
                $agenda->addOrUpdateRdv();
            // si l'événement est connu, on traîte les éventuelles changements intervenus d'un côté ou de l'autre
            } elseif (array_key_exists($vclic['id'], $knownEvents)) {
                $evt=$events[$knownEvents[$vclic['id']]];
                //cas où l'événement local a été modifié en dernier
                if ($evt['lastModified']>$vclic['updated_at']) {
                    //cas de la suppression
                    if ($evt['statut']=='deleted' and !$vclic['deleted']) {
                        $this->_sendCurl('DELETE', 'vevents/'.$vclic['id'], $this->_groupID);
                    } elseif ($vclic['start'] != $evt['start'] or $vclic['end'] != $evt['end']) {
                        $evtc=array();
                        $evtc['vevent']=array(
                            'calendar_id'=>$this->_calID,
                            'start'=>$evt['start'],
                            'end'=>$evt['end']
                        );
                        $this->_sendCurl('PUT', 'vevents/'.$vclic['id'].'.json', $this->_groupID, '', json_encode($evtc));
                    }
                //cas où l'événement clic a été modifié en dernier
                } else {
                    $agenda->set_eventID($evt['id']);
                    //cas où l'evénement a été remis depuis clic
                    if ($evt['statut']=='deleted' and !$vclic['deleted']) {
                        $agenda->undelEvent();
                    } 
                    //cas où l'evénement a été modifié sur clic
                    if ($vclic['start'] != $evt['start'] or $vclic['end'] != $evt['end']) {
                        $agenda->setStartDate($vclic['start']);
                        $agenda->setEndDate($vclic['end']);
                        $agenda->moveEvent();
                    }
                }
            }
        }
    }

}
