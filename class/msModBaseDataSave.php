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
 */

class msModuleDataSave
{

  /**
   * La suite n'est pas documentée mais s'analyse très simplement :
   * l'ID variable dans le nom de la method correspond au typeID
   */

      // dates clefs pour éviter les mauvaises suprises d'un javascript défaillant
      public function correctionDateBeforeSave($value) {
        $value=trim($value);
        if(is_numeric($value) and strlen($value)==8) {
          $value=$value{0}.$value{1}.'/'.$value{2}.$value{3}.'/'.$value{4}.$value{5}.$value{6}.$value{7};
        }
        return $value;
      }
      public function type45TreatBeforeSave($value)
      {
          return $this->correctionDateBeforeSave($value);
      }
      public function type49TreatBeforeSave($value)
      {
          return $this->correctionDateBeforeSave($value);
      }

      // identité : nom en majuscule, prenom 1er lettre maj
      public function type2TreatBeforeSave($value)
      {
          return  mb_strtoupper($value);
      }

      public function type1TreatBeforeSave($value)
      {
          return  mb_strtoupper($value);
      }

      public function type3TreatBeforeSave($value)
      {
          return mb_convert_case($value, MB_CASE_TITLE, "UTF-8");
      }

      public function type22TreatBeforeSave($value)
      {
          return mb_convert_case($value, MB_CASE_TITLE, "UTF-8");
      }

      // format téléphone et fax
      public function telephoneNumberTreatBeforeSave($value)
      {
          $tel = str_replace(' ', '', $value);
          if (strlen($tel) == 10) {
              $tel = preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '\1 \2 \3 \4 \5', $tel);
              return $tel;
          } else {
            return $value;
          }

      }

      public function type7TreatBeforeSave($value)
      {
          return $this->telephoneNumberTreatBeforeSave($value);
      }

      public function type10TreatBeforeSave($value)
      {
          return $this->telephoneNumberTreatBeforeSave($value);
      }

      public function type57TreatBeforeSave($value)
      {
          return $this->telephoneNumberTreatBeforeSave($value);
      }

      public function type58TreatBeforeSave($value)
      {
          return $this->telephoneNumberTreatBeforeSave($value);
      }

      public function type247TreatBeforeSave($value)
      {
          return $this->telephoneNumberTreatBeforeSave($value);
      }

      public function type248TreatBeforeSave($value)
      {
          return $this->telephoneNumberTreatBeforeSave($value);
      }

      //ville : en majuscule
      public function type12TreatBeforeSave($value)
      {
          return  mb_strtoupper($value);
      }

      public function type56TreatBeforeSave($value)
      {
          return  mb_strtoupper($value);
      }

      //Règle le problème du séparateur décimales
      public function formatDecimalNumber($value)
      {
          return str_replace(',', '.', $value);
      }
      public function type193TreatBeforeSave($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function type194TreatBeforeSave($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function type195TreatBeforeSave($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function type196TreatBeforeSave($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function type198TreatBeforeSave($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function type199TreatBeforeSave($value)
      {
          return $this->formatDecimalNumber($value);
      }
      public function type200TreatBeforeSave($value)
      {
          return $this->formatDecimalNumber($value);
      }

}
