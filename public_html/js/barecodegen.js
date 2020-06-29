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
 * Js pour la génration des codes bares automatiques sur la pages des données pro
 *
 * @author Maxime DEMAREST <maxime@indelog.fr>
 */

$(document).ready(function() {

    /**
     * Obtenir la valeur du champ rpps
     * @return string valeur du champ rpps
     */
    function getRppsField() {
        return $('input[name=p_rpps]').val();
    }

    /**
     * Obtenir la valeur du champ adeli
     * @return string valeur du champ adeli
     */
    function getAdeliField() {
        return $('input[name=p_adeli]').val();
    }

    function retrunBareCodeBox(genCode = false) {
        patientID = $('#identitePatient').attr("data-patientID");
        $.ajax({
            url: urlBase + '/ajax/getBareCodeGenerator/',
            type: 'post',
            data: {
                rpps: getRppsField(),
                adeli: getAdeliField(),
                genCode: genCode
            },
            dataType: "json",
        }).done(function(data) {
            $('#barecodeGenContainer').remove();
            $('#myTabContent').after(data.html);
            if (data.is_generated) {
                alert_popup("success", 'Code bare généré.');
            }
            $('#getCodeBarreButton').click(function() {
                if ($.trim(getAdeliField()).length > 0 || $.trim(getRppsField()) > 0) {
                    retrunBareCodeBox(true);
                }
            });
        }).fail(function(data) {
            alert_popup("danger", 'Echec de l\'obtention des codes bares !');
        });
    }

    $('#id_rpps_id').change(function() {
        retrunBareCodeBox();
    });

    $('#id_adeli_id').change(function() {
        retrunBareCodeBox();
    });

    if ($.trim(getAdeliField()).length > 0 || $.trim(getRppsField()) > 0) {
        retrunBareCodeBox();
    }
});
