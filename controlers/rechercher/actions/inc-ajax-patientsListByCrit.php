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
 * @edited fr33z00 <https://github.com/fr33z00>
 */

$debug='';

$template="listing";

if ($_POST['porp']=='patient') {
    $formIN='baseListingPatients';
} elseif ($_POST['porp']=='pro') {
    $formIN='baseListingPro';
} else {
    die();
}

$p['page']['porp']=$_POST['porp'];


if ($form=msSQL::sqlUniqueChamp("select yamlStructure from forms where internalName='".$formIN."' limit 1")) {
    $form=Spyc::YAMLLoad($form);


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
    $listeTypes=array_unique($listeTypes);

    foreach ($listeTypes as $type) {
        $select[]= 'd'.$type.'.value as c'.$type;
        $leftjoin[]='left join objets_data as d'.$type.' on d'.$type.'.toID=p.id and d'.$type.'.typeID='.$type.' and d'.$type.'.outdated=\'\'';
    }

    $where=null;
    if (!empty($_POST['d2'])) {
        $where.=" and d2.value like '".msSQL::cleanVar($_POST['d2'])."%' and d2.outdated='' ";
    }
    if (!empty($_POST['d3'])) {
        $where.=" and d3.value like '".msSQL::cleanVar($_POST['d3'])."%' and d3.outdated='' ";
    }
    if (is_numeric($_POST['autreCrit']) and !empty($_POST['autreCritVal'])) {
        $where.=" and d".msSQL::cleanVar($_POST['autreCrit']).".value like '".msSQL::cleanVar($_POST['autreCritVal'])."%'";
        if(!in_array($_POST['autreCrit'], $listeTypes)) {
          $select[]= 'd'.$_POST['autreCrit'].'.value as c'.$_POST['autreCrit'];
          $leftjoin[]='left join objets_data as d'.$_POST['autreCrit'].' on d'.$_POST['autreCrit'].'.toID=p.id and d'.$_POST['autreCrit'].'.typeID='.$_POST['autreCrit'].' and d'.$_POST['autreCrit'].'.outdated=\'\'';
        }
    }

    //patient ou pro en fonction
    if($_POST['porp']=='patient') $peopleType=array('pro','patient'); else $peopleType=array('pro');

    $sql='select p.type, p.id as peopleID, '.implode(', ', $select).' from people as p '.implode(' ', $leftjoin). ' where p.type in ("'.implode('", "', $peopleType).'") '.$where.' order by '.implode(', ', $orderby).' limit 50';

    if ($data=msSQL::sql2tabKey($sql, 'peopleID')) {
        for ($i=1;$i<=$col;$i++) {
            $p['page']['outputTableHead'][$i]='<th';
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
            $p['page']['outputTableHead'][$i].='>'.$form['col'.$i]['head'].'</th>';
        }

        foreach ($data as $k=>$v) {
            $row[$k]=array();
            foreach ($v as $l=>$w) {
                if (empty($w)) {
                    $row[$k][$modele[$l]][]='';
                } elseif (isset($modele[$l])) {
                    if (isset($classadd[$l])) {
                        $row[$k][$modele[$l]][]='<span class="'.$classadd[$l].'">'.$w.'</span>';
                    } else {
                        $row[$k][$modele[$l]][]=$w;
                    }
                }
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
