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
 * User : changer phonecaptureFingerprint pour l'utilisateur courant
 * (Révoquer les périphériques phonecapture logués)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (is_numeric($p['user']['id'])) {
    // Vérification de l'éxistence en base du data_type
    $dataType = new msData();
    if (!$dataType->checkDataTypeExistByName('phonecaptureFingerprint')) {
        $data= array(
          'groupe'=>'user',
          'name'=>'phonecaptureFingerprint',
          'placeholder'=>'indiquer une chaine aléatoire de caractères',
          'label'=>'phonecaptureFingerprint',
          'description'=>'clef utilisateur pour l\'identification des périphériques phonecapture',
          'formType'=>'text',
          'type'=>'base',
          'cat'=>56,
          'fromID'=>$p['user']['id'],
          'durationLife' => 3600,
          'displayOrder' => 0
        );

        $retour=$dataType->createOrUpdateDataType($data);
        if ($retour['status']!='ok') {
            die("Problème de création du data_type phonecaptureFingerprint");
        }
    }

    // Création / renouvellement de phonecaptureFingerprint de l'utilisateur
    $objet = new msObjet();
    $objet->setFromID($p['user']['id']);
    $objet->setToID($p['user']['id']);
    $key=substr(md5(rand(1, 10).rand(20, 99)), 0, 10);
    $objet->createNewObjetByTypeName('phonecaptureFingerprint', $key, 1);
}

msTools::redirRoute('userPhoneCaptureAccess');
