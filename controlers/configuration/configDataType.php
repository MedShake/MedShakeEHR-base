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
 * Config : gérer les données du modèle de données
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

 //admin uniquement
 if (!msUser::checkUserIsAdmin()) {
     $template="forbidden";
 } else {
    $template="configDataType";
    $debug='';

    $p['page']['groupe']=$match['params']['groupe'];
    if(!in_array($p['page']['groupe'], msSQL::sqlEnumList('data_types', 'groupe'))) die();

    //restriction à une cat
    if (isset($match['params']['cat']) and is_numeric($match['params']['cat'])) {
        $catRestriction= ' and t.cat = '.$match['params']['cat'];
    } else {
        $catRestriction=null;
    }


    if ($tabTypes=msSQL::sql2tab("select t.*, c.name as catName, c.label as catLabel,
        (select count(id) from objets_data as d where d.typeID=t.id ) as enfants
        from data_types as t
        left join data_cat as c on c.id=t.cat
        where t.id > 0 and t.groupe='".msSQL::cleanVar($p['page']['groupe'])."' ".$catRestriction."
        group by t.id
        order by t.module, t.displayOrder, c.label asc, t.label asc, t.name")) {


        foreach ($tabTypes as $v) {
            $p['page']['tabTypes'][$v['catName']][]=$v;
        }
    }

    // liste des catégories
    $p['page']['catList']=msSQL::sql2tabKey("select id, label from data_cat where groupe='".$p['page']['groupe']."' order by label", 'id', 'label');

    // liste des modules
    $p['page']['modules']=msModules::getInstalledModulesNames();

    // liste des types possibles
    $p['page']['typesPossibles']=msSQL::sqlEnumList('data_types', 'formType');
    sort($p['page']['typesPossibles']);
    $p['page']['typesPossibles']=array_combine($p['page']['typesPossibles'],$p['page']['typesPossibles']);

    // Liste des registres
    if($p['config']['optionGeActiverRegistres'] == 'true') {
      $registres = new msPeopleSearch;
      $registres->setPeopleType(['registre']);
      $registres->setCriteresRecherche(['registryname'=>'%']);
      $registres->setColonnesRetour(['registryname']);
      if(!empty($regs = $registres->getSimpleSearchPeople())) {
        $p['page']['registresPossibles']=array_column($regs, 'registryname', 'id');
      }

    }

 }
