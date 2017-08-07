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
 * Inbox > ajax : voir un message
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template='mailView';

$p['page']['mail']=msSQL::sqlUnique("select id, txtFileName, mailHeaderInfos, txtDatetime, hprimExpediteur, hprimAllSerialize, pjNombre, pjSerializeName, archived, assoToID from inbox where id='".$_POST['mailID']."'");
$p['page']['mail']['hprimAllSerialize']=unserialize($p['page']['mail']['hprimAllSerialize']);
$p['page']['mail']['pjSerializeName']=unserialize($p['page']['mail']['pjSerializeName']);
$p['page']['mail']['mailHeaderInfos']=unserialize($p['page']['mail']['mailHeaderInfos']);

$p['page']['mail']['corps']=msInbox::getMessageBody($p['config']['apicryptCheminInbox'].'/'.$p['page']['mail']['txtFileName']);
//hprim
$p['page']['mail']['bioHprim'] = msHprim::parseSourceHprim($p['page']['mail']['corps']);

$p['page']['mail']['patientsPossibles']=msHprim::getPossiblePatients($p['page']['mail']['hprimAllSerialize'],$p['page']['mail']['assoToID']);

$p['page']['mail']['relativePathForPJ']=str_replace($p['config']['webDirectory'], '', $p['config']['apicryptCheminInbox']);
