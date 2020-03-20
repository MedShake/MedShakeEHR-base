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
 * Patient > ajax : obtenir les détails d'une ligne de l'historique
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

if (is_numeric($_GET['objetID'])) {

    $preview = new msModBaseObjetPreview;
    $preview->setObjetID($_GET['objetID']);
    $objetGroupe=$preview->getObjetGroupe();
    $objetName=$preview->getObjetName();
    $objetModule=$preview->getObjetModule();

    if ($objetGroupe=="doc") {
        echo $preview->getGenericPreviewDocument();
    } elseif ($objetGroupe=="reglement") {
        echo $preview->getGenericPreviewReglement();
    } elseif ($objetGroupe=="mail") {
        echo $preview->getGenericPreviewMail();
    } elseif ($objetGroupe=="ordo" and $objetName=="lapExtOrdonnance") {
        echo $preview->getGenericPreviewOrdoLapExt();
    } elseif ($objetGroupe=="ordo") {
        echo $preview->getGenericPreviewOrdo();
    } elseif ($objetGroupe=="courrier") {
        echo $preview->getGenericPreviewCourrier();
    } elseif($objetGroupe=="typecs") {


        $classModuleObjet = 'msMod'.ucfirst($objetModule).'ObjetPreview';
        $methode = 'getPreview'.ucfirst($objetName);

        //si méthode existe dans base
        if(method_exists('msModBaseObjetPreview',$methode)) {
          echo $preview->$methode();
        }
        // si méthode existe dans extension proposé par le module dont le type dépend
        elseif(method_exists($classModuleObjet,$methode)) {
          $previewExtend = new $classModuleObjet;
          $previewExtend->setObjetID($_GET['objetID']);
          echo $previewExtend->$methode();
        }
        // si document qui peut être signé -> affichage du PDF
        elseif($preview->getCanBeSigned()) {
          echo $preview->getGenericPreviewPDF();
        }
        // sinon on tente au final avec le template impression
        else {
          echo $preview->getGenericPreviewFromPrintTemplate();
        }

    } else {
      echo $preview->getGenericPreviewFromPrintTemplate();
    }
    exit();
}
