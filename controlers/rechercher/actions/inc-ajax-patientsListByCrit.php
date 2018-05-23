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
 * Patients > ajax : obtenir le listing des patients ou des pros
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';

$template="listing";

if ($_POST['porp']=='patient' or $_POST['porp']=='externe' or $_POST['porp']=='today') {
    $formIN='baseListingPatients';
} elseif ($_POST['porp']=='pro') {
    $formIN='baseListingPro';
} else {
    die();
}

$p['page']['porp']=$_POST['porp'];

$form=msSQL::sqlUniqueChamp("select yamlStructure from forms where internalName='".$formIN."' limit 1");
if (!$form) {
    return;
}
$form=Spyc::YAMLLoad($form);


$p['page']['outputTableHead']=array();
$p['page']['outputTableHead'][0]='Identité';
$msdata = new msData();
$IdentiteTypes=$msdata->getTypeIDsFromName(['birthname','lastname','firstname','administrativeGenderCode','deathdate']);
$separator[0]=' ';
$modele['Identité']=0;
$classadd['Identité']='gras';

//types requis
$listeTypes=array();
$col=count($form);
for ($i=1;$i<=$col;$i++) {
    if (isset($form['col'.$i]['bloc'])) {
        foreach ($form['col'.$i]['bloc'] as $v) {
            $el=explode(',', $v);
            if(is_numeric($el[0])) {
              $name=msData::getNameFromTypeID($el[0]);
              $listeTypes[$name]=$el[0];
              $id=$el[0];
            } else {
              $typeID=msData::getTypeIDFromName($el[0]);
              $listeTypes[$el[0]]=$typeID;
              $name=$el[0];
              $id=$typeID;
            }
            //col number for type
            $modele[$name]=$i;
            //separator
            if (isset($form['col'.$i]['blocseparator'])) {
                $separator[$i]=$form['col'.$i]['blocseparator'];
            } else {
                $separator[$i]=' ';
            }
            //class
            if (count($el)>0) {
                $classadd[$name]=implode(' ', $el);
            }
        }
    }
    $p['page']['outputTableHead'][]=$form['col'.$i]['head'];
}

// création d'une temporary avec tous les champs nécessaires
$viewSelect="CREATE TEMPORARY TABLE preselect AS(
    SELECT od.toID AS id";
foreach ($IdentiteTypes as $k=>$v) {
    $viewSelect.=" ,GROUP_CONCAT(CASE WHEN od.typeID='".$v."' then od.value END) AS ".$k;
};
foreach ($listeTypes as $k=>$v) {
    $viewSelect.=" ,GROUP_CONCAT(CASE WHEN od.typeID='".$v."' then od.value END) AS ".$k;
};
if(!in_array($_POST['autreCrit'], $listeTypes)) {
    $viewSelect.=" ,GROUP_CONCAT(CASE WHEN od.typeID='".$_POST['autreCrit']."' then od.value END) AS autreCrit";
}
$viewSelect.=",GROUP_CONCAT(CASE WHEN od.typeID='".$IdentiteTypes['firstname']."' then p.type END) AS type
        FROM objets_data as od
        LEFT JOIN people as p ON od.toID=p.id
        WHERE  od.outdated='' and od.deleted=''
        GROUP BY od.toID
      )";
msSQL::sqlQuery($viewSelect);


$where='WHERE';

//1ère étape de la selection : le type de people
if($_POST['porp']=='pro') {
    $where.=" type='pro' ";
} elseif($_POST['porp']=='today') {
    $agenda=new msAgenda();
    if ($p['config']['agendaNumberForPatientsOfTheDay']) {
        $agenda->set_userID($p['config']['agendaNumberForPatientsOfTheDay']);
    } else {
        $agenda->set_userID($p['user']['id']);
    }
    $todays=$agenda->getPatientsOfTheDay();
    if (count($todays)) {
        $where.=" type in ('pro', 'patient', 'externe') and id in ('".implode("', '", array_column($todays, 'id'))."') ";
    } else {
        return;
    }
    $p['page']['extToInt']=msSQL::sql2tabKey("SELECT od.toID, od.value
          FROM objets_data AS od left join data_types AS dt
          ON od.typeID=dt.id AND od.outdated='' AND od.deleted=''
          WHERE dt.name='relationExternePatient' AND od.toID IN ('".implode("', '", array_column($todays, 'id'))."')", 'toID', 'value');
} elseif (array_key_exists('PraticienPeutEtrePatient', $p['config']) and $p['config']['PraticienPeutEtrePatient'] == 'true'){
    $where.=" type in ('pro', 'patient') ";
} else {
    $where.=" type='patient' ";
}

// 2ème étape de la selection : la recherche

if (!empty($_POST['d2'])) {
    $term=preg_replace('/  +/', ' ', msSQL::cleanVar($_POST['d2']));
    $where.=" AND (CONCAT(firstname, ' ', birthname) LIKE '".$term."%'
        OR CONCAT(firstname, ' ', lastname) LIKE '".$term."%'
        OR CONCAT(birthname, ' ', firstname) LIKE '".$term."%'
        OR CONCAT(lastname, ' ', firstname) LIKE '".$term."%'
    )";
}

if (is_numeric($_POST['autreCrit']) and !empty($_POST['autreCritVal'])) {
    if(!in_array($_POST['autreCrit'], $listeTypes)) {
        $where.=" AND autreCrit like '".msSQL::cleanVar($_POST['autreCritVal'])."%'";
    } else {
        $where.=" AND ".msData::getNameFromTypeID($_POST['autreCrit'])." like '".msSQL::cleanVar($_POST['autreCritVal'])."%'";
    }
}

// construction de la requête

$searchSelect="SELECT id,
  CASE WHEN birthname!='' and lastname!='' and administrativeGenderCode='F' THEN concat(trim(lastname),' ', trim(COALESCE(firstname,'')), ' (née ',trim(birthname), ')')
       WHEN birthname!='' and lastname!='' and administrativeGenderCode!='F' THEN concat(trim(lastname),' ', trim(COALESCE(firstname,'')), ' (né ', trim(birthname), ')')
       ELSE concat(trim(COALESCE(birthname,'')),trim(COALESCE(lastname,'')),' ',trim(COALESCE(firstname,''))) END as Identité,"
  .implode(',', array_keys($listeTypes)).
  ", type, deathdate
  FROM preselect "
  .$where.
  " ORDER BY Identité limit 50";

$data=msSQL::sql2tabKey($searchSelect, 'id');
if (!$data) {
    return;
}
// mise en forme
foreach ($data as $patientID=>$v) {
    $row[$patientID]=array();
    foreach ($v as $k=>$q) {
        if (empty($q)) {
            if(isset($modele[$k])) $row[$patientID][$modele[$k]][]='';
        } elseif (isset($modele[$k])) {
            if (isset($classadd[$k])) {
                $row[$patientID][$modele[$k]][]='<span class="'.$classadd[$k].'">'.$q.'</span>';
            } else {
                $row[$patientID][$modele[$k]][]=$q;
            }
        }
    }
    // patient dcd
    if (trim($v['deathdate'])!=='') {
        $data[$patientID]['type']='dcd';
    }
}
foreach ($row as $patientID=>$v) {
    foreach ($v as $k=>$q) {
        $p['page']['outputTableRow'][$patientID][]=implode($separator[$k], array_filter($q));
        $p['page']['outputType'][$patientID]['type']=$data[$patientID]['type'];
    }
}
