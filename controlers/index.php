<?php
if ($p['config']['agendaDistantLink']=='') {
    $agenda=new msAgenda();
    $agenda->set_userID($p['user']['id']);
    $todays=$agenda->getPatientsOfTheDay();
    if (is_array($todays) and count($todays)) {
        msTools::redirection('/todays/');
    }
}

msTools::redirection('/patients/');
