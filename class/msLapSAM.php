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
 * LAP : gestion des SAM
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msLapSAM
{
  private $_xmlUrl='https://wikipe.has-sante.fr/WikiPE/PHP/SAD_medicamentsXML.php?Date=actif';
  private $_xml;
  private $_codesSpeBySam;
  private $_samID;
  private $_toID;
  private $_fromID;

/**
 * Définir le SAM concerné
 * @param string $v ID du sam
 * @return string samID
 */
  public function setSamID($v)
  {
      if (is_string($v)) {
          return $this->_samID = $v;
      } else {
          throw new Exception('SamID is not a string');
      }
  }

/**
 * Définir l'individu concerné
 * @param int $v ID de l'individu
 * @return int toID
 */
    public function setToID($v)
    {
        if (is_numeric($v)) {
            return $this->_toID = $v;
        } else {
            throw new Exception('ToID is not numeric');
        }
    }

/**
 * Définir le user qui enregistre l'objet
 * @param int $v ID du user
 * @return int fromID
 */
    public function setFromID($v)
    {
        if (is_numeric($v)) {
            return $this->_fromID = $v;
        } else {
            throw new Exception('FromID is not numeric');
        }
    }

/**
 * Obtenir l'URL du xml source pour les SAMs
 * @return string url du xml source
 */
    public function getTheXmlUrl() {
      return $this->_xmlUrl;
    }

/**
 * Sauver en local le XML de la HAS sur les SAM
 * @return boolean true / false
 */
  public function getTheXmlFile() {
    global $p;
    if($xml=file_get_contents($this->_xmlUrl)) {
      $xml=str_replace(['encoding="iso-8859-1"', 'encoding="windows-1252"'], 'encoding="UTF8"', $xml);
      $xml=utf8_encode($xml);
      msTools::checkAndBuildTargetDir($p['homepath'].'ressources/SAM/');
      return file_put_contents($p['homepath'].'ressources/SAM/SAM.xml', $xml);
    } else {
      return false;
    }
  }

/**
 * Charger le XML local pour analyse ou extraction
 * @return object  object DOMElement
 */
  public function getSamXmlFileContent() {
    global $p;
    $document_xml = new DomDocument();
    @$document_xml->load($p['homepath'].'ressources/SAM/SAM.xml');
    return $this->_xml = $document_xml;
  }

/**
 * Obtenir la correspondance entre SAM et codes spécialité Thériaque
 * @return array idSAM => array(code1, code2 ...)
 */
  public function getCodesSpeBySAM() {
    $sams = $this->_xml->getElementsByTagName('IdSAD');
    if($sams->length) {
      foreach($sams as $sam) {
        $this->_samID=$sam->nodeValue;
        $method='_getCodesSpe4_'.$this->_samID;
        if(method_exists('msLapSAM', $method)) {
          $data[$this->_samID]=$this->$method();
        }
      }
    }
    return $this->_codesSpeBySam = $data;
  }

/**
 * Obtenir la liste des SAMs dans le XML
 * @return array tableau des SAMs
 */
  public function getSamListInXml() {
    $data=[];
    $sams = $this->_xml->getElementsByTagName('IdSAD');
    if($sams->length) {
      foreach($sams as $sam) {
        $method='_getCodesSpe4_'.$sam->nodeValue;
        $titre = $sam->parentNode->getElementsByTagName('titre')->item(0)->nodeValue;
        if(method_exists('msLapSAM', $method)) {
          $methodTrouve = 'oui';
        } else {
          $methodTrouve = 'non';
        }
        $data[]=array(
          'id'=>$sam->nodeValue,
          'titre'=>$titre,
          'methode'=>$methodTrouve
        );
      }
    }
    return $data;
  }

/**
 * Créer le fichier de correspondance entre SAM et codes spécialité (array serialize)
 */
  public function setFileCodesSpeBySAM() {
    global $p;
    $data = serialize($this->_codesSpeBySam);
    return file_put_contents($p['homepath'].'ressources/SAM/samSpeCorrespondances', $data);
  }

/**
 * Obtenir les data pour un SAM
 * @param  string $samName id du SAM (comme présent dans le XML)
 * @return array          array data SAM
 */
  public function getSamData() {
    $rd=[];
    if(!isset($this->_xml)) $this->getSamXmlFileContent();
    $xpath = new DOMXpath($this->_xml);
    $nodeList = $xpath->query('//SAD_medicament[IdSAD="'.$this->_samID.'"]');

    //retirer cet élément
    $nliste=$this->_xml->getElementsByTagName('nliste');
    foreach($nliste as $remove) {
      $remove->parentNode->removeChild($remove);
    }

    if($nodeList->length) {
      $sam = $nodeList->item(0);

      $rd['titre']=$sam->getElementsByTagName('titre')->item(0)->nodeValue;
      $rd['liste_medicaments']=$sam->getElementsByTagName('liste_medicaments')->item(0)->nodeValue;
      $rd['messageLAPV']=$this->_DOMinnerHTML($sam->getElementsByTagName('messageLAPV')->item(0));
      $rd['reference']=$this->_DOMinnerHTML($sam->getElementsByTagName('reference')->item(0));
      $rd['logo']=$this->_DOMinnerHTML($sam->getElementsByTagName('logo')->item(0));
      $rd['logoMediaType']=$sam->getElementsByTagName('logo')->item(0)->getAttribute('mediaType');
      $rd['logoRepresentation']=$sam->getElementsByTagName('logo')->item(0)->getAttribute('representation');
    }
    return $rd;
  }

/**
 * Obtenir l'objet porteur du SAM (qui est tout utilisateur confondu)
 * @param  string $samID samID
 * @return array        data du porteur SAM
 */
  public function getSamPorteurData() {
    $name2typeID=new msData;
    $name2typeID=$name2typeID->getTypeIDsFromName(['lapSam']);
    return $data=msSQL::sqlUnique("select pd.*
    from objets_data as pd
    where pd.typeID = '".$name2typeID['lapSam']."' and pd.value='".msSQL::cleanVar($this->_samID)."' and pd.deleted='' and pd.outdated=''
    order by updateDate desc
    limit 1");
  }

/**
 * Créer le porteur (multi user) du SAM
 * @param [type] $samID [description]
 */
  public function setSamPorteur($samID) {
    $obj = new msObjet();
    $obj->setToID(msPeopleSearch::getServiceID('medshake'));
    $obj->setFromID(msPeopleSearch::getServiceID('medshake'));
    return $obj->createNewObjetByTypeName('lapSam', $samID);
  }

/**
 * Obtenir le statut du SAM pour le couple patient / user
 * @return string enabled / disabled
 */
  public function getSamStatusForPatient() {
    if($porteur = $this->getSamPorteurData()) {
      $porteurID = $porteur['id'];

      $name2typeID=new msData;
      $name2typeID=$name2typeID->getTypeIDsFromName(['lapSamDisabled']);
      if($data=msSQL::sqlUnique("select pd.id
      from objets_data as pd
      where pd.typeID = '".$name2typeID['lapSamDisabled']."' and pd.toID = '".$this->_toID."' and pd.fromID = '".$this->_fromID."' and pd.deleted='' and pd.outdated=''
      order by updateDate desc
      limit 1")) {
        return 'disabled';
      } else {
        return 'enabled';
      }
    } else {
      return 'enabled';
    }
  }

/**
 * Marquer le SAM comme bloqué pour le couple patient / user
 */
  public function setSamDisabledForPatient() {
    if($porteur = $this->getSamPorteurData()) {
      $porteurID = $porteur['id'];
    } else {
      $porteurID = $this->setSamPorteur($this->_samID);
    }

    $name2typeID=new msData;
    $name2typeID=$name2typeID->getTypeIDsFromName(['lapSamDisabled']);

    $obj = new msObjet;
    $obj->setFromID($this->_fromID);
    $obj->setToID($this->_toID);
    $newmarqueur = $obj->createNewObjetByTypeName('lapSamDisabled', '', $porteurID);

  }

/**
 * Marquer le SAM comme non bloqué pour le couple patient / user
 */
  public function setSamEnabledForPatient() {
    if($porteur = $this->getSamPorteurData()) {
      $porteurID = $porteur['id'];

      $name2typeID=new msData;
      $name2typeID=$name2typeID->getTypeIDsFromName(['lapSamDisabled']);

      if($dataID=msSQL::sqlUniqueChamp("select pd.id
      from objets_data as pd
      where pd.typeID = '".$name2typeID['lapSamDisabled']."' and pd.toID = '".$this->_toID."' and pd.fromID = '".$this->_fromID."' and pd.instance = '".$porteurID."' and pd.deleted='' and pd.outdated=''
      order by updateDate desc
      limit 1")) {

        $obj = new msObjet;
        $obj->setFromID($this->_fromID);
        $obj->setToID($this->_toID);
        $obj->setObjetID($dataID);
        $obj->setDeletedObjetAndSons();

      }
    }
  }

/**
 * Obtenir la liste des patients pour lesquels le SAM est bloqué
 * @return array  tableau des patients
 */
  public function getDisabledPatientsListForSam() {
    if($porteur = $this->getSamPorteurData()) {
      $name2typeID=new msData;
      $name2typeID=$name2typeID->getTypeIDsFromName(['lapSamDisabled','lastname', 'birthname', 'firstname']);

      return msSQL::sql2tab("select p.value as prenom, CASE WHEN n.value != '' THEN n.value ELSE bn.value END as nom, o.id as objetID, DATE_FORMAT(o.registerDate, '%d/%m/%Y %H:%i') as date, o.toID as patientID
      from objets_data as o
      left join objets_data as n on n.toID=o.toID and n.typeID='".$name2typeID['lastname']."' and n.outdated='' and n.deleted=''
      left join objets_data as bn on bn.toID=o.toID and bn.typeID='".$name2typeID['birthname']."' and bn.outdated='' and bn.deleted=''
      left join objets_data as p on p.toID=o.toID and p.typeID='".$name2typeID['firstname']."' and p.outdated='' and p.deleted=''
      where o.typeID = '".$name2typeID['lapSamDisabled']."' and o.instance='".$porteur['id']."' and o.deleted='' and o.outdated='' and o.fromID='".$this->_fromID."'
      ");

    } else {
      return [];
    }
  }

/**
 * Créer / mettre à jour le commentaie patient pour le SAM
 * @param string $comment commentaire
 * @param int $objetID id de l'objet créé
 */
  public function setSamComment($comment, $objetID) {
    if(!isset($this->_samID)) {
        throw new Exception('SamID is not set');
    }
    if (!is_numeric($this->_toID)) {
        throw new Exception('ToID is not numeric');
    }
    if (!is_numeric($this->_fromID)) {
        throw new Exception('FromID is not numeric');
    }

    if($porteur = $this->getSamPorteurData()) {
      $porteurID = $porteur['id'];
    } else {
      $porteurID = $this->setSamPorteur($this->_samID);
    }

    if (!is_numeric($porteurID)) {
        throw new Exception('PorteurID is not numeric');
    }

    if (!is_numeric($objetID)) {
        $objetID='0';
    }

    $obj = new msObjet();
    $obj->setToID($this->_toID);
    $obj->setFromID($this->_fromID);

    return $obj->createNewObjetByTypeName('lapSamCommentaire', $comment, $porteurID, 0, $objetID);
  }

/**
 * Obtenir le commentaire patient d'un SAM (utilisateur spécifique)
 * @param  string $samID     samID
 * @return array            data commentaire
 */
  public function getSamCommentForPatient() {
    $porteur = $this->getSamPorteurData($this->_samID);

    $name2typeID=new msData;
    $name2typeID=$name2typeID->getTypeIDsFromName(['lapSamCommentaire']);
    $data=msSQL::sqlUnique("select pd.*
    from objets_data as pd
    where pd.typeID = '".$name2typeID['lapSamCommentaire']."' and pd.toID = '".$this->_toID."' and pd.fromID = '".$this->_fromID."' and pd.instance='".$porteur['id']."' and pd.deleted='' and pd.outdated=''
    order by updateDate desc
    limit 1");

    $data['porteurID'] = $porteur['id'];
    return $data;

  }

/**
 * Obtenir le html d'un DOMNode
 * @param  DOMNode $element DOMNode
 * @return string           html
 */
  private function _DOMinnerHTML(DOMNode $element)
  {
      $innerHTML = "";
      $children  = $element->childNodes;
      foreach ($children as $child)
      {
          $innerHTML .= $element->ownerDocument->saveHTML($child);
      }
      return $innerHTML;
  }

/**
 * Obtenir les codes spé pour les SAM qui identifient par substance
 * @param  string $samID samID
 * @return array   array des codes spé concernés par le SAM
 */
    private function _getCodesSpeBySubstance() {
      $xpath = new DOMXpath($this->_xml);
      $nodeList = $xpath->query('//SAD_medicament[IdSAD="'.$this->_samID.'"]');
      if($nodeList->length) {
        $node = $nodeList->item(0);
        $medics =$node->getElementsByTagName('medicament');

        $lap = new msLap;
        $rd=[];
        foreach($medics as $medic) {
          if($data = $lap->getCodesSpesListBySub ($medic->nodeValue, 0, 3)) {
            $rd=array_merge($rd,$data);
          }
        }
        return $rd;
      }
    }

    private function _getCodesSpe4_assos_beta2 () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Bosutinib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Crizotinib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Dabrafenib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Trametinib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Dasatinib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Erlotinib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Gefinitib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Imatinib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Lenalidomide_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Nilotinib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Pomalidomide_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Ponatinib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Ruxolitinib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Vemurafenib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Vismodegib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Cobimetinib_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Thalidomide_INCa () {
      return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Ceritinib_INCa () {
    return self::_getCodesSpeBySubstance();
    }
    private function _getCodesSpe4_Afatinib_INCa () {
      return self::_getCodesSpeBySubstance();
    }


}
