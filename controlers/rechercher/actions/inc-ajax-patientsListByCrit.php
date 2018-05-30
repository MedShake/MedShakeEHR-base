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


if ($form=msSQL::sqlUniqueChamp("select yamlStructure from forms where internalName='".$formIN."' limit 1")) {
    $form=Spyc::YAMLLoad($form);

    $form['col0'] = array(
      'head' => 'Identité',
      'bloc' => array( 'identite')
    );
    ksort($form);

    //all type
    $col=count($form);
    $listeTypes=array();
    $p['page']['outputTableHead']=array();

    for ($i=1;$i<=$col;$i++) {
        if (isset($form['col'.$i]['bloc'])) {
            foreach ($form['col'.$i]['bloc'] as $v) {
                $el=explode(',', $v);
                if(is_numeric($el[0])) {
                  $name=msData::getNameFromTypeID($el[0]);
                  $listeTypes[$name]=$el[0];
                } else {
                  $typeID=msData::getTypeIDFromName($el[0]);
                  $listeTypes[$el[0]]=$typeID;
                  $el[0]=$typeID;
                }

                //order by
                if ($i==1) {
                    if($el[0] != 'titre') $orderby[]='c'.$el[0];
                }
            }
        }
    }
    $listeTypes['identite']=0;
    $listeTypes=array_unique($listeTypes);

    foreach ($listeTypes as $k=>$type) {
      if($type != '0') {
        $select[]= 'd'.$type.'.value as c'.$type;
        $leftjoin[]='left join objets_data as d'.$type.' on d'.$type.'.toID=p.id and d'.$type.'.typeID='.$type.' and d'.$type.'.outdated=\'\'';
      }
    }

    //date de dc
    $leftjoin[]='left join objets_data as dcd on dcd.toID=p.id and dcd.typeID='.msData::getTypeIDFromName('deathdate').' and dcd.outdated=\'\' and dcd.deleted=\'\'';

    $where=null;
    if ($_POST['porp']=='today') {
        $agenda=new msAgenda();
        if ($p['config']['agendaNumberForPatientsOfTheDay']) {
            $agenda->set_userID($p['config']['agendaNumberForPatientsOfTheDay']);
        } else {
            $agenda->set_userID($p['user']['id']);
        }
        $todays=$agenda->getPatientsOfTheDay();
        if (count($todays)) {
            $where.=" and p.id in ('".implode("', '", array_column($todays, 'id'))."') ";
        } else {
            return;
        }
    }
    if (empty($_POST['d2'])) {$_POST['d2']='';}
    $where.=" and ((ln.value like '".msSQL::cleanVar($_POST['d2'])."%' and ln.outdated='') or (bn.value like '".msSQL::cleanVar($_POST['d2'])."%' and bn.outdated='') ) ";
    $leftjoin[]='left join objets_data as bn on bn.toID=p.id and bn.typeID=1 and bn.outdated=\'\'';
    $leftjoin[]='left join objets_data as ln on ln.toID=p.id and ln.typeID=2 and ln.outdated=\'\'';

    if (empty($_POST['d3'])) {$_POST['d3']='';}
    $where.=" and fn.value like '".msSQL::cleanVar($_POST['d3'])."%' and fn.outdated='' ";
    $leftjoin[]='left join objets_data as fn on fn.toID=p.id and fn.typeID=3 and fn.outdated=\'\'';

    if (is_numeric($_POST['autreCrit']) and !empty($_POST['autreCritVal'])) {
        $where.=" and d".msSQL::cleanVar($_POST['autreCrit']).".value like '".msSQL::cleanVar($_POST['autreCritVal'])."%'";
        if(!in_array($_POST['autreCrit'], $listeTypes)) {
          $select[]= 'd'.$_POST['autreCrit'].'.value as c'.$_POST['autreCrit'];
          $leftjoin[]='left join objets_data as d'.$_POST['autreCrit'].' on d'.$_POST['autreCrit'].'.toID=p.id and d'.$_POST['autreCrit'].'.typeID='.$_POST['autreCrit'].' and d'.$_POST['autreCrit'].'.outdated=\'\'';
        }
    }

    //patient ou pro en fonction
    if($_POST['porp']=='pro') {
        $peopleType=array('pro');
    } elseif($_POST['porp']=='today') {
        $peopleType=array('pro', 'patient', 'externe');
        $p['page']['extToInt']=msSQL::sql2tabKey("SELECT od.toID, od.value
              FROM objets_data AS od left join data_types AS dt
              ON od.typeID=dt.id AND od.outdated='' AND od.deleted=''
              WHERE dt.name='relationExternePatient' and od.toID in ('".implode("', '", array_column($todays, 'id'))."')", 'toID', 'value');
    } elseif (array_key_exists('PraticienPeutEtrePatient', $p['config']) and $p['config']['PraticienPeutEtrePatient']){
        $peopleType=array('pro','patient');
    } else {
        $peopleType=array('patient');
    }

    $p['page']['sqlString']=$sql='select
    CASE WHEN ln.value !="" and bn.value !="" THEN concat(ln.value, " ", fn.value, " (", bn.Value ,")")
    WHEN bn.value !="" THEN concat(bn.value, " ", fn.value)
    WHEN ln.value !="" THEN concat(ln.value, " ", fn.value)
    ELSE concat("(inconnu) ", fn.value)
    END as c0, dcd.value as deathdate,
    p.type, p.id as peopleID, '.implode(', ', $select).' from people as p '.implode(' ', $leftjoin). ' where p.type in ("'.implode('", "', $peopleType).'") '.$where.' order by trim(c0)  limit 50';

    if ($data=msSQL::sql2tabKey($sql, 'peopleID')) {
        for ($i=0;$i<=$col-1;$i++) {
            if (isset($form['col'.$i]['bloc'])) {
                foreach ($form['col'.$i]['bloc'] as $v) {
                    $el=explode(',', $v);
                    if(is_numeric($el[0])) {
                      $id=$el[0];
                    } else {
                      $id=$listeTypes[$el[0]];
                    }
                    unset($el[0]);

                    //col number for type
                    $modele['c'.$id]=$i;
                    //separator
                    if (isset($form['col'.$i]['blocseparator'])) {
                        $separator[$i]=$form['col'.$i]['blocseparator'];
                    } else {
                        $separator[$i]=' ';
                    }
                    //class
                    if (count($el)>0) {
                        $classadd['c'.$id]=implode(' ', $el);
                    }
                }
            }
            $p['page']['outputTableHead'][$i]=$form['col'.$i]['head'];
        }

        foreach ($data as $k=>$v) {
            $row[$k]=array();
            foreach ($v as $l=>$w) {
                if (empty($w)) {
                    if(isset($modele[$l])) $row[$k][$modele[$l]][]='';
                } elseif (isset($modele[$l])) {
                    if (isset($classadd[$l])) {
                        $row[$k][$modele[$l]][]='<span class="'.$classadd[$l].'">'.$w.'</span>';
                    } else {
                        $row[$k][$modele[$l]][]=$w;
                    }
                }
            }
            // patient dcd
            if(trim($v['deathdate']) !=='') {
              $data[$v['peopleID']]['type'] = 'dcd';
            }

        }

        foreach ($row as $patientID=>$v) {
            foreach ($v as $k=>$q) {
                $p['page']['outputTableRow'][$patientID][]=implode($separator[$k], array_filter($q));
                $p['page']['outputType'][$patientID]['type']=$data[$patientID]['type'];
            }
        }
    }
}
