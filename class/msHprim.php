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
 * Manipulations sur les données HPRIM
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


class msHprim
{

/**
 * Parser le paragraphe HPRIM machine d'un texte
 * @param  string $texte Le texte à traiter
 * @return array        Array avec le résultat
 */
  public static function parseSourceHprim($texte)
  {
      $lab = explode('****LAB****', $texte);
      if (isset($lab['1'])) {
          $lab = trim($lab['1']);
          if (!empty($lab)) {
              $lignes=explode("\n", $lab);
          }
          if (count($lignes)>0) {
              $r=[];
              foreach ($lignes as $ligne) {
                  $l=explode('|', $ligne);
                  $l=msTools::utf8_converter($l);
                  $l=array_pad($l, 13, '');
                  $l = array_map('trim', $l);

                  if ($l[0]=='RES' and strlen($l[1]) > 0) {
                      $r[]=array(
                      'label'=>$l[1],
                      'labelStandard'=>$l[2],
                      'typeResultat'=>$l[3],
                      'resultat'=>$l[4],
                      'unite'=>$l[5],
                      'normaleInf'=>$l[6],
                      'normaleSup'=>$l[7],
                      'indicateurAnormal'=>$l[8],
                      'statutRes'=>$l[9],
                      'resAutreU'=>$l[10],
                      'normaleInfAutreU'=>$l[11],
                      'normalSupAutreU'=>$l[12]
                );
                  }
              }
          }
          return $r;
      }
  }

/**
 * Enregistrer chaque lignee HPRIM dans la bdd
 * @param  array $tabRes  Array résultat de parseSourceHprim()
 * @param  int $fromID  ID du user
 * @param  int $toID    ID du patient concerné
 * @param  string $date    date au format date mysql (Y-m-d)
 * @param  int  $objetID ID de l'objet concerné (= ID du document source)
 * @return void
 */
    public static function saveHprim2bdd($tabRes, $fromID, $toID, $date, $objetID)
    {
        if (is_array($tabRes)) {
            foreach ($tabRes as $k => $v) {
                $v['fromID']=$fromID;
                $v['toID']=$toID;
                $v['date']=$date;
                $v['objetID']=$objetID;

                msSQL::sqlInsert('hprim', $v);

            }
        }
    }

/**
 * Parser en-tête HPRIM d'un fichier txt
 * @param  string $file fichier avec chemin complet
 * @return array       Tableau de résultat
 */
    public static function getHprimHeaderData($file)
    {
        $file = fopen("$file", "r");
        $count = "0";

        while ($count < 13) {
            $count++;
            switch ($count) {
              case "1":
              $d['codePatient'] = substr(fgets($file), 0, 10);
              break;

              case "2":
              $d['nom'] = substr(fgets($file),0 , 50);
              break;

              case "3":
              $d['prenom'] = substr(fgets($file),0 ,50);
              break;

              case "4":
              $d['adresse1'] = fgets($file);
              if(!empty($d['adresse1']) and is_numeric($d['adresse1'][0])) {
                $explo = explode(' ', $d['adresse1'], 2);
                $d['streetNumber'] = $explo[0];
                $d['street'] = $explo[1];
              }
              break;

              case "5":
              $d['adresse2'] = fgets($file);
              break;

              case "6":
              $line6 = fgets($file);
              $d['cp'] = substr($line6, 0, 5);
              $d['ville'] = substr($line6, 5);
              break;

              case "7":
              $d['ddn'] = fgets($file);
              break;

              case "8":
              $d['nss'] = fgets($file);
              if(!empty($d['nss'])) {
                if($d['nss'][0] == 1) {
                  $d['administrativeGenderCode'] = 'M';
                } elseif($d['nss'][0] == 2) {
                  $d['administrativeGenderCode'] = 'F';
                } else {
                  $d['administrativeGenderCode'] = 'U';
                }
              } else {
                $d['administrativeGenderCode'] = 'U';
              }
              break;

              case "9":
              $d['numDossier'] = fgets($file);
              break;

              case "10":
              $d['dateDossier'] = substr(fgets($file), 0, 15);
              break;

              case "11":
              $line11 = fgets($file);
              $d['codeExp'] = substr($line11, 0, 10);
              $d['expediteur'] = substr($line11, 10);
              break;

              case "12":
              $line12 = fgets($file);
              $d['codeDest'] = substr($line12, 0, 10);
              $d['destinataire'] = substr($line12, 10);
              break;
            }
        }
        $d = array_map('trim', $d);

        return $d;
    }

/**
 * Obtenir la liste des patients correspondant aux datas HPRIM
 * @param  array $hprimData Data HPRIM
 * @param int $patientID ID du patient
 * @return array            Array des patients possibles
 */
    public static function getPossiblePatients($hprimData, $patientID='')
    {
        $hprimData=array_map('trim', $hprimData);
        $hprimData=msSQL::cleanArray($hprimData);
        $nom=$ddn=$nss=$cp=array(''=>'');

        if(is_numeric($patientID)) {

          $final[$patientID] = 2;

        } else {
            $name2typeID = new msData();
            $name2typeID = $name2typeID->getTypeIDsFromName(['firstname', 'lastname', 'birthdate', 'birthname', 'postalCodePerso', 'nss']);

            //le nom de famille
            $nom=msSQL::sql2tabSimple("select toID from objets_data where typeID in ('".$name2typeID['lastname']."', '".$name2typeID['birthname']."') and value like '".$hprimData['nom']."' and outdated='' and deleted='' ");
            //le prenom
            $prenom=msSQL::sql2tabSimple("select toID from objets_data where typeID='".$name2typeID['firstname']."' and value like '".$hprimData['prenom']."' and outdated='' and deleted=''");
            //la ddn
            $ddn=msSQL::sql2tabSimple("select toID from objets_data where typeID='".$name2typeID['birthdate']."' and value = '".$hprimData['ddn']."' and outdated='' and deleted=''");
            //n secu
            $nss=msSQL::sql2tabSimple("select toID from objets_data where typeID='".$name2typeID['nss']."' and value = '".$hprimData['nss']."' and outdated='' and deleted=''");
            //code postal
            $cp=msSQL::sql2tabSimple("select toID from objets_data where typeID='".$name2typeID['postalCodePerso']."' and value = '".$hprimData['cp']."' and outdated='' and deleted=''");


            $final=array();
            if (is_array($nom)) {
                $final = array_merge($final, $nom);
            }
            if (is_array($prenom)) {
                $final = array_merge($final, $prenom);
            }
            if (is_array($ddn)) {
                $final = array_merge($final, $ddn);
            }
            if (is_array($nss)) {
                $final = array_merge($final, $nss);
            }
            if (is_array($cp)) {
                $final = array_merge($final, $cp);
            }

            $final=array_count_values($final);

            arsort($final);

            $final=array_slice($final, 0, 5, true);

        }

        foreach ($final as $k=>$v) {
            if ($v > 1) {
                $patient= new msPeople();
                $patient->setToID($k);
                $final[$k]=$patient->getSimpleAdminDatasByName();
                $final[$k]['patientType']=$patient->getType();
                $final[$k]['nbOccurence']=$v;
                $final[$k]['id']=$k;
            } else {
                unset($final[$k]);
            }
        }

        return array_values($final);
    }


}
