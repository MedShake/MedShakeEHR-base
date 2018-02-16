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
 * Actions communes aux formulaires médicaux et calculs médicaux
 * nécessaires au module
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

/**
 * Fonctions JS pour les calcules médicaux
 *
 */


// arrondir
function arrondir(nombre) {
  return Math.round(nombre * 1) / 1
}

function arrondir10(nombre) {
  return Math.round(nombre * 10) / 10
}

function arrondir100(nombre) {
  return Math.round(nombre * 100) / 100
}

// calcul IMC
function imcCalc(poids, taille) {

  taille = taille.replace(",", ".") / 100;
  poids = poids.replace(",", ".");

  if (taille > 0 && poids > 0) {
    imc = Math.round(poids / (taille * taille) * 10) / 10;
    if (imc >= 5 && imc < 90) {
      imc = imc;
    } else {
      imc = '';
    }
    return imc;
  }
}
