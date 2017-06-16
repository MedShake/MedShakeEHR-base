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
 * Logs : présente l'historique d'envoi par mail d'un document
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template="historiqueMailSend";

if ($mailsListe=msSQL::sql2tabSimple("select id from objets_data where instance='".$match['params']['objetID']."' and typeID=".msData::getTypeIDFromName("mailPorteur")." order by creationDate desc")) {
    $mailsElements=msSQL::sql2tab("select o.value, o.typeID, o.creationDate, o.instance, o.toID, t.name
    from objets_data as o
    left join data_types as t on o.typeID=t.id
    where o.instance in (".implode(',', $mailsListe).") ");

    foreach ($mailsElements as $k=>$v) {
        $p['page']['patientID']=$v['toID'];
        $p['page']['mailListe'][$v['instance']][$v['name']]=$v['value'];
        $p['page']['mailListe'][$v['instance']]['creationDate']=$v['creationDate'];
    }
}
