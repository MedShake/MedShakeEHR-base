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
 * Web scraping sur le site de la CCAM
 * (parce que l'API est probablement prévue pour dans 20 000 ans ...)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msCcamWebScraping
{

/**
* L'instance de DOMDocument
*/
  private $_dom;
/**
* Code CCAM
* @var string
*/
  private $_acteCode;
/**
* Type de la convention qui implque le tarif de l'acte
* Au 14/06/18 :
* 3 : secteur 1 et secteur 2 optam
* 4 : secteur 2 non optam
* cf menu select sur le site source. 
* @var int
*/
  private $_acteTypePS=3;

    public function __construct()
    {
        $this->_dom = new DOMDocument();
    }

/**
* Définir le cade acte
* @param string $acte code acte CCAM
*/
  public function setCodeActe($acte)
  {
      $this->_acteCode=$acte;
  }

/**
* Obtenir les data sur l'acte
* @return array data de l'acte
*/
  public function getActeData()
  {

    // data de base (basées sur secteur par défaut)
    $this->_loadCcamHtml();

      $rd=array(
      'code'=>$this->_acteCode,
      'label'=>$this->_extractTheLabel(),
      'tarifs1'=>$this->_extractThePrix(),
      'modificateurs'=>$this->_extractTheModificateurs()
    );

    // prix secteur 2 non optam
    $this->_acteTypePS=4;
      $this->_loadCcamHtml();
      $rd['tarifs2']=$this->_extractThePrix();

      return $rd;
  }

/**
* Extraire le libellé de l'acte
* @return string libellé de l'acte
*/
  private function _extractTheLabel()
  {
      $label='';
      $xpath = new DOMXPath($this->_dom);
      if ($label = $xpath->query("//span[@id='libelle_long']")) {
          $label=str_replace('LIBELLE : ', '', $label[0]->textContent);
      }
      return $label;
  }

/**
* Extraire le prix de l'acte en fonction du type de la convention
* @return float prix de l'acte
*/
  private function _extractThePrix()
  {
      $prix='';
      $xpath = new DOMXPath($this->_dom);
      if ($prix = $xpath->query("//p[@id='prix_acte']/span[@class='libelle']")) {
          $prix=$prix[0]->textContent;
          $prix=preg_filter('#.* ([0-9,]+) .*#', '$1', $prix);
          $prix=str_replace(',', '.', $prix);
      }
      return $prix;
  }

/**
* Extraire la liste des modificateurs CCAM applicables à l'acte
* @return array tableau de modificateurs applicables
*/
  private function _extractTheModificateurs()
  {
      $rd=[];
      $xpath = new DOMXPath($this->_dom);
      if ($modifs = $xpath->query("//table[@id='modificateurs']//td[@headers='col0']")) {
          foreach ($modifs as $m) {
              $rd[]=$m->textContent;
          }
      }
      return $rd;
  }

/**
* Extraire la page CCAM concerné d'ameli CCAM pour analyse.
* @return string  HTML de la page.
*/
  private function _loadCcamHtml()
  {
      global $p;

      $pstype='&liste_convention_ps='.$this->_acteTypePS;

      $url="https://www.ameli.fr/accueil-de-la-ccam/trouver-un-acte/fiche-abregee.php?code=".$this->_acteCode.$pstype;

      $agent= 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:58.0) Gecko/20100101 Firefox/58.0';

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, $agent);
      curl_setopt($ch, CURLOPT_URL, $url);
      $cookie_file = $p['config']['dicomWorkingDirectory']."cookie.txt";
      curl_setopt($ch, CURLOPT_COOKIESESSION, true);
      curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
      curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
      $result=curl_exec($ch);
      @unlink($cookie_file);
      return  @$this->_dom->loadHTML($result);
  }
}
