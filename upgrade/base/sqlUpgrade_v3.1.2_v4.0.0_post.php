<?php
$initialDir=getcwd();

$varQuiRestentDansYml = ['protocol', 'host', 'urlHostSuffixe', 'webDirectory', 'stockageLocation', 'backupLocation',
  'workingDirectory', 'cookieDomain', 'cookieDuration', 'fingerprint', 'sqlServeur', 'sqlBase', 'sqlUser', 'sqlPass',
  'sqlVarPassword', 'templatesFolder', 'twigEnvironnementCache', 'twigEnvironnementAutoescape'];

$oldVarToIgnore = ['twigPdfTemplatesDir', 'templateBaseFolder', 'homeDirectory'];

if (is_file($p['homepath'].'config/config.yml')) {
  $oldp=Spyc::YAMLLoad($p['homepath'].'config/config.yml');
  foreach($oldp as $k=>$v) {

    if($k=='dicomAutoSendPatient2Echo') $k='dicomAutoSendPatient';
    if($k=='apicryptPopPass') $k='apicryptPopPassword';
    if($k=='ecofaxPass') $k='ecofaxPassword';

    if(!in_array($k, $varQuiRestentDansYml) and !in_array($k, $oldVarToIgnore)) {
      msSQL::sqlQuery("INSERT INTO configuration (name, level, value) VALUES ('".$k."', 'default', '".$v."') ON DUPLICATE KEY UPDATE value='".$v."'");
    }
  }
}

$conf=array();
foreach($varQuiRestentDansYml as $k) {
    $conf[$k]=$p['config'][$k];
};
if (is_file($p['homepath'].'config/config.yml'))
    rename($p['homepath'].'config/config.yml', $p['homepath'].'config/config.yml.bak');
file_put_contents($p['homepath'].'config/config.yml', Spyc::YAMLDump($conf, false, 0, true));

$agendaFiles=glob($p['config']['webDirectory'].'/agendasConfigurations/configAgenda*.js');
foreach($agendaFiles as $file) {
    rename($file, str_replace($p['config']['webDirectory'].'/agendasConfigurations/configAgenda', $p['homepath'].'config/agendas/agenda', $file));
}
$rdvFiles=glob($p['homepath'].'/config/configTypesRdv*.yml');
foreach($rdvFiles as $file) {
    rename($file, str_replace($p['homepath'].'/config/configTypesRdv', $p['homepath'].'config/agendas/typesRdv', $file));
}

$pathToComposer='php '.$p['homepath'].'composer.phar';
if (exec('COMPOSER_HOME="/tmp/" composer.phar -V', $ret) and strpos($ret, 'Composer version')!==false) {
    $pathToComposer='composer.phar';
} elseif (!is_file($pathToComposer)) {
    file_put_contents($p['homepath'].'composer.phar', fopen("https://getcomposer.org/download/1.6.3/composer.phar", 'r'));
}
chdir($p['config']['webDirectory']);
exec('COMPOSER_HOME="/tmp/" '.$pathToComposer.' update 2>&1', $output);
chdir($p['homepath']);
exec('COMPOSER_HOME="/tmp/" '.$pathToComposer.' update 2>&1', $output);
chdir($initialDir);
