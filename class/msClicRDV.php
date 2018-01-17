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
    private $_groupID;
    private $_calID;
    private $_userpwd;

    private function _sendCurl($commande, $url, $data='') {
        if (!isset($this->_userpwd) or empty($this->_userpwd)) {
            return false;
        }
        $baseurl='https://sandbox.clicrdv.com/api/v1/';
        $api_key='&api_key=ee0ab7224b97430fbd7dc5a55a7bac40';
        $ch = curl_init($baseurl.$url.$api_key);
        curl_setopt($ch, CURLOPT_USERPWD, $p['user']['clicRdvUserId'].":".$pwd);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($commande = 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        } else if ($commande = 'PUT') {
            curl_setopt($ch, CURLOPT_PUT, true);
        } else if ($commande = 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        if (curl_errno($ch)) {
            $res = "Error: ".curl_error($ch);
        } else {
            $res = curl_exec($ch);
        }
        return $res;
    }

    public function setUserPwd($user='', $pwd='') {
        if (empty($user)) {
            $user=$p['user']['clicRdvUserId'];
        }
        if (empty($pwd)) {
            $pwd=msSQL::sqlUniqueChamp("set @pass=(select `value` from objets_data
                WHERE toID='".$p['user']['id']."' AND typeID='".msData::getTypeIDFromName('clicRdvPassword')."');
                SELECT CONVERT(AES_DECRYPT(UNHEX(@pass),@password), CHAR)");
        }
        $this->_userpwd = $user.":".$pwd;

    }


    public function setGroupID($groupID) {
        $this->_groupID = $groupID;
    }

    public function getGroups() {
        return $this->_sendCurl("GET", "groups"); 
    }

    public function getCalendars() {
        $req=isset($this->_groupID)?"groups/".$this->_groupID."/":'';
        $req.="/calendars";
        return $this->_sendCurl("GET", $req);
    }

    public function getInterventions() {
        $req=isset($this->_groupID)?"groups/".$this->_groupID."/":'';
        $req.="interventions";
        return $this->_sendCurl("GET", $req);
    }

    public function getIntervention($id) {
        $req=isset($this->_groupID)?"groups/".$this->_groupID."/":'';
        $req.="interventions/".$id."/";
        return $this->_sendCurl("GET", $req);
    }

    public function getEvents() {
        $req=isset($this->_groupID)?"groups/".$this->_groupID."/":'';
        $req.=isset($this->_calID)?"calendar/".$this->_calID."/":'';
        $req.="vevents";
        return $this->_sendCurl("GET", $req);
    }

    public function getEvent($id) {
        $req=isset($this->_groupID)?"groups/".$this->_groupID."/":'';
        $req.=isset($this->_calID)?"calendar/".$this->_calID."/":'';
        $req.="vevents/".$id;
        return $this->_sendCurl("GET", $req);
    }

    public function setEvent($event, $start, $end, $text="", $comment="") {
        global $p;
        $req=isset($this->_groupID)?"groups/".$this->_groupID."/":'';
        $req.="vevents/";
        $data=array("calendar_id"=>$$this->_calID,
                    "start"=>$start,
                    "end"=>$end,
                    "taker"=>$p['user']['prenom']." ".$p['user']['prenom'],
                    "colorref"=>"#CCCCCC",
                    "text"=>$text,
                    "intervention_id"=>"0",
                    "fiche_id"=>"0",
                    "comments"=>$comment);
        return $this->_sendCurl("POST", $req, $data);
    }

    public function delEvent($id) {
        $req=isset($this->_groupID)?"groups/".$this->_groupID."/":'';
        $req.="vevents/".$id;
        return $this->_sendCurl("DELETE", $req);
    }

    public function modEvent($id, $start, $end, $text="", $comment="") {
        $req=isset($this->_groupID)?"groups/".$this->_groupID."/":'';
        $req.="vevents/".$id;
        $data=array("start"=>$start,
                    "end"=>$end,
                    "taker"=>$p['user']['prenom']." ".$p['user']['prenom'],
                    "text"=>$text,
                    "comments"=>$comment);
        return $this->_sendCurl("PUT", $req, $data);
    }


}
