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
 * Patient > ajax : obtenir les détails d'une ligne de l'historique
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://github.com/fr33z00>
 */

if (is_numeric($_POST['objetID'])) {
    $data = new msObjet();
    $data->setToID($_POST['objetID']);
    $data = $data->getCompleteObjetDataByID($_POST['objetID']);

    if ($data['groupe']=="doc") {
        $template='inc-ajax-detDoc';

        $pdf = new msStockage();
        $pdf->setObjetID($_POST['objetID']);

        if ($pdf->testDocExist()) {
            $p['page']['pj']['href']=$pdf->getWebPathToDoc();
            $p['page']['pj']['html']=strtoupper($pdf->getFileExtOfDoc());
            $p['page']['pj']['filesize']= $pdf->getFileSize(0);
        }

        if (!empty($data['value'])) {
            //hprim
            $p['page']['bioHprim'] = msHprim::parseSourceHprim($data['value']);
            //texte
            $p['page']['texte']= $data['value'];
        }
    } elseif ($data['groupe']=="reglement") {
        $template='inc-ajax-detReglement';
        $data = new msObjet();

        $p['page']['datareg'] = $data->getObjetAndSons($_POST['objetID'], 'name');
        $p['page']['acteFacture']=msSQL::sqlUnique("SELECT * FROM actes WHERE id=(SELECT parentTypeID FROM objets_data WHERE id='".$_POST['objetID']."')");
    } elseif ($data['groupe']=="mail") {
        $template='inc-ajax-detMail';
        $data = new msObjet();
        $p['page']['dataMail'] = $data->getObjetAndSons($_POST['objetID'], 'name');

    } elseif ($data['groupe']=="ordo") {

        $template='inc-ajax-detOrdo';

        $name2typeID = new msData();
        $name2typeID = $name2typeID->getTypeIDsFromName(['ordoLigneOrdoALDouPas','ordoTypeImpression','ordoLigneOrdo']);

        if ($ordoData=msSQL::sql2tab("select ald.value as ald, p.value as description, p.typeID, p.id
        from objets_data as p
        left join objets_data as ald on p.id=ald.instance and ald.typeID='".$name2typeID['ordoLigneOrdoALDouPas']."' and ald.outdated='' and ald.deleted=''
        where p.instance='".$_POST['objetID']."' and p.outdated='' and p.deleted='' and p.typeID in ('".$name2typeID['ordoTypeImpression']."','".$name2typeID['ordoLigneOrdo']."')
        group by p.id, ald.id
        order by p.id asc")) {
            $modePrint='standard';

            foreach ($ordoData as $v) {
                if ($v['typeID']==$name2typeID['ordoTypeImpression']) {
                    $modePrint=$v['description'];
                } else {
                    if ($v['ald']==1) {
                        $modePrint='ALD';
                    }
                    $p['page']['courrier']['medoc'][]=$v;
                }
            }

            $p['page']['courrier']['modeprint']=$modePrint;
        }

    } else {
        $fakePDF = new msPDF();
        $fakePDF->setPageHeader('');
        $fakePDF->setPageFooter('');
        $fakePDF->setObjetID($_POST['objetID']);
        $fakePDF->makePDFfromObjetID();
        $version = $fakePDF->getContenuFinal();

        echo '<td></td><td colspan="4"><div class="well appercu">';
        echo msTools::cutHtmlHeaderAndFooter($version);
        echo '</div></td>';
    }
}
