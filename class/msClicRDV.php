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

class msClicRDV
{
    // group ID
    private $_userpwd;

    private function _sendCurl($commande, $req, $groupID='', $params='', $data='') {
        if (!isset($this->_userpwd) or empty($this->_userpwd)) {
            $this->setUserPwd();
        }
        $sb_baseurl='https://sandbox.clicrdv.com/api/v1/';
        $sb_api_key='?apikey=ee0ab7224b97430fbd7dc5a55a7bac40';
        $baseurl='https://www.clicrdv.com/api/v1/';
        $api_key='?apikey=2cb3ec1ad2744d8993529c1961d501ae';
        $group= $groupID=='' ? '' : 'groups/'.$groupID.'/';
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
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        if (curl_errno($ch)) {
            $res = 'Error: '.curl_error($ch);
        } else {
            $res = curl_exec($ch);
        }
        return $res;
    }

    public function setUserPwd($user='', $pwd='') {
        global $p;
        if (empty($user)) {
            $user=$p['config']['clicRdvUserId'];
        }
        if (empty($pwd)) {
            $pwd=msSQL::sqlUniqueChamp("SELECT CONVERT(AES_DECRYPT(UNHEX(p.value),@password), CHAR) from objets_data AS p
                WHERE p.toID='".$p['user']['id']."' AND p.typeID='".msData::getTypeIDFromName('clicRdvPassword')."' AND p.outdated='' AND p.deleted=''");
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

    public function getFiche($id, $group='') {
        return $this->_sendCurl('GET', 'fiches',$group, '&fiche_id='.$id);
    }

    private function VeventToEvent($vevent, $patientid) {
        global $p;
        if (!isset($p['config']['clicRdvConsultId'])) {
            return false;
        }
        $types=decode_json($p['config']['clicRdvConsultId'])[1];
        $class=$vevent['state']=='2'?array('hasmenu','eventAbsent'):array('hasmenu');
        return array(
              'id'=>$vevent['id'],
              'title'=> $vevent['text'],
              'allDay'=>false,
              'start'=>$vevent['start'],
              'end'=>$vevent['end'],
              'editable'=>true,
              'backgroundColor'=> $vevent['colorref'],
              'borderColor' => '#ffffff',
              'textColor'=>'#000000',
              'className'=>$class,
              'motif'=>'',
              'type'=>$types[$vevent['intervention_id']][0],
              'patientid'=>$patientid,
              'absent'=>$vevent['state']=='2'
        );
    }

    private function EventToVevent($eventID) {
        global $p;
        $event=getEventByID($eventID);
        $user=new msPeople();
        $user->setToID($event['fromID']);
        $userData=$user->getSimpleAdminDatasByName();
        $taker=(empty($userData['lastname'])?$userData['birthname']:$userData['lastname']).' '.$userData['firstname'];
        $fiche_id=$user->getPeopleDataFromDataTypeGroupe('user', ['dt.name', 'od.value'])['clicRdvPatientId'];
        if ($fiche_id===null) {
            return array(
                  'id'=>$event['id'],
                  'text'=> $event['title'],
                  'start'=>$event['start'],
                  'end'=>$event['end'],
                  'colorref'=> $event['backgroundColor'],
                  'taker'=>$taker
            );
        } else {
            return array(
                  'id'=>$event['id'],
                  'text'=> $event['title'],
                  'start'=>$event['start'],
                  'end'=>$event['end'],
                  'colorref'=> $event['backgroundColor'],
                  'taker'=>$taker
            );
        }
    }

    public function getEvents($group='', $cal='') {
        $vevents=$this->_sendCurl('GET', 'vevents.json', $group, '&calendar_id='.$cal);
        $events=array();
        foreach($events as $k=>$v) {
            $events[$k]=VeventToEvent($v);
        }
        return $events;
    }

    public function setEvent($eventID, $group='', $cal='') {
        global $p;
        $user=new msPeople();
        $user->setToID($fromID);
        $userData=$user->getSimpleAdminDatasByName();
        $taker=(empty($userData['lastname'])?$userData['birthname']:$userData['lastname']).' '.$userData['firstname'];
        $event=getEventByID($eventID);
        $cal=!empty($cal)?:(empty($p['config']['clicRdvCalId'])?0:$p['config']['clicRdvCalId']);
        $data=array('calendar_id'=> $cal,
                    'start'=>$event['start'],
                    'end'=>$event['end'],
                    'taker'=>$taker,
                    'colorref'=>$event['backgroundColor'],
                    'text'=>$event['title'],
                    'intervention_id'=>'0',
                    'fiche_id'=>'0',
                    'comments'=>'motif:'.$event['motif'].',msid:'.$event['id']);
        return $this->_sendCurl('POST', 'vevents/', $group, '', $data);
    }

    public function delEvent($id, $group='') {
        return $this->_sendCurl('DELETE', 'vevents/'.$id, $group);
    }

    public function modEvent($id, $start, $end, $absent, $fromID, $group='', $cal='', $text='', $comment='') {
        $user=new msPeople();
        $user->setToID($fromID);
        $userData=$user->getSimpleAdminDatasByName();
        $taker=(empty($userData['lastname'])?$userData['birthname']:$userData['lastname']).' '.$userData['firstname'];
        $cal=$cal?:(empty($p['config']['clicRdvCalId'])?0:$p['config']['clicRdvCalId']);
        $data=array('calendar_id'=> $cal,
                    'start'=>$start,
                    'end'=>$end,
                    'state'=>($absent=='oui'?2:0),
                    'taker'=>$taker,
                    'text'=>$text,
                    'comments'=>$comment);
        return $this->_sendCurl('PUT', 'vevents/'.$id, $group, '', $data);
    }


}
