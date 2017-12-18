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
 * Gestion SMS via allMySMS <https://www.allmysms.com/>
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msSMSallMySMS
{


/**
 * Nom de la campagne sms
 * @var string
 */
  private $_campaign_name;

/**
 * Data de la camapgne
 * @var string
 */
  private $_campaign_data;

/**
 * Réponse de l'api
 * @var string
 */
  private $_campaign_answer;

/**
 * Message à envoyer
 * @var string
 */
  private $_message;

/**
 * Nom expéditeur du SMS
 * @var string
 */
  private $_tpoa;

/**
 * Date d'envoi différé du SMS (YYYY-MM-JJ HH:MM:SS)
 * @var string
 */
 private $_date;

/**
 * Destinataire(s)
 * @var array
 */
 private $_destinataires;

/**
* Notification par mail
* @var int
*/
private $_mail_notification=1;

/**
* Dynamic : nombre de paramètres dans le messages
* @var int
*/
private $_dynamic;

/**
 * Timestamp unix pour déterminer le répoertoire de log de la campagne
 * @var int
 */
 private $_timestamp4log;

/**
* Filename pour déterminer le log de la campagne
* @var int
*/
private $_filename4log;

/**
* Data à ajouter dans le log
* @var array
*/
private $_addData4log;

/**
 * Ajouter des datas au log
 * @param array $_addData4log [description]
 */
public function set_addData4log(array $_addData4log)
{
  $this->_addData4log = $_addData4log;
  return $this;
}

/**
 * Set filename4log
 * @param string $_filename4log nom du fichier
 */
public function set_filename4log($_filename4log)
{
  $this->_filename4log = $_filename4log;
  return $this;
}

/**
 * Set timestamp4log
 * @param int $_timestamp4log timestamp
 */
public function set_timestamp4log($_timestamp4log)
{
  $this->_timestamp4log = $_timestamp4log;
  return $this;
}

/**
 * Set campaign_name
 * @param int $_campaign_name
 *
 * @return static
 */
public function set_campaign_name($_campaign_name)
{
  $this->_campaign_name = $_campaign_name;
  return $this;
}

/**
 * Set mail notification
 * @param int  $_mail_notification 0/1
 */
public function set_mail_notification($_mail_notification)
{
  $this->_mail_notification = $_mail_notification;
  return $this;
}

/**
 * Set message
 * @param string $_message message à envoyer
 */
public function set_message($_message)
{
  $this->_message = $_message;
  return $this;
}

/**
 * Set tpoa
 * @param string $_tpoa tpoa
 */
public function set_tpoa($_tpoa)
{
  $this->_tpoa = $_tpoa;
  return $this;
}

/**
 * Set date
 * @param string $_date date d'envoi différé
 */
public function set_date($_date)
{
  $this->_date = $_date;
  return $this;
}

/**
 * Ajouter un destinataire à la campagne
 * @param  string $tel    n° de téléphone sans espace
 * @param  array  $params paramètres à inclure dans message
 * @return void
 */
public function ajoutDestinataire($tel, $params=[]) {
  $tel=trim(str_ireplace(array(' ', '/', '.'), '', $tel));
  if(strlen($tel) == 10) {
    $destinataire['MOBILEPHONE']=$tel;
    if(count($params)>0) {

      if(!isset($this->_dynamic)) $this->_dynamic=count($params);

      foreach($params as $k=>$v) {
        $destinataire[$k]=$v;
      }

    }
    $this->_destinataires[]=$destinataire;
  }
}

/**
 * Générer la requète curl pour la campagne
 * @return [type] [description]
 */
  private function _generateCampaign() {

    if(!isset($this->_campaign_name)) throw new Exception('Campaign_name n\'est pas définie');
    if(!isset($this->_message)) throw new Exception('Message n\'est pas définie');
    if(!isset($this->_destinataires)) throw new Exception('Destinataires n\'est pas définie');
    if(!isset($this->_dynamic)) $this->_dynamic=0;

    $c['DATA']=array(
      "CAMPAIGN_NAME" => $this->_campaign_name,
      "MESSAGE" => $this->_message,
      "DYNAMIC" => $this->_dynamic,
      "MAIL_NOTIF"=> $this->_mail_notification
    );

    if(isset($this->_tpoa)) $c['DATA']['TPOA'] = $this->_tpoa;
    if(isset($this->_date)) $c['DATA']['DATE'] = $this->_date;

    $c['DATA']['SMS']=$this->_destinataires;

    $this->_campaign_data=$c;
  }

/**
 * Envoyer la campagne
 * @return string Retour JSON de l'api
 */
  public function sendCampaign() {
      global $p;

      if(count($this->_destinataires)>0) {
        $this->_generateCampaign();

        //$url = 'https://api.allmysms.com/http/9.0/simulateCampaign/';
        $url = 'http://api.allmysms.com/http/9.0/sendSms/';

        //set POST variables
        $fields = array(
            'login' => urlencode($p['config']['allMySmsLogin']),
            'apiKey'   => urlencode($p['config']['allMySmsApiKey']),
            'smsData'   => urlencode(json_encode($this->_campaign_data)),
        );

        $fieldsString = "";
        //url-ify the data for the POST
        foreach ($fields as $key=>$value) {
            $fieldsString .= $key.'='.$value.'&';
        }
        rtrim($fieldsString, '&');

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);

      } else {
        $result['status']='0';
        $result['statusText']="Pas de destinataires pour cette campagne - API AllMySMS non sollicitée";
        $result=json_encode($result);
      }

      $this->_campaign_answer=$result;

  }

