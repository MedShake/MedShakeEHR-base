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
 * Inbox : la page inbox
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */

$debug = '';
$template = "inbox";

$apicryptInboxMailForUserID = [];

if (!empty($p['config']['apicryptInboxMailForUserID'])) {
	$apicryptInboxMailForUserID = explode(',', $p['config']['apicryptInboxMailForUserID']);
	$apicryptInboxMailForUserID[] = $p['user']['id'];
} else {
	$apicryptInboxMailForUserID[] = $p['user']['id'];
}

if ($p['config']['designInboxMailsSortOrder'] == 'asc') {
	$p['page']['sort'] = 'asc';
} else {
	$p['page']['sort'] = 'desc';
}

$sqlImplode = msSQL::sqlGetTagsForWhereIn($apicryptInboxMailForUserID, 'mailForUserID');

if ($mails = msSQL::sql2tab("SELECT id, txtFileName, DATE_FORMAT(txtDatetime, '%Y-%m-%d') as day, hprimIdentite, hprimExpediteur, hprimAllSerialize, pjNombre, archived
    from inbox
    where archived!='y' and mailForUserID in (" . $sqlImplode['in'] . ")
    order by txtDatetime " . $p['page']['sort'] . ", txtNumOrdre " . $p['page']['sort'], $sqlImplode['execute'])) {
	foreach ($mails as $mail) {
		$mail['isValidHprim'] = msHprim::checkIfValidHprimHeaderData(unserialize($mail['hprimAllSerialize']));
		$p['page']['inbox']['mails'][$mail['day']][] = $mail;
	}
}
