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
 * Tracker les mails envoyés via Mailjet <https://www.mailjet.com/>
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msMailTrackingMailjet
{
    /**
   * ContactData
   * @var array
   */
    private $_contactData;

  /**
   * Contact Email
   * @var string
   */
    private $_contactEmail;

  /**
   * ContactID
   * @var array
   */
    private $_contactID;

  /**
   * Contact Messages List
   * @var array
   */
    private $_contactMessagesList;

  /**
   * Campaigns Data
   * @var array
   */
    private $_campaignsData;

  /**
   * @param string $_contactEmail
   *
   * @return static
   */
    public function set_contactEmail($_contactEmail)
    {
        $this->_contactEmail = $_contactEmail;
        $this->_getContactDataWithEmail();
        return $this;
    }

  /**
   * @return array
   */
    public function get_contactData()
    {
        return $this->_contactData;
    }

  /**
   * @return array
   */
    public function get_contactMessagesList()
    {
        return $this->_contactMessagesList;
    }

  /**
   * Obtenir les informations du contact via son email
   * @return array array des informations contact
   */

    private function _getContactDataWithEmail()
    {
        global $p;

        if (!isset($this->_contactEmail)) {
            throw new Exception("L'adresse email n'est pas spécifiée");
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3/REST/contactdata/".$this->_contactEmail);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($ch, CURLOPT_USERPWD, $p['config']['smtpUsername'] . ":" . $p['config']['smtpPassword']);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Curl error:' . curl_error($ch));
        } else {
            curl_close($ch);
            $this->_contactData=json_decode($result, true);
            $this->_contactID=$this->_contactData['Data'][0]['ID'];
        }
    }

  /**
   * Obtenir la liste des messages envoyés à ce contact
   * @return [type] [description]
   */
    public function getListMessagesSendedToContact()
    {
        global $p;

        if (!isset($this->_contactID)) {
            throw new Exception("Le contactID n'est pas spécifié");
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3/REST/message?Contact=".$this->_contactID."&Limit=500");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($ch, CURLOPT_USERPWD, $p['config']['smtpUsername'] . ":" . $p['config']['smtpPassword']);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Curl error:' . curl_error($ch));
        } else {
            curl_close($ch);
            $result=json_decode($result, true);
            foreach($result['Data'] as $v) {
              $tabsort[$v['ArrivedAt']]=$v;
            }
            krsort($tabsort);
            $result['Data']=$tabsort;
            return $this->_contactMessagesList=$result;
        }
    }

/**
 * Ajouter les data de la campagne à chaque message
 */
    public function addCampaignDataToMessagesList()
    {
        if(!isset($this->_contactMessagesList['Data'])) {
          throw new Exception("La liste des messages n'existe pas");
        }

        foreach ($this->_contactMessagesList['Data'] as $k=>$v) {
            $this->getCampaignData($v['CampaignID']);
            $this->_contactMessagesList['Data'][$k]['CampaignData'] = $this->_campaignsData[$v['CampaignID']]['Data'][0];
        }
    }

/**
 * Obtenir les data d'une campagne
 * @param  int $id ID de la campagne
 * @return array     array des data
 */
    public function getCampaignData($id)
    {
        if (isset($this->_campaignsData[$id])) {
            return $this->_campaignsData[$id];
        }

        global $p;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3/REST/campaign/".$id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

        curl_setopt($ch, CURLOPT_USERPWD, $p['config']['smtpUsername'] . ":" . $p['config']['smtpPassword']);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            curl_close($ch);
            $result=json_decode($result, true);
            return $this->_campaignsData[$id]=$result;
        }
    }

/**
 * Obtenir les infos sur un mail particulier
 * @param  int $id ID du mail
 * @return array     array des infos
 */
    public static function getMessageTrackingData($id) {

      global $p;

      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3/REST/messagehistory/".$id);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

      curl_setopt($ch, CURLOPT_USERPWD, $p['config']['smtpUsername'] . ":" . $p['config']['smtpPassword']);

      $result = curl_exec($ch);
      if (curl_errno($ch)) {
          echo 'Error:' . curl_error($ch);
      } else {
          curl_close($ch);
          $result=json_decode($result, true);
          return $result;
      }

    }

}
