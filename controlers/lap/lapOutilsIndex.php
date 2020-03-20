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
 * Login : index de la page LAP pour les outils liés, non spécifiques à un patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 $debug='';
 $template="lapOutilsIndex";

 // version de la BdM
 $lap = new msLap;
 $p['page']['infosTheriaque']=$lap->getTheriaqueInfos();

 //version de MedShake
 $p['page']['modules']=msModules::getInstalledModulesNamesAndVersions();

 // liste des SAMs gérés
 $lapSams = new msLapSAM;
 $lapSams->getSamXmlFileContent();
 $p['page']['lap']['samsList']=$lapSams->getSamListInXml();
