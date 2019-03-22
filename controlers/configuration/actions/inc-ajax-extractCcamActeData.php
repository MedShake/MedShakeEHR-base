<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Config > ajax : extraire les infos sur un acte CCAM
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$scrap = new msCcamNgapApi;
$scrap->setActeCode($_POST['acteCode']);
if(empty($_POST['activiteCode'])) $_POST['activiteCode']=1;
$scrap->setActiviteCode($_POST['activiteCode']);
if(empty($_POST['phaseCode'])) $_POST['phaseCode']=0;
$scrap->setPhaseCode($_POST['phaseCode']);
$scrap->setActeType($_POST['acteType']);
$scrap->setActeCodeProf($_POST['codeProf']);
echo json_encode($scrap->getActeData());
