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
 * Config : listing des backups dans le répertoire dédié
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//admin uniquement
if (!msUser::checkUserIsAdmin()) {
    $template="forbidden";
} else {
    $template="configBackups";
    $debug='';

    //test autorisation de lecture du dossier template
    if (is_readable($p['config']['backupLocation'])) {
        $p['page']['backupsDirAutorisationLecture'] = true;
    } else {
        $p['page']['backupsDirAutorisationLecture'] = false;
    }

    //test autorisation d'écriture du dossier template
    if (is_writable($p['config']['backupLocation'])) {
        $p['page']['backupsDirAutorisationEcriture'] = true;
    } else {
        $p['page']['backupsDirAutorisationEcriture'] = false;
    }

    if ($p['page']['backupsDirAutorisationLecture'] == true) {
        $files = array_diff(scandir($p['config']['backupLocation']), array('.', '..'));

        if ($files) {
            foreach ($files as $k=>$v) {
                $p['page']['backups']['files'][$k]=array(
                   'name'=>$v,
                   'size'=>msTools::getFileSize($p['config']['backupLocation'].$v)
                 );
            }
        }
    }
}
