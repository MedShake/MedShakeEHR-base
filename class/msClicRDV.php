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
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msClicRDV
{
    private $_userID;
    private $_groupID;
    private $_calID;
    private $_userpwd;

    private function _sendCurl($commande, $req, $groupID='', $params='', $data='') {
        if (!isset($this->_userpwd) or !$this->_userpwd) {
            $this->setUserPwd();
        }
        $baseurl='https://www.clicrdv.com/api/v1/';
        $api_key='?apikey='.msConfiguration::getParameterValue('clicRdvApiKey').'&format=json';
        $group= $groupID ? 'groups/'.$groupID.'/' : '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseurl.$group.$req.$api_key.$params);
        if ($commande=='POST') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_POST, true);
        } else if ($commande=='PUT') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        } else if ($commande=='DELETE') {
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
        if (!is_numeric($userID)) throw new Exception('UserID is not numeric');
        $this->_userID = $userID;
    }

    public function setUserPwd($user='', $pwd='') {
        if (!$user or !$pwd) {
            $user=msConfiguration::getParameterValue('clicRdvUserId', array('id'=>$this->_userID, 'module'=>''));
            $pwd=msConfiguration::getParameterValue('clicRdvPassword', array('id'=>$this->_userID, 'module'=>''));
        }
        $this->_userpwd = $user.':'.$pwd;

    }


    public function getGroups() {
        return $this->_sendCurl('GET', 'groups');
    }

    public function getCalendars($group='') {
        return $this->_sendCurl('GET', 'calendars', $group);
    }

    public function getInterventions($group='',$cal='') {
        return $this->_sendCurl('GET', 'interventions',$group, '&calendar_ids[]='.$cal);
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
        return $ret;
    }

    /*  envoyer un événement vers clicRDV
        $event: événement au format interne à envoyer
    */
    public function sendEvent($event) {
        try {
            $params=$this->_getUserParams();
            if (!array_key_exists('clicRdvUserId', $params)) {
                return false;
            }
            //si one n'arrive pas à acquérir le lock, c'est que la synchro est en cours.
            // tant pis... le rdv sera donc envoyé à la prochaine synchro
            if (msSQL::sqlUniqueChamp("SELECT value FROM `system` WHERE groupe='lock' and name='clicRDV'")=='true') {
                return false;
            }
            msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'true'));
            $patient=new msPeople();
            $this->_groupID=explode(':', $params['clicRdvGroupId'])[0];
            $this->_calID=explode(':', $params['clicRdvCalId'])[0];
            $interventions=json_decode($params['clicRdvConsultId'], true)[0];
            $patients=$this->_getLocalPatients()[0];
            $relatedPatients=$this->_getRelatedPatients()[0];
            $clicRDVservice=msSQL::sqlUniqueChamp("SELECT id FROM people WHERE name='clicRDV'");
            $text='fermé';
            if ($event['type']!='[off]' and $event['patientid']) {
                $patient->setToID($event['patientid']);
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
                'start'=>$event['start'],
                'end'=>$event['end'],
                'calendar_id'=>$this->_calID,
                'text'=>$text,
                'intervention_id'=>$event['type']=='[off]'?0:$interventions[$event['type']][0],
                'taker'=>'MedShakeEHR',
                'comments'=>$event['motif'],
                'from_web'=>0
            );
            if ($event['type']=='[off]' or !$event['patientid']) {
                $eventClic['vevent']['fiche_id']=0;
                $eventClic['vevent']['colorref']='#CCCCCC';
            //le patient interne a une fiche sur clic
            } elseif (array_key_exists($event['patientid'], $patients)) {
                $eventClic['vevent']['fiche_id']=$patients[$event['patientid']];
            // le patient interne est lié à un externe qui a une fiche sur clic
            } elseif (array_key_exists($patients[$event['patientid']], $relatedPatients)) {
                $eventClic['vevent']['fiche_id']=$relatedPatients[$patients[$event['patientid']]];
            } else {
                //le patient n'a pas encore de fiche sur clic, donc on la crée
                $ficheClic=array();
                $ficheClic['fiche']=array();
                $ficheClic['fiche']['group_id']=$this->_groupID;
                $ficheClic['fiche']['externid']=$event['patientid'];
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
                if ($res=json_decode($this->_sendCurl('POST', 'fiches', $this->_groupID, '', json_encode($ficheClic)), true) and array_key_exists('id', $res)) {
                    $obj=new msObjet();
                    $obj->setToID($event['patientid']);
                    $obj->setFromID($clicRDVservice);
                    $obj->createNewObjetByTypeName('clicRdvPatientId', $res['id']);
                } else {
                    msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
                    return "Erreur lors de la création d'une fiche";
                }
            }
            //envoi de l'événement et récupération de la réponse
            if ($evtc=json_decode($this->_sendCurl('POST', 'vevents', $this->_groupID, '', json_encode($eventClic)), true)) {
                //enregistrement de son ID externe dans la base et dans les événements
                msSQL::sqlQuery("UPDATE agenda SET externid='".msSQL::cleanVar($evtc['id'])."' WHERE id='".msSQL::cleanVar($event['id'])."'");
            } else {
                msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
                return "Erreur lors de la création d'un événement";
            }
            msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
            return true;
        } catch (Exception $e) {
            msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
        }
    }

    /*  modifier un événement sur clicRDV
        $event: événement au format interne à modifier
    */
    public function modEvent($event) {
        try {
            $params=$this->_getUserParams();
            if (!array_key_exists('clicRdvUserId', $params)) {
                return false;
            }
            //si one n'arrive pas à acquérir le lock, c'est que la synchro est en cours.
            // tant pis... le rdv sera donc envoyé à la prochaine synchro
            if (msSQL::sqlUniqueChamp("SELECT value FROM `system` WHERE groupe='lock' and name='clicRDV'")=='true') {
                return false;
            }
            //si l'événement n'a pas été synchronisé, on ne peut rien faire
            if (!$event['externid']) {
                return false;
            }
            msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'true'));
            $patient=new msPeople();
            $this->_groupID=explode(':', $params['clicRdvGroupId'])[0];
            $this->_calID=explode(':', $params['clicRdvCalId'])[0];
            $patients=$this->_getLocalPatients()[0];
            $relatedPatients=$this->_getRelatedPatients()[0];
            $clicRDVservice=msSQL::sqlUniqueChamp("SELECT id FROM people WHERE name='clicRDV'");
            $eventClic=array();
            $eventClic['vevent']=array(
                'calendar_id'=>$this->_calID,
                'start'=>$event['start'],
                'end'=>$event['end']
            );
            $this->_sendCurl('PUT', 'vevents/'.$event['externid'], $this->_groupID, '', json_encode($eventClic));
            msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
            return true;
        } catch (Exception $e) {
            msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
        }
    }

    /*  supprimer un événement sur clicRDV
        $event: événement au format interne à supprimer
    */
    public function delEvent($event) {
        try {
            $params=$this->_getUserParams();
            if (!array_key_exists('clicRdvUserId', $params)) {
                return false;
            }
            //si l'événement n'a pas été synchronisé, il n'y a rien à faire
            if (!$event['externid']) {
                return false;
            }
            //si one n'arrive pas à acquérir le lock, c'est que la synchro est en cours.
            // tant pis... le rdv sera donc envoyé à la prochaine synchro
            if (msSQL::sqlUniqueChamp("SELECT value FROM `system` WHERE groupe='lock' and name='clicRDV'")=='true') {
                return false;
            }
            msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'true'));
            $patient=new msPeople();
            $this->_groupID=explode(':', $params['clicRdvGroupId'])[0];
            $this->_calID=explode(':', $params['clicRdvCalId'])[0];
            $clicRDVservice=msSQL::sqlUniqueChamp("SELECT id FROM people WHERE name='clicRDV'");
            $eventClic=array();
            $eventClic['vevent']=array(
                'calendar_id'=>$this->_calID,
                'deleted'=>'1'
            );
            $this->_sendCurl('PUT', 'vevents/'.$event['externid'], $this->_groupID, '', json_encode($eventClic));
            msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
            return true;
        } catch (Exception $e) {
            msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
        }
    }

    public function syncEvents() {
        try {
            $params=$this->_getUserParams();
            if (!array_key_exists('clicRdvUserId', $params) or !$params['clicRdvUserId']) {
                return false;
            }
            if (msSQL::sqlUniqueChamp("SELECT value FROM `system` WHERE groupe='lock' and name='clicRDV'")=='true') {
                return false;
            }
            //acquisition du lock
            msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'true'));

            $this->_groupID=explode(':', $params['clicRdvGroupId'])[0];
            $this->_calID=explode(':', $params['clicRdvCalId'])[0];
            $interventions=json_decode($params['clicRdvConsultId'], true);
            $clicRDVservice=msSQL::sqlUniqueChamp("SELECT id FROM people WHERE name='clicRDV'");
            $lastupdate=msSQL::sqlUniqueChamp("SELECT value FROM `system` WHERE groupe='cron' and name='clicRDV'");
            $startdate=date("Y-m-d H:i:s");
            $enddate=(date("Y-m-d H:i:s", strtotime("+2 year")));
            $searchString='&results=all&calendar_id='.$this->_calID.
              '&conditions[0][field]=type&conditions[0][op]=%21%3D&conditions[0][value]=VfreebusyEvent'.
              '&conditions[1][field]=taker&conditions[1][op]=%21%3D&conditions[1][value]=clicRDV'.
              '&conditions[2][field]=start&conditions[2][op]=%3E%3D&conditions[2][value]='.str_replace(' ', '%20', $startdate).
              '&conditions[3][field]=end&conditions[3][op]=%3C%3D&conditions[3][value]='.str_replace(' ', '%20', $enddate);
            if ($lastupdate) {
                $searchString.='&conditions[4][field]=updated_at&conditions[4][op]=%3E%3D&conditions[4][value]='.str_replace(' ', '%20', $lastupdate);
            }
            if (($res=$this->_sendCurl('GET', 'vevents', $this->_groupID, $searchString)) === false) {
                msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
                return "Erreur de réception des données depuis clicRDV";
            }
            $res=json_decode($res, true);
            $rdvClic=array();
            if (is_array($res) and array_key_exists('records', $res)) {
                $rdvClic=$res['records'];
            }
            $obj=new msObjet();
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
                    if ($res=json_decode($this->_sendCurl('POST', 'fiches', $this->_groupID, '', json_encode($ficheClic)), true) and array_key_exists('id', $res)) {
                        $obj->setToID($vlocal['patientid']);
                        $obj->setFromID($clicRDVservice);
                        $obj->createNewObjetByTypeName('clicRdvPatientId', $res['id']);
                        $patients[0][$vlocal['patientid']]=$res['id'];
                    } else {
                        msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
                        return "Erreur lors de la création d'une fiche";
                    }
                }
                //envoi de l'événement et récupération de la réponse
                if ($evtc=json_decode($this->_sendCurl('POST', 'vevents', $this->_groupID, '', json_encode($eventClic)), true)) {
                    //enregistrement de son ID externe dans la base et dans les événements
                    msSQL::sqlQuery("UPDATE agenda SET externid='".msSQL::cleanVar($evtc['id'])."' WHERE id='".msSQL::cleanVar($vlocal['id'])."'");
                    $events[$k]['externid']=$evtc['id'];
                    $knownEvents[$evtc['id']]=$k;
                } else {
                    msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
                    return "Erreur lors de la création d'un événement";
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
                    } elseif ($fiche=json_decode($this->_sendCurl('GET', 'fiches/'.$vclic['fiche_id'], $this->_groupID), true) and array_key_exists('id', $fiche)){
                        $patient=new msPeople(); // pour supprimer le toID dans la class
                        $patient->setFromID($clicRDVservice);
                        $patient->setType('externe');
                        $patientID=$patient->createNew();
                        $patients[1][$fiche['id']]=$patientID;
                        $obj->setToID($patientID);
                        $obj->setFromID($clicRDVservice);
                        $obj->createNewObjetByTypeName('clicRdvPatientId', $fiche['id']);
                        $obj->createNewObjetByTypeName('firstname', $fiche['firstname']);
                        $obj->createNewObjetByTypeName('birthname', $fiche['lastname']);
                        $obj->createNewObjetByTypeName('personalEmail', $fiche['email']);
                        if ($fiche['birthdate']) {
                            $obj->createNewObjetByTypeName('birthdate', $fiche['birthdate']);
                        }
                        if ($fiche['firstphone'] and (!strpos('06', $fiche['firstphone']) or !strpos('07', $fiche['firstphone']))) {
                            $obj->createNewObjetByTypeName('mobilePhone', $fiche['firstphone']);
                        } elseif ($fiche['secondphone'] and (!strpos('06', $fiche['secondphone']) or !strpos('07', $fiche['secondphone']))) {
                            $obj->createNewObjetByTypeName('mobilePhone', $fiche['secondphone']);
                        } elseif ($fiche['firstphone']) {
                            $obj->createNewObjetByTypeName('homePhone', $fiche['firstphone']);
                        }
                    } else {
                        msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
                        return "Erreur lors de la récupération d'une fiche";
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
                            $this->_sendCurl('PUT', 'vevents/'.$vclic['id'], $this->_groupID, '', json_encode($evtc));
                        }
                    //cas où l'événement clic a été modifié en dernier
                    } else {
                        $agenda->set_eventID($evt['id']);
                        //cas où l'evénement a été enlevé ou remis depuis clic
                        if ($evt['statut']=='deleted' and !$vclic['deleted']) {
                            $agenda->undelEvent();
                        } elseif ($evt['statut']!='deleted' and $vclic['deleted']) {
                            $agenda->delEvent();
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
            msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
            return true;
        } catch (Exception $e) {
            msSQL::sqlInsert('system', array('name'=>'clicRDV', 'groupe'=>'lock', 'value'=>'false'));
        }
    }
}
