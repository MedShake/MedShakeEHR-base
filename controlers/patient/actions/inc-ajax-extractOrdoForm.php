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
 * Patient > ajax : obtenir le formulaire d'ordonnance
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';

//template
$template="patientOrdoForm";

//patient
$p['page']['patient']['id']=$_POST['patientID'];

//pour menu de choix de l'acte, par catégories
if ($tabTypes=msSQL::sql2tab("select p.id, p.label as optionmenu , c.label as catLabel
  from prescriptions as p
  left join prescriptions_cat as c on c.id=p.cat
  group by p.id
  order by c.displayOrder, p.id in (1,2) desc, c.label asc, p.label asc")) {
    foreach ($tabTypes as $v) {
        $p['page']['menusPrescriptions'][$v['catLabel']][]=$v;
    }
}

//si edition, on sort les datas et on ajoute un hidden avec objetID
if (is_numeric($_POST['objetID'])) {
    $p['page']['courrier']['objetID']=$_POST['objetID'];

    if ($ordoData=msSQL::sql2tab("select ald.value as ald, concat(p.parentTypeID,'_',UNIX_TIMESTAMP(),'_',p.id) as formname, 'Ligne importée' as label, p.value as description, p.typeID, concat(' (initialement : ',pres.label,')') as labelInitiale, p.id
    from objets_data as p
    left join objets_data as ald on p.id=ald.instance and ald.typeID=191 and ald.outdated='' and ald.deleted=''
    left join prescriptions as pres on pres.id=p.parentTypeID
    where p.instance='".$_POST['objetID']."' and p.outdated='' and p.deleted='' and p.typeID in (189,190)
    group by p.id
    order by p.id asc")) {
        $modePrint='standard';

        foreach ($ordoData as $v) {
            if ($v['typeID']=='189') {
                $modePrint=$v['description'];
                $p['page']['courrier']['modeprintObjetID']=$v['id'];
            } else {
                if ($v['ald']==1) {
                    $modePrint='ald';
                }
                $p['page']['courrier']['medoc'][]=$v;
            }
        }

        $p['page']['courrier']['modeprint']=$modePrint;
    }
}
