<?php
// sup fichiers inutiles ou renommés
@unlink($p['homepath'].'controlers/rechercher/patients.php');
@unlink($p['homepath'].'controlers/rechercher/actions/patientsAjax.php');
@unlink($p['homepath'].'controlers/people/actions/inc-ajax-removeRelationPatient.php');
@unlink($p['homepath'].'controlers/people/actions/inc-ajax-addRelationPatientPatient.php');
@unlink($p['homepath'].'controlers/people/actions/inc-ajax-addRelationPatientPraticien.php');
@unlink($p['homepath'].'controlers/people/actions/inc-ajax-addRelationPraticienGroupe.php');

@unlink($p['homepath'].'templates/base/rechercher/patients.html.twig');
@unlink($p['homepath'].'templates/base/page.html.twig');
@unlink($p['homepath'].'templates/base/pageTopNavbar.html.twig');
@unlink($p['homepath'].'templates/base/pageTopNavbarPatientsOfTheDay.html.twig');
@unlink($p['homepath'].'templates/base/404.html.twig');
@unlink($p['homepath'].'templates/base/forbidden.html.twig');

// composer
$initialDir=getcwd();

$pathToComposer='php '.$p['homepath'].'composer.phar';
file_put_contents($p['homepath'].'composer.phar', fopen("https://getcomposer.org/download/1.9.1/composer.phar", 'r'));

chdir($p['config']['webDirectory']);
exec('COMPOSER_HOME="/tmp/" '.$pathToComposer.' update 2>&1', $output);
chdir($p['homepath']);
exec('COMPOSER_HOME="/tmp/" '.$pathToComposer.' update 2>&1', $output);
chdir($initialDir);
