INSERT INTO configuration (name, level, value) VALUES
            ('mailRappelLogCampaignDirectory', 'default', '/app/MedShakeEHR-base/mailsRappelRdvArchives/'),
            ('smsLogCampaignDirectory', 'default', '/app/MedShakeEHR-base/smsArchives/'),
            ('apicryptCheminInbox', 'default', '/app/MedShakeEHR-base/inbox/'),
            ('apicryptCheminArchivesInbox', 'default', '/app/MedShakeEHR-base/inboxArchives/'),
            ('apicryptCheminFichierNC', 'default', '/app/MedShakeEHR-base/workingDirectory/NC/'),
            ('apicryptCheminFichierC', 'default', '/app/MedShakeEHR-base/workingDirectory/C/'),
            ('apicryptCheminVersClefs', 'default', '/app/MedShakeEHR-base/apicrypt/'),
            ('apicryptCheminVersBinaires', 'default', '/app/MedShakeEHR-base/apicrypt/bin/'),
            ('dicomWorkListDirectory', 'default', '/app/MedShakeEHR-base/workingDirectory/'),
            ('dicomWorkingDirectory', 'default', '/app/MedShakeEHR-base/workingDirectory/'),
            ('templatesPdfFolder', 'default', '/app/MedShakeEHR-base/templates/models4print/')
            ON DUPLICATE KEY UPDATE value=VALUES(value)
