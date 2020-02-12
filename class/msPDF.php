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
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

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
    /** @var string dossier de template à utiliser */
    private $_templatesPdfFolder;
    /** @var string chemin final du PDF construit */
    private $_finalPdfFile;
    /** @var array data courrier */
    private $_courrierData=[];

    /** @var array options du formulaire source (si type = cr) */
    private $_formOptions;
    /** @var string taille du papier */
    private $_paperSize='A4';
    /** @var string orientation du papier  portrait / landscape */
    private $_paperOrientation='portrait';
    /** @var string optimiser (reduction de taille) avec GhostScript */
    private $_optimizeWithGS=FALSE;
    /** @var string lap : mode d'impression anonyme */
    private $_anonymeMode=FALSE;

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
        if (!is_numeric($v)) throw new Exception('ObjetID is not numeric');
        return $this->_objetID = $v;
    }

/**
 * Utilisateur qui génère le PDF
 * @param int $v ID du user
 */
    public function setFromID($v)
    {
        if (!msPeople::checkPeopleExist($v)) {
          throw new Exception('FromID does not exist');
        }
        $this->_templatesPdfFolder = msConfiguration::getParameterValue('templatesPdfFolder', $user=array('id'=>$v, 'module'=>''));
        return $this->_fromID = $v;
    }

/**
 * Définir le patient conerné par le PDF
 * @param int $v ID du patient
 */
    public function setToID($v)
    {
        if (!msPeople::checkPeopleExist($v)) {
          throw new Exception('ToID does not exist');
        }
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
        if (!is_numeric($v)) throw new Exception('ModeleID is not numeric');
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
      return $this->_optimizeWithGS = $v;
    }

