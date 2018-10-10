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

/**
 * Définir l'ID de l'objet
 * @param int $id objetID
 */
  public function setObjetID($id) {
    $this->_objetID = $id;
    $data = new msObjet();
    $data->setToID($id); //fake
    $this->_dataObjet = $data->getCompleteObjetDataByID($id);
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
 * Obtenir le HTML de prévisualisation d'un Document
 * @return string code html
 */
  public function getGenericPreviewDocument() {
    global $p;
    $doc = new msStockage();
    $doc->setObjetID($this->_objetID);

    if ($doc->testDocExist()) {
        $p['page']['pj']['href']=$doc->getWebPathToDoc();
        $p['page']['pj']['html']=strtoupper($doc->getFileExtOfDoc());
        $p['page']['pj']['filesize']= $doc->getFileSize(0);

        if (array_key_exists($p['page']['pj']['html'], array('JPG'=>true, 'PNG'=>true))) {
            $p['page']['pj']['view']='<img style="max-width:100%;max-height:200px" src="'.$p['config']['protocol'].$p['config']['host'].$p['config']['urlHostSuffixe'].'/'.$doc->getWebPathToDoc().'"/>';
        } elseif ($p['page']['pj']['html']=='TXT') {
            $fn=$doc->getPathToDoc();
            $fsz=filesize($fn);
            $f=fopen($fn, 'r');
            $p['page']['pj']['detail']= fread($f, min(256, $fsz)).($fsz>256?"\n...":'');
        }
    }
    if (!empty($this->_dataObjet['value'])) {
        //hprim
        $p['page']['bioHprim'] = msHprim::parseSourceHprim($this->_dataObjet['value']);
        //texte
        $p['page']['texte']= $this->_dataObjet['value'];
    }

    $html = new msGetHtml;
    $html->set_template('inc-ajax-detDoc.html.twig');
    $html = $html->genererHtmlString($p);
    return $html;
  }

/**
 * Obtenir le HTML de prévisualisation d'un Mail
 * @return string code html
 */
  public function getGenericPreviewReglement() {
    global $p;
    $data = new msObjet();
    $p['page']['datareg'] = $data->getObjetAndSons($this->_objetID, 'name');
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

    $html = new msGetHtml;
    $html->set_template('inc-ajax-detReglement.html.twig');
    $html = $html->genererHtmlString($p);
    return $html;
  }

/**
 * Obtenir le HTML de prévisualisation d'un Mail
 * @return string code html
 */
  public function getGenericPreviewMail() {
    global $p;

    $data = new msObjet();
    $p['page']['dataMail'] = $data->getObjetAndSons($this->_objetID, 'name');

    $html = new msGetHtml;
    $html->set_template('inc-ajax-detMail.html.twig');
    $html = $html->genererHtmlString($p);
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

      $html = new msGetHtml;
      $html->set_template('inc-ajax-detOrdo.html.twig');
      $html = $html->genererHtmlString($p);
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

        $string = '<td></td><td colspan="4" class="py-4"><div class="card bg-light p-2 appercu">';
        $string .=  msTools::cutHtmlHeaderAndFooter($version);
        $string .=  '</div></td>';
        return $string;
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

      } else {
        $version = "Pas d'aperçu disponible pour cet élément";
      }
      $string = '<td></td><td colspan="4" class="py-4"><div class="card bg-light p-2 appercu">';
      $string .=  msTools::cutHtmlHeaderAndFooter($version);
      $string .=  '</div></td>';
      return $string;
    }

/**
 * Obtenir le HTML de prévisualisation d'une déclaration ALD
 * @return string code html
 */
      public function getPreviewCsAldDeclaration() {
        global $p;

        $data = new msObjet();
        $p['page']['dataAld'] = $data->getObjetAndSons($this->_objetID, 'name');
        $selectedAldLabel=new msData;
        $selectedAldLabel = $selectedAldLabel->getSelectOptionValue([$p['page']['dataAld']['aldNumber']['typeID']]);

        $p['page']['dataAld']['aldNumber']['aldLabel']=$selectedAldLabel[$p['page']['dataAld']['aldNumber']['typeID']][$p['page']['dataAld']['aldNumber']['value']];

        $html = new msGetHtml;
        $html->set_template('inc-ajax-detCsAldDeclaration.html.twig');
        $html = $html->genererHtmlString($p);
        return $html;
      }

}
