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
 * Config : gérer les templates de production de PDF
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

 //admin uniquement
if (!msUser::checkUserIsAdmin()) {
    $template="forbidden";
    return;
}

$template="configTemplatesPDF";
$debug='';

//utilisateurs ayant un repertoire de templates spécifique
$p['page']['templatesDirUsers']=msPeople::getUsersWithSpecificParam('templatesPdfFolder');

// si user
if (isset($match['params']['userID'])) {
    $user=array('id'=>$match['params']['userID'], 'module'=>'');
    $p['page']['repertoireTemplatesPDF']=msConfiguration::getParameterValue('templatesPdfFolder', $user);
    $p['page']['selectUser']=$match['params']['userID'];
} else {
    $p['page']['repertoireTemplatesPDF']=msConfiguration::getParameterValue('templatesPdfFolder');
}

//test autorisation de lecture du dossier template
if (is_readable($p['page']['repertoireTemplatesPDF'])) {
    $p['page']['templatesDirAutorisationLecture'] = true;
} else {
    $p['page']['templatesDirAutorisationLecture'] = false;
}

//test autorisation d'écriture du dossier template
if (is_writable($p['page']['repertoireTemplatesPDF'])) {
    $p['page']['templatesDirAutorisationEcriture'] = true;
} else {
    $p['page']['templatesDirAutorisationEcriture'] = false;
}

//templates si lecture répertoire ok
if ($p['page']['templatesDirAutorisationLecture']) {

     //scan du répertoire
    if ($listeTemplates=array_diff(scandir($p['page']['repertoireTemplatesPDF']), array('..', '.'))) {
        foreach ($listeTemplates as $k=>$tptes) {
            if(pathinfo($tptes)['extension'] == "twig") {
              $typeFichier = 'listeTemplates';
            } else {
              $typeFichier = 'listeAutres';
            }

            $p['page'][$typeFichier][$tptes]['file']=$tptes;
            if (is_readable($p['page']['repertoireTemplatesPDF'].$tptes)) {
                $p['page'][$typeFichier][$tptes]['autorisationLecture'] = true;
            } else {
                $p['page'][$typeFichier][$tptes]['autorisationLecture'] = false;
            }
            if (is_writable($p['page']['repertoireTemplatesPDF'].$tptes)) {
                $p['page'][$typeFichier][$tptes]['autorisationEcriture'] = true;
            } else {
                $p['page'][$typeFichier][$tptes]['autorisationEcriture'] = false;
            }
        }

        //extraction des forms liés à un templates
        if ($formsWithTemplates=msSQL::sql2tabKey("select concat(printModel, '.html.twig') as fichier, name, id from forms where printModel!='' ", 'fichier')) {
            foreach ($formsWithTemplates as $k=>$v) {
                if (isset($p['page']['listeTemplates'][$k])) {
                  $v['type']='Formulaire';
                  $p['page']['listeTemplates'][$k]['linkedTo'][]=$v;
                }
            }
        }

        //recherche de template lié à un paramètre de config (niveau config par défaut)
        foreach ($p['config'] as $k=>$v) {
          if(is_string($v)) {
            if (isset($p['page']['listeTemplates'][$v])) {
                $p['page']['listeTemplates'][$v]['linkedTo'][]=array('type'=>'Paramètre de la configuration de base', 'name'=>$k);
            }
          }
        }
        // templates liés aux certificats
        $certificats=new msData();
        if ($modelesCertif=$certificats->getDataTypesFromCatName('catModelesCertificats', ['formValues','label'])) {
            foreach ($modelesCertif as $v) {
                if (isset($p['page']['listeTemplates'][$v['formValues'].'.html.twig'])) {
                    $p['page']['listeTemplates'][$v['formValues'].'.html.twig']['linkedTo'][]=array('type'=>'Certificat', 'name'=>$v['label']);
                }
            }
        }
        // templates liés aux courriers
        if ($modelesCourrier=$certificats->getDataTypesFromCatName('catModelesCourriers', ['formValues','label'])) {
            foreach ($modelesCourrier as $v) {
                if (isset($p['page']['listeTemplates'][$v['formValues'].'.html.twig'])) {
                    $p['page']['listeTemplates'][$v['formValues'].'.html.twig']['linkedTo'][]=array('type'=>'Certificat', 'name'=>$v['label']);
                }
            }
        }
        // templates liés aux documents à signer
        if ($modelesDocASigner=$certificats->getDataTypesFromCatName('catModelesDocASigner', ['formValues','label'])) {
            foreach ($modelesDocASigner as $v) {
                if (isset($p['page']['listeTemplates'][$v['formValues'].'.html.twig'])) {
                    $p['page']['listeTemplates'][$v['formValues'].'.html.twig']['linkedTo'][]=array('type'=>'Document à signer', 'name'=>$v['label']);
                }
            }
        }
    }
}
