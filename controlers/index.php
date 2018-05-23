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
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

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
