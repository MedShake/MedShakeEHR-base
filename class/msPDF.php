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

use Dompdf\Dompdf;

/**
 * Générer du PDF
 * MedShakeEHR utilise des templates Twig <https://twig.sensiolabs.org/>
 * en html + css et injecte le résultat HTML dans Dompdf <https://github.com/dompdf/dompdf>
 * pour générer un PDF à sauver ou à afficher.
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */


class msPDF
{
    /** @var string Le contenu passé en POST pour constituer le corps du PDF */
    private $_bodyFromPost;
    /** @var string le corps du message */
    private $_body;
    /** @var string le contenu final avant génération */
    private $_contenuFinalPDF;
    /** @var int l'objet à partir duquel le pdf est généré */
    private $_objetID;
    /** @var int le user qui génère le pdf */
    private $_fromID;
    /** @var int patient pour lequel le pdf est généré */
    private $_toID;
    /** @var int  le type de coument (cr, courrir, ordo ...) */
    private $_type;
    /** @var int  l'ID qui permet de chercher le modèle */
    private $_modeleID;
    /** @var string header du pdf */
    private $_pageHeader;
    /** @var string footer du pdf */
    private $_pageFooter;
    /** @var string lap : mode d'impression anonyme */
    private $_anonymeMode=FALSE;
    /** @var string optimiser (reduction de taille) avec GhostScript */
    private $_optimizeWihtGS=FALSE;
    /** @var string dossier de template à utiliser */
    private $_templatesPdfFolder;

/**
 * Définir le corps du PDF : datas envoyées en POST
 * @param string $v Data envoyées en POST
 */
    public function setBodyFromPost($v)
    {
        return $this->_bodyFromPost = $v;
    }

/**
 * Définir le contenu complet du PDF avant génération
 * @param string $v COntenu complet du PDF
 */
    public function setContenuFinalPDF($v)
    {
        return $this->_contenuFinalPDF = $v;
    }

/**
 * Définir l'objetID qui sert à générer le PDF
 * @param int $v ID de l'objet source
 */
    public function setObjetID($v)
    {
        return $this->_objetID = $v;
    }

/**
 * Utilisateur qui génère le PDF
 * @param int $v ID du user
 */
    public function setFromID($v)
    {

        $this->_templatesPdfFolder = msConfiguration::getParameterValue('templatesPdfFolder', $user=array('id'=>$v, 'module'=>''));
        return $this->_fromID = $v;
    }

/**
 * Définir le patient conerné par le PDF
 * @param int $v ID du patient
 */
    public function setToID($v)
    {
        return $this->_toID = $v;
    }

/**
 * Définir le type du document (courrier, CR, ordo ...)
 * @param string $v Type du document
 */
    public function setType($v)
    {
        return $this->_type = $v;
    }

/**
 * Définir L'ID qui permet de chercher le modèle
 * @param int $v ID du modèle
 */
    public function setModeleID($v)
    {
        return $this->_modeleID = $v;
    }

/**
 * Définir le header (HTML)
 * @param string $v HTML du header
 */
    public function setPageHeader($v)
    {
        return $this->_pageHeader = $v;
    }

/**
 * Définir le footer (HTML)
 * @param string $v HTML du footer
 */
    public function setPageFooter($v)
    {
        return $this->_pageFooter = $v;
    }

/**
 * Définir le mode d'impression auteur anonyme.
 */
    public function setAnonymeMode()
    {
        return $this->_anonymeMode = TRUE;
    }

/**
 * Obtenir le contenu final complet du PDF
 * @return string HTML contenu final complet
 */
    public function getContenuFinal()
    {
        return $this->_contenuFinalPDF;
    }

/**
 * Définir le fait d'optimiser ou non le PDF final avec GhostScript
 * @param boolean $v FALSE/TRUE
 */
    public function setOptimizeWithGS($v) {
      return $this->_optimizeWihtGS = $v;
    }

/**
 * Construire un PDF à partir d'un numéro d'objet
 * @return void
 */
    public function makePDFfromObjetID()
    {
        if (!isset($this->_objetID)) {
            throw new Exception('ObjetID is not defined');
        }
        $doc = new msObjet();
        $data=$doc->getCompleteObjetDataByID($this->_objetID);
        $this->setFromID($data['fromID']);
        $this->_toID=$data['toID'];
        if($data['name'] == 'lapOrdonnance') {
            $this->_type="ordoLAP";
        } elseif ($data['groupe']=="courrier") {
            $this->_body=msTools::unbbcodifier($data['value']);
            $this->_type="courrier";
            $this->_modeleID=$data['typeID'];
        } elseif ($data['groupe']=="typecs") {
            $this->_type="cr";
        } elseif ($data['groupe']=="ordo") {
            $this->_type="ordo";
        }
        $this->makePDF();
    }


/**
 * Construire un PDF : header + body + footer
 * @return void
 */
    public function makePDF()
    {
        global $p;
        if (!isset($this->_body)) {
            $this->_makeBody();
        }
        if (!isset($this->_pageHeader)) {
            $this->_pageHeader = $this->makeWithTwig($p['config']['templateDefautPage']);
        }
        if (!isset($this->_pageFooter)) {
            $this->_pageFooter = $this->makeWithTwig('base-page-closeHtml.html.twig');
        }

        $this->_contenuFinalPDF = $this->_pageHeader.$this->_body.$this->_pageFooter;
    }
/**
 * Envoyer le PDF au navigateur pour affichage
 * @return void
 */
    public function showPDF()
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($this->_contenuFinalPDF);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('document.pdf', array('Attachment'=>0));
    }

