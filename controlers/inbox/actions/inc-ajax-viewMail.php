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
 * Inbox > ajax : voir un message
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';
$template='mailView';

if(!is_numeric($_POST['mailID'])) die;

$p['page']['mail']=msSQL::sqlUnique("select id, txtFileName, mailHeaderInfos, txtDatetime, hprimExpediteur, hprimAllSerialize, pjNombre, pjSerializeName, archived, assoToID from inbox where id='".$_POST['mailID']."'");
$p['page']['mail']['hprimAllSerialize']=unserialize($p['page']['mail']['hprimAllSerialize']);
$p['page']['mail']['pjSerializeName']=unserialize($p['page']['mail']['pjSerializeName']);
$p['page']['mail']['mailHeaderInfos']=unserialize($p['page']['mail']['mailHeaderInfos']);

$p['page']['mail']['corps']=msInbox::getMessageBody($p['config']['apicryptCheminInbox'].'/'.$p['page']['mail']['txtFileName']);
//hprim
$p['page']['mail']['bioHprim'] = msHprim::parseSourceHprim($p['page']['mail']['corps']);

$p['page']['mail']['patientsPossibles']=msHprim::getPossiblePatients($p['page']['mail']['hprimAllSerialize'],$p['page']['mail']['assoToID']);

$p['page']['mail']['relativePathForPJ']=str_replace($p['config']['webDirectory'], '', $p['config']['apicryptCheminInbox']);

// détails sur le PJ
$directoryPj = str_replace(['.txt'],['.f'],$p['page']['mail']['txtFileName']);
if(!empty($p['page']['mail']['pjSerializeName'])) {
  foreach($p['page']['mail']['pjSerializeName'] as $k=>$v) {
    $p['page']['mail']['pjData'][$k]['filePath'] = $p['config']['apicryptCheminInbox'].$directoryPj.'/'.$v;
    $p['page']['mail']['pjData'][$k]['mime']=msTools::getmimetype($p['page']['mail']['pjData'][$k]['filePath']);
    $p['page']['mail']['pjData'][$k]['fileRelativePath']=str_replace($p['config']['webDirectory'], '', $p['page']['mail']['pjData'][$k]['filePath']);

    $p['page']['mail']['pjData'][$k]['object']=array(
      'display'=>false,
      'displayType'=>'object',
      'width'=>0,
      'height'=>0,
    );

    // texte
    if($p['page']['mail']['pjData'][$k]['mime'] == 'text/plain') {
      $p['page']['mail']['pjData'][$k]['object']=array(
        'display'=>true,
        'displayType'=>'object',
        'width'=>'900px',
        'height'=>'900px',
      );
      msTools::convertPlainTextFileToUtf8($p['page']['mail']['pjData'][$k]['filePath']);
    }

    // pdf
    elseif($p['page']['mail']['pjData'][$k]['mime'] == 'application/pdf') {
      $p['page']['mail']['pjData'][$k]['object']=array(
        'display'=>true,
        'displayType'=>'object',
        'width'=>'900px',
        'height'=>'1260px',
      );
    }

    // zip
    elseif($p['page']['mail']['pjData'][$k]['mime'] == 'application/zip') {
      $p['page']['mail']['pjData'][$k]['object']=array(
        'display'=>false,
        'displayType'=>'object',
        'width'=>0,
        'height'=>0,
      );
    }

    // image
    elseif(explode('/', $p['page']['mail']['pjData'][$k]['mime'])[0] == 'image') {
      $imageInfos = getimagesize($p['page']['mail']['pjData'][$k]['filePath']);
      if($imageInfos[0]>1000) {
        $imageInfos[1]=round($imageInfos[1]*1000/$imageInfos[0]);
        $imageInfos[0]=1000;
      }
      if($imageInfos[1]>1000) {
        $imageInfos[0]=round($imageInfos[0]*1000/$imageInfos[1]);
        $imageInfos[1]=1000;
      }
      $p['page']['mail']['pjData'][$k]['object']=array(
        'display'=>true,
        'displayType'=>'img',
        'width'=>$imageInfos[0].'px',
        'height'=>$imageInfos[1].'px',
      );
    }
  }
}
