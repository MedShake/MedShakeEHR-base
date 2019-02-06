<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Requêtes AJAX > lancer un appel click2call
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

use \Ovh\Api;

$number2call = (string)trim(str_replace(' ', '',$_POST['number2call']));

$ovh = new Api( $p['config']['ovhApplicationKey'],  // Application Key
               $p['config']['ovhApplicationSecret'],  // Application Secret
               'ovh-eu',      // Endpoint of API OVH Europe
               $p['config']['ovhConsumerKey']); // Consumer Key


$result = $ovh->post('/telephony/'.$p['config']['ovhTelecomBillingAccount'].'/line/'.$p['config']['ovhTelecomServiceName'].'/click2Call', array(
   'calledNumber' => (string)$number2call, // Required:  (type: string)
   'callingNumber' => $p['config']['ovhTelecomCallingNumber'], //  (type: string)
   'intercom' => false, // Activate the calling number in intercom mode automatically (pick up and speaker automatic activation). (type: boolean)
));

if($result == null ) {
  echo json_encode(['statut'=>'ok', 'calledNumber'=>$number2call]);
} else {
  echo json_encode(['statut'=>$result]);
}
die();
