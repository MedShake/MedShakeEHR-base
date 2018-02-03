-- 2.0.0. to 2.1.0

ALTER TABLE actes_base MODIFY `tarifs1` float DEFAULT NULL;
ALTER TABLE actes_base MODIFY `tarifs2` float DEFAULT NULL;
ALTER TABLE actes MODIFY `details` text DEFAULT NULL;
ALTER TABLE data_types CHANGE `formType` `formType` ENUM('','date','email','lcc','number','select','submit','tel','text','textarea','checkbox') NOT NULL DEFAULT '';
UPDATE data_types set formType='checkbox' where id in ('436','492','493','496');

