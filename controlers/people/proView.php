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
 * people : voir les infos sur un pro 
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template='proView';

$p['page']['proDataID']=$match['params']['proID'];

$patient = new msPeople();
$patient->setToID($p['page']['proDataID']);
$p['page']['proData']=$patient->getSimpleAdminDatas();

$labels = new msData();
$p['page']['proDataLabel'] = $labels->getLabelFromTypeID(array_keys($p['page']['proData']));
