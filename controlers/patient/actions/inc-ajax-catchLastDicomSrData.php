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
 * Patient > ajax : obtenir les dernières données DICOM SR du patient
 * ou en fonction de la studyID passée en POST
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

if(!is_numeric($_POST['patientID'])) die;

$dc = new msDicomSR();
$dc->setToID($_POST['patientID']);

if(isset($_POST['studyID'])) {
  $dc->setDcStudyID($_POST['studyID']);
  $dc->getSRinstanceFromStudy();
} else {
  $dc->getLastSRinstanceFromPatientID();
}

if ($dataSR=$dc->getSrData()) {
    $keysSR=array_keys($dataSR);

    foreach ($dataSR as $k=>$v) {
        foreach ($v['calculateValues'] as $kk=>$vv) {
            $tabSR[$k.'.'.$kk]=$vv;
        }
    }


    if ($corres=msSQL::sql2tabKey("select d.id, dc.dicomTag, dc.returnValue, dc.roundDecimal
      from dicomTags as dc
      left join data_types as d on d.name = dc.typeName
      where dc.dicomTag in ('".implode("','", $keysSR)."') and dc.typeName !=''", 'id')) {
        foreach ($corres as $k=>$v) {
            if ($k>0) {
                if (isset($tabSR[$v['dicomTag'].'.'.$v['returnValue']])) {
                    $data['data'][$k]=round($tabSR[$v['dicomTag'].'.'.$v['returnValue']], $v['roundDecimal']);
                    $data['debug'][$k]=$v['returnValue'];
                } elseif (isset($tabSR[$v['dicomTag'].'.bv'])) {
                    $data['data'][$k]=round($tabSR[$v['dicomTag'].'.bv'], $v['roundDecimal']);
                    $data['debug'][$k]='bv';
                } elseif (isset($tabSR[$v['dicomTag'].'.defaut'])) {
                    $data['data'][$k]=round($tabSR[$v['dicomTag'].'.defaut'], $v['roundDecimal']);
                    $data['debug'][$k]='defaut';
                }
            }
        }
        $data['find']=true;
        $data['dicom']=array(
          'study'=>$dc->getDcStudyID(),
          'serie'=>$dc->getDcSerieID(),
          'instance'=>$dc->getDcInstanceID()
        );
    } else {
        $data['find']=false;
    }
} else {
    $data['find']=false;
}

header('Content-Type: application/json');

echo json_encode($data);

die();
