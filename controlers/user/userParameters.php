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

$form = new msForm();
$form->setFormIDbyName($p['page']['formIN']='userParameters');
$p['page']['form']=$form->getForm();

if(isset($p['config']['clicRdvUserId'])) {
    $p['page']['form']['structure'][1][1]['elements'][1]['value']['preValue']=$p['config']['clicRdvUserId'];
    if (!empty($p['config']['clicRdvPassword'])) {
        $p['page']['form']['structure'][1][1]['elements'][2]['value']['preValue']='********';

        if(isset($p['config']['clicRdvGroupId'])) {
            $p['page']['form']['structure'][1][1]['elements'][3]['value']['formValues'][$p['config']['clicRdvGroupId']]=explode(':',$p['config']['clicRdvGroupId'])[1];
            $p['page']['form']['structure'][1][1]['elements'][3]['value']['preValue']=$p['config']['clicRdvGroupId'];
        }
        if(isset($p['config']['clicRdvCalId'])) {
            $p['page']['form']['structure'][1][1]['elements'][4]['value']['formValues'][$p['config']['clicRdvCalId']]=explode(':',$p['config']['clicRdvCalId'])[1];
            $p['page']['form']['structure'][1][1]['elements'][4]['value']['preValue']=$p['config']['clicRdvCalId'];
        }
    }
}

