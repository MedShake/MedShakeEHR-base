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
 * Patient > action : changer la création date d'un élément de l'historique
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

if (isset($_POST['objetID']) and isset($_POST['newCreationDate'])) {
    if (!is_numeric($_POST['objetID'])) {
        die("Erreur:L'objet indiqué n'est pas valide");
    }
    if (!msTools::validateDate($_POST['newCreationDate'], 'Y-m-d H:i:s')) {
        die("Erreur:La date indiquée n'est pas valide");
    }

    $objet=new msObjet();
    $objet->setObjetID($_POST['objetID']);
    $objet->setCreationDate($_POST['newCreationDate']);
    $objet->changeCreationDate();

    echo('ReloadHistorique');
} else {
  echo('Avertissement: aucun élément modifié');
}
