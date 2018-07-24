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
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

class msModBaseCalcMed
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

/**
 * DDR vers DDG
 * @param  string $ddr DDR au format d/m/Y
 * @return string      DDG au format d/m/Y
 */
    public static function ddr2ddg($ddr)
    {
        $date = DateTime::createFromFormat('d/m/Y', $ddr);
        $date->add(new DateInterval('P14D'));
        return $date->format('d/m/Y');
    }


/**
 * DDG vers DDR
 * @param  string $ddg DDG au format d/m/Y
 * @return string      DDR au format d/m/Y
 */
    public static function ddg2ddr($ddg)
    {
        $date = DateTime::createFromFormat('d/m/Y', $ddg);
        $date->sub(new DateInterval('P14D'));
        return $date->format('d/m/Y');
    }

/**
 * DDR vers terme au jour
 * @param  string $ddr  DDR au format d/m/Y
 * @param  string $jour Jour au format d/m/Y
 * @return string       terme au format xSA + xJ
 */
    public static function ddr2terme($ddr, $jour)
    {
        $ddr = DateTime::createFromFormat('d/m/Y', $ddr);
        $jour = DateTime::createFromFormat('d/m/Y', $jour);
        $interval = date_diff($ddr, $jour);
        $nbjours=$interval->format('%a');

        $nbsemaines = floor($nbjours/7);
        $plus = $nbjours-($nbsemaines *7);
        $chaine =  $nbsemaines.'SA';
        if ($plus > 0) {
            $chaine.=' + '.$plus.'J';
        }
        return $chaine;
    }


/**
 * DDG vers terme au jour
 * @param  string $ddg  DDG au format d/m/Y
 * @param  string $jour Jour au format d/m/Y
 * @return string       terme au format xSA + xJ
 */
    public static function ddg2terme($ddg, $jour)
    {
        $ddg = DateTime::createFromFormat('d/m/Y', $ddg);
        $jour = DateTime::createFromFormat('d/m/Y', $jour);
        $interval = date_diff($ddg, $jour);
        $nbjours=$interval->format('%a') + 14; #on corrige pour sortir en SA

        $nbsemaines = floor($nbjours/7);
        $plus = $nbjours-($nbsemaines *7);
        $chaine =  $nbsemaines.'SA';
        if ($plus > 0) {
            $chaine.=' + '.$plus.'J';
        }
        return $chaine;
    }

/**
 * DDG vers le terme exprimé en SA
 * @param  string $ddg  DDG au format d/m/Y
 * @param  string $jour Jour au format d/m/Y
 * @return float       Nb de SA avec 1 décimale
 */
    public static function ddg2termeMath($ddg, $jour)
    {
        $ddg = DateTime::createFromFormat('d/m/Y', $ddg);
        $jour = DateTime::createFromFormat('d/m/Y', $jour);
        $interval = date_diff($ddg, $jour);
        $nbjours=$interval->format('%a') + 14; #on corrige pour sortir en SA

        return ($nbjours/7);
    }

}
