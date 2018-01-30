<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00>
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
 * enregistrement des paramètres utilisateur
 *
 * @author fr33z00 <https://github.com/fr33z00>
 */

if ($p['config']['agendaService'] != 'clicRDV') {
    return;
}

unset($_SESSION['formErreursReadable'], $_SESSION['formErreurs'], $_SESSION['formValues']);

$formIN=$_POST['formIN'];

//construc validation rules
$form = new msForm();
$form->setformIDbyName($formIN);
$form->setPostdatas($_POST);
//$validation=$form->getValidation();

$setCRDV=false;

$objet = new msObjet();
$objet->setFromID($p['user']['id']);
$objet->setToID($p['user']['id']);


if (!empty($_POST['p_clicRdvUserId']) and $_POST['p_clicRdvPassword']!='********') {
    $clicRDV = new msClicRDV();
    $clicRDV->setUserPwd($_POST['p_clicRdvUserId'], $_POST['p_clicRdvPassword']);
    if (empty($_POST['p_clicRdvPassword'])) {
        unset($_SESSION['form'][$formIN]);
        $changeMdp=false;
        $_SESSION['form'][$formIN]['validationErrorsMsg'][]='Le champ de mot de passe clicRDV est vide.';
        msTools::redirRoute('userParameters');
    } elseif ($clicRDV->getGroups()===false) {
        unset($_SESSION['form'][$formIN]);
        $changeMdp=false;
        $_SESSION['form'][$formIN]['validationErrorsMsg'][]='Une erreur est survenue durant la tentative d\'accès à clicRDV. Vérifiez vos identifiants.';
        msTools::redirRoute('userParameters');
    } else {
        $setCRDV=true;
    }
} else{
    if ($data=$objet->getLastObjetByTypeName('clicRdvUserId')) {
        msSQL::sqlQuery("UPDATE objets_data SET deleted='y', deletedByID='".$p['user']['id']."' where id='".$data['id']."'");
    }
    if ($data=$objet->getLastObjetByTypeName('clicRdvPassword')) {
        msSQL::sqlQuery("UPDATE objets_data SET deleted='y', deletedByID='".$p['user']['id']."' where id='".$data['id']."'");
    }
}


if ($setCRDV) {
    $objet->createNewObjetByTypeName('clicRdvUserId', $_POST['p_clicRdvUserId']);
    $passID=$objet->createNewObjetByTypeName('clicRdvPassword', $_POST['p_clicRdvPassword']);
    msSQL::sqlQuery("UPDATE objets_data set value=HEX(AES_ENCRYPT('".$_POST['p_clicRdvPassword']."',@password)) WHERE id='".$passID."' limit 1");
}

if (!empty($_POST['p_clicRdvGroupId']) and $_POST['p_clicRdvGroupId']!=$p['config']['clicRdvGroupId']) {
    $objet->createNewObjetByTypeName('clicRdvGroupId', $_POST['p_clicRdvGroupId']);
}
if (!empty($_POST['p_clicRdvCalId']) and $_POST['p_clicRdvGroupId']!=$p['config']['clicRdvCalId']) {
    $objet->createNewObjetByTypeName('clicRdvCalId', $_POST['p_clicRdvCalId']);
}


$consult = array();
for ($i=0; !empty($_POST['p_clicRdvConsultId'.$i]); $i++) {
    $exp=explode(':', $_POST['p_clicRdvConsultId'.$i]);
    $consult[0][$exp[0]]=array($exp[1], $exp[2]);
    $consult[1][$exp[1]]=array($exp[0], $exp[2]);
}

if (!empty($consult)) {
    $objet->createNewObjetByTypeName('clicRdvConsultId', json_encode($consult));
}
msTools::redirRoute('/');
