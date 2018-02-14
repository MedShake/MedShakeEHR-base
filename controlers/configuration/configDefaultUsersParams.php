<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00>
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
 * Config : gérer les paramètres de configuration par défaut des utilisateurs
 *
 * @author fr33z00 <https://github.com/fr33z00>
 */

//admin uniquement
if (!msUser::checkUserIsAdmin()) {
    $template="forbidden";
} else {
    $debug='';
    $template='configDefaultUsersParams';

    $titres=array(
      'protocol'=>'Service Web',
      'sqlServeur'=>'Service MySQL',
      'PraticienPeutEtrePatient'=>"Permettre aux praticiens d'être patients",
      'administratifSecteurHonoraires'=>'administratif',
      'templatesPdfFolder'=>"Modèles de documents",
      'smtpTracking'=>'Serveur SMTP (Mail)',
      'apicryptCheminInbox'=>'Apicrypt',
      'faxService'=>'Fax en ligne',
      'dicomHost'=>'Dicom',
      'phonecaptureFingerprint'=>'PhoneCapture',
      'agendaService'=>'Agenda',
      'mailRappelLogCampaignDirectory'=>'Rappels RDV par mail',
      'smsProvider'=>'Rappels SMS (cf /servicesTiers/sms/ )',
      'templatesFolder'=>"Gestionnaire d'affichage"
      );
    $commentaires=array(
      'PraticienPeutEtrePatient'=>'si false, il faudra créer une fiche patient pour le praticien',
      'templatesPdfFolder'=>'dossier par défaut',
      'templateDefautPage'=>'modèle par defaut',
      'templateOrdoHeadAndFoot'=>'ordonnance',
      'templateOrdoBody'=>'ordonnance',
      'templateOrdoALD'=>'ordonnance',
      'templateCrHeadAndFoot'=>'compte rendu',
      'templateCourrierHeadAndFoot'=>'courrier et certificat',
      'agendaDistantLink'=>'si agendaService est actif, alors agendaDistantLink doit être vide',
      'twigEnvironnementCache'=>"false ou chemin (ex: /tmp/templates_cache/)"
    );
    unset($p['configDefaut']['homeDirectory']);
    foreach($p['configDefaut'] as $k=>$v) {
        if (array_key_exists($k, $titres)) {
            $p['page']['params'][]=array('type'=>'titre', 'value'=>$titres[$k]);
        }
        if ($v === true) {
            $v='true';
        } elseif ($v===false) {
            $v='false';
        }
        $p['page']['params'][]=array('type'=>'param', 'name'=>$k, 'value'=>$v, 'commentaire'=>array_key_exists($k, $commentaires)?$commentaires[$k]:'');
    }
}
