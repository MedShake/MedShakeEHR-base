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
 * Calculs médicaux
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msModuleCalcMed
{


/**
 * Calcule de l'IMC
 * @param  float $poidskg  poids en kg
 * @param  float $taillecm taille en cm
 * @return float           IMC
 */
      public static function imc($poidskg, $taillecm) {
        if(is_numeric($poidskg) and is_numeric($taillecm)) {
          return number_format(round($poidskg / ($taillecm/100 * $taillecm/100), 1), 1, '.', '');
        }
      }

}
