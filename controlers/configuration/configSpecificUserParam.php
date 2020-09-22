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
 * Config : gérer les paramètres de configuration spécifiques à un utilisateur
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

//admin uniquement
if (!msUser::checkUserIsAdmin()) {
    $template="forbidden";
    return;
}
$debug='';
$template='configSpecificUserParam';

$p['page']['userID']=$match['params']['userID'];
$prat=new msPeople();
$prat->setToID($p['page']['userID']);
$p['page']['userData']=$prat->getSimpleAdminDatasByName();
$module=$prat->getModule();

$p['page']['userParams']=[];
if($data=msConfiguration::getUserParamaters($p['page']['userID'])) {
    foreach($data as $k=>$v) {
        if ($k =='agendaNumberForPatientsOfTheDay' or $k=='administratifComptaPeutVoirRecettesDe') {
            $v['formValues']=msSQL::sql2tabKey("SELECT `id`, `name` FROM `people` WHERE `name`!='' and `type`='pro'", "id", "name");
            unset($v['formValues'][$p['page']['userID']]);
            if ($v['name'] == 'agendaNumberForPatientsOfTheDay') {
                $v['formValues']['0']='';
                ksort($v['formValues']);
            }
                if ($v['name'] == 'administratifComptaPeutVoirRecettesDe') {
                $v['value']=explode(',', $v['value']);
            }
        } elseif ($v['type']=='password') {
            $v['value']=str_repeat('*',strlen($v['value']));
        }
        $p['page']['userParams'][$v['cat']][]=$v;
    }
}
ksort($p['page']['userParams']);

$p['page']['availableParams']=msConfiguration::listAvailableParameters(array('id'=>$p['page']['userID'],'module'=>$module));
foreach($p['page']['availableParams'] as $k=>$v) {
  $p['page']['availableParams'][$k]['saniCat']=msTools::sanitizeFilename($v['cat']);
}
$p['page']['availableCats']=msConfiguration::getListOfParametersCat();

// templates user
$p['page']['userTemplates']=msConfiguration::getUserTemplatesList();
