<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * Config : gestion des templates de paramétrage utilisateur
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 */

 //admin uniquement
if (!msUser::checkUserIsAdmin()) {
    $template="forbidden";
    return;
}

$template="configUserTemplates";
$debug='';

$p['page']['repertoireUserTemplates'] = $homepath.'config/userTemplates/';
msTools::checkAndBuildTargetDir($p['page']['repertoireUserTemplates']);

//test autorisation de lecture du dossier template
if (is_readable($p['page']['repertoireUserTemplates'])) {
    $p['page']['templatesDirAutorisationLecture'] = true;
} else {
    $p['page']['templatesDirAutorisationLecture'] = false;
}

//test autorisation d'écriture du dossier template
if (is_writable($p['page']['repertoireUserTemplates'])) {
    $p['page']['templatesDirAutorisationEcriture'] = true;
} else {
    $p['page']['templatesDirAutorisationEcriture'] = false;
}

//templates si lecture répertoire ok
if ($p['page']['templatesDirAutorisationLecture']) {

     //scan du répertoire
    if ($listeTemplates=array_diff(scandir($p['page']['repertoireUserTemplates']), array('..', '.'))) {
        foreach ($listeTemplates as $k=>$tptes) {
            $p['page']['listeTemplates'][$tptes]['file']=$tptes;
            if (is_readable($p['page']['repertoireUserTemplates'].$tptes)) {
                $p['page']['listeTemplates'][$tptes]['autorisationLecture'] = true;
            } else {
                $p['page']['listeTemplates'][$tptes]['autorisationLecture'] = false;
            }
            if (is_writable($p['page']['repertoireUserTemplates'].$tptes)) {
                $p['page']['listeTemplates'][$tptes]['autorisationEcriture'] = true;
            } else {
                $p['page']['listeTemplates'][$tptes]['autorisationEcriture'] = false;
            }
        }

    }
}
