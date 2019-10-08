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
 * Gnupg
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msGnupg
{
    private $_gnupg;
    private $_publicKey;
    private $_publicKeyFingerprint;
    private $_peopleID;

    function __construct() {
      global $p;
      putenv('GNUPGHOME='.$p['homepath'].'.gnupg');
      error_reporting(E_ALL);
      $gpg = new gnupg;
      $gpg->seterrormode(GNUPG_ERROR_WARNING);
      $this->_gnupg = $gpg;
    }

/**
 * Définir l'utilisateur
 * @param int $peopleID ID utilisateur
 */
    public function setPeopleID($peopleID) {
      if(!msPeople::checkPeopleExist($peopleID)) {
        throw new Exception("PeopleID does not exist");
      }
      $this->_peopleID=$peopleID;
      $this->_getPublicKey();
      $this->_getKeyFingerprint();
    }

/**
 * Obtenir la clef publique de l'utilisateur
 * @return string clef publique GPG
 */
    private function _getPublicKey() {
      $ob = new msObjet;
      $ob->setToID($this->_peopleID);
      return $this->_publicKey = $ob->getLastObjetValueByTypeName('pgpPublicKey');
    }

/**
 * Obtenir le fingerprint de la clef publique de l'utilisateur
 * On passe par l'import car on ne sais pas faire mieux (todo ?)
 * @return string fingerprint clef publique utilisateur
 */
    private function _getKeyFingerprint() {
      $imp=$this->_gnupg->import($this->_publicKey);
      return $this->_publicKeyFingerprint = $imp['fingerprint'];
    }

/**
 * Obtenir du texte chiffré
 * @param  string $txt texte en entrée
 * @return string      bloc GPG
 */
    public function chiffrerTexte($txt) {
      if(!isset($this->_publicKeyFingerprint)) throw new Exception("PublicKeyFingerprint is not defined");
      $this->_gnupg->addencryptkey($this->_publicKeyFingerprint);
      return $this->_gnupg->encrypt($txt);
    }

}
