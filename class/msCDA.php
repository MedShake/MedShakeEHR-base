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
 *
 * Créer une version CDA d'un document
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


class msCDA
{
    private $_objetID;
    private $_objetTag;
    private $_template;
    private $_codeActe;
    private $_codeDocument;
/**
 * Définir l'objetID
 * @param int $objetID objetID
 */
    public function setObjetID($objetID)
    {
        if (!is_numeric($objetID)) {
            throw new Exception('ObjetID is not numeric');
        }
        $this->_objetID = $objetID;

        $courrier = new msCourrier();
        $courrier->setObjetID($this->_objetID);
        $this->_objetTag=$courrier->getDataByObjetID();
        return $this->_objetID;
    }

/**
 * Obtenir le XML CDA
 * @return string XML CDA
 */
    public function getCdaXml()
    {
        global $p;
        $this->_ajusterTags();
        $this->_ajouterTags();
        $this->_ajouterTagsFormulaireOrigine();
        $this->_getPDF();

        $html = new msGetHtml();
        $html->set_templateFileExt('.xml.twig');
        $html->set_template($this->_template);
        $html->set_templatesDirectories([$p['config']['templatesCdaFolder']]);
        $tags['tag']=$this->_objetTag;
        $xml = $html->genererHtmlVar($tags);

        if (function_exists('tidy_repair_string')) {
            $xml=tidy_repair_string($xml, array(
            'output-xml' => true,
            'input-xml' => true,
            'indent' => true,
            'wrap' => 0
            ));
        }
        return $xml;
    }

/**
 * Ajuster les tags courrier à la production d'un CDA
 * @return void
 */
    private function _ajusterTags()
    {
        //patient
        $this->_objetTag['birthdate']=msTools::readableDate2Reverse($this->_objetTag['birthdate']);
        $this->_objetTag['homePhone']=str_replace(' ', '',$this->_objetTag['homePhone']);
        $this->_objetTag['mobilePhone']=str_replace(' ', '',$this->_objetTag['mobilePhone']);
        $this->_objetTag['personalEmail']=str_replace(' ', '',$this->_objetTag['personalEmail']);

        //ps auteur
        $aTraiter=['AuteurInitial_telPro', 'AuteurInitial_telPro2', 'AuteurInitial_mobilePhonePro', 'AuteurInitial_faxPro', 'AuteurInitial_emailApicrypt', 'AuteurInitial_profesionnalEmail'];
        foreach($aTraiter as $tag) {
          if(isset($this->_objetTag[$tag])) $this->_objetTag[$tag]=str_replace(' ', '',$this->_objetTag[$tag]);
        }

        //logiciel
        $this->_objetTag['softwareName']='MedShakeEHR';
    }

/**
 * Ajouter les tags nécessaires à la production d'un CDA
 * @return void
 */
    private function _ajouterTags() {
      if(isset($this->_objetTag['AuteurInitial_PSCodeProSpe'])) {
        $codes = msExternalData::getJdvDataFromXml('JDV_J01-XdsAuthorSpecialty-CI-SIS.xml');
        $this->_objetTag['AuteurInitial_PSCodeProSpe_codeSystem']=$codes[$this->_objetTag['AuteurInitial_PSCodeProSpe']]['codeSystem'];
        $this->_objetTag['AuteurInitial_PSCodeProSpe_displayName']=$codes[$this->_objetTag['AuteurInitial_PSCodeProSpe']]['displayName'];
      }
      if(isset($this->_objetTag['AuteurInitial_PSCodeStructureExercice'])) {
        $codes = msExternalData::getJdvDataFromXml('JDV_J02-HealthcareFacilityTypeCode_CI-SIS.xml');
        $this->_objetTag['AuteurInitial_PSCodeStructureExercice_codeSystem']=$codes[$this->_objetTag['AuteurInitial_PSCodeStructureExercice']]['codeSystem'];
        $this->_objetTag['AuteurInitial_PSCodeStructureExercice_displayName']=$codes[$this->_objetTag['AuteurInitial_PSCodeStructureExercice']]['displayName'];
      }
    }

/**
 * Ajouter les tags issus du formulaire d'origine de l'objet
 * @return void
 */
    private function _ajouterTagsFormulaireOrigine() {
      $ob=new msObjet();
      $ob->setID($this->_objetID);
      if($formIN = $ob->getOriginFormNameFromObjetID()) {
        if($datYaml=msForm::getFormUniqueRawField($formIN, 'cda')) {
          $d = Spyc::YAMLLoad($datYaml);

          $this->_template=$d['template'];

          // clinicalDocument / documentationOf / serviceEvent / code
          $this->_getDocumentationOfServiceEventCode($d);

          // clinicalDocument/title
          if(!isset($d['clinicalDocument']['title']) or empty($d['clinicalDocument']['title'])) {
            $d['clinicalDocument']['title']=$this->_objetTag['cda_serviceEvent_displayName'];
          }
          $this->_objetTag['cda_clinicalDocument_title']=$d['clinicalDocument']['title'];

          // clinicalDocument/code
          $this->_getClinicalDocumentCode();
        }
      }
    }

/**
 * Obtenir le code du document
 * @return void
 */
    private function _getClinicalDocumentCode() {
      $tabVal=msExternalData::getJdvDataFromXml('JDV_J07-XdsTypeCode_CI-SIS.xml');
      $this->_objetTag['cda_clinicalDocument_code_code']=$tabVal[$this->_codeDocument]['code'];
      $this->_objetTag['cda_clinicalDocument_code_displayName']=$tabVal[$this->_codeDocument]['displayName'];
      $this->_objetTag['cda_clinicalDocument_code_codeSystem']=$tabVal[$this->_codeDocument]['codeSystem'];
      $this->_objetTag['cda_clinicalDocument_code_codeSystemName']='LOINC';
    }

/**
 * Obtenir le codage du document en fonction de l'acte (CCAM) attaché lors de sa production
 * @param  array $d data issues du yaml
 * @return void
 */
    private function _getDocumentationOfServiceEventCode($d) {

      $documentationOf=$d['clinicalDocument']['documentationOf'];
      $actesPossibles=$d['actesPossibles'];
      unset($d);

      if(isset($documentationOf['serviceEvent']['paramConditionServiceEvent'])) {
        // param simple ou association de param
        if(is_array($documentationOf['serviceEvent']['paramConditionServiceEvent'])) {
          foreach($documentationOf['serviceEvent']['paramConditionServiceEvent'] as $v) {
            if(isset($this->_objetTag[$v])) $expectedKeyParts[]=$this->_objetTag[$v];
          }
          $expectedKey=implode('|',$expectedKeyParts);
        } elseif(is_string($documentationOf['serviceEvent']['paramConditionServiceEvent'])) {
          $expectedKey=$this->_objetTag[$documentationOf['serviceEvent']['paramConditionServiceEvent']];
        }

        $this->_codeActe=$documentationOf['serviceEvent']['code'][$expectedKey];
      }

      if(isset($actesPossibles[$this->_codeActe]['serviceEventCode']) and !empty($actesPossibles[$this->_codeActe]['serviceEventCode'])) {
        $this->_objetTag['cda_serviceEvent_code']=$this->_codeActe;
        foreach($actesPossibles[$this->_codeActe]['serviceEventCode'] as $k=>$v) {
          $this->_objetTag['cda_serviceEvent_'.$k]=$v;
        }
      }

      $this->_codeDocument=$actesPossibles[$this->_codeActe]['clinicalDocumentCode'];
    }

/**
 * Obtenir le PDF en encodé base 64 correspondant à objetID
 * Le créer si besoin
 * @return string PDF base 64
 */
    private function _getPDF() {
      $doc = new msStockage;
      $doc->setObjetID($this->_objetID);

      if (!$doc->testDocExist()) {
          $pdf= new msPDF();
          $pdf->setObjetID($this->_objetID);
          $pdf->makePDFfromObjetID();
          $pdf->savePDF();
      }
      $this->_objetTag['documentPdfBase64']= base64_encode(file_get_contents($doc->getPathToDoc()));
    }
}
