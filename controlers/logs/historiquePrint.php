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
 * Logs : présente l'historique d'impression d'un document
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$debug='';
$template="historiquePrint";

$objet = new msObjet();
$p['page']['patient'] = $objet->getObjetDataByID($match['params']['objetID'], ['toID']);


if ($p['page']['print']=msSQL::sql2tab("select id, creationDate, value, toID from printed where objetID='".$match['params']['objetID']."' order by creationDate desc")) {
    foreach ($p['page']['print'] as $k=>$v) {
        $p['page']['print'][$k]['value'] = msTools::cutHtmlHeaderAndFooter($v['value']); 
    }
}