/**
 * Loguer la campagne sous forme de fichier json
 * @return void
 */
  public function logCampaign() {

    global $p;

    if(!isset($this->_campaign_answer)) throw new Exception('Campaign_answer n\'est pas définie');
    if(!isset($this->_timestamp4log)) $this->_timestamp4log=time();
    if(!isset($this->_filename4log)) $this->_filename4log=$this->_campaign_name;

    $tab=json_decode($this->_campaign_answer, true);
    $tab['timestamp_send']=time();
    $tab['campaign_data']=$this->_campaign_data;

    if(isset($this->_addData4log)) {
      foreach ($this->_addData4log as $k => $v) {
        $tab[$k]=$v;
      }
    }

    $tab=json_encode($tab);

    //log json
    $logFileDirectory=$p['config']['smsLogCampaignDirectory'].date('Y/m/d/', $this->_timestamp4log);
    msTools::checkAndBuildTargetDir($logFileDirectory);
    file_put_contents($logFileDirectory.$this->_filename4log, $tab);

  }

/**
 * Loguer le crédit SMS restant
 * @return [type] [description]
 */
public function logCreditsRestants() {
  global $p;
  if(!isset($this->_campaign_answer)) throw new Exception('Campaign_answer n\'est pas définie');
  $credits=json_decode($this->_campaign_answer, true);
  if(isset($credits['credits'])) {
    $credits=$credits['credits']/15;
    file_put_contents($p['config']['workingDirectory'].$p['config']['smsCreditsFile'], $credits);
  }
}

/**
 * Ajouter les infos accusé de réception au log
 * @param string $logFile fichier de log à amender
 */
  public function addAcksToLogs($logFile) {
    if(is_file($logFile)) {
      $data=json_decode(file_get_contents($logFile), true);

      unset($data['acks']);
      if(isset($data['campaignId'])) {
        $acks=$this->getAcksRecep($data['campaignId']);
        if(is_array($acks)) $data['acks']=$acks;

        $datajson=json_encode($data);
        file_put_contents($logFile, $datajson);
        if(is_array($acks)) return $acks; else return null;
      } else {
        return null;
      }
    }
  }

