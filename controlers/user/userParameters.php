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
 */

$debug='';
$template="userParameters";

$p['page']['useClicRDV']=$p['config']['agendaService'];
if ($p['page']['useClicRDV'] != 'clicRDV') {
    return;
}

$form = new msForm();
$form->setFormIDbyName($p['page']['formIN']='baseUserParameters');

if(isset($p['config']['clicRdvUserId'])) {
    $preValues=array('p_clicRdvUserId' => $p['config']['clicRdvUserId']);
    if (!empty($p['config']['clicRdvPassword'])) {
        $preValues['p_clicRdvPassword']='********';
        if(!empty($p['config']['clicRdvGroupId'])) {
            $preValues['p_clicRdvGroupId']=$p['config']['clicRdvGroupId'];
        }
        if(!empty($p['config']['clicRdvCalId'])) {
            $preValues['p_clicRdvCalId']=$p['config']['clicRdvCalId'];
        }
    }
    $form->setPrevalues($preValues);
}

$p['page']['form']=$form->getForm();

$p['page']['clicRdvConsultsRel']='[]';
$consults=msAgenda::getRdvTypes($p['user']['id']);
$p['page']['form']['structure'][1][1]['elements'][5]['value']['formValues']=array();
foreach ($consults as $k=>$v) {
    $p['page']['form']['structure'][1][1]['elements'][5]['value']['formValues'][$k]=$v['descriptif'].' (MedShakeEHR)';
}
if (count($consults)) {
    $p['page']['clicRdvConsults']=json_encode($consults);
}

if(isset($p['config']['clicRdvUserId'])) {
    if (!empty($p['config']['clicRdvPassword'])) {
        if(!empty($p['config']['clicRdvGroupId'])) {
            $p['page']['form']['structure'][1][1]['elements'][3]['value']['formValues'][$p['config']['clicRdvGroupId']]=explode(':',$p['config']['clicRdvGroupId'])[1];
        }
        if(!empty($p['config']['clicRdvCalId'])) {
            $p['page']['form']['structure'][1][1]['elements'][4]['value']['formValues'][$p['config']['clicRdvCalId']]=explode(':',$p['config']['clicRdvCalId'])[1];
        }
        if (isset($p['config']['clicRdvConsultId']) and $p['config']['clicRdvConsultId']!='') {
            $p['page']['clicRdvConsultsRel']=json_encode(json_decode($p['config']['clicRdvConsultId'])[1]);
        }
    }
}
