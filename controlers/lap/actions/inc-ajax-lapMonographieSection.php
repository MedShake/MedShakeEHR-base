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
 * LAP : ajax > charger une section de monographie
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$debug='';

$mono=new msLapMonographie();
$mono->setSpe($_POST['spe']);
$p['page']['spe']=$_POST['spe'];

if($_POST['section'] == 'administratif') {
  $p['page']['speData']=$mono->getSpeData();
  $p['page']['administratif']=$mono->getMonoAdministratif();
}
elseif($_POST['section'] == 'formesPharma') {
  $p['page']['speData']=$mono->getSpeData();
  $p['page']['formesPharma']=$mono->getMonoFormesPharmaceutiques();
}
elseif($_POST['section'] == 'composition') {
  $p['page']['composition']=$mono->getMonoComposition();
}
elseif($_POST['section'] == 'classifications') {
  $p['page']['classifications']=$mono->getMonoClassifications();
}
elseif($_POST['section'] == 'pharmacologie') {
  $p['page']['pharmacodynamie']=$mono->getMonoPharmacodynamie();
  $p['page']['pharmacocinetique']=$mono->getMonoPharmacocinetique();
  $p['page']['securitePreclinique']=$mono->getMonoSecuritePreclinique();
}
elseif($_POST['section'] == 'recommandations') {
  $p['page']['recommandations']=$mono->getMonoRecommandations();
}
elseif($_POST['section'] == 'presentations') {
  $p['page']['presentations']=$mono->getMonoPresentations();
}
elseif($_POST['section'] == 'indications') {
  $p['page']['indications']=$mono->getMonoIndications();
}
elseif($_POST['section'] == 'nonindications') {
  $p['page']['nonindications']=$mono->getMonoCIPEMG(4);
}
elseif($_POST['section'] == 'contreindications') {
  $p['page']['contreindications']=$mono->getMonoCIPEMG(1);
}
elseif($_POST['section'] == 'mgpe') {
  $p['page']['contreindications']=$mono->getMonoCIPEMG(2);
}
elseif($_POST['section'] == 'noncontreindications') {
  $p['page']['noncontreindications']=$mono->getMonoCIPEMG(3);
}
elseif($_POST['section'] == 'interactions') {
  $p['page']['interactions']=$mono->getMonoInteractionsMedicamenteuses();
}
elseif($_POST['section'] == 'posologies') {
  $p['page']['posologies']=$mono->getMonoPosologies();
}
elseif($_POST['section'] == 'modeAdministration') {
  $p['page']['modeAdministration']=$mono->getMonoModeAdministration();
}
elseif($_POST['section'] == 'grossesse') {
  $p['page']['grossesse']=$mono->getMonoGrossesse();
  $p['page']['allaitement']=$mono->getMonoAlaitementEtFemmeAgePocreer();
}
elseif($_POST['section'] == 'effetsindesirables') {
  $p['page']['effetsindesirables']=$mono->getMonoEffetsIndesirables();
}
elseif($_POST['section'] == 'conduite') {
  $p['page']['conduite']=$mono->getMonoConduite();
}
elseif($_POST['section'] == 'mvgeneriques') {
  $p['page']['mvPere']=$mono->getMonoMedicamentVirtuelTheriaque();
  $p['page']['gen']=$mono->getMonoGeneriques();
}

$html = new msGetHtml;
$html->set_template('lapMono'.ucfirst($_POST['section']).'.html.twig');
$html = $html->genererHtmlVar($p);

echo json_encode(array(
  'html'=>$html,
  'section'=>$_POST['section']
));
