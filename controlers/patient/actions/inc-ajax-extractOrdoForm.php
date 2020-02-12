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
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

$debug='';

if (!isset($delegate)) {
  if (!isset($_POST['objetID']) || $_POST['objetID']==='') {
      $ordoForm=$_POST['ordoForm'];
      $porteur=$_POST['porteur'];
      $userID=$_POST['asUserID']?:$p['user']['id'];
      $module=$_POST['module'];
  } else {
      $res=msSQL::sql2tab("SELECT dt.module AS module, dt.formValues AS form, dt.name as porteur, dt.fromID AS userID FROM data_types as dt
        LEFT JOIN objets_data as od ON dt.id=od.typeID
        WHERE od.id='".msSQL::cleanVar($_POST['objetID'])."' limit 1");
      $ordoForm=$res[0]['form'];
      $porteur=$res[0]['porteur'];
      $userID=$res[0]['userID'];
      $module=$res[0]['module'];
  }
  //si le formulaire d'ordonnance n'est pas celui de base, c'est au module de gérer (à moins qu'il délègue)
  if ($ordoForm!='') {
        $hook=$p['homepath'].'/controlers/module/'.$_POST['module'].'/patient/actions/inc-hook-extractOrdoForm.php';
        if ($module!='' and $module!='base' and is_file($hook)) {
            include $hook;
        }
        if (!isset($delegate)) {
            return;
        }
  }
}

//template
$template="patientOrdoForm";

$p['page']['ordo']=array('module'=>$module, 'ordoForm'=>$ordoForm, 'porteur'=>$porteur, 'asUserID'=>$_POST['asUserID']);

//patient
$p['page']['patient']['id']=$_POST['patientID'];

//pour menu de choix de l'acte, par catégories
if ($tabTypes=msSQL::sql2tab("select p.id, p.label as optionmenu , c.label as catLabel
  from prescriptions as p
  left join prescriptions_cat as c on c.id=p.cat
  where p.toID in ('0','".msSQL::cleanVar($userID)."') and c.type='nonlap'
  group by p.id
  order by c.displayOrder, p.id in (1,2) desc, c.label asc, p.label asc")) {
    foreach ($tabTypes as $v) {
        $p['page']['menusPrescriptions'][$v['catLabel']][]=$v;
    }
}

// impression par défaut nb lignes prescriptions
if($p['config']['optionGeActiverLapInterne'] == 'true' or $p['config']['optionGeActiverLapExterne'] == 'true' ) {
  $p['page']['courrier']['ordoImpressionNbLignes'] = 'n';
} else {
  $p['page']['courrier']['ordoImpressionNbLignes'] = 'o';
}

//si edition, on sort les datas et on ajoute un hidden avec objetID
if (is_numeric($_POST['objetID'])) {
    $p['page']['courrier']['objetID']=$_POST['objetID'];

    $name2typeID = new msData();
    $name2typeID = $name2typeID->getTypeIDsFromName(['ordoLigneOrdoALDouPas','ordoTypeImpression','ordoLigneOrdo','ordoImpressionNbLignes']);

    if ($ordoData=msSQL::sql2tab("select ald.value as ald, concat(p.parentTypeID,'_',UNIX_TIMESTAMP(),'_',p.id) as formname, 'Ligne importée' as label, p.value as description, p.typeID, concat(' (initialement : ',pres.label,')') as labelInitiale, p.id
    from objets_data as p
    left join objets_data as ald on p.id=ald.instance and ald.typeID='".$name2typeID['ordoLigneOrdoALDouPas']."' and ald.outdated='' and ald.deleted=''
    left join prescriptions as pres on pres.id=p.parentTypeID
    where p.instance='".$_POST['objetID']."' and p.outdated='' and p.deleted='' and p.typeID in ('".$name2typeID['ordoTypeImpression']."','".$name2typeID['ordoLigneOrdo']."','".$name2typeID['ordoImpressionNbLignes']."')
    group by p.id, ald.id
    order by p.id asc")) {

        $modePrint='standard';

        foreach ($ordoData as $v) {
            if ($v['typeID']==$name2typeID['ordoImpressionNbLignes']) {
                $p['page']['courrier']['ordoImpressionNbLignes']=$v['description'];
            } elseif ($v['typeID']==$name2typeID['ordoTypeImpression']) {
                $modePrint=$v['description'];
                $p['page']['courrier']['modeprintObjetID']=$v['id'];
            } else {
                if ($v['ald']=='true' or $v['ald']=='1') {
                    $modePrint='ald';
                }
                $p['page']['courrier']['medoc'][]=$v;
            }
        }

        $p['page']['courrier']['modeprint']=$modePrint;
    }
}
