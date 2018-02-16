<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00>
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
 * Patient > ajax : obtenir une ligne pour l'ordonnance
 *
 * @author fr33z00 <https://github.com/fr33z00>
 */

$patient=new msPeople();
$patient->setToID($_POST['patientID']);
$patientData=$patient->getSimpleAdminDatasByName();
$naissance=DateTime::createFromFormat('d/m/Y', $patientData['birthdate']);

$dataBrutes=msSQL::sql2tab("SELECT dt.name, od.value, od.registerDate AS date
  FROM objets_data AS od LEFT JOIN data_types AS dt
  ON od.typeID=dt.id AND od.toID='".$_POST['patientID']."' and deleted=''
  WHERE dt.groupe='medical'
  ORDER BY od.registerDate ASC");

header('Content-Type: application/json');

if (!is_array($dataBrutes)) {
    exit("ok");
}
$data=array();
foreach ($dataBrutes as $d) {
    $idx=($naissance->diff(DateTime::createFromFormat('Y-m-d H:i:s', $d['date'])))->format('%a');
    $data[$idx][$d['name']]=str_replace(',','.',$d['value']);
    $data[$idx]['date']=$d['date'];
}

$Pmin=1000;
$Pmax=0;
$Tmin=1000;
$Tmax=0;
$Imin=1000;
$Imax=0;
foreach ($data as $k=>$d) {
    if (!array_key_exists('poids', $d)) {
        if (!array_key_exists('taillePatient', $d)) {
            if (!isset($mesureAnt) or !isset($mesureAnt['poids'])) {
                unset($data[$k]);
            } else {
                $data[$k]=array('value'=>round($mesureAnt['poids']*10000/($mesureAnt['taille']*$mesureAnt['taille']), 1), 'date'=>$d['date'], 'mesure'=>false);
            }
        } else {
            $imc=round($mesureAnt['poids']*10000/($d['taillePatient']*$d['taillePatient']), 1);
            $data[$k]=array('poids'=>array('value'=>$mesureAnt['poids'], 'reel'=>false),
                            'taille'=>array('value'=>$d['taillePatient'], 'reel'=>true),
                            'imc'=>array('value'=>$imc, 'reel'=>false), 'date'=>$d['date']);
            $mesureAnt['taille']=$d['taillePatient'];
            $Tmin=$d['taillePatient']<$Tmin?$d['taillePatient']:$Tmin;
            $Tmax=$d['taillePatient']>$Tmax?$d['taillePatient']:$Tmax;
            $Imin=$imc<$Imin?$imc:$Imin;
            $Imax=$imc>$Imax?$imc:$Imax;
        }
    } else {
        if (!array_key_exists('taillePatient', $d)) {
            if (!isset($mesureAnt['taille'])) {
                unset($data[$k]);
            } else {
                $imc=round($d['poids']*10000/($mesureAnt['taille']*$mesureAnt['taille']), 1);
                $data[$k]=array('poids'=>array('value'=>$d['poids'], 'reel'=>true),
                                'taille'=>array('value'=>$mesureAnt['taille'], 'reel'=>false),
                                'imc'=>array('value'=>$imc, 'reel'=>true), 'date'=>$d['date']);
                $Pmin=$d['poids']<$Pmin?$d['poids']:$Pmin;
                $Pmax=$d['poids']>$Pmax?$d['poids']:$Pmax;
                $Imin=$imc<$Imin?$imc:$Imin;
                $Imax=$imc>$Imax?$imc:$Imax;
            }
        } else {
            $imc=round($d['poids']*10000/($d['taillePatient']*$d['taillePatient']), 1);
            $data[$k]=array('poids'=>array('value'=>$d['poids'], 'reel'=>true),
                            'taille'=>array('value'=>$d['taillePatient'], 'reel'=>true),
                            'imc'=>array('value'=>$imc, 'reel'=>true), 'date'=>$d['date']);
            $mesureAnt=array('poids'=>$d['poids'], 'taille'=>$d['taillePatient']);
            $Tmin=$d['taillePatient']<$Tmin?$d['taillePatient']:$Tmin;
            $Tmax=$d['taillePatient']>$Tmax?$d['taillePatient']:$Tmax;
            $Pmin=$d['poids']<$Pmin?$d['poids']:$Pmin;
            $Pmax=$d['poids']>$Pmax?$d['poids']:$Pmax;
            $Imin=$imc<$Imin?$imc:$Imin;
            $Imax=$imc>$Imax?$imc:$Imax;
        }
    }
}

reset($data);
$Xmin=key($data);
end($data);
$Xmax=key($data);
$data['bornes']=array('Xmin'=>$Xmin, 'Xmax'=>$Xmax, 'Ymin'=>array('poids'=>$Pmin, 'taille'=>$Tmin, 'imc'=>$Imin), 'Ymax'=>array('poids'=>$Pmax, 'taille'=>$Tmax, 'imc'=>$Imax));

exit(json_encode($data));
