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
 */

// le formulaire latéral ATCD
$formLat = new msForm();
$p['page']['formNumberbaseATCD']=$p['page']['listeForms'][]=$formLat->setFormIDbyName('baseATCD');
$formLat->getPrevaluesForPatient($match['params']['patient']);
$p['page']['formLat']=$formLat->getForm();

//formulaire de synthèse patient
$formSynthese = new msForm();
$p['page']['formNumberbaseSynthese']=$p['page']['listeForms'][]=$formSynthese->setFormIDbyName('baseSynthese');
$formSynthese->getPrevaluesForPatient($match['params']['patient']);
$p['page']['formSynthese']=$formSynthese->getForm();

//types de consultation de base.
$typeCsBase=new msData;
$p['page']['typeCsBase']=$typeCsBase->getDataTypesFromCatName('csBase', array('id','label', 'formValues'));