/**
 * Sauver le PDF dans le stockage
 * @return void
 */
    public function savePDF()
    {
        global $p;
        if (!isset($this->_objetID)) {
            throw new Exception('ObjetID is not defined');
        }

        $dompdf = new Dompdf();
        $dompdf->loadHtml($this->_contenuFinalPDF);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf = $dompdf->output();

        $folder=msStockage::getFolder($this->_objetID);
        msTools::checkAndBuildTargetDir($p['config']['stockageLocation'].$folder.'/');
        $finalFile = $p['config']['stockageLocation'].$folder.'/'.$this->_objetID.'.pdf';

        if($this->_optimizeWihtGS == TRUE and msTools::commandExist('gs')) {
          $tempFile = $p['config']['workingDirectory'].$this->_objetID.'.pdf';
          file_put_contents($tempFile, $pdf);
          exec('gs -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/prepress -sOutputFile='.$finalFile.' '.$tempFile);
          unlink($tempFile);
        } else {
          file_put_contents($finalFile, $pdf);
        }

        //sauver la copie en base
        $this->_savePrinted();
    }

/**
 * Enregistrer en base l'impression
 * @return void
 */
    private function _savePrinted()
    {
        global $p;
        $data=array(
        'fromID'=>$this->_fromID,
        'toID'=>$this->_toID,
        'type'=>$this->_type,
        'objetID'=>$this->_objetID,
        'title'=>'',
        'value'=>$this->_contenuFinalPDF
      );

      if($this->_anonymeMode) $data['anonyme'] = 'y';

      // test préalable car pour courrier dont le body est injecté en POST, ca ne fonctionne pas.
      if(isset($p['page']['courrier'])) {
        $data['serializedTags']=serialize($p['page']['courrier']);
      }

        msSQL::sqlInsert('printed', $data, false);
    }
/**
 * Construire le corps de PDF suivant le type de document
 * @return void
 */
    private function _makeBody()
    {
        global $p;

        //contenu du body passé en POST
        if (isset($this->_bodyFromPost)) {
            $this->_body=$this->_bodyFromPost;

            //si c'est un courrier
            if ($this->_type=='courrier') {

              //on déclare le modèle de page
              if (!isset($this->_pageHeader)) {
                  $this->_pageHeader = $this->makeWithTwig($p['config']['templateCourrierHeadAndFoot']);
              }

              //c'est un certificat en édition alors il faut le sauver dans objets_data (et on marque le précedent outdated)
              if (isset($this->_objetID)) {
                  $sauverPd = new msObjet();
                  $sauverPd->setToID($this->_toID);
                  $sauverPd->setFromID($this->_fromID);
                  $this->_objetID=$sauverPd->createNewObjet($this->_modeleID, msTools::bbcodifier($this->_body), '0', '0', $this->_objetID);
              }
              //nouveau courrier : on sauve
              else {
                  $sauverPd = new msObjet();
                  $sauverPd->setToID($this->_toID);
                  $sauverPd->setFromID($this->_fromID);
                  $this->_objetID=$sauverPd->createNewObjet($this->_modeleID, msTools::bbcodifier($this->_body));
              }
            }
        }
        //sinon
        else {
            //si c'est un compte rendu
            if ($this->_type=='cr') {
                $this->_makeBodyForCr();
            }
            //si c'est un courrier
            elseif ($this->_type=='courrier') {
                $this->_makeBodyForCourrier();
            }
            //si c'est une ordo
            elseif ($this->_type=='ordo') {
                $this->_makeBodyForOrdo();
            }
            //si c'est une ordo LAP
            elseif ($this->_type=='ordoLAP') {
                $this->_makeBodyForOrdoLAP();
            }
        }
    }

