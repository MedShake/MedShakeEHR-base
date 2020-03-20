<?php

// retrait des anciens fichiers JS formulaire
@unlink($p['config']['webDirectory'].'/js/module/formsScripts/aldDeclaration.js');
@unlink($p['config']['webDirectory'].'/js/module/formsScripts/atcdStrucDeclaration.js');

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
