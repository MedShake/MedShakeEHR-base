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
 * LAP : ajax > chercher des prescriptions
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 $name2typeID = new msData();
 $name2typeID = $name2typeID->getTypeIDsFromName(['lapMedicamentCodeATC', 'lapMedicamentCodeSubstanceActive', 'lapMedicamentPresentationCodeTheriaque', 'lapMedicamentSpecialiteCodeTheriaque', 'firstname', 'lastname', 'birthname','lapMedicamentSpecialiteNom','lapMedicamentDC', 'administrativeGenderCode', 'birthdate']);

 //gestion des dates
 if(!empty($_POST['beginPeriode'])) {
   $beginPeriode = DateTime::createFromFormat('d/m/Y', $_POST['beginPeriode']);
   $whereBeginPeriode = "and w.registerDate >= '".$beginPeriode->format('Y-m-d 00:00:00')."'";
 } else {
   $whereBeginPeriode='';
 }
 if(!empty($_POST['endPeriode'])) {
   $endPeriode = DateTime::createFromFormat('d/m/Y', $_POST['endPeriode']);
   $whereEndPeriode = "and w.registerDate <= '".$endPeriode->format('Y-m-d 23:59:59')."'";
 } else {
   $whereEndPeriode='';
 }

 // gestion du sexe
 if($_POST['sexe'] == 'F' or $_POST['sexe'] == 'M') {
   $leftJoinSexe = "left join objets_data as sex on sex.toID=w.toID and sex.typeID='".$name2typeID['administrativeGenderCode']."' and sex.outdated='' and sex.deleted=''";
   $whereSexe = " and sex.value='".msSQL::cleanVar($_POST['sexe'])."'";
 } else {
   $leftJoinSexe=$whereSexe='';
 }

 $patientsList = msSQL::sql2tab("SELECT w.fromID, w.toID, w.registerDate,
 CASE
   WHEN TIMESTAMPDIFF(YEAR,STR_TO_DATE(bd.value, '%d/%m/%Y'),w.registerDate) >= 2 THEN  TIMESTAMPDIFF(YEAR,STR_TO_DATE(bd.value, '%d/%m/%Y'),w.registerDate)
   WHEN TIMESTAMPDIFF(MONTH,STR_TO_DATE(bd.value, '%d/%m/%Y'),w.registerDate) >= 1 THEN TIMESTAMPDIFF(MONTH,STR_TO_DATE(bd.value, '%d/%m/%Y'),w.registerDate)
   ELSE TIMESTAMPDIFF(DAY,STR_TO_DATE(bd.value, '%d/%m/%Y'),w.registerDate)
   END as ageALaPresc,
 CASE
   WHEN TIMESTAMPDIFF(YEAR,STR_TO_DATE(bd.value, '%d/%m/%Y'),w.registerDate) >= 2 THEN  'ans'
   WHEN TIMESTAMPDIFF(MONTH,STR_TO_DATE(bd.value, '%d/%m/%Y'),w.registerDate) >= 1 THEN 'mois'
   ELSE 'jours'
   END as ageALaPrescUnite,
 CASE
   WHEN o.value != '' and bn1.value != '' THEN concat(o.value, ' (', bn1.value, ') ', o2.value)
   WHEN o.value != '' THEN concat(o.value, ' ', o2.value)
   ELSE concat(bn1.value, ' ', o2.value)
   END as identiteDossier, spe.value as specialite, dc.value as dci, bd.value as birthdate
 FROM objets_data as w
 $leftJoinSexe
 left join objets_data as spe on spe.instance=w.instance and spe.typeID='".$name2typeID['lapMedicamentSpecialiteNom']."'
 left join objets_data as dc on dc.instance=w.instance and dc.typeID='".$name2typeID['lapMedicamentDC']."'
 left join objets_data as o on o.toID=w.toID and o.typeID='".$name2typeID['lastname']."' and o.outdated='' and o.deleted=''
 left join objets_data as o2 on o2.toID=w.toID and o2.typeID='".$name2typeID['firstname']."' and o2.outdated='' and o2.deleted=''
 left join objets_data as bn1 on bn1.toID=w.toID and bn1.typeID='".$name2typeID['birthname']."' and bn1.outdated='' and bn1.deleted=''
 left join objets_data as bd on bd.toID=w.toID and bd.typeID='".$name2typeID['birthdate']."' and bd.outdated='' and bd.deleted=''
 where w.outdated ='' and w.deleted=''and w.typeID = '".$name2typeID[msSQL::cleanVar($_POST['typeRecherche'])]."' and w.value like '".msSQL::cleanVar($_POST['code'])."' $whereBeginPeriode $whereEndPeriode $whereSexe
 order by w.registerDate desc
 ");

 header('Content-Type: application/json');
 echo json_encode(array('patientsList'=>$patientsList));