/**
 * Construire le corps du PDF pour une ordonnance
 * @return void
 */
    private function _makeBodyForOrdoLAP()
    {
      global $p;

      // sortir les infos patient
      $courrier = new msCourrier();
      $courrier->setObjetID($this->_objetID);
      $p['page']['courrier']=$courrier->getOrdoData();

      // sortir data ordonnance
      $ordo = new msLapOrdo;
      $ordo->setOrdonnanceID($this->_objetID);
      $tabOrdo=$ordo->getOrdonnance();
      $modePrint='standard';

      if(isset($tabOrdo['ordoMedicsALD'])) {
        $modePrint='ald';
        foreach($tabOrdo['ordoMedicsALD'] as $k=>$l) {
          if(count($l['medics']) > 1) {
            $p['page']['courrier']['medoc']['ald'][$k]=$l['ligneData']['voieUtilisee'].' - '.$l['ligneData']['dureeTotaleHuman']."\n";
            foreach($l['medics'] as $km=>$m) {
              $p['page']['courrier']['medoc']['ald'][$k].= ($km+1) .'- '.$m['nomUtileFinal'];
              if($m['isNPS'] == 'true') {
                $p['page']['courrier']['medoc']['ald'][$k].= ' [non substituable';
                if($m['motifNPS'] != '') $p['page']['courrier']['medoc']['ald'][$k].=' - '.$m['motifNPS'];
                $p['page']['courrier']['medoc']['ald'][$k].= ']';
              }
              $p['page']['courrier']['medoc']['ald'][$k].= "\n".implode("\n", $m['posoHumanCompleteTab'])."\n";
              if($p['config']['lapPrintAllergyRisk'] == 'true' and isset($m['risqueAllergique'])) {
                if($m['risqueAllergique']) $p['page']['courrier']['medoc']['ald'][$k].= "Un risque théorique d'allergie ou d'intolérance vous concernant est connu pour ce traitement.\n";
              }
            }
            if(!empty(trim($l['ligneData']['consignesPrescription']))) {
              $p['page']['courrier']['medoc']['ald'][$k].= $l['ligneData']['consignesPrescription']."\n";
            }
          } else {
            $m=$l['medics'][0];
            $p['page']['courrier']['medoc']['ald'][$k]=$m['nomUtileFinal'];
            $p['page']['courrier']['medoc']['ald'][$k].=" - ".$l['ligneData']['voieUtilisee'];
            if($m['isNPS'] == 'true') {
              $p['page']['courrier']['medoc']['ald'][$k].= ' - [non substituable';
              if($m['motifNPS'] != '') $p['page']['courrier']['medoc']['ald'][$k].=' - '.$m['motifNPS'];
              $p['page']['courrier']['medoc']['ald'][$k].= ']';
            }
            $p['page']['courrier']['medoc']['ald'][$k].= "\n".implode("\n", $m['posoHumanCompleteTab'])."\n";
            if($p['config']['lapPrintAllergyRisk'] == 'true' and isset($m['risqueAllergique'])) {
              if($m['risqueAllergique']) $p['page']['courrier']['medoc']['ald'][$k].= "Un risque théorique d'allergie ou d'intolérance vous concernant est connu pour ce traitement.\n";
            }
            if(!empty(trim($l['ligneData']['consignesPrescription']))) {
              $p['page']['courrier']['medoc']['ald'][$k].= $l['ligneData']['consignesPrescription']."\n";
            }
          }
        }
      }

      if(isset($tabOrdo['ordoMedicsG'])) {
        foreach($tabOrdo['ordoMedicsG'] as $k=>$l) {
          if(count($l['medics']) > 1) {
            $p['page']['courrier']['medoc']['standard'][$k]=$l['ligneData']['voieUtilisee'].' - '.$l['ligneData']['dureeTotaleHuman']."\n";
            foreach($l['medics'] as $km=>$m) {
              $p['page']['courrier']['medoc']['standard'][$k].= ($km+1) .'- '.$m['nomUtileFinal'];
              if($m['isNPS'] == 'true') {
                $p['page']['courrier']['medoc']['standard'][$k].= ' [non substituable';
                if($m['motifNPS'] != '') $p['page']['courrier']['medoc']['standard'][$k].=' - '.$m['motifNPS'];
                $p['page']['courrier']['medoc']['standard'][$k].= ']';
              }
              $p['page']['courrier']['medoc']['standard'][$k].= "\n".implode("\n", $m['posoHumanCompleteTab'])."\n";
              if($p['config']['lapPrintAllergyRisk'] == 'true' and isset($m['risqueAllergique'])) {
                if($m['risqueAllergique']) $p['page']['courrier']['medoc']['standard'][$k].= "Un risque théorique d'allergie ou d'intolérance vous concernant est connu pour ce traitement.\n";
              }
            }
            if(!empty(trim($l['ligneData']['consignesPrescription']))) {
              $p['page']['courrier']['medoc']['standard'][$k].= $l['ligneData']['consignesPrescription']."\n";
            }
          } else {
            $m=$l['medics'][0];
            $p['page']['courrier']['medoc']['standard'][$k]=$m['nomUtileFinal'];
            $p['page']['courrier']['medoc']['standard'][$k].=" - ".$l['ligneData']['voieUtilisee'];
            if($m['isNPS'] == 'true') {
              $p['page']['courrier']['medoc']['standard'][$k].= ' - [non substituable';
              if($m['motifNPS'] != '') $p['page']['courrier']['medoc']['standard'][$k].=' - '.$m['motifNPS'];
              $p['page']['courrier']['medoc']['standard'][$k].= ']';
            }
            $p['page']['courrier']['medoc']['standard'][$k].= "\n".implode("\n", $m['posoHumanCompleteTab'])."\n";
            if($p['config']['lapPrintAllergyRisk'] == 'true' and isset($m['risqueAllergique'])) {
              if($m['risqueAllergique']) $p['page']['courrier']['medoc']['standard'][$k].= "Un risque théorique d'allergie ou d'intolérance vous concernant est connu pour ce traitement.\n";
            }
            if(!empty(trim($l['ligneData']['consignesPrescription']))) {
              $p['page']['courrier']['medoc']['standard'][$k].= $l['ligneData']['consignesPrescription']."\n";
            }
          }
        }
      }

      //si on sort en mode ald alors on va annuler les header et footer standard
      if ($modePrint=='ald') {
        $this->_pageHeader=$this->_pageFooter='';
        if($this->_anonymeMode) {
          $p['page']['courrier']['printModel']='ordonnanceAnonymeALD.html.twig';
        } else {
          $p['page']['courrier']['printModel']=$p['config']['templateOrdoALD'];
        }
      } else {
        if($this->_anonymeMode) {
          $this->_pageHeader=$this->_pageFooter='';
          $p['page']['courrier']['printModel']='ordonnanceAnonyme.html.twig';
        } else {
          $this->_pageHeader= $this->makeWithTwig($p['config']['templateOrdoHeadAndFoot']);
          $p['page']['courrier']['printModel']=$p['config']['templateOrdoBody'];
        }
      }

      //on génère le body avec twig
      $this->_body =  $this->makeWithTwig($p['page']['courrier']['printModel']);

    }

