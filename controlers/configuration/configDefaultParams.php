<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00>
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
 * Config : gérer les paramètres de configuration globaux
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

//admin uniquement
if (!msUser::checkUserIsAdmin()) {
    $template="forbidden";
    return;
}
$debug='';
$template='configDefaultParams';
// extraction des paramètres réels si besoin (host / cookieDomain qui peuvent changer si IP dynamique par exemple)
$p['configInYml']=Spyc::YAMLLoad($homepath.'config/config.yml');
$p['page']['params']=array(
  array('cat'=>'Serveur','type'=>'texte','name'=>'protocol','value'=>$p['config']['protocol'],'readonly'=>true),
  array('cat'=>'Serveur','type'=>'texte','name'=>'host','value'=>$p['configInYml']['host'],'readonly'=>true),
  array('cat'=>'Serveur','type'=>'texte','name'=>'urlHostSuffixe','value'=>$p['config']['urlHostSuffixe'],'readonly'=>true),
  array('cat'=>'Serveur','type'=>'texte','name'=>'webDirectory','value'=>$p['config']['webDirectory'],'readonly'=>true),
  array('cat'=>'Serveur','type'=>'texte','name'=>'stockageLocation','value'=>$p['config']['stockageLocation']),
  array('cat'=>'Serveur','type'=>'texte','name'=>'backupLocation','value'=>$p['config']['backupLocation']),
  array('cat'=>'Serveur','type'=>'texte','name'=>'workingDirectory','value'=>$p['config']['workingDirectory']),
  array('cat'=>'Serveur','type'=>'texte','name'=>'cookieDomain','value'=>$p['configInYml']['cookieDomain']),
  array('cat'=>'Serveur','type'=>'texte','name'=>'cookieDuration','value'=>$p['config']['cookieDuration']),
  array('cat'=>'Serveur','type'=>'texte','name'=>'fingerprint','value'=>$p['config']['fingerprint'],'readonly'=>true),

  array('cat'=>'Serveur MySQL','type'=>'texte','name'=>'sqlServeur','value'=>$p['config']['sqlServeur'],'readonly'=>true),
  array('cat'=>'Serveur MySQL','type'=>'texte','name'=>'sqlBase','value'=>$p['config']['sqlBase'],'readonly'=>true),
  array('cat'=>'Serveur MySQL','type'=>'texte','name'=>'sqlUser','value'=>$p['config']['sqlUser'],'readonly'=>true),
  array('cat'=>'Serveur MySQL','type'=>'texte','name'=>'sqlPass','value'=>$p['config']['sqlPass'],'readonly'=>true),
  array('cat'=>'Serveur MySQL','type'=>'texte','name'=>'sqlVarPassword','value'=>$p['config']['sqlVarPassword'],'readonly'=>true),

  array('cat'=>'Service d\'affichage','name'=>'templatesFolder','value'=>$p['config']['templatesFolder'],'type'=>'texte'),
  array('cat'=>'Service d\'affichage','name'=>'twigEnvironnementCache','value'=>$p['config']['twigEnvironnementCache']?:'false','type'=>'false/dossier','description'=>'ex: /tmp/templates_cache/'),
  array('cat'=>'Service d\'affichage','name'=>'twigEnvironnementAutoescape','value'=>$p['config']['twigEnvironnementAutoescape']?:'false','type'=>'false/texte'),
  array('cat'=>'Service d\'affichage','name'=>'twigDebug','value'=>$p['config']['twigDebug']?:'false','type'=>'false/true')
);

$p['page']['params']=array_merge($p['page']['params'], msConfiguration::getDefaultParameters());

$p['page']['cats']=msSQL::sql2tabKey("SELECT DISTINCT(cat) FROM configuration WHERE level='default'", 'cat', 'cat');
unset($p['page']['cats']['Options']);
ksort($p['page']['cats'], SORT_NATURAL | SORT_FLAG_CASE);
$p['page']['cats']=array_merge(['Serveur', 'Serveur MySQL', 'Service d\'affichage', 'Options'], $p['page']['cats']);
