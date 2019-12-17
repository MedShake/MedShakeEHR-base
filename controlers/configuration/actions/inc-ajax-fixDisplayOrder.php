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
 * Config : fixer les displayOrder de data types en fonction de l'odre d'apparition dans un form
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if (!msUser::checkUserIsAdmin()) {die("Erreur: vous n'êtes pas administrateur");}

if(!isset($_POST['formid']) or !is_numeric($_POST['formid'])) die("Error");

$form = new msForm;
$form->setFormID($_POST['formid']);
$yaml=$form->getFormRawData(['yamlStructure'])['yamlStructure'];
preg_match_all("#\s+ - (?!template|label)([\w]+)#i", $yaml, $matchIN);
if(!empty($matchIN[1])) {
  foreach($matchIN[1] as $k=>$v) {
    msSQL::sqlQuery("update data_types set displayOrder='".($k+1)."' where name='".$v."' limit 1");
  }
}
exit(json_encode(array('ok')));
