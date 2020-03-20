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
 * Paramètres utilisateur > > ajax : lister les catégories de prescriptions types
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$tab['lap']=[];
$tab['nonlap']=[];

// Catégories des prescriptions types
if($tabCatPres=msSQL::sql2tabKey("select c.*, count(p.id) as enfants
from prescriptions_cat as c
left join prescriptions as p on c.id=p.cat
where c.toID in ('0','".$p['user']['id']."')
group by c.id
order by c.displayOrder asc, c.label asc", 'id')) {

  foreach($tabCatPres as $k=>$v) {
    $tab[$v['type']][]=$v;
  }
  $p['page']['tabCatPres']=array(
    'nonlap'=>$tab['nonlap'],
    'lap'=>$tab['lap']
  );
}

$html = new msGetHtml;
$html->set_template('inc-ajax-tabUserParametersPresCatList.html.twig');
$html = $html->genererHtmlVar($p);

echo json_encode(array(
  'html'=>$html,
  'catLap'=>$tab['lap'],
  'catNonLap'=>$tab['nonlap'],
));
