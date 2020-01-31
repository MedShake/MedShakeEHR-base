<?php

// Enregistrement des plugins si présents

if(class_exists('msApicrypt2')) {
  msSQL::sqlQuery("INSERT IGNORE INTO `system` (`name`, `groupe`, `value`) VALUES ('apicrypt2Plugin', 'plugin', 'v1.0.0');");
  msTools::checkAndBuildTargetDir($p['homepath'].'config/plugins/apicrypt2Plugin/');

  $content=array(
    "version"=>"v1.0.0",
    "nom"=>'Apicrypt 2',
    "auteurs"=>['Bertrand Boutillier'=>'https://www.medshake.net/membre/1/'],
    "licence"=>'Toute reproduction et diffusion interdite - ©EIRL Bertrand Boutillier',
    "description"=>"Plugin pour l'utilisation d'Apicrypt 2",
    "documentation"=>'https://www.logiciel-cabinet-medical.fr/',
    "sources"=>''
  );

  file_put_contents($p['homepath'].'config/plugins/apicrypt2Plugin/aboutPluginApicrypt2Plugin.yml', Spyc::YAMLDump($content, false, 0, TRUE));

}

if(is_dir(msTools::setDirectoryLastSlash($p['config']['webDirectory']).'modulesExternes/VitalOnline')) {
  msSQL::sqlQuery("INSERT IGNORE INTO `system` (`name`, `groupe`, `value`) VALUES ('vitalonlinePlugin', 'plugin', 'v1.0.0');");
  msTools::checkAndBuildTargetDir($p['homepath'].'config/plugins/vitalonlinePlugin/');

  $content=array(
    "version"=>"v1.0.0",
    "nom"=>'VitalOnline',
    "auteurs"=>['Bertrand Boutillier'=>'https://www.medshake.net/membre/1/'],
    "licence"=>'Toute reproduction et diffusion interdite - ©EIRL Bertrand Boutillier',
    "description"=>"Plugin pour l'utilisation de VitalOnline",
    "documentation"=>'https://www.logiciel-cabinet-medical.fr/',
    "sources"=>''
  );

  file_put_contents($p['homepath'].'config/plugins/vitalonlinePlugin/aboutPluginVitalonlinePlugin.yml', Spyc::YAMLDump($content, false, 0, TRUE));

}
