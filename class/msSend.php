<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * Envoyer : EN TRAVAUX !
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


class msSend
{

  private $_patientID;
  private $_objectID;

  private $_sendType;
  private $_sendService='';

  private $_to=[];
  private $_cc=[];
  private $_bcc=[];
  private $_from;
  private $_fromName='';
  private $_replyTo=[];
  private $_subject;
  private $_body;
  private $_bodyHtml=false;
  private $_attachments=[];
  private $_attachmentsBaseName=[];
  private $_params=[];

  private $_isSendingSucceeded=false;
  private $_returnedData;
  private $_returnedTrackingNumber;

  private $_isWritingSucceeded=false;

  public function setPatientID($patientID) {
    if(!is_numeric($patientID)) throw new Exception("PatientID is not numeric.");
    $this->_patientID=$patientID;
  }

  public function setObjectID($objectID) {
    if(!is_numeric($objectID)) throw new Exception("ObjectID is not numeric.");
    $this->_objectID=$objectID;
  }

  public function setSendType($sendType) {
    $this->_sendType=$sendType;
  }

  public function setSendService($sendService) {
    $this->_sendService=$sendService;
  }

  public function setTo($to) {
    $this->_to=array_merge($this->_to, (array)$to);
  }

  public function setCc($cc) {
    $this->_cc=array_merge($this->_cc, (array)$cc);
  }

  public function setBcc($bcc) {
    $this->_bcc=array_merge($this->_bcc, (array)$bcc);
  }

  public function setFrom($from) {
    $this->_from=$from;
  }

  public function setFromName($fromName) {
    $this->_fromName=$fromName;
  }

  public function setSubject($subject) {
    $this->_subject=$subject;
  }

  public function setBody($body) {
    $this->_body=$body;
  }

  public function setBodyHtml($bodyHtml) {
    if(!is_bool($bodyHtml)) {
      throw new Exception('BodyHtml is not bool');
    }
    $this->_bodyHtml=$bodyHtml;
  }

  public function setAttachments($attachments) {
    $this->_attachments=array_merge($this->_attachments, (array)$attachments);
  }

  public function setAttachmentsBaseName($attachmentsBaseName) {
    $this->_attachmentsBaseName=array_merge($this->_attachmentsBaseName, (array)$attachmentsBaseName);
  }

  public function setAttachmentByObjectID($id) {
    $doc = new msStockage;
    $doc->setObjetID($id);
    $this->_attachments[]=$doc->getPathToDoc();
  }

  public function setParams($params) {
    $this->_params=array_merge($this->_params, (array)$params);
  }

/**
 * Envoyer
 * @return boolean true/false
 */
  public function send() {
    $methodTypeService =  '_send_'.$this->_sendType.'_'.$this->_sendService;
    $methodType =  '_send_'.$this->_sendType;

    // essayer type + service
    if(method_exists('msSend', $methodTypeService)) {
      return $this->_isSendingSucceeded = $this->$methodTypeService();
    }
    // régression à type seule
    elseif(method_exists('msSend', $methodType)) {
      return $this->_isSendingSucceeded = $this->$methodType();
    }
    // sinon false
    else {
      return $this->_isSendingSucceeded = false;
    }

  }

