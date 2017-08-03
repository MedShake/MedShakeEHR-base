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
 * Extraction des données dicom SR en provenance d'Orthanc
 * NB : Utilise dcm2xml
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msDicomSR extends msDicom
{
    protected $_xmlSrData;
    protected $_xmlAllNumContainers;
    protected $_allNumArray;
    protected $_formatedDataSR;


/**
 * Obtenir les data SR d'une instance SR
 * @return array array des data
 */
    public function getSrData()
    {
        if (!isset($this->_dcInstanceID)) {
            throw new Exception('InstanceID is not set');
        }

        $this->getDcmFile();
        $this->convertDcm2Xml();
        $this->loadXmlSR();
        $this->extracAllContainerFromXml();
        $this->extractNumFromContainer();
        return $this->formatSrData();
    }


/**
 * Rapatrier un fichier .dcm via une requète curl
 * @return void
 */
    public function getDcmFile()
    {
        if (!isset($this->_dcInstanceID)) {
            throw new Exception('InstanceID is not set');
        }

        global $p;
        $url=$this->_baseCurlUrl.'/instances/'.$this->_dcInstanceID.'/file/';
        msTools::checkAndBuildTargetDir($p['config']['dicomWorkingDirectory'].$p['user']['id'].'/');
        $saveto = $p['config']['dicomWorkingDirectory'].$p['user']['id'].'/'.$this->_dcInstanceID.'.dcm';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $raw=curl_exec($ch);
        curl_close($ch);
        if (strlen($raw)>0) {
            file_put_contents($saveto, $raw);
        }
        
    }

/**
 * Convertir un dcm SR en xml
 * @return void
 */
      public function convertDcm2Xml()
      {
          global $p;
          $from = $p['config']['dicomWorkingDirectory'].$p['user']['id'].'/'.$this->_dcInstanceID.'.dcm';
          $to = $p['config']['dicomWorkingDirectory'].$p['user']['id'].'/'.$this->_dcInstanceID.'.xml';
          exec("dsr2xml $from $to", $output);
      }

/**
 * Charger le XML
 * @return [type] [description]
 */
    public function loadXmlSR()
    {
        global $p;
        $xmlfile = $p['config']['dicomWorkingDirectory'].$p['user']['id'].'/'.$this->_dcInstanceID.'.xml';
        $this->_xmlSrData = file_get_contents($xmlfile);
    }

/**
 * Extraire tous les containers du XML
 * @return object containers extraits
 */
    public function extracAllContainerFromXml()
    {

        //$xml = new SimpleXMLElement($this->_xmlSrData);
        if ($xml = simplexml_load_string($this->_xmlSrData)) {
            return $this->_xmlAllNumContainers = $xml->xpath('/report/document/content/container/container[//num]');
        }
    }

/**
 * Extraire toutes les valeurs numériques des containers
 * @return array  array des valeurs numériques
 */
    public function extractNumFromContainer()
    {
        $i=0;
        if (isset($this->_xmlAllNumContainers)) {
            foreach ($this->_xmlAllNumContainers as $k=>$cont) {
                $foetusID=1;
                $struct=[];

          // foetus ID
          if (isset($cont->text->concept->value)) {
              if ($cont->text->concept->value == '11951-1') {
                  $foetusID=$cont->text->value;
              }
          }

          // structure (concept)
          //  if (isset($cont->concept)) {
          //      $struct[(string) $cont->concept->value]= (string) $cont->concept->meaning;
          //  }

          // structure (racine)
          // if (isset($cont->code->value)) {
          //     $struct[(string) $cont->code->value]= (string) $cont->code->meaning;
          // }

          // mesures (racine)
          foreach ($cont->num as $mesure) {
              $data[$i] =  array(
              'CodeValue'=> (string) $mesure->concept->value,
              'CodeMeaning'=> (string) $mesure->concept->meaning,
              'NumericValue'=> (string) $mesure->value,
              'MeasurementUnits'=> (string) $mesure->unit->value,
              'FoetusID'=> (string) $foetusID
              );

              // si structure
              if (!empty($struct)) {
                  $data[$i]['structure'] = $struct;
              }

              // si code
              if ($mesure->code->meaning) {
                  $data[$i]['precision'][(string) $mesure->code->value] =  (string) $mesure->code->meaning;
              }

              $i++;
          }

          //sous cont
          foreach ($cont->container as $cont) {

            // foetus ID
            if (isset($cont->text->concept->value)) {
                if ($cont->text->concept->value == '11951-1') {
                    $foetusID=$cont->text->value;
                }
            }

            // structure (concept)
             if (isset($cont->concept)) {
                 $struct[(string) $cont->concept->value]= (string) $cont->concept->meaning;
             }

            // structure (racine)
            if (isset($cont->code->value)) {
                $struct[(string) $cont->code->value]= (string) $cont->code->meaning;
            }


              foreach ($cont->num as $mesure) {
                  $data[$i] =  array(
                'CodeValue'=> (string) $mesure->concept->value,
                'CodeMeaning'=> (string) $mesure->concept->meaning,
                'NumericValue'=> (string) $mesure->value,
                'MeasurementUnits'=> (string) $mesure->unit->value,
                'FoetusID'=> (string) $foetusID
                );

                // si structure
                if (!empty($struct)) {
                    $data[$i]['structure'] = $struct;
                }

                // si code
                if ($mesure->code->meaning) {
                    $data[$i]['precision'][(string) $mesure->code->value] =  (string) $mesure->code->meaning;
                }

                  $i++;
              }
          }
            }
            return $this->_allNumArray = $data;
        }
    }

/**
 * Formater les valeurs numériques, les regrouper, faire des calculs
 * @return array array des valeurs numérique du SR
 */
    public function formatSrData()
    {
        global $p;
        $data=[];
        if (isset($this->_allNumArray)) {
            foreach ($this->_allNumArray as $v) {
                if (!empty($v['CodeValue'])) {
                    $clef=$v['FoetusID'].'.'.$v['CodeValue'];
                    if (!empty($v['structure'])) {
                        $clef.='.'.@implode('.', @array_keys($v['structure']));
                    }


                    $clefReadabled='F'.$v['FoetusID'].'::'.@implode('::', $v['structure']).'::'.strtoupper($v['CodeMeaning']);
                    $clefReadabled=str_replace(" ", "_", $clefReadabled);


                    if (!key_exists($clef, $data)) {
                        $data[$clef]=$v;
                        unset($data[$clef]['NumericValue']);
                    }
                    $data[$clef]['clefReadabled']=$clefReadabled;

                    if (isset($v['precision'])) {
                        foreach ($v['precision'] as $pk=>$pv) {
                            $data[$clef]['PrecisionValue'][$pk]=$pv;
                            $data[$clef]['NumericValue'][$pk]=$v['NumericValue'];
                        }
                    } else {
                        $data[$clef]['NumericValue'][]=$v['NumericValue'];
                        $data[$clef]['PrecisionValue'][]='std';
                    }
                    unset($data[$clef]['precision']);

                    if ($calculateValues=$this->_calculateValues($data[$clef])) {
                        $data[$clef]['calculateValues']=$calculateValues;
                    }

                    if ($p['config']['dicomDiscoverNewTags']=='true') {
                        $this->_saveNewDcTagsInDB($data);
                    }
                }
            }



            ksort($data);
            return $this->_formatedDataSR=$data;
        }
    }

/**
 * Faire des calculs sur chaque valeur du SR
 * @param  array $sr array d'une mesure
 * @return array     array des valeurs calculées
 */
    private function _calculateValues($sr)
    {
        $r=[];
        $numericStd=array_intersect_key($sr['NumericValue'], array_flip(array_keys($sr['PrecisionValue'], 'std')));
        if (count($numericStd)>0) {
            $sum = array_sum($numericStd);
            $r['avg'] = round($sum / count($numericStd), 2);
            $r['max'] = max($numericStd);
            $r['min'] = min($numericStd);
            $r['defaut'] = $r['max'];
        } elseif (!empty($sr['NumericValue'])) {
            $r['defaut']=array_values($sr['NumericValue'])[0];
        }
        if (isset($sr['NumericValue']['R-002E1'])) {
            $r['bv'] = $sr['NumericValue']['R-002E1'];
            $r['defaut'] = $sr['NumericValue']['R-002E1'];
        }
        return $r;
    }

/**
 * Enregistrer en base les nouveau tag DICOM SR rencontrés
 * (en vue des les attacher à une donnée MedShake si besoin)
 * @param  array $tab data DICOM
 * @return void
 */
    private function _saveNewDcTagsInDB($tab)
    {
        foreach ($tab as $k=>$v) {
            if (!msSQL::sqlUniqueChamp("select dicomTag from dicomTags where dicomTag='".$k."' limit 1")) {
                $data= array(
              'dicomTag'=>$k,
              'dicomCodeMeaning'=>$v['clefReadabled'],
              'dicomUnits'=>$v['MeasurementUnits']
              );
                msSQL::sqlInsert("dicomTags", $data);
            } else {
                msSQL::sqlQuery("update dicomTags set dicomCodeMeaning='".$v['clefReadabled']."' where where dicomTag='".$k."'");
            }
        }
    }
}
