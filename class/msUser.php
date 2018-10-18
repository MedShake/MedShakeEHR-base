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
 * Indentification de l'utilisateur
 * @return bool|array Si succès renvoie array avec données utilisateur
 */
    public static function userIdentification()
    {
        global $p;
        if (!isset($_COOKIE['userName'])) {
            return msUser::cleanBadAuth();
        }
        if (!isset($_COOKIE['userPass'])) {
            return msUser::cleanBadAuth();
        }
        $fingerprint_partiel = $_SERVER['HTTP_ACCEPT_LANGUAGE'].$p['config']['fingerprint'].$_SERVER['HTTP_USER_AGENT'];


        $user=msSQL::sqlUnique("select id, name, CAST(AES_DECRYPT(pass,@password) AS CHAR(50)) as pass, rank, module from people where name='".msSQL::cleanVar($_COOKIE['userName'])."' and lastLogFingerprint=sha1(concat('".$fingerprint_partiel."',lastLogDate)) LIMIT 1");

        if ($_COOKIE['userPass']==md5(md5(sha1(md5($user['pass']))))) {

            $name2typeID = new msData();
            $name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'birthname']);

            $user['prenom']=msSQL::sqlUniqueChamp("select value from objets_data where typeID='".$name2typeID['firstname']."' and toID='".$user['id']."' and outdated='' and deleted='' limit 1");
            $user['nom']=msSQL::sqlUniqueChamp("select value from objets_data where typeID='".$name2typeID['lastname']."' and toID='".$user['id']."' and outdated='' and deleted='' limit 1");
            if(!$user['nom']) {
                $user['nom']=msSQL::sqlUniqueChamp("select value from objets_data where typeID='".$name2typeID['birthname']."' and toID='".$user['id']."' and outdated='' and deleted='' limit 1");
            }
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
        $user=msSQL::sqlUnique("select id, CAST(AES_DECRYPT(pass,@password) AS CHAR(50)) as pass, rank from people where id='".$userID."' LIMIT 1");

        //recherche clef de salage spécifique au user
        $p['config']['phonecaptureFingerprint']=msConfiguration::getUserParameterValue('phonecaptureFingerprint', $userID);

        if ($_COOKIE['userPassPc']==md5(md5(sha1(md5($user['pass'].$p['config']['phonecaptureFingerprint']))))) {
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
 * @param  int $userID userID
 * @param  string $pass  password
 * @return bool         true/false
 */
    public function checkLogin($userName, $pass)
    {
        if ($userlogin=msSQL::sqlUnique("select id, name, CAST(AES_DECRYPT(pass,@password) AS CHAR(50)) as pass from people where name='".msSQL::cleanVar($userName)."' and pass=AES_ENCRYPT('".msSQL::cleanVar($pass)."',@password)")) {
            $this->_userID=$userlogin['id'];
            $this->_userName=$userlogin['name'];
            $this->_userPass=$userlogin['pass'];
            $this->_loginChecked=true;
            return true;
        } else {
            $this->_loginChecked=false;
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

        $userPass=md5(md5(sha1(md5($this->_userPass))));
        setcookie("userName", $this->_userName, (time()+$p['config']['cookieDuration']), "/", $p['config']['cookieDomain']);
        setcookie("apacheLogUserID", $this->_userID, (time()+$p['config']['cookieDuration']), "/", $p['config']['cookieDomain']);
        setcookie("userPass", $userPass, (time()+$p['config']['cookieDuration']), "/", $p['config']['cookieDomain']);
    }
}