  public function write() {
    if(!$this->_isSendingSucceeded) throw new Exception("L'envoi n'a pas été correctement effectué");

    $methodTypeService =  '_write_'.$this->_sendType.'_'.$this->_sendService;
    $methodType =  '_write_'.$this->_sendType;

    // essayer type + service
    if(method_exists('msSend', $methodTypeService)) {
      return $this->_isWritingSucceeded = $this->$methodTypeService();
    }
    // régression à type seule
    elseif(method_exists('msSend', $methodType)) {
      return $this->_isWritingSucceeded = $this->$methodType();
    }
    // sinon false
    else {
      return $this->_isWritingSucceeded = false;
    }

  }

/**
 * Envoyer par SMTP
 * @return boolean true/false
 */
  private function _sendSmtp() {
    global $p;

    $mail = new PHPMailer\PHPMailer\PHPMailer;
    $mail->CharSet = 'UTF-8';
    //$mail->SMTPDebug = 4;
    $mail->isSMTP();
    $mail->Host = $p['config']['smtpHost'];
    $mail->SMTPAuth = true;
    $mail->Username = $p['config']['smtpUsername'];
    $mail->Password = $p['config']['smtpPassword'];
    if($p['config']['smtpOptions'] == 'on') {
      $mail->SMTPOptions = array(
        'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
        )
      );
    }
    if(!empty($p['config']['smtpSecureType'])) $mail->SMTPSecure = $p['config']['smtpSecureType'];
    $mail->Port = $p['config']['smtpPort'];

    $mail->isHTML($this->_bodyHtml);
    $mail->Subject = $this->_subject;

    $mail->setFrom($this->_from, empty($this->_fromName)?$p['config']['smtpFromName']:$this->_fromName);
    foreach($this->_to as $to) {
      $mail->addAddress($to);
    }

    if(isset($this->_replyTo) and !empty($this->_replyTo)) {
      foreach($this->_replyTo as $replyTo) {
        $mail->addReplyTo($replyTo);
      }
    }

    if(isset($this->_cc) and !empty($this->_cc)) {
      foreach($this->_cc as $cc) {
        $mail->addCC($cc);
      }
    }

    if(isset($this->_bcc) and !empty($this->_bcc)) {
      foreach($this->_bcc as $bcc) {
        $mail->addBCC($bcc);
      }
    }

    foreach ($this->_attachments as $k=>$attachment) {
        if(isset($this->_attachmentsBaseName[$k]) and !empty($this->_attachmentsBaseName[$k])) {
          $docName=$this->_attachmentsBaseName[$k].'.'.pathinfo($attachment, PATHINFO_EXTENSION);
        } elseif(isset($this->_attachmentsBaseName) and !empty($this->_attachmentsBaseName)) {
          if(count($this->_attachments) > 1) {
            $docName=$this->_attachmentsBaseName[0].$k.'.'.pathinfo($attachment, PATHINFO_EXTENSION);
          } else {
            $docName=$this->_attachmentsBaseName[0].'.'.pathinfo($attachment, PATHINFO_EXTENSION);
          }
        } else {
          $docName='document.'.pathinfo($attachment, PATHINFO_EXTENSION);
        }
        $mail->addAttachment($attachment, $docName);
    }

    if($this->_bodyHtml) {
      $mail->Body = nl2br($this->_body);
      $mail->AltBody = $this->_body;
    } else {
      $mail->Body = $this->_body;
    }

    return $mail->send();
  }

/**
 * Envoyer par SMTP (non chiffré)
 * @return boolean  true/false
 */
  private function _send_ns() {
    return $this->_sendSmtp();
  }

/**
 * Ecrire après envoi par SMTP
 * @return boolean  true/false
 */
  private function _write_ns() {
    global $p;

    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);
    $patient->setToID($this->_patientID);

    //support (avec PJ ou sans)
    if (isset($this->_objectID)) {
        $supportID=$patient->createNewObjetByTypeName('mailPorteur', '', $this->_objectID);
    } else {
        $supportID=$patient->createNewObjetByTypeName('mailPorteur', '');
    }

    //from
    $patient->createNewObjetByTypeName('mailFrom', $this->_from, $supportID);
    //to
    $patient->createNewObjetByTypeName('mailTo', $this->_to[0], $supportID);
    //subject
    $patient->createNewObjetByTypeName('mailSujet', $this->_subject, $supportID);
    //message
    $patient->createNewObjetByTypeName('mailBody', $this->_body, $supportID);
    //pj ID
    if (isset($this->_objectID)) {
        $patient->createNewObjetByTypeName('mailPJ1', $this->_objectID, $supportID);
    }
    return true;
  }


