<?php
if ($p['config']['agendaDistantLink']=='') {
    $agenda=new msAgenda();
    $agenda->set_userID($p['user']['id']);
    $todays=array_column('id', $agenda->getPatientsOfTheDay());
    if (count($todays)) {
        msTools::redirection('/todays/');
    }
}

msTools::redirection('/patients/');
