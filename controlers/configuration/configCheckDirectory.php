<?php
/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
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
 * Config :vérifications techniques pour le fonctionnement normal
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

 //admin uniquement
 if (!msUser::checkUserIsAdmin()) {
     $template="forbidden";
 } else {
     $template="configCheckDirectory";
     $debug='';

      //repertoire de stockage
      if (is_dir($p['config']['stockageLocation'])) {
          $p['page']['check']['stockageLocation']['is_dir']=true;
          if (is_writable($p['config']['stockageLocation'])) {
              $p['page']['check']['stockageLocation']['is_writable']=true;
          }
      } else {
          $p['page']['check']['stockageLocation']['is_dir']=false;
          $p['page']['check']['stockageLocation']['is_writable']=false;
      }

      //repertoire inbox
      if (is_dir($p['config']['apicryptCheminInbox'])) {
          $p['page']['check']['apicryptCheminInbox']['is_dir']=true;
          if (is_writable($p['config']['apicryptCheminInbox'])) {
              $p['page']['check']['apicryptCheminInbox']['is_writable']=true;
          }
      } else {
          $p['page']['check']['apicryptCheminInbox']['is_dir']=false;
          $p['page']['check']['apicryptCheminInbox']['is_writable']=false;
      }

      //repertoire inbox archive
      if (is_dir($p['config']['apicryptCheminArchivesInbox'])) {
          $p['page']['check']['apicryptCheminArchivesInbox']['is_dir']=true;
          if (is_writable($p['config']['apicryptCheminArchivesInbox'])) {
              $p['page']['check']['apicryptCheminArchivesInbox']['is_writable']=true;
          }
      } else {
          $p['page']['check']['apicryptCheminArchivesInbox']['is_dir']=false;
          $p['page']['check']['apicryptCheminArchivesInbox']['is_writable']=false;
      }

      //repertoire non crypte apicrypt
      if (is_dir($p['config']['apicryptCheminFichierNC'])) {
          $p['page']['check']['apicryptCheminFichierNC']['is_dir']=true;
          if (is_writable($p['config']['apicryptCheminFichierNC'])) {
              $p['page']['check']['apicryptCheminFichierNC']['is_writable']=true;
          }
      } else {
          $p['page']['check']['apicryptCheminFichierNC']['is_dir']=false;
          $p['page']['check']['apicryptCheminFichierNC']['is_writable']=false;
      }

      //repertoire crypte apicrypt
      if (is_dir($p['config']['apicryptCheminFichierC'])) {
          $p['page']['check']['apicryptCheminFichierC']['is_dir']=true;
          if (is_writable($p['config']['apicryptCheminFichierC'])) {
              $p['page']['check']['apicryptCheminFichierC']['is_writable']=true;
          }
      } else {
          $p['page']['check']['apicryptCheminFichierC']['is_dir']=false;
          $p['page']['check']['apicryptCheminFichierC']['is_writable']=false;
      }
      //repertoire agenda
      $p['config']['agendas']=$p['homepath'].'config/agendas/';
      if (is_dir($p['homepath'].'config/agendas/')) {
          $p['page']['check']['agendas']['is_dir']=true;
          if (is_writable($p['homepath'].'config/agendas/')) {
              $p['page']['check']['agendas']['is_writable']=true;
          }
      } else {
          $p['page']['check']['agendas']['is_dir']=false;
          $p['page']['check']['agendas']['is_writable']=false;
      }
      //repertoire templates PDF
      if (is_dir($p['config']['templatesPdfFolder'])) {
          $p['page']['check']['templatesPdfFolder']['is_dir']=true;
          if (is_writable($p['config']['templatesPdfFolder'])) {
              $p['page']['check']['templatesPdfFolder']['is_writable']=true;
          }
      } else {
          $p['page']['check']['templatesPdfFolder']['is_dir']=false;
          $p['page']['check']['templatesPdfFolder']['is_writable']=false;
      }
 
      //repertoire de backup
      if (is_dir($p['config']['backupLocation'])) {
          $p['page']['check']['backupLocation']['is_dir']=true;
          if (is_writable($p['config']['backupLocation'])) {
              $p['page']['check']['backupLocation']['is_writable']=true;
          }
      } else {
          $p['page']['check']['backupLocation']['is_dir']=false;
          $p['page']['check']['backupLocation']['is_writable']=false;
      }

}
