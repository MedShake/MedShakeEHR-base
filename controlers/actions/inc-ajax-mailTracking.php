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
 * Requêtes AJAX > retourner les infos de tracking d'un mail
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */
if (!empty($p['config']['smtpTracking'])) {
    $msMailTracking='msMailTracking'.$p['config']['smtpTracking'];
    if (class_exists($msMailTracking)) {

        if ($data= $msMailTracking::getMessageTrackingData($_POST['mailID'])) {
            $res=[];
            $res['numberEvents']= $data['Count'];
            $res['mailTrackingID']=$_POST['mailID'];
            foreach ($data['Data'] as $k=>$v) {
                $res['lastStatus']=$v['EventType'];
                $res['lastDate']=date("d/m/Y H:i:s", $v['EventAt']);
                $res['data'][$k]=$v;
            }

            echo json_encode($res);
        }
    } 
}
