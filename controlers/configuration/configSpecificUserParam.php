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
 */

 //admin uniquement
 if (!msUser::checkUserIsAdmin()) {
     $template="forbidden";
 } else {
     $debug='';
     $template='configSpecificUserParam';
     $data = new msPeople();
     $data->setToID($match['params']['userID']);

     $p['page']['userID']=$match['params']['userID'];

     $p['page']['userData']=$data->getSimpleAdminDatas();

     if($data=$data->getPeopleDataFromDataTypeGroupe('user', ['dt.*', 'od.value as userVal'])) {
       foreach($data as $v) {
         if (array_key_exists('name', $v) and ($v['name'] =='agendaNumberForPatientsOfTheDay' or $v['name'] == 'administratifComptaPeutVoirRecettesDe')) {
             $v['formValues']==msSQL::sql2tabKey("SELECT id, name FROM people WHERE name!='' and type='pro'", "id", "name");
             if ($v['name'] == 'administratifComptaPeutVoirRecettesDe') {
                 $v['userVal']=explode(',', $v['userVal']);
             }
         }
         $p['page']['userParams'][$v['cat']][]=$v;
       }
     }

     $p['page']['configDefaut']=Spyc::YAMLLoad('../config/config.yml');

     // liste des catégories
     if ($p['page']['catList']=msSQL::sql2tabKey("select id, label from data_cat where groupe='user' order by label", 'id', 'label'));
 }
