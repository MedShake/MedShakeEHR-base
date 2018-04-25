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
 * Phonecapture : landingpage QR code
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';
$template='';

if (isset($match['params']['key'])) {
    $urlCrypt = new Atrapalo\UrlCrypt\UrlCrypt();

    $toDecrypt=urldecode($match['params']['key']);
    $key=bin2hex($p['config']['fingerprint']);

    $decrypted = $urlCrypt->decrypt($toDecrypt, $key);

    $params=explode('&&', $decrypted);
    $error=[];
    if(!is_numeric($params[0])) $error[]="Identifiant utilisateur invalide.";
    if(!is_numeric($params[1])) $error[]="Date d'expiration invalide.";
    //vérification ancienneté de la date du QR code
    if(time()-$params[1]> 300) $error[]="Ce QR code n'est plus valide";

    if(count($error)>0) {
      $p['page']['error']=$error;
      $template='phonecaptureError';
    } else {
      //mdp de l'utilisateur
      $userPass=msSQL::sqlUniqueChamp("select CAST(AES_DECRYPT(pass,@password) AS CHAR(50)) as pass from people where id='".msSQL::cleanVar($params[0])."' and LENGTH(pass)>0");

      //recherche de fingeprint specifique utilisateur
      $p['config']['phonecaptureFingerprint']=msConfiguration::getUserParameterValue('phonecaptureFingerprint', msSQL::cleanVar($params[0]));

      $userPass=md5(md5(sha1(md5($userPass.$p['config']['phonecaptureFingerprint']))));

      setcookie("userIdPc", $params[0], (time()+$p['config']['phonecaptureCookieDuration']), "/", $p['config']['cookieDomain']);
      setcookie("userPassPc", $userPass, (time()+$p['config']['phonecaptureCookieDuration']), "/", $p['config']['cookieDomain']);

      msTools::redirection('/phonecapture/');

    }
}
