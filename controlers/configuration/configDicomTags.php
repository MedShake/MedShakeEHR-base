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
 * Config : liste des tags DICOM rencontrés et associés à une data
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 *
 * SQLPREPOK
 */

//admin uniquement
if (!msUser::checkUserIsAdmin()) {
	$template = "forbidden";
} else {
	$template = "configDicomTags";
	$debug = '';

	if ($tags = msSQL::sql2tab("SELECT dt.*, d.label, dc.label as labelCat
     from dicomTags dt
      left join data_types as d on d.name=dt.typeName
      left join data_cat as dc on dc.id=d.cat
      where dt.dicomTag !='' order by dt.dicomCodeMeaning")) {

		foreach ($tags as $v) {
			$p['page']['tags'][$v['dicomTag']][] = $v;
		}
	}
}
