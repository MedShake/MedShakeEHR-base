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
 * Outils : statistiques
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//vérification droits
if ($p['config']['droitStatsPeutVoirStatsGenerales'] != 'true') {
  $template="forbidden";
} else {

  $template="statsGenerales";
  $debug='';

  $statsExclusionPatients=msSQL::cleanArray(explode(',',$p['config']['statsExclusionPatients']));
  $statsExclusionCats=msSQL::cleanArray(explode(',', $p['config']['statsExclusionCats']));

  // consultations
  if ($tabTypes=msSQL::sql2tab("select t.id, t.name, t.label, c.name as catName, c.label as catLabel,
      (select count(id) from objets_data as d where d.typeID=t.id and d.toID not in ('".implode("', '", $statsExclusionPatients)."') and d.outdated='' and d.deleted='') as actifs, (select count(id) from objets_data as d where d.typeID=t.id and d.toID not in ('".implode("', '", $statsExclusionPatients)."') and (d.outdated='y' or d.deleted='y')) as deleted
      from data_types as t
      left join data_cat as c on c.id=t.cat
      where t.id > 0 and t.groupe='typecs' and c.name not in ('".implode("', '", $statsExclusionCats)."')
      group by t.id
      order by t.module, c.label asc, t.label asc, t.name")) {

      foreach ($tabTypes as $v) {
          $p['page']['forms'][$v['catName']][]=$v;
      }
  }

  // patients
  $p['page']['dossiers']=msSQL::sql2tabKey("SELECT count(id) as nb, `type` FROM `people` where `id` not in ('".implode("', '", $statsExclusionPatients)."') group by `type`", 'type', 'nb');
}
