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
 * Paramètres utilisateur > > ajax : lister les prescriptions types
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


 if ($tabTypes=msSQL::sql2tab("select p.* , c.name as catName, c.label as catLabel
      from prescriptions as p
      left join prescriptions_cat as c on c.id=p.cat
      where p.toID in ('0', '".$p['user']['id']."') and c.type='nonlap'
      group by p.id
      order by c.label asc, p.label asc")) {
     foreach ($tabTypes as $v) {
         $p['page']['tabTypes'][$v['catName']][]=$v;
     }
 }


 $p['page']['catList']=msSQL::sql2tabKey("select id, label from prescriptions_cat where type='nonlap' order by label", 'id', 'label');

$html = new msGetHtml;
$html->set_template('inc-ajax-tabUserParametersPresList.html.twig');
$html = $html->genererHtmlString($p);

echo json_encode(array('html'=>$html));
