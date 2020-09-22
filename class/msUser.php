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
 * Gestion des utilisateurs de l'EHR
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

use OTPHP\TOTP;

class msUser
{

/**
 * @var int $_userID ID de l'utilisateur concerné
 */
    private $_userID;
/**
 * @var int $_userName nom de l'utilisateur concerné
 */
    private $_userName;
/**
 * @var string $_userPass Password de l'individus concerné
 */
    private $_userPass;
/**
 * @var bool $_loginChecked Login vérifié ou non
 */
    private $_loginChecked;
/**
 * @var int $_nowUnixTimestamp Fixe le timestamp unix
 */
    private $_nowUnixTimestamp;
/**
 * @var string $_userSecret2fa clef 2fa décodée
 */
    private $_userSecret2fa=null;

    private $_userPasswordRecoveryStr;

/**
 * Définir le userID
 * @param int $userID userID
 */
    public function setUserID($userID) {
      if(msPeople::checkPeopleExist($userID)) {
        $this->_userID = $userID;
      } else {
        throw new Exception('UserID does not exist');
      }
    }

/**
 * Indentification de l'utilisateur
 * @return bool|array Si succès renvoie array avec données utilisateur
 */
    public function userIdentification()
    {
        global $p;
        if (!isset($_COOKIE['userName'])) {
            return msUser::cleanBadAuth();
        }
        if (!isset($_COOKIE['userPass'])) {
            return msUser::cleanBadAuth();
        }
        $fingerprint_partiel = $_SERVER['HTTP_ACCEPT_LANGUAGE'].$p['config']['fingerprint'].$_SERVER['HTTP_USER_AGENT'];

        $user=msSQL::sqlUnique("select id, name, CAST(AES_DECRYPT(pass,@password) AS CHAR(100)) as pass, `rank`, module,
         CASE WHEN secret2fa is null THEN null ELSE CAST(AES_DECRYPT(secret2fa,@password) AS CHAR(110)) END as secret2fa
         from people where name='".msSQL::cleanVar($_COOKIE['userName'])."' and lastLogFingerprint=sha1(concat('".$fingerprint_partiel."',lastLogDate)) LIMIT 1");

        if(password_verify($user['pass'],$_COOKIE['userPass'])) {

            $name2typeID = new msData();
            $name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'birthname']);

            $user['prenom']=msSQL::sqlUniqueChamp("select value from objets_data where typeID='".$name2typeID['firstname']."' and toID='".$user['id']."' and outdated='' and deleted='' limit 1");
            $user['nom']=msSQL::sqlUniqueChamp("select value from objets_data where typeID='".$name2typeID['lastname']."' and toID='".$user['id']."' and outdated='' and deleted='' limit 1");
            if(!$user['nom']) {
                $user['nom']=msSQL::sqlUniqueChamp("select value from objets_data where typeID='".$name2typeID['birthname']."' and toID='".$user['id']."' and outdated='' and deleted='' limit 1");
            }

            $this->_userID = $user['id'];
            $this->_userName = $user['name'];
            $this->_userSecret2fa = $user['secret2fa'];

            return $user;
        } else {
            return msUser::cleanBadAuth();
        }
    }

/**
 * Indentification de l'utilisateur pour les pages phonecapture
 * @return bool|array Si succès renvoie array avec données utilisateur
 */
    public static function userIdentificationPhonecapture()
    {
        global $p;
        if (!is_numeric($_COOKIE['userIdPc'])) {
            return msUser::cleanBadAuth();
        }
        if (!isset($_COOKIE['userPassPc'])) {
            return msUser::cleanBadAuth();
        }

        $userID=msSQL::cleanVar($_COOKIE['userIdPc']);
        $user=msSQL::sqlUnique("select id, CAST(AES_DECRYPT(pass,@password) AS CHAR(100)) as pass, `rank` from people where id='".$userID."' LIMIT 1");

        //recherche clef de salage spécifique au user
        $p['config']['phonecaptureFingerprint']=msConfiguration::getUserParameterValue('phonecaptureFingerprint', $userID);

        if(password_verify($user['pass'].$p['config']['phonecaptureFingerprint'],$_COOKIE['userPassPc'])) {
            return $user;
        } else {
            $duration=msConfiguration::getParameterValue('phonecaptureCookieDuration');
            $domain=msConfiguration::getParameterValue('cookieDomain');
            setcookie("userIdPc", '', (time()-$duration), "/", $domain);
            setcookie("userPassPc", '', (time()-$duration), "/", $domain);
            unset($_SESSION);
            return null;
        }
    }