/**
 * Obtenir les infos accusés de réception
 * @param  string $campId ID dans le campagne
 * @return array         tableau des infos accusé de réception
 */
  public function getAcksRecep($campId) {

    global $p;

    $url = 'https://api.allmysms.com/http/9.0/getAcks/';

    //set POST variables
    $fields = array(
        'login' => urlencode($p['config']['allMySmsLogin']),
        'apiKey'   => urlencode($p['config']['allMySmsApiKey']),
        'campId'   => urlencode($campId),
    );

    $fieldsString = "";
    //url-ify the data for the POST
    foreach ($fields as $key=>$value) {
        $fieldsString .= $key.'='.$value.'&';
    }
    rtrim($fieldsString, '&');

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //execute post
    $result = curl_exec($ch);

    //close connection
    curl_close($ch);

    $data = json_decode($result, true);

    if(isset($data['status'])) {if($data['status'] == '0') return null;}
    if(isset($data['acks'])) return $data['acks']; else return null;
  }

public function getSendedCampaignData($date) {

  global $p;

  $logFile=$p['config']['smsLogCampaignDirectory'].date('Y/m/d/', $date).'RappelsRDV.json';
  if(is_file($logFile)) {
    if($data=file_get_contents($logFile)) {
      $data=json_decode($data, true);

      //obtenir les accusés réception si non présents
      if(!isset($data['acks'])) {
        $acksdata=$this->addAcksToLogs($logFile);
        if(is_array($acksdata))$data['acks']=$acksdata;
      }

      //boucle sur liste patients
      if(isset($data['patientsList'])) {
        foreach($data['patientsList'] as $v){
          $dataw[$v['heure']]=$v;
        }
      }

      //boucle sur liste des envois
      if(isset($data['campaign_data']['DATA']['SMS'])) {
        foreach($data['campaign_data']['DATA']['SMS'] as $v){
          $v['telDisplay'] = preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '\1 \2 \3 \4 \5', $v['MOBILEPHONE']);
          $dataw[$v['PARAM_2']]=array_merge($dataw[$v['PARAM_2']], $v);
        }
      }

      //accusés récept
      if(isset($data['acks'])) {
        if(is_array($data['acks'])) {
          foreach($data['acks'] as $v) {
            $v['phoneNumberFr']=preg_replace("#^33([0-9]{9})$#", "0$1", $v['phoneNumber']);
            $dataAcc[$v['phoneNumberFr']]=$v;
          }

          //intégartion Acc recep
          foreach ($dataw as $k => $v) {
            if(isset($v['MOBILEPHONE']) ) {
              if(isset($dataAcc[$v['MOBILEPHONE']]) ) {
                $dataw[$k]['accRecep']=$dataAcc[$v['MOBILEPHONE']];
              }
            }
          }


        }
      }

      //générer le tableau de retour, partie sms
      $i=0;
      foreach($dataw as $k=>$v) {
        $smsReturnTab[$i]=array(
          "id"=>$v['id'],
          "identite"=>$v['identite'],
          "typeCs"=>$v['type'],
          "heureRdv"=>$v['heure'],
        );
        if(isset($v['MOBILEPHONE'])) $smsReturnTab[$i]['telFr']=$v['MOBILEPHONE'];
        if(isset($v['telDisplay'])) $smsReturnTab[$i]['telFrDisplay']=$v['telDisplay'];
        if(isset($v['accRecep'])) {
          $smsReturnTab[$i]['accRecepStatus']=$v['accRecep']['status'];
          $smsReturnTab[$i]['accRecepComment']=$v['accRecep']['comment'];
          $smsReturnTab[$i]['accRecepRecepDate']=$v['accRecep']['receptionDate'];
        }
        $i++;
      }

      //générer le tableau de retour, partie sms
      $campaignReturnTab=@array(
        'status' => $data['status'],
        'statusText' => $data['statusText'],
        'invalidNumbers' => $data['invalidNumbers'],
        'campaignId' => $data['campaignId'],
        'creditsUsed' => $data['creditsUsed'],
        'nbContacts' => $data['nbContacts'],
        'nbSms' => $data['nbSms'],
        'credits' => $data['credits'],
      );

      return array("campaign"=>$campaignReturnTab, "sms"=>$smsReturnTab);

    }
  }
}

}
