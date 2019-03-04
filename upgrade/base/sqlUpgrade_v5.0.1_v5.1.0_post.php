<?php
// composer
$initialDir=getcwd();

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

// vérifier s'il ne manque pas un choix dans les relations patient <-> patient
$data = new msData();
$typeID = $data->getTypeIDFromName('relationPatientPatient');
$options = $data->getSelectOptionValue(array($typeID))[$typeID];
if (!in_array('tante / oncle', $options)) {
  $options['nièce / neveu']='tante / oncle';
  $options=Spyc::YAMLDump($options, false, 0, true);
  msSQL::sqlQuery("update data_types set formValues='".msSQL::cleanVar($options)."' where name='relationPatientPatient' limit 1");
}
