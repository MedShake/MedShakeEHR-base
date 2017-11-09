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
 * Module gynéco obstétrique
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
   * DDG vers Dates marqueurs T1
   * @param  string $ddg DDG au format d/m/Y
   * @return array      array
   */
    public static function ddg2datesMST21($ddg)
    {
        #dc1 start
        $date = DateTime::createFromFormat('d/m/Y', $ddg);
        $date->add(new DateInterval('P63D'));
        $tab['dc1_s']=$date->format('d/m/Y');
        #dc1 end
        $date = DateTime::createFromFormat('d/m/Y', $ddg);
        $date->add(new DateInterval('P83D'));
        $tab['dc1_e']=$date->format('d/m/Y');

        #ds2 start
        $date = DateTime::createFromFormat('d/m/Y', $ddg);
        $date->add(new DateInterval('P84D'));
        $tab['ds2_s']=$date->format('d/m/Y');
        #ds2 end
        $date = DateTime::createFromFormat('d/m/Y', $ddg);
        $date->add(new DateInterval('P111D'));
        $tab['ds2_e']=$date->format('d/m/Y');

        #ms2 start
        $date = DateTime::createFromFormat('d/m/Y', $ddg);
        $date->add(new DateInterval('P84D'));
        $tab['ms2_s']=$date->format('d/m/Y');
        #ms2 end
        $date = DateTime::createFromFormat('d/m/Y', $ddg);
        $date->add(new DateInterval('P111D'));
        $tab['ms2_e']=$date->format('d/m/Y');

        return $tab;
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

// DDG vers terme du jour en SA avec 1 decimale
/**
 * DDG vers le terme exprimé en SA avec 1 décimale
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

        return round(($nbjours/7), 1);
    }

/**
 * Calculer percentile PC
 * @param  float $PCm PC
 * @param  float $SA  SA
 * @return int      percentile
 */
    public static function pc100($PCm, $SA)
    {
        $PC = round((44.4924 * 1 - (2.7182 * ($SA)) * 1 + (0.6673 * pow(($SA), 2)) * 1 - (0.0107 * pow(($SA), 3))), 2);
        $PCds = round((2.7945 * 1 + (0.345 * ($SA))), 2);
        $PCzs = round(($PCm - $PC) / $PCds, 2);
        $PC100 = round(((1 / (1 + exp(-1.5976 * ($PCzs) - 0.0706 * pow(($PCzs), 3)))) * 100), 2);
        return round($PC100);
    }

/**
 * Calculer percentile BIP
 * @param  float $BIPm BIP
 * @param  float $SA  SA
 * @return int      percentile
 */
    public static function bip100($BIPm, $SA)
    {
        $BIP = round((31.2452 * 1 - (2.8466 * ($SA)) * 1 + (0.2577 * pow(($SA), 2)) * 1 - (0.0037 * pow(($SA), 3))), 2);
        $BIPds = round((1.5022 * 1 + (0.0636 * ($SA))), 2);
        $BIPzs = round((($BIPm - $BIP) / $BIPds), 2);
        $BIP100 = round(((1 / (1 + exp(-1.5976 * ($BIPzs) - 0.0706 * pow(($BIPzs), 3)))) * 100), 2);
        return round($BIP100);
    }

/**
 * Calculer percentile PA
 * @param  float $PAm PA
 * @param  float $SA  SA
 * @return int      percentile
 */
    public static function pa100($PAm, $SA)
    {
        $PA = round((42.7794 * 1 - (2.7882 * ($SA)) * 1 + (0.5715 * pow(($SA), 2)) * 1 - (0.008 * pow(($SA), 3))), 2);
        $PAds = round((-2.3658 * 1 + (0.6459 * ($SA))), 2);
        $PAzs = round((($PAm - $PA) / $PAds), 2);
        $PA100 = round(((1 / (1 + exp(-1.5976 * ($PAzs) - 0.0706 * pow(($PAzs), 3)))) * 100), 2);
        return round($PA100);
    }

/**
 * Calculer percentile Fémur
 * @param  float $LFm LF
 * @param  float $SA  SA
 * @return int      percentile
 */
    public static function lf100($LFm, $SA)
    {
        $LF = round((-27.085 * 1 + (2.9223 * ($SA)) * 1 + (0.0148 * pow(($SA), 2)) * 1 - (0.0006 * pow(($SA), 3))), 2);
        $LFds = round((1.0809 * 1 + (0.0609 * ($SA))), 2);
        $LFzs = round((($LFm - $LF) / $LFds), 2);
        $LF100 = round(((1 / (1 + exp(-1.5976 * ($LFzs) - 0.0706 * pow(($LFzs), 3)))) * 100), 2);
        return round($LF100);
    }

/**
 * Calculer percentile poids
 * @param  float $EPFcalc PA
 * @param  float $SA  SA
 * @return int      percentile
 */
    public function poids100($EPFcalc, $SA)
    {
        $EPFatt = round((pow(2.71828182845904, (0.578 + (0.332*($SA)) * 1 - (0.00354 * pow(($SA), 2))))), 2);
        $EPFds = round((0.127 * ($EPFatt)), 2);
        $EPFzs = round((($EPFcalc - $EPFatt)/$EPFds), 2);
        $EPF100 = round(((1/(1 + exp(-1.5976 * ($EPFzs) - 0.0706 * pow(($EPFzs), 3)))) * 100), 2);
        return round($EPF100);
    }

}
