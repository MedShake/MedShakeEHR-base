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
 * @contrib fr33z00 <https://github.com/fr33z00>
 */
class msGetHtml
{

	/**
	 * template à utiliser
	 * @var string
	 */
	private $_template;
	/**
	 * extension du nom de fichier de template à utiliser
	 * @var string
	 */
	private $_templateFileExt = '.html.twig';
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
		$_template = str_ireplace($this->_templateFileExt, '', $_template);
		$this->_template = $_template;
		return $this;
	}

	/**
	 * Définir l'extension des fichiers de templates
	 * @param string $templateFileExt .html.twig ou .xml.twig sauf cas particulier
	 */
	public function set_templateFileExt($templateFileExt)
	{
		$this->_templateFileExt = $templateFileExt;
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
			$twigEnvironment['cache'] = $p['config']['twigEnvironnementCache'];
		} else {
			$twigEnvironment['cache'] = false;
		}
		if (isset($p['config']['twigEnvironnementAutoescape'])) {
			$twigEnvironment['autoescape'] = $p['config']['twigEnvironnementAutoescape'];
		} else {
			$twigEnvironment['autoescape'] = false;
		}

		if (isset($p['config']['twigDebug'])) {
			$twigEnvironment['debug'] = $p['config']['twigDebug'];
		} else {
			$twigEnvironment['debug'] = false;
		}

		// Lancer Twig
		$loader = new \Twig\Loader\FilesystemLoader($this->_templatesDirectories);
		$twig = new \Twig\Environment($loader, $twigEnvironment);
		$twig->getExtension(\Twig\Extension\CoreExtension::class)->setDateFormat('d/m/Y', '%d days');
		$twig->getExtension(\Twig\Extension\CoreExtension::class)->setTimezone('Europe/Paris');

		if ($twigEnvironment['debug'])
			$twig->addExtension(new \Twig\Extension\DebugExtension());

		// filtre pour la verification de l'existence d'un fichier (utile dans la surcharge du menu principal)
		$fileExist = new \Twig\TwigFilter('file_exist', function ($string) {
			foreach ($this->_templatesDirectories as $template) {
				if (file_exists($template.$string))
					return true;
			}
			return false;
		});

		$twig->addFilter($fileExist);

		return $twig->render($this->_template . $this->_templateFileExt, $p);
	}

	/**
	 * Générer le HTML et le retourner mais avec variable injectée
	 * @return string HTML généré par le moteur de template
	 */
	public function genererHtmlVar($var)
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
			$twigEnvironment['cache'] = $p['config']['twigEnvironnementCache'];
		} else {
			$twigEnvironment['cache'] = false;
		}

		if (isset($p['config']['twigEnvironnementAutoescape'])) {
			$twigEnvironment['autoescape'] = $p['config']['twigEnvironnementAutoescape'];
		} else {
			$twigEnvironment['autoescape'] = false;
		}

		if (isset($p['config']['twigDebug'])) {
			$twigEnvironment['debug'] = $p['config']['twigDebug'];
		} else {
			$twigEnvironment['debug'] = false;
		}

		// Lancer Twig
		$loader = new \Twig\Loader\FilesystemLoader($this->_templatesDirectories);
		$twig = new \Twig\Environment($loader, $twigEnvironment);
		$twig->getExtension(\Twig\Extension\CoreExtension::class)->setDateFormat('d/m/Y', '%d days');
		$twig->getExtension(\Twig\Extension\CoreExtension::class)->setTimezone('Europe/Paris');

		if ($twigEnvironment['debug'])
			$twig->addExtension(new \Twig\Extension\DebugExtension());

		// filtre pour la verification de l'existence d'un fichier (utile dans la surcharge du menu principal)
		$fileExist = new \Twig\TwigFilter('file_exist', function ($string) {
			foreach ($this->_templatesDirectories as $template) {
				if (file_exists($template.$string))
					return true;
			}
			return false;
		});

		$twig->addFilter($fileExist);

		return $twig->render($this->_template . $this->_templateFileExt, $var);
	}


	/**
	 * Obtenir l'interprétation d'une chaine comportant des balises twig
	 * @param string $string chaine à interpréter
	 * @param array $var array pour les tags
	 * @return string         chaine interprétée
	 */
	public static function genererHtmlFromString($string, $var)
	{
		global $p;
		// les variables d'environnement twig
		if (isset($p['config']['twigEnvironnementCache'])) {
			$twigEnvironment['cache'] = $p['config']['twigEnvironnementCache'];
		} else {
			$twigEnvironment['cache'] = false;
		}

		if (isset($p['config']['twigEnvironnementAutoescape'])) {
			$twigEnvironment['autoescape'] = $p['config']['twigEnvironnementAutoescape'];
		} else {
			$twigEnvironment['autoescape'] = false;
		}

		if (isset($p['config']['twigDebug'])) {
			$twigEnvironment['debug'] = $p['config']['twigDebug'];
		} else {
			$twigEnvironment['debug'] = false;
		}

		if (empty($string)) return;

		$tplName = uniqid('string_template_', true);
		$loader = new \Twig\Loader\ArrayLoader([$tplName => $string]);
		$twig = new \Twig\Environment($loader, $twigEnvironment);
		$twig->getExtension(\Twig\Extension\CoreExtension::class)->setDateFormat('d/m/Y', '%d days');
		$twig->getExtension(\Twig\Extension\CoreExtension::class)->setTimezone('Europe/Paris');

		if ($twigEnvironment['debug'])
			$twig->addExtension(new \Twig\Extension\DebugExtension());

		return $twig->render($tplName, $var);
	}

	/**
	 * Construire les répertoires par défaut à interroger pour obtenir le template
	 * @return array Tableau des répertoires
	 */
	private function _construcDefaultTemplatesDirectories()
	{
		global $p;

		$this->_templatesDirectories = [];
		//templates utilisateur
		if (isset($p['user']['id'])) {
			$userFolder = $p['config']['templatesFolder'] . 'templatesUser' . $p['user']['id'] . '/';
			if (is_dir($userFolder)) {
				$this->_templatesDirectories[] = $userFolder;
				$this->_templatesDirectories = array_merge($this->_templatesDirectories, msTools::getAllSubDirectories($userFolder, '/'));
			}
		}
		//templates module
		$moduleFolder = $p['config']['templatesFolder'] . (isset($p['user']['module']) ? $p['user']['module'] : "public");
		if (is_dir($moduleFolder)) {
			$this->_templatesDirectories[] = $moduleFolder;
			$this->_templatesDirectories = array_merge($this->_templatesDirectories, msTools::getAllSubDirectories($moduleFolder, '/'));
		}

		//templates module non connecté (public)
		if (!isset($p['user']['module'])) {
			$modules = msModules::getInstalledModulesNames();
			foreach ($modules as $module) {
				if ($module != "base") {
					$moduleFolder = $p['config']['templatesFolder'] . $module . '/public';
					if (is_dir($moduleFolder)) {
						$this->_templatesDirectories[] = $moduleFolder;
						$this->_templatesDirectories = array_merge($this->_templatesDirectories, msTools::getAllSubDirectories($moduleFolder, '/'));
					}
				}
			}
		}

		//templates base
		$baseFolder = $p['config']['templatesFolder'] . "base";
		if (is_dir($baseFolder)) {
			$this->_templatesDirectories[] = $baseFolder;
			$this->_templatesDirectories = array_merge($this->_templatesDirectories, msTools::getAllSubDirectories($baseFolder, '/'));
		}

		//templates pdf
		if (isset($p['config']['templatesPdfFolder']) and is_dir($p['config']['templatesPdfFolder'])) {
			$this->_templatesDirectories[] = $p['config']['templatesPdfFolder'];
		}
		return $this->_templatesDirectories;
	}
}
