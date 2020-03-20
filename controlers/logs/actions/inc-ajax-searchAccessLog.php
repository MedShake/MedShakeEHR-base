<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
 * Bertrand Boutillier <b.boutillier@gmail.com>
 * http://www.medshake.net
 *
 * MedShakeEHR is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * MedShakeEHR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MedShakeEHR.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Logs : recherche dans les access logs apache
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */



 $log = new msLog;
 $log->setFile(getenv("MEDSHAKEEHRLOGFILE"));
 if(!empty($_POST['userID'])) $log->setUserID($_POST['userID']);
 if(!empty($_POST['userIP'])) $log->setUserIP($_POST['userIP']);
 if(!empty($_POST['dateStart'])) $log->setDateStart($_POST['dateStart']);
 if(!empty($_POST['dateStartOperator'])) $log->setDateStartOperator($_POST['dateStartOperator']);
 if(!empty($_POST['heureStart'])) $log->setHeureStart($_POST['heureStart']);
 if(!empty($_POST['dateEnd'])) $log->setDateEnd($_POST['dateEnd']);
 if(!empty($_POST['dateEndOperator'])) $log->setDateEndOperator($_POST['dateEndOperator']);
 if(!empty($_POST['heureEnd'])) $log->setHeureEnd($_POST['heureEnd']);
 if(!empty($_POST['urlPattern'])) $log->setUrlPattern($_POST['urlPattern']);
 if(!empty($_POST['nbLignes'])) $log->setNbLignes($_POST['nbLignes']);

 exit(json_encode($log->getDataWithAwk()));
