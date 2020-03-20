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
 * Config : gérer les clefs apicrypt
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

 //admin uniquement
if (!msUser::checkUserIsAdmin()) {
    $template="forbidden";
    return;
}
$template="configApicryptClefs";
$debug='';

//utilisateurs ayant un repertoire de clefs spécifiques
$p['page']['apicryptClefsUsers']=msPeople::getUsersWithSpecificParam('apicryptCheminVersClefs');

// si user
if (isset($match['params']['userID'])) {
    $user=array('id'=>$match['params']['userID'], 'module'=>'');
    $p['page']['repertoireClefs']=msConfiguration::getParameterValue('apicryptCheminVersClefs', $user).'Clefs/';
    $p['page']['selectUser']=$match['params']['userID'];
} else {
    $p['page']['repertoireClefs']=msConfiguration::getParameterValue('apicryptCheminVersClefs').'Clefs/';
}

//test autorisation de lecture du dossier clef
if (is_readable($p['page']['repertoireClefs'])) {
    $p['page']['listeClefsAutorisationLecture'] = true;
} else {
    $p['page']['listeClefsAutorisationLecture'] = false;
}

//test autorisation d'écriture' du dossier clef
if (is_writable($p['page']['repertoireClefs'])) {
    $p['page']['listeClefsAutorisationEcriture'] = true;
} else {
    $p['page']['listeClefsAutorisationEcriture'] = false;
}

//clefs si lecture répertoire ok
if ($p['page']['listeClefsAutorisationLecture']) {
    if ($listeClefs=array_diff(scandir($p['page']['repertoireClefs']), array('..', '.'))) {
        foreach ($listeClefs as $k=>$clef) {
            if(is_dir($p['page']['repertoireClefs'].$clef)) {
              continue;
            }
            $p['page']['listeClefs'][$k]['file']=$clef;
            $p['page']['listeClefs'][$k]['filesize']=msTools::getFileSize($p['page']['repertoireClefs'].$clef, 2);
            $p['page']['listeClefs'][$k]['fileInfo']=pathinfo($p['page']['repertoireClefs'].$clef);

            if (is_readable($p['page']['repertoireClefs'].$clef)) {
               $p['page']['listeClefs'][$k]['autorisationLecture'] = true;
            } else {
               $p['page']['listeClefs'][$k]['autorisationLecture'] = false;
            }
            if (is_writable($p['page']['repertoireClefs'].$clef)) {
               $p['page']['listeClefs'][$k]['autorisationEcriture'] = true;
            } else {
               $p['page']['listeClefs'][$k]['autorisationEcriture'] = false;
            }
        }
    }
}
