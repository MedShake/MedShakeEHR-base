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
 *
 * SQLPREPOK
 */

$debug = '';
$template = "historiqueMailSend";

$name2typeID = new msData();
$name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'mailPorteur']);

if ($mailsListe = msSQL::sql2tabSimple("SELECT id from objets_data where instance= :instance and typeID=:typeID order by creationDate desc", ['instance' => $match['params']['objetID'], 'typeID' => $name2typeID['mailPorteur']])) {

	$sqlImplode = msSQL::sqlGetTagsForWhereIn($mailsListe, 'inst');
	$mailsElements = msSQL::sql2tab("SELECT o.value, o.typeID, o.creationDate, o.instance, o.toID, t.name, o.fromID
    from objets_data as o
    left join data_types as t on o.typeID=t.id
    where o.instance in (" . $sqlImplode['in'] . ") ", $sqlImplode['execute']);

	foreach ($mailsElements as $k => $v) {
		$p['page']['patientID'] = $v['toID'];
		$p['page']['mailListe'][$v['instance']][$v['name']] = $v['value'];
		$p['page']['mailListe'][$v['instance']]['creationDate'] = $v['creationDate'];
		$p['page']['mailListe'][$v['instance']]['expediteurID'] = $v['fromID'];
	}
}

$p['page']['expediteurs'] = msSQL::sql2tabKey("SELECT m.fromID as id, concat(p.value, ' ', n.value) as identite
  from objets_data as m
  left join objets_data as n on n.toID=m.fromID and n.typeID = :lastname and n.outdated = '' and n.deleted =''
  left join objets_data as p on p.toID=m.fromID and p.typeID = :firstname and p.outdated = '' and p.deleted =''
  where m.typeID = :mailPorteur
  group by m.fromID, p.value, n.value
  order by n.value", "id", "identite", $name2typeID);
