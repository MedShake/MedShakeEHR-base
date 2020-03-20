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
 * Liaison entre MedShakeEHR et Orthanc <http://www.orthanc-server.com/>,
 * Orthanc est serveur DICOM libre et open source qui doit être installé sur
 * le réseau informatique où MedShake est employé. L'appareil d'imagerie doit
 * être configuré pour envoyer ses données à Orthanc (Stockage et SR).
 * MedShakeEHR récupère ensuite mesures et images auprès d'Orthanc.
 * A l'inverse, MedShakeEHR envoie les données patient à Orthanc qui les adressera
 * à l'appareil d'imagerie pour pré configurer l'examen.
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msDicom
{
    /**
 * @var int ID patient de MedShakeEHR
 */
    private $_toID;
/**
 * @var int ID patient Orthanc
 */
    private $_dcPatientID;
/**
 * @var string studyID Orthanc
 */
    private $_dcStudyID;
/**
 * @var array data étude d'orthanc sur l'étude courante
 */
    private $_dcStudyData;
/**
 * @var string ID serie orthanc
 */
    private $_dcSerieID;
/**
 * @var string ID de l'instance Orthanc
 */
    protected $_dcInstanceID;
/**
 * @var array data Orthanc sur l'instance
 */
    private $_dcInstanceData;
/**
 * @var array array data Orthanc sur l'instance courante
 */
    private $_dcInstanceDataSR;
/**
 * @var string URL de base pour requète curl
 */
    protected $_baseCurlUrl; //ll'url de base pour requète curl

/**
 * @return string
 */
public function getDcStudyID()
{
    return $this->_dcStudyID;
}

/**
 * @return string
 */
public function getDcSerieID()
{
    return $this->_dcSerieID;
}

/**
 * @return string
 */
public function getDcInstanceID()
{
    return $this->_dcInstanceID;
}

/**
 * Construire l'url de base pour curl
 */
    public function __construct()
    {
        global $p;
        $this->_baseCurlUrl=$p['config']['dicomProtocol'].$p['config']['dicomHost'].':'.$p['config']['dicomPort'];
    }

/**
 * Definir l'ID patient
 * Definir l'ID Orthanc via l'ID patient MedShakeEHR
 * @param [type] $v [description]
 */
    public function setToID($v)
    {
      if (msPeople::checkPeopleExist($v)) {
          $this->_toID = $v;
          $this->_makeDcPatientID();
          return $this->_toID;
      } else {
          throw new Exception('ToID does not exist');
      }
    }

/**
 * Définir la studyID pour Orthanc
 * @param string $v studyID Orthanc
 */
    public function setDcStudyID($v)
    {
        return $this->_dcStudyID = $v;
    }

/**
 * Définir la serieID pour Orthanc
 * @param string $v serieID Orthanc
 */
    public function setDcSerieID($v)
    {
        return $this->_dcSerieID = $v;
    }

/**
 * Définir l'instanceID pour Orthanc
 * @param string $v instanceID Orthanc
 */
    public function setDcInstanceID($v)
    {
        return $this->_dcInstanceID = $v;
    }

/**
 * Obtenir les infos système Orthanc
 * @return array data system Orthanc
 */
    public function getOrthancSystemInfo()
    {
      $url=$this->_baseCurlUrl.'/system';
      return  $this->_dcGetContent($url);
    }

/**
 * Obtenir les stats Orthanc
 * @return array stats orthanc
 */
    public function getOrthancStats()
    {
      $url=$this->_baseCurlUrl.'/statistics';
      return  $this->_dcGetContent($url);
    }

/**
 * Obtenir les data via un studyID Orthanc
 * @return array array
 */
    public function getStudyDcData()
    {
        $url=$this->_baseCurlUrl.'/studies/'.$this->_dcStudyID;
        return  $this->_dcStudyData = $this->_dcGetContent($url);
    }

/**
 * Obtenir la studyID à partir de l'instanceID
 * @return string studyID
 */
    public function getStudyDcFromInstance()
    {
        $url=$this->_baseCurlUrl.'/instances/'.$this->_dcInstanceID.'/study';
        $data = $this->_dcInstanceData = $this->_dcGetContent($url);
        return $data['ID'];
    }

/**
 * Obtenir les tags DICOM d'une instance
 * @return array tags DICOM de l'instance
 */
    public function getInstanceDcTags()
    {
        if (!isset($this->_dcInstanceID)) {
            throw new Exception('InstanceID is not set');
        }

        $url=$this->_baseCurlUrl.'/instances/'.$this->_dcInstanceID.'/simplified-tags/';
        return  $this->_dcInstanceData = $this->_dcGetContent($url);
    }

/**
 * Obtenir et sauver l'image d'une instance
 * @return void
 */
    public function getImageFromInstance()
    {
        global $p;
        if (!isset($this->_dcStudyID)) {
            $this->_dcStudyID=$this->getStudyDcFromInstance();
        }
        if($framesArray=$this->_dcGetNumberOfFramesInInstance()) {
          $targetDirectory = $p['config']['dicomWorkingDirectory'].$p['user']['id'].'/'.$this->_dcStudyID.'/';
          msTools::checkAndBuildTargetDir($targetDirectory);

          foreach($framesArray as $frame) {
            //$url=$this->_baseCurlUrl.'/instances/'.$this->_dcInstanceID.'/preview';
            $url=$this->_baseCurlUrl.'/instances/'.$this->_dcInstanceID.'/frames/'.$frame.'/preview';
            $saveto = $targetDirectory.$this->_dcInstanceID.'-'.$frame.'.png';
            $this->_dcGetImage($url, $saveto);

          }
          return $framesArray;
        }
    }

/**
 * Obtenir toutes les images d'une étude
 * @return array chemin relatif de toutes les images
 */
    public function getAllImagesFromStudy()
    {
        global $p;
        $url=$this->_baseCurlUrl.'/studies/'.$this->_dcStudyID.'/instances';
        $tabImg=[];
        $data =  $this->_dcGetContent($url);
        foreach ($data as $k=>$v) {
            $this->_dcInstanceID = $v['ID'];
            if($framesArray=$this->getImageFromInstance()) {
              foreach($framesArray as $frame) {
                $file=$p['config']['workingDirectory'].$p['user']['id'].'/'.$this->_dcStudyID.'/'.$this->_dcInstanceID.'-'.$frame.'.png';
                if (is_file($file)) {
                    $tabImg[$this->_dcInstanceID][$frame]=str_replace($p['config']['webDirectory'], '', $file);
                }
              }
            }
        }
        return $tabImg;
    }

/**
 * Obtenir toutes les études d'un patient
 * @return array données sur toutes les études
 */
    public function getAllStudiesFromPatientDcData()
    {
        $url=$this->_baseCurlUrl.'/patients/'.$this->_dcPatientID.'/studies/';
        //return  $this->_dcGetContent($url);

        $studies =  $this->_dcGetContent($url);
        if (!isset($studies['HttpError'])) {
            if (count($studies)>1) {
                foreach ($studies as $k=>$study) {
                    $r[$study['MainDicomTags']['StudyDate'].$study['MainDicomTags']['StudyTime']]=$study;
                }
                krsort($r);
                $r=array_values($r);
            } else {
                $r=$studies;
            }
            return $r;
        } else {
          return $studies;
        }

    }

/**
 * Obtenir la dernière instance SR pour un patient
 * ( = rapatrier les mesures de l'examen du jour)
 * @return array data SR
 */
    public function getLastSRinstanceFromPatientID()
    {
        if ($pd=$this->getAllStudiesFromPatientDcData()) {
            $this->_dcStudyID = $pd[0]['ID'];
            return $this->getSRinstanceFromStudy();
        }

        return false;
    }

/**
 * Obtenir l'instance des data SR d'une étude à partir de study
 * @return array data SR de l'étude
 */
    public function getSRinstanceFromStudy()
    {
        global $p;
        $url=$this->_baseCurlUrl.'/studies/'.$this->_dcStudyID.'/series';
        $data =  $this->_dcGetContent($url);

        $i=0;
        while (isset($data[$i]) and !isset($stop)) {
            if ($data[$i]['MainDicomTags']['Modality']=='SR') {
                $this->_dcInstanceID=$data[$i]['Instances'][0];
                $this->_dcStudyID=$data[$i]['ParentStudy'];
                $this->_dcSerieID=$data[$i]['ID'];

                $stop='stop';
            }
            $i++;
        }
        if (isset($stop)) {
            return $this->_dcInstanceID;
        } else {
            return false;
        }
    }

/**
 * Obtenir le nombre d'instances dans une série
 * @return int nombre d'instances
 */
    public function getNumberInstancesInSeries() {

      if (!isset($this->_dcSerieID)) {
          throw new Exception('SerieID is not set');
      }

      global $p;
      $url=$this->_baseCurlUrl.'/series/'.$this->_dcSerieID;
      $data =  $this->_dcGetContent($url);
      if(is_array($data['Instances'])) return count($data['Instances']);
      return '0';
    }

/**
 * Construire l'ID patient Orthanc
 * @return void
 */
    private function _makeDcPatientID()
    {
        global $p;
        $s=$p['config']['dicomPrefixIdPatient'].$this->_toID;
        $this->_dcPatientID = $this->constructIdOrthanc($s);
    }

/**
 * Construire le sha1 délimité spécifique à Orthanc
 * @param  string $s chaine à passer en sha1
 * @return string    ID Orthanc
 */
    public function constructIdOrthanc($s) {
      $s=sha1($s);
      $s=chunk_split($s, 8, '-');
      $s=rtrim($s, '-');
      return $s;
    }

/**
 * Faire une requète curl vers Orthanc
 * @param  string $url url curl
 * @return array      array php
 */
    private function _dcGetContent($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result=curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

/**
 * Rapatrier une image via une requète curl
 * @param  string $url    url curl
 * @param  string $saveto fichier sauvé et chemin
 * @return void
 */
    private function _dcGetImage($url, $saveto)
    {
        if (!is_file($saveto)) {
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
    }

/**
 * Obtenir le nombre de frames (images) dans une instance
 * @return array Array des frames [0,1,2 ...]
 */
    private function _dcGetNumberOfFramesInInstance() {
      if (!isset($this->_dcInstanceID)) {
          throw new Exception('InstanceID is not set');
      }

      $url=$this->_baseCurlUrl.'/instances/'.$this->_dcInstanceID.'/frames';
      return $this->_dcGetContent($url);
    }

}
