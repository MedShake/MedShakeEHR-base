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
 * Config > action : éditer un formulaire
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

$form=new msForm();
$form->setFormID($_POST['id']);
$formdata=$form->getFormFromDb();
$cleanForm=$form->cleanForm($_POST['yamlStructure']);

$data=array(
    'id'=>$_POST['id'],
    'module'=>$_POST['module'],
    'internalName'=>$_POST['internalName'],
    'name'=>$_POST['name'],
    'description'=>$_POST['description'],
    'cat'=>$_POST['cat'],
    'yamlStructure'=>$cleanForm,
    'formAction'=>$_POST['formAction'],
    'printModel'=>$_POST['printModel'],
    'cda'=>$_POST['cda'],
    'javascript'=>$_POST['javascript'],
    'options'=>$_POST['options'],
);

msSQL::sqlInsert('forms', $data);

echo json_encode("ok");
