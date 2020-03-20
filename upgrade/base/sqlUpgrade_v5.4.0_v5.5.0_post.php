<?php

// retrait des anciens fichiers JS formulaire
@unlink($p['config']['webDirectory'].'/js/module/formsScripts/baseATCD.js');
@unlink($p['config']['webDirectory'].'/js/module/formsScripts/baseSynthese.js');

@unlink($p['config']['webDirectory'].'/bower.json');

@unlink($p['homepath'].'controlers/outils/exportDataDownload.php');
