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
 * Login : page de login
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template="userParameters";

/************
* Password
************/

$formPassword=new msForm();
$formPassword->setFormIDbyName($p['page']['formIN']='baseUserParametersPassword');
$p['page']['formPassword']=$formPassword->getForm();
$formPassword->addSubmitToForm($p['page']['formPassword'], $class='btn-primary insertBefore');
$formPassword->setFieldAttrAfterwards($p['page']['formPassword'], 'password', ['label'=>'Nouveau mot de passe']);
$formPassword->setFieldAttrAfterwards($p['page']['formPassword'], 'verifPassword', ['label'=>'Confirmation du nouveau mot de passe']);

$p['page']['secret2faUri'] = $iUser->get2faUri();


$p['page']['hasAgenda']=false;
$people= new msPeople();
$usersWithAgenda=$people->getUsersListForService('administratifPeutAvoirAgenda');
if (!is_array($usersWithAgenda) or !array_key_exists($p['user']['id'], $usersWithAgenda)) {
  $p['page']['hasAgenda']=false;
} else {
  $p['page']['hasAgenda']=true;
}
/************
* Agenda
************/
//paramètres de l'agenda
if(is_file($p['homepath'].'config/agendas/agenda'.$p['user']['id'].'.yml')) {
  $p['page']['agenda']=Spyc::YAMLLoad($p['homepath'].'config/agendas/agenda'.$p['user']['id'].'.yml');
} else {
  $p['page']['agenda']=array('minTime'=>'08:00', 'maxTime'=>'20:00', 'slotDuration'=>'00:20',
                            'Lundi'=>array('worked'=> true, 'visible'=>true, 'minTime'=>'09:00', 'maxTime'=>'19:00', 'pauseStart'=>'12:00', 'pauseEnd'=>'13:00'),
                            'Mardi'=>array('worked'=> true, 'visible'=>true, 'minTime'=>'09:00', 'maxTime'=>'19:00', 'pauseStart'=>'12:00', 'pauseEnd'=>'13:00'),
                            'Mercredi'=>array('worked'=> true, 'visible'=>true, 'minTime'=>'09:00', 'maxTime'=>'19:00', 'pauseStart'=>'12:00', 'pauseEnd'=>'13:00'),
                            'Jeudi'=>array('worked'=> true, 'visible'=>true, 'minTime'=>'09:00', 'maxTime'=>'19:00', 'pauseStart'=>'12:00', 'pauseEnd'=>'13:00'),
                            'Vendredi'=>array('worked'=> true, 'visible'=>true, 'minTime'=>'09:00', 'maxTime'=>'19:00', 'pauseStart'=>'12:00', 'pauseEnd'=>'13:00'),
                            'Samedi'=>array('worked'=> true, 'visible'=>true, 'minTime'=>'09:00', 'maxTime'=>'12:00', 'pauseStart'=>'12:00', 'pauseEnd'=>'12:00'),
                            'Dimanche'=>array('worked'=> false, 'visible'=>false, 'minTime'=>'09:00', 'maxTime'=>'12:00', 'pauseStart'=>'12:00', 'pauseEnd'=>'12:00')
                          );
}

/************
* consultations
************/
// types de rendez-vous
if(is_file($p['homepath'].'config/agendas/typesRdv'.$p['user']['id'].'.yml')) {
    $consults=Spyc::YAMLLoad($p['homepath'].'config/agendas/typesRdv'.$p['user']['id'].'.yml');
    $usedTypes=msSQL::sql2tabSimple("SELECT DISTINCT(type) FROM agenda");
    foreach ($consults as $k=>$v) {
        if (is_array($usedTypes) and in_array($k, $usedTypes)) {
            $v['readonly']=true;
        }
        $p['page']['consultations'][str_replace('[','',str_replace(']','',$k))]=$v;
    }
}


/************
* clicRDV
************/

