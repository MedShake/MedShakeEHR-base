<?php

msSQL::sqlQuery("INSERT INTO configuration (name, level, value) VALUES
  ('mailRappelLogCampaignDirectory', 'default', '".$p['config']['mailRappelLogCampaignDirectory']."'),
  ('smsLogCampaignDirectory', 'default', '".$p['config']['smsLogCampaignDirectory']."'),
  ('apicryptCheminInbox', 'default', '".$p['config']['apicryptCheminInbox']."'),
  ('apicryptCheminArchivesInbox', 'default', '".$p['config']['apicryptCheminArchivesInbox']."'),
  ('apicryptCheminFichierNC', 'default', '".$p['config']['apicryptCheminFichierNC']."'),
  ('apicryptCheminFichierC', 'default', '".$p['config']['apicryptCheminFichierC']."'),
  ('apicryptCheminVersClefs', 'default', '".$p['config']['apicryptCheminVersClefs']."'),
  ('apicryptCheminVersBinaires', 'default', '".$p['config']['apicryptCheminVersBinaires']."'),
  ('dicomWorkListDirectory', 'default', '".$p['config']['dicomWorkListDirectory']."'),
  ('dicomWorkingDirectory', 'default', '".$p['config']['dicomWorkingDirectory']."'),
  ('templatesPdfFolder', 'default', '".$p['config']['templatesPdfFolder']."')
  ON DUPLICATE KEY UPDATE value=VALUES(value)");

$conf=array();
foreach(['protocol', 'host', 'urlHostSuffixe', 'webDirectory', 'stockageLocation', 'backupLocation', 
  'workingDirectory', 'cookieDomain', 'cookieDuration', 'fingerprint', 'sqlServeur', 'sqlBase', 'sqlUser', 'sqlPass', 
  'sqlVarPassword', 'templatesFolder', 'twigEnvironnementCache', 'twigEnvironnementAutoescape',
'mailRappelLogCampaignDirectory', 'smsLogCampaignDirectory', 'apicryptCheminInbox', 'apicryptCheminArchivesInbox', 
'apicryptCheminFichierNC', 'apicryptCheminFichierC', 'apicryptCheminVersClefs', 'apicryptCheminVersBinaires',
'dicomWorkListDirectory', 'dicomWorkingDirectory', 'templatesPdfFolder'] as $k) {
    $conf[$k]=$p['config'][$k];
};
if (is_file($p['homepath'].'config/config.yml'))
    rename($p['homepath'].'config/config.yml', $p['homepath'].'config/config.yml.bak');
file_put_contents($p['homepath'].'config/config.yml', Spyc::YAMLDump($conf, false, 0, true));

