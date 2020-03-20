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

$formIN=$_POST['formIN'];

//construc validation rules
$form = new msFormValidation();
$form->setformIDbyName($formIN);
$form->setPostdatas($_POST);
//$validation=$form->getValidation();

$setCRDV=false;

$objet = new msObjet();
$objet->setFromID($p['user']['id']);
$objet->setToID($p['user']['id']);

header('Content-Type: application/json');

msConfiguration::setUserParameterValue('clicRdvUserId', $_POST['p_clicRdvUserId'], $p['user']['id']);
if (empty($p['config']['clicRdvPassword']) or $_POST['p_clicRdvPassword']!=str_repeat('*',strlen(msConfiguration::getParameterValue('clicRdvPassword', array('id'=>$p['user']['id'], 'module'=>''))))) {
    msConfiguration::setUserParameterValue('clicRdvPassword', $_POST['p_clicRdvPassword'], $p['user']['id']);
}
if (!empty($_POST['p_clicRdvGroupId']) and $_POST['p_clicRdvGroupId']!=$p['config']['clicRdvGroupId']) {
    msConfiguration::setUserParameterValue('clicRdvGroupId', $_POST['p_clicRdvGroupId'], $p['user']['id']);
}
if (!empty($_POST['p_clicRdvCalId']) and $_POST['p_clicRdvGroupId']!=$p['config']['clicRdvCalId']) {
    msConfiguration::setUserParameterValue('clicRdvCalId', $_POST['p_clicRdvCalId'], $p['user']['id']);
}

$consult = array();
for ($i=0; !empty($_POST['p_clicRdvConsultId'.$i]); $i++) {
    $exp=explode(':', $_POST['p_clicRdvConsultId'.$i]);
    $consult[0][$exp[0]]=array($exp[1], $exp[2]);
    $consult[1][$exp[1]]=array($exp[0], $exp[2]);
}

if (!empty($consult)) {
    msConfiguration::setUserParameterValue('clicRdvConsultId', json_encode($consult), $p['user']['id']);
}

header('Content-Type: application/json');
echo json_encode(array('status'=>'success'));
