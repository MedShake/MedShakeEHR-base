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
 * @contrib Michaël Val
 */

class msModBaseDataSave
{
    /**
     * Méthode centrale pour trimmer et traiter les valeurs avant enregistrement
     */
    public function processValueBeforeSave($value, $type)
    {
        // Remplacer les espaces insécables par des espaces standard
        $value = preg_replace('/\xc2\xa0/', ' ', $value);
        // Supprimer les espaces avant et après
        $value = trim($value);

        switch ($type) {
            case 'uppercase':
                return mb_strtoupper($value);

            case 'titlecase':
                return mb_convert_case($value, MB_CASE_TITLE, "UTF-8");

            case 'phone':
                $tel = preg_replace('([-_ \.])', '', $value);
                if (strlen($tel) == 10) {
                    return preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '\1 \2 \3 \4 \5', $tel);
                }
                return $value;

            case 'decimal':
                return str_replace(',', '.', $value);

            case 'date':
                if (is_numeric($value) && strlen($value) == 8) {
                    return $value[0] . $value[1] . '/' . $value[2] . $value[3] . '/' . $value[4] . $value[5] . $value[6] . $value[7];
                } elseif (strlen($value) == 8 && substr_count($value, '/') == 2) {
                    $decompoDate = explode('/', $value);
                    if (isset($decompoDate[2]) && strlen($decompoDate[2]) == 2) {
                        return $decompoDate[0] . '/' . $decompoDate[1] . '/' . '20' . $decompoDate[2];
                    }
                }
                return $value;

            case 'trim':
                return $value;

            default:
                return $value;
        }
    }

    /**
     * Méthodes spécifiques pour les différents types de données
     */
    public function tbs_DDR($value)
    {
        return $this->processValueBeforeSave($value, 'date');
    }

    public function tbs_ddgReel($value)
    {
        return $this->processValueBeforeSave($value, 'date');
    }

    public function tbs_lastname($value)
    {
        return $this->processValueBeforeSave($value, 'uppercase');
    }

    public function tbs_birthname($value)
    {
        return $this->processValueBeforeSave($value, 'uppercase');
    }

    public function tbs_firstname($value)
    {
        return $this->processValueBeforeSave($value, 'titlecase');
    }

    public function tbs_othersfirstname($value)
    {
        return $this->processValueBeforeSave($value, 'titlecase');
    }

    public function tbs_mobilePhone($value)
    {
        return $this->processValueBeforeSave($value, 'phone');
    }

    public function tbs_homePhone($value)
    {
        return $this->processValueBeforeSave($value, 'phone');
    }

    public function tbs_telPro($value)
    {
        return $this->processValueBeforeSave($value, 'phone');
    }

    public function tbs_faxPro($value)
    {
        return $this->processValueBeforeSave($value, 'phone');
    }

    public function tbs_mobilePhonePro($value)
    {
        return $this->processValueBeforeSave($value, 'phone');
    }

    public function tbs_telPro2($value)
    {
        return $this->processValueBeforeSave($value, 'phone');
    }

    public function tbs_city($value)
    {
        return $this->processValueBeforeSave($value, 'uppercase');
    }

	public function tbs_postalCodePerso($value)
    {
        return $this->processValueBeforeSave($value, 'trim');
    }

	public function tbs_streetNumber($value)
    {
        return $this->processValueBeforeSave($value, 'trim');
    }

    public function tbs_street($value)
    {
        return $this->processValueBeforeSave($value, 'trim');
    }

    public function tbs_postalCodePro($value)
    {
        return $this->processValueBeforeSave($value, 'trim');
    }

    public function tbs_villeAdressePro($value)
    {
        return $this->processValueBeforeSave($value, 'uppercase');
    }

	public function tbs_numAdressePro($value)
    {
        return $this->processValueBeforeSave($value, 'trim');
    }

    public function tbs_rueAdressePro($value)
    {
        return $this->processValueBeforeSave($value, 'trim');
    }

    public function tbs_regleCheque($value)
    {
        return $this->processValueBeforeSave($value, 'decimal');
    }

    public function tbs_regleCB($value)
    {
        return $this->processValueBeforeSave($value, 'decimal');
    }

    public function tbs_regleEspeces($value)
    {
        return $this->processValueBeforeSave($value, 'decimal');
    }

    public function tbs_regleFacture($value)
    {
        return $this->processValueBeforeSave($value, 'decimal');
    }

    public function tbs_regleTarifCejour($value)
    {
        return $this->processValueBeforeSave($value, 'decimal');
    }

    public function tbs_regleDepaCejour($value)
    {
        return $this->processValueBeforeSave($value, 'decimal');
    }

    public function tbs_regleTiersPayeur($value)
    {
        return $this->processValueBeforeSave($value, 'decimal');
    }

    public function tbs_clairanceCreatinine($value)
    {
        return $this->processValueBeforeSave($value, 'decimal');
    }

    public function tbs_creatinineMgL($value)
    {
        return $this->processValueBeforeSave($value, 'decimal');
    }

    public function tbs_creatinineMicroMolL($value)
    {
        return $this->processValueBeforeSave($value, 'decimal');
    }

    public function tbs_poids($value)
    {
        return $this->processValueBeforeSave($value, 'decimal');
    }

    public function tbs_taillePatient($value)
    {
        return $this->processValueBeforeSave($value, 'decimal');
    }

    public function tbs_nss($value)
    {
        return $this->processValueBeforeSave($value, 'trim');
    }

    public function tbs_nmu($value)
    {
        return $this->processValueBeforeSave($value, 'trim');
    }

}