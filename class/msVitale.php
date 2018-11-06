<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Carte Vitale et CPS
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msVitale
{

  private $_jsonCpsVitaleDataFromExternalMod;

/**
 * Obtenir du module tiers les data CPS et Vital au format JSON sans les rapprocher des dossiers patients potentiellement concordants
 * @return string data au format JSON
 */
  public function getJsonCpsVitaleDataFromExternalMod() {
    global $p;
    $file=$p['config']['protocol'].$p['config']['host'].$p['config']['urlHostSuffixe'].'/modulesExternes/'.$p['config']['vitaleService'].'/lireCpsEtVitale.php?hoteLecteurIp='.$p['config']['vitaleHoteLecteurIP'].'&nomRessourcePS='.$p['config']['vitaleNomRessourcePS'].'&nomRessourceLecteur='.$p['config']['vitaleNomRessourceLecteur'];

    return $this->_jsonCpsVitaleDataFromExternalMod=file_get_contents($file);
  }

/**
 * Obtenir du module tiers les data CPS et Vital au format JSON en les rapprochant des dossiers patients potentiellement concordants
 * @return string data au format JSON
 */
  public function getJsonCpsVitalDataWithPeopleID() {
    if(empty($this->_jsonCpsVitaleDataFromExternalMod)) $this->getJsonCpsVitaleDataFromExternalMod();

    $data=json_decode($this->_jsonCpsVitaleDataFromExternalMod, true);

    $name2typeID = new msData();
    $name2typeID = $name2typeID->getTypeIDsFromName(['nss']);
    if(!empty($data['vitale']['data'])) {
      foreach($data['vitale']['data'] as $index=>$dat) {
        if($toID = msSQL::sql2tabSimple("select toID from objets_data where typeID='".$name2typeID['nss']."' and value = '".$dat[9].$dat[10]."' group by toID ")) {
          $data['vitale']['correspondances'][$index]=$toID;
        }
      }
    }

    return json_encode($data);
  }

}