/**
 * Construire un PDF à partir d'un numéro d'objet
 * @return void
 */
    public function makePDFfromObjetID()
    {
        if (!is_numeric($this->_objetID)) {
            throw new Exception('ObjetID is not numeric');
        }
        $doc = new msObjet();
        $doc->setObjetID($this->_objetID);
        $data=$doc->getCompleteObjetDataByID();
        $this->setFromID($data['fromID']);
        $this->_toID=$data['toID'];
        if($data['name'] == 'lapExtOrdonnance') {
            $this->_type="ordoLapExt";
        } elseif($data['name'] == 'lapOrdonnance') {
            $this->_type="ordoLAP";
        } elseif ($data['groupe']=="courrier") {
            $this->_body=msTools::unbbcodifier($data['value']);
            $this->_type="courrier";
            $this->_modeleID=$data['typeID'];
        } elseif ($data['groupe']=="typecs") {
            $this->_type="cr";
        } elseif ($data['groupe']=="ordo") {
            $this->_type="ordo";
        } elseif ($data['groupe']=="reglement") {
            $this->_type="reglement";
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

        // remonter aux options du form d'origine si compte rendu et ajuster.
        if($this->_type=='cr') {
          $this->_getOriginFormOptions();

          if (isset($this->_formOptions['optionsPdf']['onMake']['paperSize'])) {
            $this->_paperSize = $this->_formOptions['optionsPdf']['onMake']['paperSize'];
          }
          if (isset($this->_formOptions['optionsPdf']['onMake']['paperOrientation'])) {
            $this->_paperOrientation = $this->_formOptions['optionsPdf']['onMake']['paperOrientation'];
          }
          if (isset($this->_formOptions['optionsPdf']['onMake']['pageHeaderTemplate'])) {
            $this->_pageHeader = $this->_formOptions['optionsPdf']['onMake']['pageHeaderTemplate'];
          }
          if (isset($this->_formOptions['optionsPdf']['onMake']['pageFooterTemplate'])) {
            $this->_pageFooter = $this->_formOptions['optionsPdf']['onMake']['pageFooterTemplate'];
          }
        }

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
        $dompdf->setPaper($this->_paperSize, $this->_paperOrientation);
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

        // PDF issu de la construction HTML (dompdf)
        $dompdf = new Dompdf();
        $dompdf->loadHtml($this->_contenuFinalPDF);
        $dompdf->setPaper($this->_paperSize, $this->_paperOrientation);
        $dompdf->render();
        $pdf = $dompdf->output();

        $folder=msStockage::getFolder($this->_objetID);
        msTools::checkAndBuildTargetDir($p['config']['stockageLocation'].$folder.'/');
        $this->_finalPdfFile = $p['config']['stockageLocation'].$folder.'/'.$this->_objetID.'.pdf';
        file_put_contents($this->_finalPdfFile, $pdf);

        //si c'est un compte rendu on va rechercher les options du form et on agit
        if ($this->_type=='cr') {

            if(isset($this->_formOptions['optionsPdf']['onSave']['optimizeWithGS']) and $this->_formOptions['optionsPdf']['onSave']['optimizeWithGS'] == true) {
              $this->_optimizeWithGS = TRUE;
            }

            // Construction d'un PDF complémentaire concaténé ou en remplacement via un PDF utilisé comme template (fpdf)
            if(isset($this->_formOptions['optionsPdf']['templatePdf']['source']) and is_file($this->_formOptions['optionsPdf']['templatePdf']['source'])) {
              $this->_savePdfFromTemplatePdf();
            }

            // Concaténation avec PDF fixe existant
            if(isset($this->_formOptions['optionsPdf']['onSave']['append'])) {
              $files=[];
              foreach($this->_formOptions['optionsPdf']['onSave']['append'] as $file) {
                if(is_file($file)) {
                  $files[]=$file;
                }
              }
              if(!empty($files)) {
                $tempFile = $p['config']['workingDirectory'].$this->_objetID.'.pdf';
                copy($this->_finalPdfFile, $tempFile);
                system("pdftk $tempFile ".implode(' ', $files)." output $this->_finalPdfFile dont_ask", $errcode);
                unlink($tempFile);
              }
            }
        }

        // Optimisation du PDF final si demandée
        if($this->_optimizeWithGS == TRUE and msTools::commandExist('gs')) {
          $tempFile = $p['config']['workingDirectory'].$this->_objetID.'.pdf';
          rename($this->_finalPdfFile,$tempFile);
          self::optimizeWithGS($tempFile, $this->_finalPdfFile);
          unlink($tempFile);
        }

        //sauver la copie en base (ne concerne que ce qui est généré par dompdf)
        $this->_savePrinted();
    }

/**
 * Retourner l'objetID courant
 * @return int objetID
 */
    public function getObjetID()
    {
      return $this->_objetID;
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
      if(isset($this->_courrierData)) {
        $data['serializedTags']=serialize($this->_courrierData);
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

              // permet de charger les tags page.courrier dans les template twig
              $courrier = new msCourrier();
              $courrier->setPatientID($this->_toID);
	      $courrier->setFromID($this->_fromID);
	      if (!empty($this->_objetID)) $courrier->setObjetID($this->_objetID);
              $this->_courrierData=$courrier->getCourrierData();

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
            $courrier = new msCourrier();
            $courrier->setObjetID($this->_objetID);

            //si c'est un compte rendu
            if ($this->_type=='cr') {
                $this->_courrierData=$courrier->getCrData();
                $this->_makeBodyForCr();
            }
            //si c'est un courrier
            elseif ($this->_type=='courrier') {
                $this->_courrierData=$courrier->getCourrierData();
                $this->_makeBodyForCourrier();
            }
            //si c'est une ordo
            elseif ($this->_type=='ordo') {
                $this->_courrierData=$courrier->getOrdoData();
                $this->_makeBodyForOrdo();
            }
            //si c'est une ordo LAP
            elseif ($this->_type=='ordoLAP') {
                $this->_courrierData=$courrier->getOrdoData();
                $this->_makeBodyForOrdoLAP();
            }
            //si c'est une ordo LAP Externe
            elseif ($this->_type=='ordoLapExt') {
                $this->_courrierData=$courrier->getOrdoData();
                $this->_makeBodyForOrdoLapExt();
            }
            //si c'est un règlement
            elseif ($this->_type=='reglement') {
                $this->_courrierData=$courrier->getReglementData();
                $this->_makeBodyForReglement();
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

      // sortir data ordonnance
      $ordo = new msLapOrdo;
      $ordo->setOrdonnanceID($this->_objetID);
      $tabOrdo=$ordo->getOrdonnance();
      $modePrint='standard';

      if(isset($tabOrdo['ordoMedicsALD'])) {
        $modePrint='ald';
        foreach($tabOrdo['ordoMedicsALD'] as $k=>$l) {
          if(count($l['medics']) > 1) {
            $this->_courrierData['medoc']['ald'][$k]=$l['ligneData']['voieUtilisee'].' - '.$l['ligneData']['dureeTotaleHuman']."\n";
            foreach($l['medics'] as $km=>$m) {
              $this->_courrierData['medoc']['ald'][$k].= ($km+1) .'- '.$m['nomUtileFinal'];
              if($m['isNPS'] == 'true') {
                $this->_courrierData['medoc']['ald'][$k].= ' [non substituable';
                if($m['motifNPS'] != '') $this->_courrierData['medoc']['ald'][$k].=' - '.$m['motifNPS'];
                $this->_courrierData['medoc']['ald'][$k].= ']';
              }
              $this->_courrierData['medoc']['ald'][$k].= "\n".implode("\n", $m['posoHumanCompleteTab'])."\n";
              if($p['config']['lapPrintAllergyRisk'] == 'true' and isset($m['risqueAllergique'])) {
                if($m['risqueAllergique']) $this->_courrierData['medoc']['ald'][$k].= "Un risque théorique d'allergie ou d'intolérance vous concernant est connu pour ce traitement.\n";
              }
            }
            if(!empty(trim($l['ligneData']['consignesPrescription']))) {
              $this->_courrierData['medoc']['ald'][$k].= $l['ligneData']['consignesPrescription']."\n";
            }
          } else {
            $m=$l['medics'][0];
            $this->_courrierData['medoc']['ald'][$k]=$m['nomUtileFinal'];
            $this->_courrierData['medoc']['ald'][$k].=" - ".$l['ligneData']['voieUtilisee'];
            if($m['isNPS'] == 'true') {
              $this->_courrierData['medoc']['ald'][$k].= ' - [non substituable';
              if($m['motifNPS'] != '') $this->_courrierData['medoc']['ald'][$k].=' - '.$m['motifNPS'];
              $this->_courrierData['medoc']['ald'][$k].= ']';
            }
            $this->_courrierData['medoc']['ald'][$k].= "\n".implode("\n", $m['posoHumanCompleteTab'])."\n";
            if($p['config']['lapPrintAllergyRisk'] == 'true' and isset($m['risqueAllergique'])) {
              if($m['risqueAllergique']) $this->_courrierData['medoc']['ald'][$k].= "Un risque théorique d'allergie ou d'intolérance vous concernant est connu pour ce traitement.\n";
            }
            if(!empty(trim($l['ligneData']['consignesPrescription']))) {
              $this->_courrierData['medoc']['ald'][$k].= $l['ligneData']['consignesPrescription']."\n";
            }
          }
        }
      }

      if(isset($tabOrdo['ordoMedicsG'])) {
        foreach($tabOrdo['ordoMedicsG'] as $k=>$l) {
          if(count($l['medics']) > 1) {
            $this->_courrierData['medoc']['standard'][$k]=$l['ligneData']['voieUtilisee'].' - '.$l['ligneData']['dureeTotaleHuman']."\n";
            foreach($l['medics'] as $km=>$m) {
              $this->_courrierData['medoc']['standard'][$k].= ($km+1) .'- '.$m['nomUtileFinal'];
              if($m['isNPS'] == 'true') {
                $this->_courrierData['medoc']['standard'][$k].= ' [non substituable';
                if($m['motifNPS'] != '') $this->_courrierData['medoc']['standard'][$k].=' - '.$m['motifNPS'];
                $this->_courrierData['medoc']['standard'][$k].= ']';
              }
              $this->_courrierData['medoc']['standard'][$k].= "\n".implode("\n", $m['posoHumanCompleteTab'])."\n";
              if($p['config']['lapPrintAllergyRisk'] == 'true' and isset($m['risqueAllergique'])) {
                if($m['risqueAllergique']) $this->_courrierData['medoc']['standard'][$k].= "Un risque théorique d'allergie ou d'intolérance vous concernant est connu pour ce traitement.\n";
              }
            }
            if(!empty(trim($l['ligneData']['consignesPrescription']))) {
              $this->_courrierData['medoc']['standard'][$k].= $l['ligneData']['consignesPrescription']."\n";
            }
          } else {
            $m=$l['medics'][0];
            $this->_courrierData['medoc']['standard'][$k]=$m['nomUtileFinal'];
            $this->_courrierData['medoc']['standard'][$k].=" - ".$l['ligneData']['voieUtilisee'];
            if($m['isNPS'] == 'true') {
              $this->_courrierData['medoc']['standard'][$k].= ' - [non substituable';
              if($m['motifNPS'] != '') $this->_courrierData['medoc']['standard'][$k].=' - '.$m['motifNPS'];
              $this->_courrierData['medoc']['standard'][$k].= ']';
            }
            $this->_courrierData['medoc']['standard'][$k].= "\n".implode("\n", $m['posoHumanCompleteTab'])."\n";
            if($p['config']['lapPrintAllergyRisk'] == 'true' and isset($m['risqueAllergique'])) {
              if($m['risqueAllergique']) $this->_courrierData['medoc']['standard'][$k].= "Un risque théorique d'allergie ou d'intolérance vous concernant est connu pour ce traitement.\n";
            }
            if(!empty(trim($l['ligneData']['consignesPrescription']))) {
              $this->_courrierData['medoc']['standard'][$k].= $l['ligneData']['consignesPrescription']."\n";
            }
          }
        }
      }

      //si on sort en mode ald alors on va annuler les header et footer standard
      if ($modePrint=='ald') {
        $this->_pageHeader=$this->_pageFooter='';
        if($this->_anonymeMode) {
          $this->_courrierData['printModel']='ordonnanceAnonymeALD.html.twig';
        } else {
          $this->_courrierData['printModel']=$p['config']['templateOrdoALD'];
        }
      } else {
        if($this->_anonymeMode) {
          $this->_pageHeader=$this->_pageFooter='';
          $this->_courrierData['printModel']='ordonnanceAnonyme.html.twig';
        } else {
          $this->_pageHeader= $this->makeWithTwig($p['config']['templateOrdoHeadAndFoot']);
          $this->_courrierData['printModel']=$p['config']['templateOrdoBody'];
        }
      }

      // choix impression du nombre de lignes de prescriptions forcé à oui
      $this->_courrierData['ordoImpressionNbLignes'] = 'o';

      //on génère le body avec twig
      $this->_body =  $this->makeWithTwig($this->_courrierData['printModel']);

    }

/**
 * Construire le corps du PDF pour une ordonnance LAP Externe
 * @return void
 */
    private function _makeBodyForOrdoLapExt()
    {
      global $p;

      $classLapExt = 'msLapExt'.ucfirst($p['config']['utiliserLapExterneName']);

      if(method_exists($classLapExt, 'makeBodyForOrdo')) {

        $lapExt = new $classLapExt;
        $this->_courrierData['medoc'] = $lapExt->makeBodyForOrdo($this->_objetID);

        if(!empty($this->_courrierData['medoc']['ald'])) {
          $modePrint='ald';
        } else {
          $modePrint='';
        }

        //si on sort en mode ald alors on va annuler les header et footer standard
        if ($modePrint=='ald') {
          $this->_pageHeader=$this->_pageFooter='';
          if($this->_anonymeMode) {
            $this->_courrierData['printModel']='ordonnanceAnonymeALD.html.twig';
          } else {
            $this->_courrierData['printModel']=$p['config']['templateOrdoALD'];
          }
        } else {
          if($this->_anonymeMode) {
            $this->_pageHeader=$this->_pageFooter='';
            $this->_courrierData['printModel']='ordonnanceAnonyme.html.twig';
          } else {
            $this->_pageHeader= $this->makeWithTwig($p['config']['templateOrdoHeadAndFoot']);
            $this->_courrierData['printModel']=$p['config']['templateOrdoBody'];
          }
        }
        // choix impression du nombre de lignes de prescriptions forcé à oui
        $this->_courrierData['ordoImpressionNbLignes'] = 'o';

        //on génère le body avec twig
        $this->_body =  $this->makeWithTwig($this->_courrierData['printModel']);
      } else {
        $this->_body = "La configuration du LAP externe est incomplète ou défaillante.";
      }

    }
/**
 * Construire le corps du PDF pour une ordonnance
 * @return void
 */
    private function _makeBodyForOrdo()
    {
        global $p;

        $name2typeID = new msData();
        $name2typeID = $name2typeID->getTypeIDsFromName(['ordoLigneOrdoALDouPas','ordoTypeImpression', 'ordoImpressionNbLignes']);

        if ($ordoData=msSQL::sql2tab("select p.*, ald.value as ald
          from objets_data as p
          left join objets_data as ald on p.id=ald.instance and ald.typeID='".$name2typeID['ordoLigneOrdoALDouPas']."' and ald.outdated='' and ald.deleted=''
          where p.instance='".$this->_objetID."' and p.outdated='' and p.deleted=''
          group by p.id, ald.value
          order by p.id asc")) {

            $modePrint='standard';
            $this->_courrierData['ordoImpressionNbLignes']='o';

            if($p['config']['optionGeActiverLapInterne'] == 'true' or $p['config']['optionGeActiverLapExterne'] == 'true' ) {
              $this->_courrierData['ordoImpressionNbLignes'] = 'n';
            } else {
              $this->_courrierData['ordoImpressionNbLignes'] = 'o';
            }

            foreach ($ordoData as $v) {
                //on chope au passage l'impression on non du nombre de lignes de pres
                if ($v['typeID']==$name2typeID['ordoImpressionNbLignes']) {
                    $this->_courrierData['ordoImpressionNbLignes']=$v['value'];
                }
                //on chope au passage le mode d'impression
                elseif ($v['typeID']==$name2typeID['ordoTypeImpression']) {
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
                    $this->_courrierData['medoc'][$key][]=$v['value'];
                }
            }

            //si on sort en mode ald alors on va annuler les header et footer standard
            if ($modePrint=='ald') {
                $this->_pageHeader=$this->_pageFooter='';
                $this->_courrierData['printModel']=$p['config']['templateOrdoALD'];
            } else {
                $this->_pageHeader= $this->makeWithTwig($p['config']['templateOrdoHeadAndFoot']);
                $this->_courrierData['printModel']=$p['config']['templateOrdoBody'];
            }

            //on génère le body avec twig
            $this->_body =  $this->makeWithTwig($this->_courrierData['printModel']);
        }
    }

/**
 * Construire le corps du PDF pour un compte rendu
 * @return void
 */
    private function _makeBodyForCr()
    {
        global $p;

        //on déclare le modèle de page
        // soit il remonte des data de msCourrier (via msModuleDataCourrier)
        if(isset($this->_courrierData['templateCrHeadAndFoot'])) {
            $this->_pageHeader = $this->makeWithTwig($this->_courrierData['templateCrHeadAndFoot']);
        }
        // soit on prend le modèle par défaut de la config
        elseif (!isset($this->_pageHeader)) {
            $this->_pageHeader = $this->makeWithTwig($p['config']['templateCrHeadAndFoot']);
        }

        //on génère le body avec twig
        $this->_body =  $this->makeWithTwig($this->_courrierData['printModel']);
    }

/**
 * Construire le coprs du PDF pour un courrier (ou certificat)
 * @return void
 */
    private function _makeBodyForCourrier()
    {
        global $p;

        $dataform = new msObjet();
        $dataform->setObjetID($this->_objetID);
        $dataform=$dataform->getObjetDataByID(['value']);
        $this->_body = msTools::unbbcodifier($dataform['value']);

        //on déclare le modèle de page
        if (!isset($this->_pageHeader)) {
            $this->_pageHeader = $this->makeWithTwig($p['config']['templateCourrierHeadAndFoot']);
        }

        $data=new msData();
        if ($printModel=$data->getDataType($this->_modeleID, ['formValues'])) {
            $this->_courrierData['printModel']=$printModel['formValues'].'.html.twig';
        } else {
            $this->_courrierData['printModel']='defaut.html.twig';
        }
    }


/**
 * Construire le corps du PDF pour un règlement
 * @return void
 */
    private function _makeBodyForReglement()
    {
        global $p;

        //on déclare le modèle de page
        // soit il remonte des data de msCourrier (via msModuleDataCourrier)
        if(isset($this->_courrierData['templateCrHeadAndFoot'])) {
            $this->_pageHeader = $this->makeWithTwig($this->_courrierData['templateCrHeadAndFoot']);
        }
        // soit on prend le modèle par défaut de la config
        elseif (!isset($this->_pageHeader)) {
            $this->_pageHeader = $this->makeWithTwig($p['config']['templateCrHeadAndFoot']);
        }

        //on génère le body avec twig
        $this->_body =  $this->makeWithTwig($this->_courrierData['printModel']);
    }


/**
 * Utiliser Twig pour obtenir du HTML à partir des templates et l'injecter dans dompdf
 * @param  string $template le template à traiter (avec son extension .html.Twig)
 * @return string           le HTML à utiliser
 */
    public function makeWithTwig($template)
    {
        global $p;

        //on agrège les data courrier qui peuvent provenir d'en dehors de la class avec celles de la class
        if(isset($p['page']['courrier']) and !empty($p['page']['courrier'])) {
          $p['page']['courrier']=array_merge($this->_courrierData, $p['page']['courrier']);
          $this->_courrierData=$p['page']['courrier'];
        } else {
          $p['page']['courrier']=$this->_courrierData;
        }

        if(isset($this->_templatesPdfFolder)) {
          $templatesPdfFolder=$this->_templatesPdfFolder;
        } else {
          $templatesPdfFolder=$p['config']['templatesPdfFolder'];
        }
        // les variables d'environnement twig
        if(isset($p['config']['twigEnvironnementCache'])) $twigEnvironment['cache']=$p['config']['twigEnvironnementCache']; else $twigEnvironment['cache']=false;
        if(isset($p['config']['twigEnvironnementAutoescape'])) $twigEnvironment['autoescape']=$p['config']['twigEnvironnementAutoescape']; else $twigEnvironment['autoescape']=false;

        $loaderPDF = new \Twig\Loader\FilesystemLoader($templatesPdfFolder);
        $twigPDF = new \Twig\Environment($loaderPDF, $twigEnvironment);
        $twigPDF->getExtension(\Twig\Extension\CoreExtension::class)->setDateFormat('d/m/Y', '%d days');
        $twigPDF->getExtension(\Twig\Extension\CoreExtension::class)->setTimezone('Europe/Paris');
        return $twigPDF->render($template, $p);
    }

/**
 * Optimisation du PDF avec ghostscript
 * @param  string $source      fichier source
 * @param  string $destination fichier destination
 * @return void
 */
    public static function optimizeWithGS($source, $destination) {
        exec('gs -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/prepress -sOutputFile='.escapeshellarg($destination).' '.escapeshellarg($source));
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

/**
 * Générer et sauver un PDF basé sur un PDF tiers utilisé comme template de fond de page
 * Le PDF généré peut être concaténé au PDF standard généré par le formulaire (dompdf) ou le remplacer
 * @return void
 */
    private function _savePdfFromTemplatePdf() {
      global $p;

      $pdfDat = $this->_formOptions['optionsPdf']['templatePdf'];

      if(is_file($pdfDat['source'])) {
        $pdfDat['source']=$pdfDat['source'];
      } elseif(is_file($p['config']['templatesPdfFolder'].$pdfDat['source'])) {
        $pdfDat['source']=$p['config']['templatesPdfFolder'].$pdfDat['source'];
      } elseif(is_file($p['homepath'].'templates/PDF/'.$pdfDat['source'])) {
        $pdfDat['source']=$p['homepath'].'templates/PDF/'.$pdfDat['source'];
      } else {
        return;
      }

      $pdf = new FPDI('P','mm', $this->_paperSize);

      $pages = $pdf->setSourceFile($pdfDat['source']);

      if(isset($pdfDat['defautFontSize'])) $defautFontSize=$pdfDat['defautFontSize']; else $defautFontSize=10;
      if(isset($pdfDat['defautTextColor'])) $defautTextColor=explode(',' , $pdfDat['defautTextColor']); else $defautTextColor=[0,0,0];
      if(isset($pdfDat['defautFont'])) $defautFont=$pdfDat['defautFont']; else $defautFont='Arial';

      for($i=1;$i<=$pages;$i++) {
        $pdf->AddPage();
        $tplIdx = $pdf->importPage($i);
        $pdf->useTemplate($tplIdx, 0, 0);

        if(isset($pdfDat['pagesTxtMapping']['page'.$i])) {
          $data2write = $this->_getDataToWriteOnTemplate($pdfDat['pagesTxtMapping']['page'.$i]);

          foreach($data2write as $param) {
            if(isset($param['param'][2]) and !empty($param['param'][2])) {
              $pdf->SetFontSize((int)$param['param'][2]);
            } else {
              $pdf->SetFontSize((int)$defautFontSize);
            }
            if(isset($param['param'][3]) and !empty($param['param'][3])) {
              $param['param'][3]=explode(',', $param['param'][3]);
              $pdf->SetTextColor($param['param'][3][0], $param['param'][3][1], $param['param'][3][2]);
            } else {
              $pdf->SetTextColor($defautTextColor[0], $defautTextColor[1], $defautTextColor[2]);
            }
            if(isset($param['param'][4]) and !empty($param['param'][4])) {
              $pdf->SetFont($param['param'][4]);
            } else {
              $pdf->SetFont($defautFont);
            }
            if(isset($param['param'][0], $param['param'][1]) and is_numeric($param['param'][0]) and is_numeric($param['param'][1])) {
              $pdf->SetXY($param['param'][0], $param['param'][1]);
              if(isset($param['param'][5])) {
                $toWrite=$param['param'][5];
              } elseif(isset($this->_courrierData[$param['dataName']])) {
                $toWrite=$this->_courrierData[$param['dataName']];
              } else {
                $toWrite=null;
              }
              if(!empty($toWrite)) $pdf->Write(0, iconv('UTF-8', 'windows-1252', $toWrite));
            }
          }
        }

        if(isset($pdfDat['pagesImgMapping']['page'.$i])) {
          $data2write = $this->_getImgToWriteOnTemplate($pdfDat['pagesImgMapping']['page'.$i]);
          foreach($data2write as $param) {
            $imgPath=$param['imgPath'];
            $param=$param['param'];
            if(!isset($param[0]) or empty($param[0])) $param[0]=0;
            if(!isset($param[1]) or empty($param[1])) $param[1]=0;
            if(!isset($param[2]) or empty($param[2])) $param[2]=0;
            if(!isset($param[3]) or empty($param[3])) $param[3]=0;
            if(!isset($param[4]) or empty($param[4])) $param[4]='';
            if(is_file($imgPath)) {
              $imgPath=$imgPath;
            } elseif(is_file($p['config']['templatesPdfFolder'].$imgPath)) {
              $imgPath=$p['config']['templatesPdfFolder'].$imgPath;
            } else {
              $imgPath=null;
            }

            if($imgPath) $pdf->Image($imgPath,$param[0],$param[1],$param[2],$param[3],$param[4]);
          }
        }
      }

      if($pdfDat['mode'] == 'replace') {
        @unlink($this->_finalPdfFile);
        file_put_contents($this->_finalPdfFile, $pdf->Output('S'));
      } elseif($pdfDat['mode'] == 'concat') {
        $pagesComp = $pdf->setSourceFile($this->_finalPdfFile);
        for($i=1;$i<=$pagesComp;$i++) {
          $pdf->AddPage();
          $tplIdx = $pdf->importPage($i);
          $pdf->useTemplate($tplIdx, 0, 0);
          @unlink($this->_finalPdfFile);
          file_put_contents($this->_finalPdfFile, $pdf->Output('S'));
        }
      }
    }

/**
 * Obtenir les data txt à écrire sur le PDF template
 * @param  array $arrayFromYaml data entrées en options du formulaire
 * @return array                data transformées
 */
    private function _getDataToWriteOnTemplate($arrayFromYaml) {
      global $p;
      $tabReturn=[];
      foreach($arrayFromYaml as $dataName=>$params) {
        if(array_key_first($arrayFromYaml[$dataName]) === 0) {
          $params2use = $arrayFromYaml[$dataName];
        } elseif(isset($this->_courrierData[$dataName], $params[$this->_courrierData['val_'.$dataName]])) {
          $params2use = $params[$this->_courrierData['val_'.$dataName]];
        } elseif(isset($this->_courrierData[$dataName], $params[$this->_courrierData[$dataName]])) {
          $params2use = $params[$this->_courrierData[$dataName]];
        } else {
          unset($params2use);
        }
        if(isset($params2use)) {
          if(is_array($params2use[array_key_first($params2use)])) {
            foreach($params2use as $pa) {
              $tabReturn[] = array(
                'dataName' => $dataName,
                'param' => $pa
              );
            }
          } else {
            $tabReturn[] = array(
              'dataName' => $dataName,
              'param' => $params2use
            );
          }
        }
      }
      return $tabReturn;
    }

/**
 * Obtenir les data image à écrire sur le PDF template
 * @param  array $arrayFromYaml data entrées en options du formulaire
 * @return array                data transformées
 */
    private function _getImgToWriteOnTemplate($arrayFromYaml) {
      global $p;
      $tabReturn=[];
      foreach($arrayFromYaml as $imgPath=>$params) {
        if(array_key_first($arrayFromYaml[$imgPath]) === 0) {
          $params2use = $arrayFromYaml[$imgPath];
        }
        if(is_array($params2use[array_key_first($params2use)])) {
          foreach($params2use as $pa) {
            $tabReturn[] = array(
              'imgPath' => $imgPath,
              'param' => $pa
            );
          }
        } else {
          $tabReturn[] = array(
            'imgPath' => $imgPath,
            'param' => $params2use
          );
        }
      }
      return $tabReturn;
    }

/**
 * Obtenir les options du formulaire d'origine d'un compte-rendu
 * @return array array des options
 */
    private function _getOriginFormOptions() {
      $formNameOrigin = new msObjet();
      $formNameOrigin->setObjetID($this->_objetID);
      $formNameOrigin = $formNameOrigin->getOriginFormNameFromObjetID();
      $form = new msForm();
      $form->setFormIDbyName($formNameOrigin);
      return $this->_formOptions = $form->getFormOptions();
    }
}
