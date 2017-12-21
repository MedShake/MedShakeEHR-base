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
 * Pilotage du moteur de template
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

class msGetHtml
{

/**
 * template à utiliser
 * @var string
 */
  private $_template;
/**
 * répertoire(s) ou le template doit être trouvé
 * @var array
 */
  private $_templatesDirectories;

/**
 * Définir le template
 * @param string $_template
 *
 * @return static
 */
  public function set_template($_template)
  {
      $_template=str_ireplace('.html.twig', '', $_template);
      $this->_template = $_template;
      return $this;
  }

/**
 * Définir les répertoires des templates
 * @param array $_templatesDirectories
 *
 * @return static
 */
  public function set_templatesDirectories(array $_templatesDirectories)
  {
      if (!array($_templatesDirectories)) {
          throw new Exception('TemplatesDirectories is not an array');
      }
      $this->_templatesDirectories = $_templatesDirectories;
      return $this;
  }

/**
 * Ajouter un répertoire à la liste des répetoires de templates
 * @param string $_addDirectory
 *
 * @return static
 */
  public function addDirectoryToTemplatesDirectories($_addDirectory)
  {
      if (!is_dir($_addDirectory)) {
          throw new Exception('Directory dont exist');
      }
      $this->_templatesDirectories[] = $_addDirectory;
      return $this;
  }

/**
 * Générer le HTML et le retourner
 * @return string HTML générer par le moteur de template
 */
  public function genererHtml()
  {
      global $p;

      if (!isset($this->_template)) {
          throw new Exception('Template is not defined');
      }

      if (!isset($this->_templatesDirectories)) {
          $this->_construcDefaultTemplatesDirectories();
      }

      // les variables d'environnement twig
      if (isset($p['config']['twigEnvironnementCache'])) {
          $twigEnvironment['cache']=$p['config']['twigEnvironnementCache'];
      } else {
          $twigEnvironment['cache']=false;
      }
      if (isset($p['config']['twigEnvironnementAutoescape'])) {
          $twigEnvironment['autoescape']=$p['config']['twigEnvironnementAutoescape'];
      } else {
          $twigEnvironment['autoescape']=false;
      }

      // Lancer Twig
      $loader = new Twig_Loader_Filesystem($this->_templatesDirectories);
      $twig = new Twig_Environment($loader, $twigEnvironment);
      $twig->getExtension('Twig_Extension_Core')->setDateFormat('d/m/Y', '%d days');
      $twig->getExtension('Twig_Extension_Core')->setTimezone('Europe/Paris');

      return $twig->render($this->_template.'.html.twig', $p);
  }

/**
 * Construire les répertoires par défaut à interroger pour obtenir le template
 * @return array Tableau des répertoires
 */
 private function _construcDefaultTemplatesDirectories()
 {
     global $p;

     //repertoire perso de templates
     $templatesPerso=$p['config']['homeDirectory'].'templates/templatesUser'.$p['user']['id'].'/';
     if (is_dir($templatesPerso)) {
         $twigTemplatePersoDirs=msTools::getAllSubDirectories($templatesPerso, '/');
         array_unshift($twigTemplatePersoDirs, $templatesPerso);
     } else {
         $twigTemplatePersoDirs=[];
     }
     // module
     if (is_dir($p['config']['templatesModuleFolder'])) {
         $twigTemplateModuleDirs=msTools::getAllSubDirectories($p['config']['templatesModuleFolder'], '/');
         array_unshift($twigTemplateModuleDirs, $p['config']['templatesModuleFolder']);
     } else {
         $twigTemplateModuleDirs=[];
     }
     //base
     if (is_dir($p['config']['templatesBaseFolder'])) {
         $twigTemplateBaseDirs=msTools::getAllSubDirectories($p['config']['templatesBaseFolder'], '/');
         array_unshift($twigTemplateBaseDirs, $p['config']['templatesBaseFolder']);
     } else {
         $twigTemplateBaseDirs=[];
     }

     //merge
     $this->_templatesDirectories=array_merge($twigTemplatePersoDirs, $twigTemplateModuleDirs, $twigTemplateBaseDirs);

     //templates pdf
     if (is_dir($p['config']['templatesPdfFolder'])) {
         $this->_templatesDirectories[]=$p['config']['templatesPdfFolder'];
     }

     return $this->_templatesDirectories;
 }
}
