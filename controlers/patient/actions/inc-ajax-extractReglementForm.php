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
 * Patient > ajax : obtenir le formulaire de règlement
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

$debug='';

if (!isset($delegate)) {
  if (!isset($_POST['objetID']) || $_POST['objetID']==='') {
      $reglementForm=$_POST['reglementForm'];
      $porteur=$_POST['porteur'];
      $userID=$p['user']['id'];
      $module=$_POST['module'];
  } else {
      $res=msSQL::sql2tab("SELECT dt.module AS module, dt.formValues AS form, dt.name as porteur, dt.fromID AS userID FROM data_types as dt
        LEFT JOIN objets_data as od ON dt.id=od.typeID
        WHERE od.id='".$_POST['objetID']."' limit 1");
      $reglementForm=$res[0]['form'];
      $porteur=$res[0]['porteur'];
      $userID=$res[0]['userID'];
      $module=$res[0]['module'];
  }
  //si le formulaire de règlement n'est pas celui de base, c'est au module de gérer (à moins qu'il délègue)
  if ($reglementForm!='baseReglement') {
      $hook=$p['config']['homeDirectory'].'/controlers/module/'.$module.'/patient/actions/inc-ajax-extractReglementForm.php';
      if ($module!='' and $module!='base' and is_file($hook)) {
          include $hook;
      }
      if (!isset($delegate)) {
          return;
      }
  }
}

//template
$template="patientReglementForm";

//patient
$p['page']['patient']['id']=$_POST['patientID'];


//pour menu de choix de l'acte, par catégories
if ($tabTypes=msSQL::sql2tab("select a.* , c.label as catLabel
  from actes as a
  left join actes_cat as c on c.id=a.cat
  where a.toID in ('0','".$userID."') and c.module='".$module."'
  group by a.id
  order by c.displayOrder, c.label asc, a.label asc")) {
    foreach ($tabTypes as $k=>$v) {

        //n° de facture correspondant
        $v['numIndexFSE']=$k+1;

        //on récupère détails
        $v['details']=Spyc::YAMLLoad($v['details']);


        $p['page']['menusActes'][$v['catLabel']][]=$v;
    }
}

//edition : acte choisi :
if (isset($_POST['objetID'])) {
    $p['page']['formActes']['prevalue']=msSQL::sqlUniqueChamp("select parentTypeID from objets_data where id='".$_POST['objetID']."' limit 1 ");
} else {
    $p['page']['formActes']['prevalue']=null;
}
$form = new msForm();
$form->setFormIDbyName($reglementForm);
$form->setTypeForNameInForm('byName');
if (isset($_POST['objetID'])) {
    $form->setPrevalues(msSQL::sql2tabKey("select typeID, value from objets_data where id='".$_POST['objetID']."' or instance='".$_POST['objetID']."'", 'typeID', 'value'));
}
$p['page']['form']=$form->getForm();
$form->addSubmitToForm($p['page']['form'], 'btn-warning btn-lg btn-block');

//ajout champs cachés au form
$p['page']['form']['addHidden']=array(
  'porteur'=>$porteur,
  'reglementForm'=>$reglementForm,
  'module'=>$module,
  'patientID'=>$_POST['patientID'],
  'acteID'=>$p['page']['formActes']['prevalue'],
);
if (isset($_POST['objetID'])) {
    $p['page']['form']['addHidden']['objetID']=$_POST['objetID'];
}
