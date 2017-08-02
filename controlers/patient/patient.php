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
 * Patient : la page du dossier patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$debug='';
$template="patient";

//le patient
$patient = new msPeople();
$patient->setToID($match['params']['patient']);
$p['page']['patient']['id']=$match['params']['patient'];
$p['page']['patient']['administrativeDatas']=$patient->getAdministrativesDatas();
$p['page']['patient']['administrativeDatas'][8]['age']=$patient->getAge();

//type du dossier
$p['page']['patient']['dossierType']=msSQL::sqlUniqueChamp("select type from people where id='".$match['params']['patient']."' limit 1");

//historique du jour des consultation du patient
$p['page']['patient']['today']=$patient->getToday();

//historique complet des consultation du patient
$p['page']['patient']['historique']=$patient->getHistorique();

//les certificats
$certificats=new msData();
$p['page']['modelesCertif']=$certificats->getDataTypesFromCatName('catModelesCertificats', ['id','label']);
//les courriers
$p['page']['modelesCourrier']=$certificats->getDataTypesFromCatName('catModelesCourriers', ['id','label']);

//les correspondants et liens familiaux
$name2typeID = new msData();
$name2typeID = $name2typeID->getTypeIDsFromName(['relationID', 'relationPatientPraticien', 'relationPatientPatient', 'titre']);

$p['page']['correspondants']=msSQL::sql2tab("select o.value as pratID, c.value as typeRelation, n.value as nom, p.value as prenom, t.value as titre 
from objets_data as o
inner join objets_data as c on c.instance=o.id and c.typeID='".$name2typeID['relationPatientPraticien']."' and c.value != 'patient'
left join objets_data as n on n.toID=o.value and n.typeID=2 and n.outdated='' and n.deleted=''
left join objets_data as p on p.toID=o.value and p.typeID=3 and p.outdated='' and p.deleted=''
left join objets_data as t on t.toID=o.value and t.typeID='".$name2typeID['titre']."' and t.outdated='' and t.deleted=''
where o.toID='".$match['params']['patient']."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated=''
group by o.value order by typeRelation = 'MT' desc, nom asc");

$p['page']['liensFamiliaux']=msSQL::sql2tab("select o.value as patientID, c.value as typeRelation, n.value as nom, p.value as prenom, d.value as ddn
from objets_data as o
inner join objets_data as c on c.instance=o.id and c.typeID='".$name2typeID['relationPatientPatient']."'
left join objets_data as n on n.toID=o.value and n.typeID=2 and n.outdated='' and n.deleted=''
left join objets_data as p on p.toID=o.value and p.typeID=3 and p.outdated='' and p.deleted=''
left join objets_data as d on d.toID=o.value and d.typeID=8 and p.outdated='' and p.deleted=''
where o.toID='".$match['params']['patient']."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated=''
group by o.value order by nom asc");
