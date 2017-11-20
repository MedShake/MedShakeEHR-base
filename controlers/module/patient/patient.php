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
foreach ($forms as $k=>$v)
  if ($k != 'baseConsult' && $k != 'baseImportExternal')
    $p['page']['formName_'.$k]=$p['page']['listeForms'][]=$k;

$form_baseATCD = new msForm();
$form_baseATCD->setFormIDbyName('baseATCD');
$form_baseATCD->getPrevaluesForPatient($match['params']['patient']);
$p['page']['formData_baseATCD']=$form_baseATCD->getForm();

$form_baseSynthese = new msForm();
$form_baseSynthese->setFormIDbyName('baseSynthese');
$form_baseSynthese->getPrevaluesForPatient($match['params']['patient']);
$p['page']['formData_baseSynthese']=$form_baseSynthese->getForm();

$typeCs_csBase = new msData;
$p['page']['typeCs_csBase']=$typeCs_csBase->getDataTypesFromCatName('csBase', array('id','label', 'formValues'));