$p['page']['useClicRDV']=$p['config']['agendaService'] == 'clicRDV';

if ($p['page']['useClicRDV']) {
    $typesRdv = new msAgenda;
    $typesRdv->set_userID($p['user']['id']);
    $consults=$typesRdv->getRdvTypes();
    $p['page']['clicRdvConsultsRel']='[]';
    if (count($consults)) {
        $p['page']['clicRdvConsults']=json_encode($consults);
    }

    $formClicRdv=new msForm();
    $formClicRdv->setFormIDbyName($p['page']['formIN']='baseUserParametersClicRdv');

    $options=array();
    $options['clicRdvConsultId']=array();
    foreach ($consults as $k=>$v) {
      $options['clicRdvConsultId'][$k]=$v['descriptif'].' (MedShakeEHR)';
    }

    if(isset($p['config']['clicRdvUserId'])) {
        $preValues=array('clicRdvUserId' => $p['config']['clicRdvUserId']);
        if (!empty($p['config']['clicRdvPassword'])) {
            $preValues['clicRdvPassword']=str_repeat('*',strlen(msConfiguration::getParameterValue('clicRdvPassword', array('id'=>$p['user']['id'], 'module'=>''))));
            if(!empty($p['config']['clicRdvGroupId'])) {
                $preValues['clicRdvGroupId']=$p['config']['clicRdvGroupId'];
                $options['clicRdvGroupId']=array('0'=> explode(':',$p['config']['clicRdvGroupId'])[1]);
            }
            if(!empty($p['config']['clicRdvCalId'])) {
                $preValues['clicRdvCalId']=$p['config']['clicRdvCalId'];
                $options['clicRdvCalId']=array('0'=> explode(':',$p['config']['clicRdvCalId'])[1]);
            }
            if (isset($p['config']['clicRdvConsultId']) and $p['config']['clicRdvConsultId']!='') {
                $p['page']['clicRdvConsultsRel']=json_encode(json_decode($p['config']['clicRdvConsultId'])[1]);
            }
        }
        $formClicRdv->setPrevalues($preValues);
    }
    $formClicRdv->setOptionsForSelect($options);
    $p['page']['formClicRdv']=$formClicRdv->getForm();
    $formClicRdv->addSubmitToForm($p['page']['formClicRdv'], $class='btn-primary insertBefore');

}

/************
* LAP
************/

// liste des SAMs gérés
$lapSams = new msLapSAM;
$lapSams->getSamXmlFileContent();
$p['page']['lap']['samsList']=$lapSams->getSamListInXml();

// Paramètres LAP de l'utilisateur
$p['page']['lap']['params']=msConfiguration::getCatParametersForUser('LAP', array('id'=>$p['user']['id'], 'module'=>''));

// Types des prescriptions types
 $p['page']['typesPrescriptionsList']=array('lap'=>'Prescriptions LAP', 'nonlap'=>'Prescriptions hors LAP');

 /************
 * Ergonomie et design
 ************/

 // Paramètres Ergonomie de l'utilisateur
 $p['page']['ergonomie']['params']=msConfiguration::getCatParametersForUser('Ergonomie et design', array('id'=>$p['user']['id'], 'module'=>$p['user']['module']));

 /************
 * Administratif
 ************/

 // Paramètres Règlements
 $p['page']['reglement']['params']=msConfiguration::getCatParametersForUser('Règlements', array('id'=>$p['user']['id'], 'module'=>$p['user']['module']));
 $p['page']['reglement']['params']['administratifReglementFormulaires']=explode(',',$p['page']['reglement']['params']['administratifReglementFormulaires']);

 //Correspondances codeProf NGAP
 $p['page']['codeProf']=msReglementActe::getCodeProfLabel();


 $dataReg=new msData;
 $p['page']['reglement']['possibleForms']=$dataReg->getDataTypesFromCatName('porteursReglement', ['label', 'name', 'module', 'description'], 'module, description');
