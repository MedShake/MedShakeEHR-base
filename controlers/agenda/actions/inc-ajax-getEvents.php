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
 * Agenda : obtenir les events pour peupler l'agenda
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$events = new msAgenda();
$events->setStartDate($_GET['start']);
$events->setEndDate($_GET['end']);
$events->set_userID($match['params']['userID']);
if ($p['config']['agendaJoursFeriesAfficher'] == 'true') $events->set_addPublicHolidaysToEvents(true);

header('Content-Type: application/json');
echo json_encode($events->getEvents());