/**
 * Construire le corps du PDF pour une ordonnance
 * @return void
 */
    private function _makeBodyForOrdo()
    {
        global $p;

        $name2typeID = new msData();
        $name2typeID = $name2typeID->getTypeIDsFromName(['ordoLigneOrdoALDouPas','ordoTypeImpression']);

        if ($ordoData=msSQL::sql2tab("select p.*, ald.value as ald
          from objets_data as p
          left join objets_data as ald on p.id=ald.instance and ald.typeID='".$name2typeID['ordoLigneOrdoALDouPas']."' and ald.outdated='' and ald.deleted=''
          where p.instance='".$this->_objetID."' and p.outdated='' and p.deleted=''
          group by p.id, ald.value
          order by p.id asc")) {

            // sortir les infos
            $courrier = new msCourrier();
            $courrier->setObjetID($this->_objetID);
            $p['page']['courrier']=$courrier->getOrdoData();


            $modePrint='standard';

            foreach ($ordoData as $v) {
                //on chope au passage le mode d'impression
                if ($v['typeID']==$name2typeID['ordoTypeImpression']) {
                    $modePrint=$v['value'];
                }

                // on traite ligne par ligne
                else {
                    if ($v['ald']=='1' or $v['ald']=='true') {
                        $modePrint='ald';
                        $key='ald';
                    } else {
                        $key='standard';
                    }
                    $p['page']['courrier']['medoc'][$key][]=$v['value'];
                }
            }

            //si on sort en mode ald alors on va annuler les header et footer standard
            if ($modePrint=='ald') {
                $this->_pageHeader=$this->_pageFooter='';
                $p['page']['courrier']['printModel']=$p['config']['templateOrdoALD'];
            } else {
                $this->_pageHeader= $this->makeWithTwig($p['config']['templateOrdoHeadAndFoot']);
                $p['page']['courrier']['printModel']=$p['config']['templateOrdoBody'];
            }

            //on génère le body avec twig
            $this->_body =  $this->makeWithTwig($p['page']['courrier']['printModel']);
        }
    }

/**
 * Construire le corps du PDF pour un compte rendu
 * @return void
 */
    private function _makeBodyForCr()
    {
        global $p;
        $courrier = new msCourrier();
        $courrier->setObjetID($this->_objetID);
        $p['page']['courrier']=$courrier->getCrData();

        //on déclare le modèle de page
        // soit il remonte des data de msCourrier (via msModuleDataCourrier)
        if(isset($p['page']['courrier']['templateCrHeadAndFoot'])) {
            $this->_pageHeader = $this->makeWithTwig($p['page']['courrier']['templateCrHeadAndFoot']);
        }
        // soit on prend le modèle par défaut de la config
        elseif (!isset($this->_pageHeader)) {
            $this->_pageHeader = $this->makeWithTwig($p['config']['templateCrHeadAndFoot']);
        }

        //on génère le body avec twig
        $this->_body =  $this->makeWithTwig($p['page']['courrier']['printModel']);
    }

/**
 * Construire le coprs du PDF pour un courrier (ou certificat)
 * @return void
 */
    private function _makeBodyForCourrier()
    {
        global $p;
        $courrier = new msCourrier();
        $courrier->setPatientID($this->_toID);
        $p['page']['courrier']=$courrier->getCourrierData();

        $dataform = new msObjet();
        $dataform=$dataform->getObjetDataByID($this->_objetID, ['value']);
        $this->_body = msTools::unbbcodifier($dataform['value']);

        //on déclare le modèle de page
        if (!isset($this->_pageHeader)) {
            $this->_pageHeader = $this->makeWithTwig($p['config']['templateCourrierHeadAndFoot']);
        }

        $data=new msData();
        if ($printModel=$data->getDataType($this->_modeleID, ['formValues'])) {
            $p['page']['courrier']['printModel']=$printModel['formValues'].'.html.twig';
        } else {
            $p['page']['courrier']['printModel']='defaut.html.twig';
        }
    }

/**
 * Utiliser Twig pour obtenir du HTML à partir des templates et l'injecter dans dompdf
 * @param  string $template le template à traiter (avec son extension .html.Twig)
 * @return string           le HTML à utiliser
 */
    public function makeWithTwig($template)
    {
        global $p;
        if(isset($this->_templatesPdfFolder)) {
          $templatesPdfFolder=$this->_templatesPdfFolder;
        } else {
          $templatesPdfFolder=$p['config']['templatesPdfFolder'];
        }
        // les variables d'environnement twig
        if(isset($p['config']['twigEnvironnementCache'])) $twigEnvironment['cache']=$p['config']['twigEnvironnementCache']; else $twigEnvironment['cache']=false;
        if(isset($p['config']['twigEnvironnementAutoescape'])) $twigEnvironment['autoescape']=$p['config']['twigEnvironnementAutoescape']; else $twigEnvironment['autoescape']=false;

        $loaderPDF = new Twig_Loader_Filesystem($templatesPdfFolder);
        $twigPDF = new Twig_Environment($loaderPDF, $twigEnvironment);
        $twigPDF->getExtension('Twig_Extension_Core')->setDateFormat('d/m/Y', '%d days');
        $twigPDF->getExtension('Twig_Extension_Core')->setTimezone('Europe/Paris');
        return $twigPDF->render($template, $p);
    }

/**
 * Obtenir un duplicata du pdf
 * @return void
 */
    public function getDuplicata() {
      global $p;

      $pdf = new msStockage;
      $pdf->setObjetID($this->_objetID);
      if (!$pdf->testDocExist()) {
        $this->makePDFfromObjetID();
        $this->savePDF();
      }
      $original = $pdf->getPathToDoc();

      msTools::checkAndBuildTargetDir($p['config']['workingDirectory'].$p['user']['id'].'/');
      $tempfile=$p['config']['workingDirectory'].$p['user']['id'].'/'.time().'.pdf';
      $watermark=$p['homepath'].'templates/duplicata.pdf';
      system("pdftk $original background $watermark output $tempfile dont_ask", $errcode);
      if (!$errcode && $ih=fopen($tempfile, 'r')) {
          header('Content-Type: application/pdf');
          fpassthru($ih);
          fclose($ih);
      } else {
          echo "Problème de génération du duplicata";
      }
      unlink($tempfile);
    }

}
