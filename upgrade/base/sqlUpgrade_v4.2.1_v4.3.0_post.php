<?php

msSQL::sqlQuery("INSERT INTO configuration (name, level, value) VALUES
          ('templatesCdaFolder', 'default', '".$homepath."templates/CDA/')
          ON DUPLICATE KEY UPDATE value=VALUES(value)");
