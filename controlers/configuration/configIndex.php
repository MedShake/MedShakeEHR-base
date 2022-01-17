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
 * Config : sommaire
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

//admin uniquement
if (!msUser::checkUserIsAdmin()) {
    $template="forbidden";
} else {
    $template="configIndex";
    $debug='';

    if (is_file($homepath.'public_html/install.php')) {
      $p['page']['alerteInstall']=true;
      if (@is_writable($homepath.'public_html/install.php')) {
        $p['page']['alerteInstallW']=true;
      } else {
        $p['page']['alerteInstallW']=false;
      }
    } else {
      $p['page']['alerteInstall']=false;
    }

    if (is_file($homepath.'public_html/self-installer.php')) {
      if (@is_writable($homepath.'public_html/self-installer.php')) {
        $p['page']['alerteInstallSelfW']=true;
      } else {
        $p['page']['alerteInstallSelfW']=false;
      }
      $p['page']['alerteInstallSelf']=true;
    } else {
      $p['page']['alerteInstallSelf']=false;
    }

    if(class_exists('msApicrypt2')) {
      $p['page']['apicrypt2present'] = true;
    }

	if (is_file($homepath.'public_html/bddEdit.php')) {
	  if (@is_writable($homepath.'public_html/bddEdit.php')) {
		$p['page']['alerteAdminer']=true;
	  } else {
		$p['page']['alerteAdminer']=false;
	  }
	  $p['page']['alerteAdminer']=true;
	} else {
	  $p['page']['alerteAdminer']=false;
	}
}