/**
 * Nettoyage si mauvaise identification
 * @return bool  Retourne False
 */
    public static function cleanBadAuth()
    {
        global $match, $p;
        include $p['homepath'].'controlers/login/logOutDo.php';
        return false;
    }

/**
 * Vérifier le login utilisateur
 * @param  int $userName userName
 * @param  string $pass  password
 * @return bool         true/false
 */
    public function checkLogin($userName, $pass)
    {
        $userlogin=msSQL::sqlUnique("select id, name, CAST(AES_DECRYPT(pass,@password) AS CHAR(100)) as pass,
        CASE WHEN secret2fa is null THEN null ELSE CAST(AES_DECRYPT(secret2fa,@password) AS CHAR(110)) END as secret2fa from people where name='".msSQL::cleanVar($userName)."' limit 1");
        if (password_verify($pass, $userlogin['pass'])) {
            $this->_userID=$userlogin['id'];
            $this->_userName=$userlogin['name'];
            $this->_userPass=$userlogin['pass'];
            $this->_loginChecked=true;
            $this->_userSecret2fa=$userlogin['secret2fa'];
            return true;
        } else {
            if($this->_checkPasswordFormatAndUpdate()) {
              return $this->checkLogin($userName, $pass);
            }
            $this->_loginChecked=false;
            return false;
        }
    }

/**
 * Vérifier la présence d'une clef OTP pour l'utilisateur courant
 * @return boolean true/false
 */
    public function check2faValidKey() {
      if($this->_userSecret2fa != null) {
        return true;
      } else {
        return false;
      }
    }

/**
 * Vérifier le code OTP fourni à la connexion du membre
 * @param  int $code integral 6 chiffres
 * @return boolean       true/false
 */
    public function check2fa($code) {
      $otp = TOTP::create(
        $this->_userSecret2fa,
        30,     // période (30s)
        'sha1', // algo
        6       // nombre de chiffres
      );
      return $otp->verify($code);
    }

/**
 * Créer une clef OTP en base pour l'utilisateur courant
 * @return array secret2fa => clef, uri => utl pour QR code
 */
    public function set2fa() {
      if (!is_numeric($this->_userID)) {
          throw new Exception("UserId n'est pas numérique");
      }
      if (!is_string($this->_userName)) {
          throw new Exception("UserId n'est pas une chaine");
      }

      global $p;
      $otp = TOTP::create(
        null,
        30,     // période (30s)
        'sha1', // algo
        6       // nombre de chiffres
      );
      $otp->setLabel($this->_userName);
      $otp->setIssuer($p['config']['designAppName']);

      msSQL::sqlQuery("UPDATE people set secret2fa=AES_ENCRYPT('".msSQL::cleanVar($otp->getSecret())."',@password) WHERE id='".$this->_userID."' limit 1");

      return array(
        'secret2fa'=>$otp->getSecret(),
        'uri'=>urldecode($otp->getProvisioningUri())
      );
    }

/**
 * Revoquer la clef utilisateur de double authentification
 * @return boolean true/false
 */
    public function set2faUserKeyRevoked($uid) {
      if (!is_numeric($uid)) {
          throw new Exception("UserID n'est pas numérique");
      }
      return msSQL::sqlQuery("UPDATE people set secret2fa = null WHERE id='".$uid."' limit 1");
    }

/**
 * Obtenir l'URI à partir de la clef de l'utilisateur courant
 * @return string URI (NON url encodée)
 */
    public function get2faUri() {
      global $p;
      $otp = TOTP::create(
        $this->_userSecret2fa,
        30,     // période (30s)
        'sha1', // algo
        6       // nombre de chiffres
      );
      $otp->setLabel($this->_userName);
      $otp->setIssuer($p['config']['designAppName']);
      return urldecode($otp->getProvisioningUri());
    }

/**
 * Vérifier le login utilisateur via le userID
 * @param  int $userID userID
 * @param  string $pass   userPass
 * @return bool          true/false
 */
    public function checkLoginByUserID($userID, $pass) {
      if($userName = msSQL::sqlUniqueChamp("select name from people where id='".msSQL::cleanVar($userID)."' limit 1")) {
        return $this->checkLogin($userName, $pass);
      } else {
        return false;
      }
    }

/**
 * Effectuer le login
 * @return void
 */
    public function doLogin()
    {
        $this->_logLastCon();
        $this->_loginSetCookies();
        return $this->_userID;
    }

/**
 * Effectuer un logout
 * @return void
 */
    public static function doLogout()
    {
        global $p;
        setcookie("userName", '', (time()-$p['config']['cookieDuree']), "/", $p['config']['cookieDomain']);
        setcookie("apacheLogUserID", '', (time()-$p['config']['cookieDuree']), "/", $p['config']['cookieDomain']);
        setcookie("userPass", '', (time()-$p['config']['cookieDuree']), "/", $p['config']['cookieDomain']);
        setcookie("userIdPc", '', (time()-$p['config']['cookieDuree']), "/", $p['config']['cookieDomain']);
        setcookie("userPassPc", '', (time()-$p['config']['cookieDuree']), "/", $p['config']['cookieDomain']);
        unset($_SESSION);
    }

/**
 * Mettre à jour la password d'un utilisateur
 * @param int $userID user id
 * @param string $userPass user password
 */
    public static function setUserNewPassword($userID, $userPass) {
      if (!is_numeric($userID)) {
          throw new Exception('UserID is not numeric');
      }
      $userPass = password_hash($userPass, PASSWORD_DEFAULT);
      return msSQL::sqlQuery("UPDATE people set pass=AES_ENCRYPT('".msSQL::cleanVar($userPass)."',@password) WHERE id='".$userID."' limit 1");
    }

/**
 * Obtenir le userID à partir de name
 * @param  string $name name
 * @return int       userID
 */
    public static function getUserIdFromName($name) {
        if (!is_string($name)) {
            throw new Exception('Name is not a string');
        }
        return msSQL::sqlUniqueChamp("SELECT id FROM people WHERE name='".msSQL::cleanVar($name)."' limit 1");
    }

/**
 * Obtenir le username à partir de l'id
 * @param  int $name userID
 * @return string       username
 */
    public static function getUsernameFromId($id) {
        if (!is_numeric($id)) {
            throw new Exception('Id is not numeric');
        }
        return msSQL::sqlUniqueChamp("SELECT name FROM people WHERE id='".msSQL::cleanVar($id)."' limit 1");
    }

/**
 * Obtenir le password d'un utilisateur via son ID
 * @param  int $userID userID
 * @return string         password
 */
    public static function getUserPassByUserID($userID) {
      if (!is_numeric($userID)) {
          throw new Exception('UserID is not numeric');
      }
      return msSQL::sqlUniqueChamp("select CAST(AES_DECRYPT(pass,@password) AS CHAR(100)) as pass from people where id='".msSQL::cleanVar($userID)."' and LENGTH(pass)>0");
    }

/**
 * Vérifier si un utilisater est admin
 * @return bool true or false
 */
    public static function checkUserIsAdmin()
    {
        global $p;
        if ($p['user']['rank']=='admin') {
            return true;
        } else {
            return false;
        }
    }

/**
 * Loguer la dernière connexion
 * @return void
 */
    private function _logLastCon()
    {
        if (!$this->_loginChecked) {
            throw new Exception('Login is not yet checked');
        }
        if (!is_numeric($this->_userID)) {
            throw new Exception('UserID is not numeric');
        }
        global $p;
        $this->_nowUnixTimestamp=time();

        $fingerprint = sha1($_SERVER['HTTP_ACCEPT_LANGUAGE'].$p['config']['fingerprint'].$_SERVER['HTTP_USER_AGENT'].date("Y-m-d H:i:s", $this->_nowUnixTimestamp));

        $data=array(
            'id'=>$this->_userID,
            'lastLogIP'=>$_SERVER['REMOTE_ADDR'],
            'lastLogDate'=>date("Y-m-d H:i:s", $this->_nowUnixTimestamp),
            'lastLogFingerprint'=>$fingerprint
        );
        msSQL::sqlInsert('people', $data);
    }

/**
 * Poser les cookies
 * @return void
 */
    private function _loginSetCookies()
    {
        if (!$this->_loginChecked) {
            throw new Exception('Login is not yet checked');
        }
        global $p;
        $userPass=password_hash($this->_userPass,PASSWORD_DEFAULT);
        setcookie("userName", $this->_userName, (time()+$p['config']['cookieDuration']), "/", $p['config']['cookieDomain'], false, true);
        setcookie("apacheLogUserID", $this->_userID, (time()+$p['config']['cookieDuration']), "/", $p['config']['cookieDomain'], false, true);
        setcookie("userPass", $userPass, (time()+$p['config']['cookieDuration']), "/", $p['config']['cookieDomain'], false, true);
    }

/**
 * Fonction permettant la transition de password
 * @return bool true|false
 */
     private function _checkPasswordFormatAndUpdate() {
       $cols = msSQL::sql2tabKey("SHOW COLUMNS FROM people", "Field");
       if($cols['pass']['Type']=="varbinary(60)") {
         msSQL::sqlQuery("ALTER TABLE `people` CHANGE `pass` `pass` VARBINARY(1000) NULL DEFAULT NULL");
         if($pass=msSQL::sql2tabKey("select id, CAST(AES_DECRYPT(pass,@password) AS CHAR(100)) as pass from people where pass != '' ", 'id', 'pass')) {
           foreach($pass as $id=>$pass) {
             msUser::setUserNewPassword($id, $pass);
           }
           return true;
         }
         return false;
       } else {
         return false;
       }
     }

/**
 * Envoyer un mail de création de compte utilisateur
 * @param  int $userID ID utilisateur
 * @return bool         true/false
 */
     public static function mailUserNewAccount($userID) {
       global $p;
       if (!is_numeric($userID)) {
           throw new Exception('UserID is not numeric');
       }
       $people = new msPeople();
       $people->setToID($userID);
       $people->setFromID($p['user']['id']);
       $peopleData = $people->getSimpleAdminDatasByName();

       $mailTo='';
       if(isset($peopleData['profesionnalEmail']) and !empty($peopleData['profesionnalEmail'])) {
         $mailTo = $peopleData['profesionnalEmail'];
       } elseif(isset($peopleData['personalEmail'])  and !empty($peopleData['personalEmail'])) {
         $mailTo = $peopleData['personalEmail'];
       }

       $mail = new msSend();
       $mail->setSendType('ns');
       $mail->setSendService($p['config']['smtpTracking']);
       $mail->setTo($mailTo);
       $mail->setFrom($p['config']['smtpFrom']);
       $mail->setFromName($p['config']['smtpFromName']);
       $mail->setSubject("Votre compte ".$p['config']['designAppName']);
       $mail->setBody("Bonjour\n\nVoici votre nom d'utilisateur pour ".$p['config']['designAppName']." : ".msUser::getUsernameFromId($userID)."\nLe mot de passe correspondant sera délivré dans un second mail.\n\nBien cordialement,\n\nL'administrateur");
       return $mail->send();
     }

/**
 * Envoyer le mot de passe initial par mail
 * @param  int $userID   ID user
 * @param  string $password mot de passe
 * @return bool           true/false
 */
     public static function mailUserNewPassword($userID, $password) {
       global $p;
       if (!is_numeric($userID)) {
           throw new Exception('UserID is not numeric');
       }
       $people = new msPeople();
       $people->setToID($userID);
       $people->setFromID($p['user']['id']);
       $peopleData = $people->getSimpleAdminDatasByName();

       $mailTo='';
       if(isset($peopleData['profesionnalEmail']) and !empty($peopleData['profesionnalEmail'])) {
         $mailTo = $peopleData['profesionnalEmail'];
       } elseif(isset($peopleData['personalEmail'])  and !empty($peopleData['personalEmail'])) {
         $mailTo = $peopleData['personalEmail'];
       }

       $mail = new msSend();
       $mail->setSendType('ns');
       $mail->setSendService($p['config']['smtpTracking']);
       $mail->setTo($mailTo);
       $mail->setFrom($p['config']['smtpFrom']);
       $mail->setFromName($p['config']['smtpFromName']);
       $mail->setSubject("Votre compte ".$p['config']['designAppName']);
       $mail->setBody("Bonjour\n\nVoici votre mot de passe pour ".$p['config']['designAppName']." : ".$password."\n\nBien cordialement,\n\nL'administrateur");
       return $mail->send();
     }

/**
 * Initialiser un nouveau processus de recouvrement de password
 * @return bool           true/false
 */
     public function setUserAccountToNewPasswordRecoveryProcess() {
       if(!isset($this->_userID)) throw new Exception('UserID is not defined');

       $this->_userPasswordRecoveryStr = msTools::getRandomStr(25);
       return msSQL::sqlQuery("UPDATE people set lastLostPassDate=NOW(), lastLostPassRandStr='".$this->_userPasswordRecoveryStr."'  WHERE id='".$this->_userID."' limit 1");
     }

/**
 * Fermer le processus de recouvrement de password
 * @return bool           true/false
 */
     public function setUserAccountPasswordRecoveryProcessClosed() {
       if(!isset($this->_userID)) throw new Exception('UserID is not defined');
       return msSQL::sqlQuery("UPDATE people set lastLostPassRandStr=NULL  WHERE id='".$this->_userID."' limit 1");
     }

/**
 * Envoyer l'email de modification du mot de passe
 * @param  string $email email
 * @return bool        true/false suivant résultat expédition mail
 */
     public function mailUserPasswordRecoveryProcess($email) {
       if(!isset($this->_userID)) throw new Exception('UserID is not defined');
       if(!isset($this->_userPasswordRecoveryStr)) throw new Exception('UserPasswordRecoveryStr is not defined');
       global $p;

       $mail = new msSend();
       $mail->setSendType('ns');
       $mail->setSendService($p['config']['smtpTracking']);
       $mail->setTo($email);
       $mail->setBodyHtml(FALSE);
       $mail->setFrom($p['config']['smtpFrom']);
       $mail->setFromName($p['config']['smtpFromName']);
       $mail->setSubject("Votre compte ".$p['config']['designAppName']);

       $link = $p['config']['protocol'].$p['config']['host'].$p['config']['urlHostSuffixe']."/public/lostPassword/setNew/".$this->_userPasswordRecoveryStr."/";

       $mail->setBody("Bonjour\n\nVoici un lien pour recouvrer l'usage de votre compte  ".$p['config']['designAppName']." :\n".$link."\n\nCe lien est valable 10 minutes\n\nBien cordialement,\n\nL'administrateur");
       return $mail->send();
     }

/**
 * Créer un username unique pour le login en fonction de l'identité
 * @param  string $fn firstname
 * @param  string $ln lastname
 * @param  string $bn birthname
 * @return string     username
 */
    public static function makeRandomUniqLoginUsername($fn='', $ln='', $bn='') {
      if(empty($fn) and empty($ln) and empty($bn)) throw new Exception('Identite is empty');

      $l=[];
      if(!empty($fn)) {
       $fn=msTools::stripAccents($fn);
       $fn=str_replace(['\'', '-'], ' ', $fn);
       if(!empty($fn[0]) and ctype_alpha($fn[0])) $l[]=$fn[0];
      }

      if(!empty($ln)) {
       $ln=msTools::stripAccents($ln);
       $ln=str_replace(['\'', '-'], ' ', $ln);
       if(!empty($ln)) $l[]=$ln;
      } elseif(!empty($bn)) {
       $bn=msTools::stripAccents($bn);
       $bn=str_replace(['\'', '-'], ' ', $bn);
       if(!empty($bn)) $l[]=$bn;
      }

      $firstpart = strtolower(implode('', $l));
      if(empty($firstpart)) $firstpart=msTools::getRandomStr(10,'abcdefghijklmnopqrstuvwxyz');
      if(strlen($firstpart) > 20) {
        $firstpart=substr($firstpart,0, 20);
      }

      $secondpart = msTools::getRandomStr(4,'123456789');
      $username = $firstpart.$secondpart;

      if(msSQL::sqlUniqueChamp("select name from people where name='".msSQL::cleanVar($username)."' limit 1")) {
        $username = makeRandomUniqLoginUsername($fn, $ln, $bn);
      }

      return $username;
    }

}
