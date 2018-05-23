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
 * Paramètres utilisateur > > ajax : créer une catégorie de prescriptions types
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//check & validate datas
$gump=new GUMP();
$_POST = $gump->sanitize($_POST);

unset($_POST['groupe']);

if (isset($_POST['id'])) {
    $gump->validation_rules(array(
            'id'=> 'required|numeric',
            'name'=> 'required|alpha_numeric',
            'label'     => 'required',
        ));
} else {
    $gump->validation_rules(array(
            'name'=> 'required|alpha_numeric|presence_bdd,prescriptions_cat',
            'label'     => 'required',
        ));
}

$validated_data = $gump->run($_POST);

if ($validated_data === false) {
    $return['status']='failed';
    $return['msg']=$gump->get_errors_array();
} else {
    $validated_data['fromID']=$p['user']['id'];
    $validated_data['creationDate']=date("Y-m-d H:i:s");

    if (msSQL::sqlInsert('prescriptions_cat', $validated_data) > 0) {
        $return['status']='ok';
    } else {
        $return['status']='failed';
        $return['msg']=mysqli_error($mysqli);
    }
}
echo json_encode($return);
