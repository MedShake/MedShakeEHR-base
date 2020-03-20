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
 * enregistrement des paramètres d'agenda utilisateur
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */


$preparams=array();

foreach ($_POST as $k=>$v) {
    preg_match('/(desc|back|border|duree|key|utilisable)_(.*)/', $k, $matches);
    if (isset($matches[2])) {
        $preparams[$matches[2]][$matches[1]]=$v;
    }
}
foreach ($preparams as $k=>$v) {
    if ($v['key']) {
        $params["'[".$v['key']."]'"]=array('descriptif'=>$v['desc'], 'backgroundColor'=>$v['back'], 'borderColor'=>$v['border'], 'duree'=>$v['duree'], 'utilisable'=>$v['utilisable']);
    }
}

if(file_put_contents($p['homepath'].'config/agendas/typesRdv'.$p['user']['id'].'.yml', Spyc::YAMLDump($params, false, 0, true))) {
  header('Content-Type: application/json');
  exit(json_encode(array('status'=>'success')));
} else {
  header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
  exit();
}
