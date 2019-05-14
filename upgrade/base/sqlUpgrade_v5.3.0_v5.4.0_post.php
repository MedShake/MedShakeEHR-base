<?php
// composer
$initialDir=getcwd();

$pathToComposer='php '.$p['homepath'].'composer.phar';
file_put_contents($p['homepath'].'composer.phar', fopen("https://getcomposer.org/download/1.8.5/composer.phar", 'r'));

chdir($p['config']['webDirectory']);
exec('COMPOSER_HOME="/tmp/" '.$pathToComposer.' update 2>&1', $output);
chdir($p['homepath']);
exec('COMPOSER_HOME="/tmp/" '.$pathToComposer.' update 2>&1', $output);
chdir($initialDir);
