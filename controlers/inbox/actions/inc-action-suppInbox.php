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
 * Inbox > action : retirer de la inbox le message déjà classé
 * (déjà archivé à la pahase de classement)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if(!is_numeric($_POST['mailID'])) die;

if ($data=msSQL::sqlUnique("select txtFileName, hprimAllSerialize, pjSerializeName  from inbox where id='".msSQL::cleanVar($_POST['mailID'])."' and archived='c' ")) {
    $pj['pjSerializeName']=unserialize($data['pjSerializeName']);
    $sourceFolder = str_replace('.txt', '.f', $data['txtFileName']);

    if (count($pj['pjSerializeName'])>0) {
        foreach ($pj['pjSerializeName'] as $file) {

            $source=$p['config']['apicryptCheminInbox'].$sourceFolder.'/'.$file;

            if (is_file($source)) {
                unlink($source);
            }
        }

        rmdir($p['config']['apicryptCheminInbox'].$sourceFolder.'/');
    }

    unlink($p['config']['apicryptCheminInbox'].$data['txtFileName']);
    msSQL::sqlQuery("update inbox set archived='y' where id='".msSQL::cleanVar($_POST['mailID'])."' limit 1");
}

msTools::redirection('/inbox/');