/**
 * Envoyer par ecofax OVH (non chiffré)
 * @return boolean true/false
 */
  private function _send_ecofax_ecofaxOVH() {
    return $this->_sendSmtp();
  }

/**
 * Ecrire après envoi par ecofax OVH (non chiffré)
 * @return boolean true/false
 */
  private function _write_ecofax_ecofaxOVH() {
    global $p;
    $patient = new msObjet();
    $patient->setFromID($p['user']['id']);
    $patient->setToID($this->_patientID);

    //support (avec PJ ou sans)
    if (isset($this->_objectID)) {
        $supportID=$patient->createNewObjetByTypeName('mailPorteur', '', $this->_objectID);
    } else {
        $supportID=$patient->createNewObjetByTypeName('mailPorteur', '');
    }

    //from
    $patient->createNewObjetByTypeName('mailFrom', $this->_from, $supportID);
    //to
    $patient->createNewObjetByTypeName('mailTo', $this->_to[0], $supportID);
    //numero destinataire
    $patient->createNewObjetByTypeName('mailToEcofaxNumber', $this->_params['mailToEcofaxNumber'], $supportID);
    //numero destinataire
    $patient->createNewObjetByTypeName('mailToEcofaxName', $this->_params['mailToEcofaxName'], $supportID);
    //pj ID
    if (isset($this->_objectID)) {
        $patient->createNewObjetByTypeName('mailPJ1', $this->_objectID, $supportID);
    }
  }

/**
 * Envoyer via Mailjet (non chiffré)
 * @return boolean true/false
 */
  private function _send_ns_Mailjet() {
    global $p;
    $mailParams=array(
      "FromEmail"=>$this->_from,
      "FromName"=>$p['config']['smtpFromName'],
      "Subject"=>$this->_subject,
      "Text-part"=>$this->_body,
      "Html-part"=>nl2br($this->_body)
    );

    foreach($this->_to as $to) {
      $mailParams['Recipients'][]['Email']=$to;
    }

    foreach ($this->_attachments as $k=>$attachment) {

      if(isset($this->_attachmentsBaseName[$k]) and !empty($this->_attachmentsBaseName[$k])) {
        $docName=$this->_attachmentsBaseName[$k].'.'.pathinfo($attachment, PATHINFO_EXTENSION);
      } elseif(isset($this->_attachmentsBaseName) and !empty($this->_attachmentsBaseName)) {
        if(count($this->_attachments) > 1) {
          $docName=$this->_attachmentsBaseName[0].$k.'.'.pathinfo($attachment, PATHINFO_EXTENSION);
        } else {
          $docName=$this->_attachmentsBaseName[0].'.'.pathinfo($attachment, PATHINFO_EXTENSION);
        }
      } else {
        $docName='document.'.pathinfo($attachment, PATHINFO_EXTENSION);
      }

      $mime=msTools::getmimetype($attachment);
      $contenu=file_get_contents($attachment);
      if (!mb_detect_encoding($contenu, 'utf-8', true) and $mime == 'text/plain') {
          $contenu = utf8_encode($contenu);
      }
      $contenu=base64_encode($contenu);

      $mailParams['Attachments'][]=
      [
        'Content-type' => $mime,
        'Filename' => $docName,
        'content' => $contenu
      ];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.mailjet.com/v3/send");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mailParams));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERPWD, $p['config']['smtpUsername'] . ":" . $p['config']['smtpPassword']);

    $headers = array();
    $headers[] = "Content-Type: application/json";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
      echo ('Error:' . curl_error($ch));
      return false;
    } else {
      curl_close($ch);
      $result = json_decode($result, true);
      $this->_returnedData = $result;
      $this->_returnedTrackingNumber = $result['Sent'][0]['MessageID'];
      return true;
    }
  }


}
