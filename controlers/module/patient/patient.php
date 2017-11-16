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
 * Patient : la page du dossier patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @edited fr33z00 <https://www.github.com/fr33z00>
 */

$forms = msSQL::sql2tabKey("SELECT internalName FROM forms WHERE groupe='medical'", "internalName");
foreach ($forms as $k=>$v) {
  //noms des scripts JS (potentiels) associés aux formulaires
  $p['page']['formName_'.$k]=$p['page']['listeForms'][]=$k;
  //données de formulaires
  $form = new msForm();
  $form->setFormIDbyName($k);
  $form->getPrevaluesForPatient($match['params']['patient']);
  $p['page']['formData_'.$k]=$form->getForm();
}

//données de consultation
$typesCs = msSQL::sql2tabKey("SELECT name FROM `data_cat` WHERE groupe='typecs'", "name");
foreach ($typesCs as $k=>$v){
  $typeCs=new msData;
  $p['page']['typeCs_'.$k]=$typeCs->getDataTypesFromCatName($k, array('id','label', 'formValues'));
}
