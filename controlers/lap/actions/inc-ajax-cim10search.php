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
 * LAP : ajax > extraction de code / label cim 10
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$template='inc-cim10searchTableResults';

$lap=new msLap;
$rd=[];
$data=$lap->getCIM10fromKeywords(str_replace(' ', '%',$_POST['term']));
if (is_array($data)) {
    foreach ($data as $code=>$label) {
        if (strlen($code)<=4 and strlen($code)>=3) {
            $codepere=substr($code, 0, 3);
            if (key_exists($codepere, $data)) {
                $rd[$codepere][$code]=$label;
                ksort($rd[$codepere]);
            } else {
                if($peresearch = $lap->getCIM10LabelFromCode($codepere)) {
                  $rd[$codepere][$codepere]=$peresearch;
                  $rd[$codepere][$code]=$label;
                } else {
                  $rd['ZZ']['A0']="Autres";
                  $rd['ZZ'][$code]=$label;
                  ksort($rd['ZZ']);
                }

            }
        }
    }
}

ksort($rd);

$p['page']['cim10code']=$rd;
unset($rd);
