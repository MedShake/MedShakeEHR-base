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
 * Gérer la preview des éléments de l'historique patient - module base
 *
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 */

class msModBaseObjetPreview
{
  protected $_objetID;
  protected $_dataObjet;
  protected $_pdfOrientation;

/**
 * Définir l'ID de l'objet
 * @param int $id objetID
 */
  public function setObjetID($id) {
    if(!is_numeric($id)) throw new Exception('ID is not numeric');
    $this->_objetID = $id;
    $data = new msObjet();
    $data->setObjetID($id);
    $this->_dataObjet = $data->getCompleteObjetDataByID();
  }

/**
 * Obtenir le groupe du type de l'objet
 * @return string groupe du type de l'objet
 */
  public function getObjetGroupe() {
    return $this->_dataObjet['groupe'];
  }

/**
 * Obtenir le name du type de l'objet
 * @return string name du type de l'objet
 */
  public function getObjetName() {
    return $this->_dataObjet['name'];
  }

/**
 * Obtenir le module du type de l'objet
 * @return string module du type de l'objet
 */
  public function getObjetModule() {
    return $this->_dataObjet['module'];
  }

/**
 * Obtenir l'info sur le fait que le document puisse êter signé par le patient
 * @return bool TRUE/FALSE
 */
  public function getCanBeSigned() {
    if($this->_dataObjet['placeholder'] == 'o' and $this->_dataObjet['groupe'] == 'typecs') {
      return TRUE;
    } else {
      return FALSE;
    }
  }

/**
 * Obtenir le HTML de prévisualisation d'un Document
 * @return string code html
 */
  public function getGenericPreviewDocument() {
    global $p;
    $doc = new msStockage();
    $doc->setObjetID($this->_objetID);

    if ($doc->testDocExist()) {
        $p['page']['doc']['id']=$this->_objetID;
        $p['page']['doc']['uniqid']=uniqid();
        $p['page']['doc']['href']=$doc->getWebPathToDoc();
        $p['page']['doc']['ext']=strtoupper($doc->getFileExtOfDoc());
        $p['page']['doc']['mime']=$doc->getFileMimeType();
        if($p['page']['doc']['mime'] == 'application/pdf') {
          $this->_pdfOrientation = $doc->getPdfOrientation();
        }
        $p['page']['doc']['filesize']= $doc->getFileSize(0);
        $p['page']['doc']['displayParams']=$this->getFilePreviewParams($p['page']['doc']['mime'], $doc->getPathToDoc());
        $p['page']['doc']['origine']=$doc->getDocOrigin();
    }
    if (!empty($this->_dataObjet['value'])) {
        //hprim
        $p['page']['bioHprim'] = msHprim::parseSourceHprim($this->_dataObjet['value']);
        //texte
        $p['page']['texte']= $this->_dataObjet['value'];
    }

    $html = new msGetHtml;
    $html->set_template('inc-ajax-detDoc.html.twig');
    $html = $html->genererHtmlVar($p);
    return $html;
  }

/**
 * Obtenir les paramètres d'affichage du document de la ligne d'historique
 * @param  string $mime mimetype du doc
 * @param  string $file fichier
 * @return array       tableau de paramètres
 */
  public function getFilePreviewParams($mime, $file) {
    $tab=array(
      'display'=>false,
      'displayType'=>'object',
      'width'=>0,
      'height'=>0,
    );

    // texte
    if($mime == 'text/plain') {
      $tab=array(
        'display'=>true,
        'displayType'=>'object',
        'width'=>'900px',
        'height'=>'900px',
      );
    }

    // pdf
    elseif($mime == 'application/pdf') {
      if(!isset($this->_pdfOrientation)) {
        $this->_pdfOrientation = msTools::getPdfOrientation($file);
      }
      if($this->_pdfOrientation == "landscape") {
        $tab=array(
          'display'=>true,
          'displayType'=>'object',
          'width'=>'1250px',
          'height'=>'1000px',
          'orientation'=> $this->_pdfOrientation
        );
      } else {
        $tab=array(
          'display'=>true,
          'displayType'=>'object',
          'width'=>'900px',
          'height'=>'1260px',
          'orientation'=> $this->_pdfOrientation
        );
      }
    }

    // zip
    elseif($mime == 'application/zip') {
      $tab=array(
        'display'=>false,
        'displayType'=>'object',
        'width'=>0,
        'height'=>0,
      );
    }

    // image
    elseif(explode('/', $mime)[0] == 'image') {
      $imageInfos = getimagesize($file);
      if($imageInfos[0]>1000) {
        $imageInfos[1]=round($imageInfos[1]*1000/$imageInfos[0]);
        $imageInfos[0]=1000;
      }
      if($imageInfos[1]>1000) {
        $imageInfos[0]=round($imageInfos[0]*1000/$imageInfos[1]);
        $imageInfos[1]=1000;
      }
      $tab=array(
        'display'=>true,
        'displayType'=>'img',
        'width'=>$imageInfos[0].'px',
        'height'=>$imageInfos[1].'px',
      );
    }
    return $tab;
  }

/**
 * Obtenir le html pour l'inclusion du fichier propre à un document
 * @return string html
 */
  public function getFilePreviewDocument() {
    global $p;
    $doc = new msStockage();
    $doc->setObjetID($this->_objetID);

    if ($doc->testDocExist()) {
        $p['page']['pj']['href']=$doc->getWebPathToDoc();
        $p['page']['pj']['html']=strtoupper($doc->getFileExtOfDoc());
    }

    if($p['page']['pj']['html'] == 'PDF') {
      $p['page']['doc']['mime']=$doc->getFileMimeType();
      if(!isset($this->_pdfOrientation)) {
        $this->_pdfOrientation = $doc->getPdfOrientation();
      }
      $p['page']['doc']['displayParams']=$this->getFilePreviewParams($p['page']['doc']['mime'], $doc->getPathToDoc());

      $html = '<object
        data="'.$p['page']['pj']['href'].'"
        width="'.$p['page']['doc']['displayParams']['width'].'"
        height="'.$p['page']['doc']['displayParams']['height'].'"
        style="border: 15px solid #DDD"
        type="'.$p['page']['doc']['mime'].'">
      </object>';
    } elseif($p['page']['pj']['html'] == 'TXT') {
      $html = nl2br(file_get_contents($doc->getPathToDoc()));
    }
    return $html;
  }

/**
 * Obtenir le HTML de prévisualisation d'un Mail
 * @return string code html
 */
  public function getGenericPreviewReglement() {
    global $p;
    $data = new msObjet();
    $data->setObjetID($this->_objetID);
    $p['page']['datareg'] = $data->getObjetAndSons('name');
    $p['page']['typeFormHonoraires']=msSQL::sqlUniqueChamp("SELECT dt.formValues AS form FROM data_types as dt
    LEFT JOIN objets_data as od ON dt.id=od.typeID WHERE od.id='".$this->_objetID."' limit 1");
    $p['page']['acteFacture']=msSQL::sqlUnique("SELECT * FROM actes WHERE id=(SELECT parentTypeID FROM objets_data WHERE id='".$this->_objetID."')");

    // détails de la facturation
    if(isset($p['page']['datareg']['regleDetailsActes'])) {
      $p['page']['datareg']['regleDetailsActes']['value'] = json_decode($p['page']['datareg']['regleDetailsActes']['value'], TRUE);
      if(empty($p['page']['acteFacture']['label'])) {
        $p['page']['acteFacture']['label']=implode(' + ', array_column($p['page']['datareg']['regleDetailsActes']['value'], 'acte'));
      }
    }

    // si retour post FSE
    if(isset($p['page']['datareg']['regleFseData'])) {
      $p['page']['dataregFse']=json_decode($p['page']['datareg']['regleFseData']['value'], true)[0];
      foreach($p['page']['dataregFse']['dataDetail'] as $acte) {
        if($acte['is_ligne_ok'] == 1) {
          $p['page']['dataregFse']['actesOK'][]=$acte['code_prestation'];
        } else {
          $p['page']['dataregFse']['actesKO'][]=$acte['code_prestation'];
        }
      }
    }

    $html = new msGetHtml;
    $html->set_template('inc-ajax-detReglement.html.twig');
    $html = $html->genererHtmlVar($p);
    return $html;
  }

/**
 * Obtenir le HTML de prévisualisation d'un Mail
 * @return string code html
 */
  public function getGenericPreviewMail() {
    global $p;

    $data = new msObjet();
    $data->setObjetID($this->_objetID);
    $p['page']['dataMail'] = $data->getObjetAndSons('name');

    $html = new msGetHtml;
    $html->set_template('inc-ajax-detMail.html.twig');
    $html = $html->genererHtmlVar($p);
    return $html;
  }

/**
 * Obtenir le HTML de prévisualisation d'une Ordo (non LAP)
 * @return string code html
 */
    public function getGenericPreviewOrdo() {
      global $p;

      $name2typeID = new msData();
      $name2typeID = $name2typeID->getTypeIDsFromName(['ordoLigneOrdoALDouPas','ordoTypeImpression','ordoLigneOrdo']);

      if ($ordoData=msSQL::sql2tab("select ald.value as ald, p.value as description, p.typeID, p.id
      from objets_data as p
      left join objets_data as ald on p.id=ald.instance and ald.typeID='".$name2typeID['ordoLigneOrdoALDouPas']."' and ald.outdated='' and ald.deleted=''
      where p.instance='".$this->_objetID."' and p.outdated='' and p.deleted='' and p.typeID in ('".$name2typeID['ordoTypeImpression']."','".$name2typeID['ordoLigneOrdo']."')
      group by p.id, ald.id
      order by p.id asc")) {
          $modePrint='standard';

          foreach ($ordoData as $v) {
              if ($v['typeID']==$name2typeID['ordoTypeImpression']) {
                  $modePrint=$v['description'];
              } else {
                  if ($v['ald']==1) {
                      $modePrint='ALD';
                  }
                  $p['page']['courrier']['medoc'][]=$v;
              }
          }
          $p['page']['courrier']['modeprint']=$modePrint;
      }

      //version pdf
      $p['page']['pdfHtml'] = $this->getFilePreviewDocument();

      $html = new msGetHtml;
      $html->set_template('inc-ajax-detOrdo.html.twig');
      $html = $html->genererHtmlVar($p);
      return $html;
    }

/**
 * Obtenir le HTML de prévisualisation d'une Ordo LAP Externe
 * @return string code html
 */
    public function getGenericPreviewOrdoLapExt() {
      //version pdf
      $p['page']['pdfHtml'] = $this->getFilePreviewDocument();

      $html = new msGetHtml;
      $html->set_template('inc-ajax-detOrdoLapExt.html.twig');
      $html = $html->genererHtmlVar($p);
      return $html;
    }

/**
 * Obtenir le HTML de prévisualisation d'un Courrier
 * @return string code html
 */
      public function getGenericPreviewCourrier() {
        global $p;
        $fakePDF = new msPDF();
        $fakePDF->setPageHeader('');
        $fakePDF->setPageFooter('');
        $fakePDF->setObjetID($this->_objetID);
        $fakePDF->makePDFfromObjetID();
        $version = $fakePDF->getContenuFinal();
        $p['page']['txtVersion']=  msTools::cutHtmlHeaderAndFooter($version);

        //version pdf
        $p['page']['pdfHtml'] = $this->getFilePreviewDocument();

        $html = new msGetHtml;
        $html->set_template('inc-ajax-detCourrier.html.twig');
        $html = $html->genererHtmlVar($p);
        return $html;

      }

/**
 * Obtenir le HTML de prévisualisation à partir du template d'impression
 * @return string code html
 */
    public function getGenericPreviewFromPrintTemplate() {
      global $p;
      $string='';
      $courrier=new msCourrier;
      $modelImpression = $courrier->getPrintModel($this->_dataObjet['formValues']);
      $templatesPdfFolder = msConfiguration::getParameterValue('templatesPdfFolder', ['id'=>$this->_dataObjet['fromID'], 'module'=>'']);
      if(is_file($templatesPdfFolder.$modelImpression.'.html.twig')) {
        $fakePDF = new msPDF();
        $fakePDF->setPageHeader('');
        $fakePDF->setPageFooter('');
        $fakePDF->setObjetID($this->_objetID);
        $fakePDF->makePDFfromObjetID();
        $version = $fakePDF->getContenuFinal();
        $p['page']['txtVersion'] = msTools::cutHtmlHeaderAndFooter($version);
      }
      // si pdf existant
      $stockage = new msStockage;
      $stockage->setObjetID($this->_objetID);
      if($stockage->testDocExist()) {
        $p['page']['pdfVersion'] = $this->getFilePreviewDocument();
      }
      // si rien on va utiliser le template automatique.
      if (!isset($p['page']['txtVersion']) and !isset($p['page']['pdfVersion'])) {
        $form = new msForm;
        $form->setFormIDbyName($this->_dataObjet['formValues']);
        $form->getForm();
        $courrier->setObjetID($this->_objetID);
        $tag['tag']=$courrier->getDataByObjetID();
        $p['page']['txtVersion'] = msGetHtml::genererHtmlFromString($form->getFlatBasicTemplateCode(), $tag );

        // et si vraiment rien, message impossibilité
        if(empty($p['page']['txtVersion'])) $p['page']['txtVersion'] = "Pas d'aperçu disponible pour cet élément";
      }

      $html = new msGetHtml;
      $html->set_template('inc-ajax-detGenericPreview.html.twig');
      $html = $html->genererHtmlVar($p);
      return $html;
    }

/**
 * Obtenir le PDF embarqué
 * @return string code html pour PDF embarqué
 */
    public function getGenericPreviewPDF() {
      $doc = new msStockage();
      $doc->setObjetID($this->_objetID);
      if (!$doc->testDocExist()) {
        $pdf= new msPDF();
        $pdf->setObjetID($this->_objetID);
        $pdf->makePDFfromObjetID();
        $pdf->savePDF();
      }
      $this->_pdfOrientation = $doc->getPdfOrientation();
      $p['page']['pdfVersion'] = $this->getFilePreviewDocument();

      $html = new msGetHtml;
      $html->set_template('inc-ajax-detGenericPreview.html.twig');
      $html = $html->genererHtmlVar($p);
      return $html;
    }

/**
 * Obtenir le HTML de prévisualisation d'une déclaration ALD
 * @return string code html
 */
      public function getPreviewCsAldDeclaration() {
        global $p;

        $data = new msObjet();
        $data->setObjetID($this->_objetID);
        $p['page']['dataAld'] = $data->getObjetAndSons('name');
        $selectedAldLabel=new msData;
        $selectedAldLabel = $selectedAldLabel->getSelectOptionValue([$p['page']['dataAld']['aldNumber']['typeID']]);

        $p['page']['dataAld']['aldNumber']['aldLabel']=$selectedAldLabel[$p['page']['dataAld']['aldNumber']['typeID']][$p['page']['dataAld']['aldNumber']['value']];

        $html = new msGetHtml;
        $html->set_template('inc-ajax-detCsAldDeclaration.html.twig');
        $html = $html->genererHtmlVar($p);
        return $html;
      }

}
