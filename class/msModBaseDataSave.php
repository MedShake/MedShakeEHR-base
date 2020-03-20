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
 *
 * Traitement d'une donnée avant enregistrement pour formatage
  *
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

class msModBaseDataSave
{

  /**
   * La suite n'est pas documentée mais s'analyse très simplement :
   * le nom variable dans le nom de la method correspond au nom du type
   */

      // dates clefs pour éviter les mauvaises suprises d'un javascript défaillant
      public function correctionDateBeforeSave($value) {
        $value=trim($value);
        // si absence de slash
        if(is_numeric($value) and strlen($value)==8) {
          $value=$value[0].$value[1].'/'.$value[2].$value[3].'/'.$value[4].$value[5].$value[6].$value[7];
        // si année sur 2 chiffres au lieu de 4
        } elseif(strlen($value)==8 and substr_count($value, '/') == 2 ) {
          $decompoDate=explode('/', $value);
          if(isset($decompoDate[2]) and strlen($decompoDate[2])==2) {
            $value=$decompoDate[0].'/'.$decompoDate[1].'/'.'20'.$decompoDate[2];
          }
        }
        return $value;
      }
      public function tbs_DDR($value)
      {
          return $this->correctionDateBeforeSave($value);
      }
      public function tbs_ddgReel($value)
      {
          return $this->correctionDateBeforeSave($value);
      }

      // identité : nom en majuscule, prenom 1er lettre maj
      public function tbs_lastname($value)
      {
          return  mb_strtoupper($value);
      }

      public function tbs_birthname($value)
      {
          return  mb_strtoupper($value);
      }

      public function tbs_firstname($value)
      {
          return mb_convert_case($value, MB_CASE_TITLE, "UTF-8");
      }

      public function tbs_othersfirstname($value)
      {
          return mb_convert_case($value, MB_CASE_TITLE, "UTF-8");
      }

      // format téléphone et fax
      public function telephoneNumberTreatBeforeSave($value)
      {
          $tel = preg_replace('([-_ \.])', '', $value);
          if (strlen($tel) == 10) {
              $tel = preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '\1 \2 \3 \4 \5', $tel);
              return $tel;
          } else {
            return $value;
          }

      }

      public function tbs_mobilePhone($value)
      {
          return $this->telephoneNumberTreatBeforeSave($value);
      }

      public function tbs_homePhone($value)
      {
          return $this->telephoneNumberTreatBeforeSave($value);
      }

      public function tbs_telPro($value)
      {
          return $this->telephoneNumberTreatBeforeSave($value);
      }

      public function tbs_faxPro($value)
      {
          return $this->telephoneNumberTreatBeforeSave($value);
      }

      public function tbs_mobilePhonePro($value)
      {
          return $this->telephoneNumberTreatBeforeSave($value);
      }

      public function tbs_telPro2($value)
      {
          return $this->telephoneNumberTreatBeforeSave($value);
      }

      //ville : en majuscule
      public function tbs_city($value)
      {
          return  mb_strtoupper($value);
      }

      public function tbs_villeAdressePro($value)
      {
          return  mb_strtoupper($value);
      }

      //Règle le problème du séparateur décimales
      public function formatDecimalNumber($value)
      {
          return str_replace(',', '.', $value);
      }
      public function tbs_regleCheque($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function tbs_regleCB($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function tbs_regleEspeces($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function tbs_regleFacture($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function tbs_regleTarifCejour($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function tbs_regleDepaCejour($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function tbs_regleTiersPayeur($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function tbs_clairanceCreatinine($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function tbs_creatinineMgL($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function tbs_creatinineMicroMolL($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function tbs_poids($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function tbs_taillePatient($value)
      {
          return $this->formatDecimalNumber($value);
      }
}
