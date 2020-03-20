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
 * Patient : accès aux tabs du dossier patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 $debug='';
 $tab=$match['params']['tab'];

 $acceptedModes=array(
     'tabDicomStudiesList', // liste des studies dicom du patient
     'tabDicomStudyView', // voir une étude dicom
     'tabLiensPatient', // relation patient / patient et praticien
     'tabLAP', // lap
     'tabBio', // bio hprim
 );

 if (!in_array($tab, $acceptedModes)) {
     die;
 }

 //inclusion
 if(is_file($p['homepath'].'controlers/patient/tabs/'.$tab.'.php')) {
    include($p['homepath'].'controlers/patient/tabs/'.$tab.'.php');
 }
