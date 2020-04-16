<?php
/*
* This file is part of MedShakeEHR.
*
* Copyright (c) 2020
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
* Gestion des individus : droits entre eux
*
* @author Bertrand Boutillier <b.boutillier@gmail.com>
*/

class msPeopleRelationsDroits extends msPeopleRelations
{

/**
 * Obtenir le statut d'un utilisateur dans un groupe
 * @return string membre / admin
 */
      public function getCurrentUserStatusInGroup() {
        global $p;
        return $this->_getPeopleStatus($p['user']['id'], $this->_toID);
      }

/**
 * Obtenir les registres autorisés dans un dossier patient
 * on remonte aux registres via patientId -> pratId créateur du dossier -> groupes -> registres
 * @return array array peopleId=>data
 */
      public function getRegistresPatient($onlyActiv = false) {
        $pratID = $this->getFromID();
        $this->setToID($pratID);
        $this->setRelationType('relationPraticienGroupe');
        $lRegistres=[];
        if($groupes = $this->getRelations()) {
          foreach($groupes as $groupe=>$gdata) {
            $registres = new msPeopleRelations();
            $registres->setToID($gdata['peopleID']);
            $registres->setRelationType('relationGroupeRegistre');
            if($listeRegistres = $registres->getRelations(['registryState', 'registryname', 'registryPrefixTech'])) {
              foreach($listeRegistres as $registreData) {
                if(($onlyActiv and $registreData['registryState'] == 'actif') or $onlyActiv == false) {
                  $lRegistres[$registreData['peopleID']]=$registreData;
                }
              }
            }
          }
        }
        return $lRegistres;
      }

/**
 * Obtenir le statut d'un people vis à vis d'un autre
 * @param  int $people1 people dont il faut obtenir le statut
 * @param  int $people2 people en vis à vis
 * @return string          statut ou null si relation inexistante.
 */
      private function _getPeopleStatus($people1, $people2) {

        if (!is_numeric($people1)) {
            throw new Exception('People1 is not numeric');
        }

        if (!is_numeric($people2)) {
            throw new Exception('People2 is not numeric');
        }

        $data = new msData();
        $name2typeID = $data->getTypeIDsFromName(['relationID']);

        $status = msSql::sqlUniqueChamp("select c.value as typeRelation
        from objets_data as o
        inner join objets_data as c on c.instance=o.id
        where o.toID='".$people1."' and o.typeID='".$name2typeID['relationID']."' and o.deleted='' and o.outdated='' and o.value='".$people2."'
        limit 1");
        if(!$status) {
          return null;
        } else {
          return $status;
        }

      }

}
