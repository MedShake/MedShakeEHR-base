ALTER TABLE `system` CHANGE `groupe` `groupe` ENUM('system','module','cron','lock','plugin') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'system';
