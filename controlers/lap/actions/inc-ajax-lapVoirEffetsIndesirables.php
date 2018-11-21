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
 * LAP : ajax > voir effets indésirables d'un medic
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */
$debug='';
//$template='inc-lapSearchMedicTableResults';

$lap = new msLap;
$p['page']['spe']=$lap->getSpecialiteByCode($_POST['codeSpe'], 1, 3)[0];
$p['page']['eiCli']=array_column($lap->getEffetsIndesirables($_POST['codeSpe'],1),'texteffet');
$p['page']['eiParaCli']=array_column($lap->getEffetsIndesirables($_POST['codeSpe'],2),'texteffet');
$p['page']['eiCliSd']=array_column($lap->getEffetsIndesirables($_POST['codeSpe'],3),'texteffet');
$p['page']['eiParaCliSd']=array_column($lap->getEffetsIndesirables($_POST['codeSpe'],4),'texteffet');

sort($p['page']['eiCli']);

$html = new msGetHtml;
$html->set_template('inc-lapInfosMedicEI');
$html = $html->genererHtmlVar($p);

echo json_encode(array(
  'html'=>$html,
  'titreModal'=>$p['page']['spe']['sp_nomlong']
));
