<?php
if ($p['config']['agendaDistantLink']=='') {
    $agenda=new msAgenda();
    if ($p['config']['agendaNumberForPatientsOfTheDay']) {
        $agenda->set_userID($p['config']['agendaNumberForPatientsOfTheDay']);
    } else {
        $agenda->set_userID($p['user']['id']);
    }
    $todays=$agenda->getPatientsOfTheDay();
    if (count($todays)) {
        msTools::redirection('/todays/');
    }
}

msTools::redirection('/patients/');
